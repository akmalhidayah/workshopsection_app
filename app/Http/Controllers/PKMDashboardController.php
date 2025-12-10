<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Notification;
use App\Models\DokumenOrder;
use App\Models\JenisKawatLas;
use App\Models\KawatLas;
use App\Models\KawatLasDetail;
use App\Models\Hpp1;
use App\Models\PurchaseOrder;
use App\Models\LHPP;
use App\Models\SPK;
use App\Models\Lpj;
use Illuminate\Support\Facades\Storage;

class PKMDashboardController extends Controller
{
    public function index()
    {
        // Ambil semua notifikasi dengan relasi PurchaseOrder
        $notifications = Notification::with('purchaseOrder')->get();
    
        // Hitung total pekerjaan
        $totalPekerjaan = $notifications->count();
    
        // Hitung pekerjaan yang menunggu (progress < 100)
        $pekerjaanMenunggu = $notifications->filter(function ($notification) {
            return $notification->purchaseOrder && $notification->purchaseOrder->progress_pekerjaan < 100;
        })->count();
    
        // Hitung pekerjaan yang selesai (progress 100%)
        $pekerjaanSelesai = $notifications->filter(function ($notification) {
            return $notification->purchaseOrder && $notification->purchaseOrder->progress_pekerjaan === 100;
        })->count();
    
        // Hitung total progress (rata-rata dari semua pekerjaan)
        $totalProgress = $notifications->filter(function ($notification) {
            return $notification->purchaseOrder;
        })->pluck('purchaseOrder.progress_pekerjaan')->avg() ?? 0;
    
        // Data untuk daftar pekerjaan
        $targetDates = $notifications->filter(function ($notification) {
            return $notification->purchaseOrder && $notification->purchaseOrder->target_penyelesaian;
        })->map(function ($notification) {
            return [
                'date' => $notification->purchaseOrder->target_penyelesaian,
                'description' => "Pekerjaan: {$notification->notification_number}"
            ];
        })->values();
    
        // Kirim data ke view
        return view('pkm.dashboard', [
            'totalPekerjaan' => $totalPekerjaan,
            'pekerjaanMenunggu' => $pekerjaanMenunggu,
            'pekerjaanSelesai' => $pekerjaanSelesai,
            'totalProgress' => round($totalProgress, 2),
            'targetDates' => $targetDates,
        ]);
    }

    public function jobWaiting(Request $request)
{
    try {
        // Ambil filter dari request
        $priority = $request->input('priority');
        $search   = $request->input('search');

        // Eager load relasi penting untuk menghindari N+1
        $baseQuery = Notification::with([
            'purchaseOrder',
            'dokumenOrders',
            'scopeOfWork',
            'lhpp',
            'lpj',
            'hpp1',
            'spk'
        ]);

        // Jika ada filter priority eksplisit dari UI: gunakan itu saja (case-insensitive)
        if (!empty($priority)) {
            $baseQuery->whereRaw("LOWER(IFNULL(priority,'')) = ?", [strtolower($priority)]);
        }

        // Selama tidak ada filter priority, kita ingin menampilkan:
        // - notifikasi yang punya PO dengan approval_target & approve_manager = true
        // OR
        // - notifikasi yang priority = 'urgent' (tetap muncul walau tanpa PO)
        $baseQuery->where(function ($q) use ($priority) {
            // always include those with purchaseOrder approved by manager
            $q->whereHas('purchaseOrder', function ($qq) {
                $qq->whereNotNull('approval_target')->where('approve_manager', true);
            });

            // jika user tidak mem-filter priority, tambahkan OR untuk urgent agar selalu muncul
            if (empty($priority)) {
                $q->orWhereRaw("LOWER(IFNULL(priority,'')) IN ('urgently','urgent')");
            }
        });

        // optional search by notification_number
        if (!empty($search)) {
            $baseQuery->where('notification_number', 'like', "%{$search}%");
        }

        // Ambil semua lalu transform; ordering: urgent paling atas
        $notifications = $baseQuery
            ->orderByRaw("
                CASE
                    WHEN LOWER(IFNULL(priority,'')) IN ('urgently','urgent') THEN 0
                    WHEN LOWER(IFNULL(priority,'')) = 'hard' THEN 1
                    WHEN LOWER(IFNULL(priority,'')) = 'medium' THEN 2
                    WHEN LOWER(IFNULL(priority,'')) = 'low' THEN 3
                    ELSE 4
                END ASC
            ")
            ->orderBy('created_at', 'desc')
            ->get();

        // Cek kolom LPJ untuk fitur ppl secara defensif
        $lpjModel = new Lpj();
        $lpjTable = $lpjModel->getTable();
        $hasPplColumn = \Schema::hasColumn($lpjTable, 'ppl_document_path');
        $hasPplTerm1  = \Schema::hasColumn($lpjTable, 'ppl_document_path_termin1');

        // route maps untuk admin dan non-admin (approval/public)
        $adminMap = [
            'createhpp1' => 'admin.inputhpp.download_hpp1',
            'createhpp2' => 'admin.inputhpp.download_hpp2',
            'createhpp3' => 'admin.inputhpp.download_hpp3',
            'createhpp4' => 'admin.inputhpp.download_hpp4',
        ];
        $publicMap = [
            'createhpp1' => 'approval.hpp.download_hpp1',
            'createhpp2' => 'approval.hpp.download_hpp2',
            'createhpp3' => 'approval.hpp.download_hpp3',
            'createhpp4' => 'approval.hpp.download_hpp4',
        ];

        $user = auth()->user();

        // Transform setiap notification: set flags / normalized paths / route names untuk Blade
        $notifications->each(function ($notification) use ($hasPplColumn, $hasPplTerm1, $adminMap, $publicMap, $user) {
            try {
                $notification->isLhppAvailable = (bool) ($notification->lhpp ?? false);
                $notification->isLpjAvailable  = (bool) ($notification->lpj ?? false);

                $isLpp = false;
                if ($notification->lpj) {
                    if ($hasPplColumn && !empty($notification->lpj->ppl_document_path)) $isLpp = true;
                    if ($hasPplTerm1 && !empty($notification->lpj->ppl_document_path_termin1)) $isLpp = true;
                }
                $notification->isLppAvailable = (bool) $isLpp;

                $docs = collect($notification->dokumenOrders ?? []);
                $types = $docs->pluck('jenis_dokumen')->map(fn($v) => strtolower(trim((string)$v)))->values();

                $notification->isAbnormalAvailable = $types->contains('abnormalitas') || $types->contains('abnormal') || $types->contains('abnormality');
                $notification->isGambarTeknikAvailable = $types->contains('gambar_teknik') || $types->contains('gambar') || $types->contains('gambar-teknik') || $types->contains('technical_image');

                $notification->isScopeOfWorkAvailable = (!empty($notification->scopeOfWork))
                    || $types->contains('scope_of_work')
                    || $types->contains('scope')
                    || $types->contains('scopeofwork')
                    || $types->contains('scope-of-work');

                $hpp = $notification->hpp1 ?? null;
                $notification->isHppAvailable = (bool) $hpp;
                $notification->hpp_file_path = null;
                $notification->hpp_file_exists = false;
                $notification->source_form = $hpp->source_form ?? $notification->source_form ?? null;
                $notification->total_amount = $hpp->total_amount ?? $notification->total_amount ?? 0;

                if ($hpp) {
                    $rawPath = $hpp->file_path ?? $hpp->pdf_path ?? null;
                    if ($rawPath) {
                        $path = ltrim($rawPath, '/');
                        try {
                            $notification->hpp_file_exists = Storage::disk('public')->exists($path);
                            $notification->hpp_file_path = $path;
                        } catch (\Exception $e) {
                            \Log::warning("Cek HPP file failed for {$notification->notification_number}: " . $e->getMessage());
                            $notification->hpp_file_exists = false;
                            $notification->hpp_file_path = null;
                        }
                    }
                }

                $notification->isSpkAvailable = (bool) ($notification->spk ?? false);

                $source = $notification->source_form ?? '';
                $isAdmin = false;
                if ($user) {
                    $isAdmin = (method_exists($user, 'hasRole') && $user->hasRole('admin'))
                               || (!empty($user->is_admin))
                               || (isset($user->usertype) && $user->usertype === 'admin');
                }

                $downloadRouteName = $isAdmin ? ($adminMap[$source] ?? null) : ($publicMap[$source] ?? null);
                $notification->download_route_name = $downloadRouteName;
                $notification->has_hpp_fallback = !empty($notification->hpp_file_exists) && !empty($notification->hpp_file_path);

            } catch (\Exception $e) {
                \Log::warning("jobWaiting transform failed for {$notification->notification_number}: " . $e->getMessage());
                $notification->isLhppAvailable = $notification->isLpjAvailable = $notification->isLppAvailable = false;
                $notification->isAbnormalAvailable = $notification->isGambarTeknikAvailable = $notification->isScopeOfWorkAvailable = false;
                $notification->isHppAvailable = $notification->isSpkAvailable = false;
                $notification->hpp_file_exists = false;
                $notification->hpp_file_path = null;
                $notification->source_form = $notification->source_form ?? null;
                $notification->download_route_name = null;
                $notification->has_hpp_fallback = false;
            }
        });

        // Buang notifikasi yang sudah lengkap (LHPP + LPJ + PPL)
        $filtered = $notifications->reject(function ($n) {
            return ($n->isLhppAvailable && $n->isLpjAvailable && $n->isLppAvailable);
        })->values();

        // Pagination manual
        $page = (int) $request->input('page', 1);
        $perPage = 10;
        $total = $filtered->count();
        $sliced = $filtered->forPage($page, $perPage)->values();

        $paginated = new LengthAwarePaginator($sliced, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        if (!$request->ajax()) {
            return view('pkm.jobwaiting', ['notifications' => $paginated]);
        }

        return response()->json(['status' => 'success', 'data' => $paginated], 200);

    } catch (\Illuminate\Database\QueryException $e) {
        \Log::error("jobWaiting database error: " . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan database saat mengambil data.'], 500);
    } catch (\Exception $e) {
        \Log::error("jobWaiting unexpected error: " . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan.'], 500);
    }
}



 public function updateProgress(Request $request, $notification_number)
{
    try {
        // ✅ Validasi input
        $validated = $request->validate([
            'progress_pekerjaan'   => 'nullable|integer|min:10|max:100',
            'catatan'              => 'nullable|string|max:1000',
            'target_penyelesaian'  => 'nullable|date',
        ]);

        // ✅ Cari atau buat PurchaseOrder
        $purchaseOrder = PurchaseOrder::firstOrCreate(
            ['notification_number' => $notification_number],
            ['purchase_order_number' => null, 'progress_pekerjaan' => 0]
        );

        // ✅ Update catatan jika ada
        if (!empty($validated['catatan'])) {
            $purchaseOrder->catatan = $validated['catatan'];
        }

        // ✅ Update target penyelesaian jika ada
        if (!empty($validated['target_penyelesaian'])) {
            $purchaseOrder->target_penyelesaian = $validated['target_penyelesaian'];
        }

        // ✅ Update progress hanya jika lebih tinggi
        if (!empty($validated['progress_pekerjaan'])) {
            $newProgress = $validated['progress_pekerjaan'];

            if ($newProgress > $purchaseOrder->progress_pekerjaan) {
                $purchaseOrder->progress_pekerjaan = $newProgress;
            }
        }

        // ✅ Simpan perubahan
        $purchaseOrder->save();

        // Jika request via AJAX
        if ($request->ajax()) {
            return response()->json([
                'message'            => 'Progress pekerjaan berhasil diperbarui.',
                'new_progress'       => $purchaseOrder->progress_pekerjaan,
                'catatan'            => $purchaseOrder->catatan,
                'target_penyelesaian'=> $purchaseOrder->target_penyelesaian,
            ], 200);
        }

        // Jika request via form biasa
        return back()->with('success', 'Progress pekerjaan berhasil diperbarui.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        // 🔴 Error validasi
        if ($request->ajax()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors'  => $e->errors(),
            ], 422);
        }
        return back()->withErrors($e->errors())->withInput();

    } catch (\Exception $e) {
        // 🔴 Error tak terduga
        \Log::error("Error updateProgress: " . $e->getMessage());

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui progress.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return back()->with('error', 'Terjadi kesalahan saat memperbarui progress.');
    }
}
public function laporan(Request $request)
{
    try {
        // ambil filter dari request
        $notificationNumber = $request->input('notification_number');
        $status = $request->input('status'); // '' | 'complete' | 'incomplete'
        $perPage = 10;
        $page = (int) $request->input('page', 1);

        // base query: eager load relations yang dibutuhkan
        $query = Notification::with(['lhpp', 'lpj', 'hpp1', 'purchaseOrder', 'dokumenOrders']);

        // filter by notification number (partial)
        $query->when($notificationNumber, fn($q) => $q->where('notification_number', 'like', "%{$notificationNumber}%"));

        // ambil semua kandidat (kita akan filter di collection agar complete/incomplete lebih fleksibel)
        $notifications = $query->orderBy('created_at', 'desc')->get();

        // ========== copy jobWaiting helper checks ==========
        $lpjModel = new Lpj();
        $lpjTable = $lpjModel->getTable();
        $hasPplColumn = \Schema::hasColumn($lpjTable, 'ppl_document_path');
        $hasPplTerm1  = \Schema::hasColumn($lpjTable, 'ppl_document_path_termin1');

        $adminMap = [
            'createhpp1' => 'admin.inputhpp.download_hpp1',
            'createhpp2' => 'admin.inputhpp.download_hpp2',
            'createhpp3' => 'admin.inputhpp.download_hpp3',
            'createhpp4' => 'admin.inputhpp.download_hpp4',
        ];
        $publicMap = [
            'createhpp1' => 'approval.hpp.download_hpp1',
            'createhpp2' => 'approval.hpp.download_hpp2',
            'createhpp3' => 'approval.hpp.download_hpp3',
            'createhpp4' => 'approval.hpp.download_hpp4',
        ];
        $user = auth()->user();
        // ====================================================

        // transform supaya blade tidak melakukan query ulang dan untuk menentukan status
        $notifications = $notifications->map(function ($notification) use ($hasPplColumn, $hasPplTerm1, $adminMap, $publicMap, $user) {

            // HPP
            $hpp = $notification->hpp1;
            $notification->isHppAvailable = (bool) $hpp;
            $notification->source_form    = $hpp->source_form ?? $notification->source_form ?? '-';
            $notification->total_amount   = $hpp->total_amount ?? 0;

            // LHPP / total biaya prioritas: LHPP.total_biaya > HPP.total_amount
            $lhpp = $notification->lhpp;
            $notification->has_lhpp = (bool) $lhpp;
            $notification->total_biaya = (float) ($lhpp->total_biaya ?? $notification->total_amount ?? 0);

            // Purchase Order
            $po = $notification->purchaseOrder;
            $notification->has_po_document = !empty($po->po_document_path);

            // LPJ per-termin (pakai model Lpj helpers if available)
            $lpj = $notification->lpj;
            if ($lpj) {
                $notification->termin1_paid = method_exists($lpj, 'isTermin1Paid') ? $lpj->isTermin1Paid() : (!empty($lpj->termin1) && $lpj->termin1 === 'sudah');
                $notification->termin2_paid = method_exists($lpj, 'isTermin2Paid') ? $lpj->isTermin2Paid() : (!empty($lpj->termin2) && $lpj->termin2 === 'sudah');

                $notification->lpj_path_termin1 = method_exists($lpj, 'getLpjPathForTermin') ? $lpj->getLpjPathForTermin(1) : ($lpj->lpj_document_path_termin1 ?? $lpj->lpj_document_path ?? null);
                $notification->ppl_path_termin1 = method_exists($lpj, 'getPplPathForTermin') ? $lpj->getPplPathForTermin(1) : ($lpj->ppl_document_path_termin1 ?? $lpj->ppl_document_path ?? null);

                $notification->lpj_path_termin2 = method_exists($lpj, 'getLpjPathForTermin') ? $lpj->getLpjPathForTermin(2) : ($lpj->lpj_document_path_termin2 ?? null);
                $notification->ppl_path_termin2 = method_exists($lpj, 'getPplPathForTermin') ? $lpj->getPplPathForTermin(2) : ($lpj->ppl_document_path_termin2 ?? null);

                $notification->has_lpj_t1_file = !empty($notification->lpj_path_termin1);
                $notification->has_ppl_t1_file = !empty($notification->ppl_path_termin1);
                $notification->has_lpj_t2_file = !empty($notification->lpj_path_termin2);
                $notification->has_ppl_t2_file = !empty($notification->ppl_path_termin2);
            } else {
                $notification->termin1_paid = false;
                $notification->termin2_paid = false;
                $notification->lpj_path_termin1 = null;
                $notification->ppl_path_termin1 = null;
                $notification->lpj_path_termin2 = null;
                $notification->ppl_path_termin2 = null;
                $notification->has_lpj_t1_file = $notification->has_ppl_t1_file = $notification->has_lpj_t2_file = $notification->has_ppl_t2_file = false;
            }

            // HITUNG paidPercent dan paidAmount
            if ($notification->termin1_paid && $notification->termin2_paid) {
                $notification->paid_percent = 100;
            } elseif ($notification->termin1_paid) {
                $notification->paid_percent = 95;
            } else {
                $notification->paid_percent = 0;
            }
            $notification->paid_amount = (int) round($notification->total_biaya * ($notification->paid_percent / 100));
// GARANSI fallback (DITERAPKAN UBAH: prioritaskan LHPP.tanggal_selesai, ambil garansi_months dari tabel Garansi jika ada)
$garansiStart = null;

// 1) Prefer LHPP.tanggal_selesai (start date biasanya berdasarkan tanggal selesai pekerjaan pada LHPP)
if ($lhpp && !empty($lhpp->tanggal_selesai)) {
    try { $garansiStart = \Carbon\Carbon::parse($lhpp->tanggal_selesai)->startOfDay(); } catch (\Throwable $e) { $garansiStart = null; }
}

// 2) Jika belum ada, cek LPJ.tanggal_ttd (bila model punya field ini)
if (!$garansiStart && $lpj) {
    if (!empty($lpj->tanggal_ttd)) {
        try { $garansiStart = \Carbon\Carbon::parse($lpj->tanggal_ttd)->startOfDay(); } catch (\Throwable $e) { /* ignore */ }
    }
}

// 3) Jika belum ada, fallback ke LPJ.update_date
if (!$garansiStart && $lpj && !empty($lpj->update_date)) {
    try {
        $garansiStart = $lpj->update_date instanceof \Carbon\Carbon ? $lpj->update_date->copy()->startOfDay() : \Carbon\Carbon::parse($lpj->update_date)->startOfDay();
    } catch (\Throwable $e) { $garansiStart = null; }
}

// 4) Jika belum ada, fallback ke PO.created_at
if (!$garansiStart && $po && !empty($po->created_at)) {
    try {
        $garansiStart = $po->created_at instanceof \Carbon\Carbon ? $po->created_at->copy()->startOfDay() : \Carbon\Carbon::parse($po->created_at)->startOfDay();
    } catch (\Throwable $e) { $garansiStart = null; }
}

// 5) Jika masih belum, pakai notification.created_at
if (!$garansiStart) {
    try {
        $garansiStart = $notification->created_at instanceof \Carbon\Carbon ? $notification->created_at->copy()->startOfDay() : \Carbon\Carbon::parse($notification->created_at)->startOfDay();
    } catch (\Throwable $e) { $garansiStart = null; }
}

// Ambil garansi_months: prioritas dari tabel Garansi, lalu fallback ke LPJ jika ada
$garansiRecord = \App\Models\Garansi::where('notification_number', $notification->notification_number)->first();

$garansiMonths = null;
if ($garansiRecord && $garansiRecord->garansi_months !== null) {
    $garansiMonths = (int) $garansiRecord->garansi_months;
} elseif ($lpj && isset($lpj->garansi_months) && $lpj->garansi_months !== null) {
    $garansiMonths = (int) $lpj->garansi_months;
} else {
    // jika tidak ada info garansi, biarkan null (frontend akan menampilkan '-')
    $garansiMonths = null;
}

// Simpan start + months ke notification (dipakai blade)
$notification->garansi_start = $garansiStart;
$notification->garansi_months = $garansiMonths;

// Hitung end date & status secara defensif
$notification->garansi_end = null;
$notification->garansi_days_remaining = null;
$notification->garansi_status = '-';

if ($garansiMonths !== null && $garansiStart instanceof \Carbon\Carbon) {
    if ($garansiMonths === 0) {
        // 0 => tanpa garansi, tetapkan end = start (frontend bisa interpretasikan sebagai 'Habis')
        $notification->garansi_end = $garansiStart->copy();
    } else {
        try {
            $notification->garansi_end = $garansiStart->copy()->addMonthsNoOverflow($garansiMonths);
        } catch (\Throwable $e) {
            try {
                $notification->garansi_end = $garansiStart->copy()->addMonths($garansiMonths);
            } catch (\Throwable $e2) {
                $notification->garansi_end = null;
            }
        }
    }

    if ($notification->garansi_end instanceof \Carbon\Carbon) {
        $now = \Carbon\Carbon::now()->startOfDay();
        // diffInDays with signed result: positive if end in future, negative if past
        $diff = $now->diffInDays($notification->garansi_end, false);
        $notification->garansi_days_remaining = (int) $diff;

        if ($diff > 1) {
            $notification->garansi_status = 'Masih Berlaku';
        } elseif ($diff === 1) {
            $notification->garansi_status = 'Besok';
        } elseif ($diff === 0) {
            $notification->garansi_status = 'Terakhir hari ini';
        } else {
            $notification->garansi_status = 'Habis';
        }
    } else {
        $notification->garansi_status = '-';
    }
} else {
    // ada garansi_months tapi start belum tersedia -> tunjukkan '-' agar admin tahu TTD/Start belum lengkap
    if ($garansiMonths !== null && !($garansiStart instanceof \Carbon\Carbon)) {
        $notification->garansi_status = '-';
    } else {
        // tidak ada info garansi
        $notification->garansi_status = '-';
    }
}


            // ========== replicate jobWaiting HPP handling ==========
            $rawHpp = $hpp;
            $notification->hpp_file_path = null;
            $notification->hpp_file_exists = false;
            if ($rawHpp) {
                $rawPath = $rawHpp->file_path ?? $rawHpp->pdf_path ?? null;
                if ($rawPath) {
                    $path = ltrim($rawPath, '/');
                    try {
                        $notification->hpp_file_exists = Storage::disk('public')->exists($path);
                        $notification->hpp_file_path = $path;
                    } catch (\Exception $e) {
                        \Log::warning("Cek HPP file failed for {$notification->notification_number}: " . $e->getMessage());
                        $notification->hpp_file_exists = false;
                        $notification->hpp_file_path = null;
                    }
                }
            }

            $source = $notification->source_form ?? '';
            $isAdmin = false;
            if ($user) {
                $isAdmin = (method_exists($user, 'hasRole') && $user->hasRole('admin'))
                           || (!empty($user->is_admin))
                           || (isset($user->usertype) && $user->usertype === 'admin');
            }

            $downloadRouteName = $isAdmin ? ($adminMap[$source] ?? null) : ($publicMap[$source] ?? null);
            $notification->download_route_name = $downloadRouteName;
            $notification->has_hpp_fallback = !empty($notification->hpp_file_exists) && !empty($notification->hpp_file_path);
            // ====================================================

            // STATUS COMPLETE: definisi = HPP + PO document + LHPP + LPJ file termin1 + PPL file termin1
            $notification->is_complete = (
                $notification->isHppAvailable &&
                $notification->has_po_document &&
                $notification->has_lhpp &&
                (!empty($notification->lpj_path_termin1) || !empty(optional($notification->lpj)->lpj_document_path)) &&
                (!empty($notification->ppl_path_termin1) || !empty(optional($notification->lpj)->ppl_document_path))
            );

            return $notification;
        });

        // APPLY STATUS FILTER di collection
        if ($status === 'complete') {
            $notifications = $notifications->filter(fn($n) => $n->is_complete)->values();
        } elseif ($status === 'incomplete') {
            $notifications = $notifications->reject(fn($n) => $n->is_complete)->values();
        } else {
            $notifications = $notifications->values();
        }

        // PAGINATION MANUAL
        $total = $notifications->count();
        $sliced = $notifications->forPage($page, $perPage)->values();

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('pkm.laporan', ['notifications' => $paginated]);

    } catch (\Exception $e) {
        \Log::error('Error di PKMDashboardController::laporan - ' . $e->getMessage(), [
            'request' => $request->all()
        ]);
        abort(500, 'Terjadi kesalahan saat memuat laporan PKM.');
    }
}

    public function showLHPP($notification_number)
    {
        $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();
        return view('pkm.showlhpp', compact('lhpp'));
    }

    public function notificationDetail($notification_number)
{
    $notification = Notification::with(['lhpp', 'lpj'])->where('notification_number', $notification_number)->firstOrFail();
    return view('pkm.notification-detail', compact('notification'));
}

}