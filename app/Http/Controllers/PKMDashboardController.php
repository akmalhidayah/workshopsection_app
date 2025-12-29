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
    public function downloadDirectorHpp($notificationNumber)
{
    $notification = Notification::with('hpp1')->where('notification_number', $notificationNumber)->firstOrFail();

    $hpp = $notification->hpp1;
    abort_if(!$hpp || empty($hpp->director_uploaded_file), 404);

    $path = ltrim($hpp->director_uploaded_file, '/');

    if (!Storage::disk('private')->exists($path)) {
        abort(404);
    }

    return Storage::disk('private')->download($path);
}

/**
 * Resolve HPP link untuk Job Waiting
 * Semua HPP ada di model Hpp1 (dibedakan oleh source_form)
 */
private function resolveHppForNotification(
    $notification,
    array $adminMap,
    array $publicMap,
    bool $isAdmin
): void {
    // reset
    $notification->isHppAvailable = false;
    $notification->hpp_file_path = null;
    $notification->hpp_file_exists = false;
    $notification->download_route_name = null;
    $notification->has_hpp_fallback = false;

    /** @var \App\Models\Hpp1|null $hpp */
    $hpp = $notification->hpp1 ?? null;
    if (!$hpp) {
        return;
    }

    $notification->isHppAvailable = true;
    $source = $hpp->source_form ?? null;
    $notification->source_form = $source;
    $notification->total_amount = $hpp->total_amount ?? 0;

    // ===============================
    // PRIORITAS 1: FILE UPLOAD DIREKTUR
    // (HPP1 & HPP3 SAJA)
    // ===============================
if (
    in_array($source, ['createhpp1', 'createhpp3'], true) &&
    !empty($hpp->director_uploaded_file)
) {
    $path = $hpp->director_uploaded_file;

    if (str_starts_with($path, 'storage/')) {
        $path = substr($path, strlen('storage/'));
    }
\Log::debug('HPP DIRECTOR FILE', [
    'raw'    => $hpp->director_uploaded_file,
    'path'   => $path,
    'exists' => Storage::disk('private')->exists($path),
]);


if (Storage::disk('private')->exists($path)) {
    $notification->hpp_file_path = $path;
    $notification->hpp_file_exists = true;
    $notification->has_hpp_fallback = true;
    return;
}

}



    // ===============================
    // PRIORITAS 2: PDF ROUTE (fallback)
    // ===============================
    if (in_array($source, ['createhpp1', 'createhpp2', 'createhpp3', 'createhpp4'], true)) {
        $notification->download_route_name = $isAdmin
            ? ($adminMap[$source] ?? null)
            : ($publicMap[$source] ?? null);

        $notification->has_hpp_fallback = true;
    }
}
public function jobWaiting(Request $request)
{
    try {
        $priority = $request->input('priority');
        $search   = $request->input('search');

$baseQuery = Notification::notApprovedWorkshop()
    ->with([
        'purchaseOrder',
        'dokumenOrders',
        'scopeOfWork',
        'lhpp',
        'lpj',
        'hpp1',
        'spk'
    ]);


        if (!empty($priority)) {
            $baseQuery->whereRaw("LOWER(IFNULL(priority,'')) = ?", [strtolower($priority)]);
        }

        $baseQuery->where(function ($q) use ($priority) {
            $q->whereHas('purchaseOrder', function ($qq) {
                $qq->whereNotNull('approval_target')
                   ->where('approve_manager', true);
            });

            if (empty($priority)) {
                $q->orWhereRaw("LOWER(IFNULL(priority,'')) IN ('urgently','urgent')");
            }
        });

        if (!empty($search)) {
            $baseQuery->where('notification_number', 'like', "%{$search}%");
        }

        $notifications = $baseQuery
            ->orderByRaw("
                CASE
                    WHEN LOWER(IFNULL(priority,'')) IN ('urgently','urgent') THEN 0
                    WHEN LOWER(IFNULL(priority,'')) = 'hard' THEN 1
                    WHEN LOWER(IFNULL(priority,'')) = 'medium' THEN 2
                    WHEN LOWER(IFNULL(priority,'')) = 'low' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('created_at', 'desc')
            ->get();

        // ====== cek LPJ PPL column ======
        $lpjTable = (new Lpj())->getTable();
        $hasPplColumn = \Schema::hasColumn($lpjTable, 'ppl_document_path');
        $hasPplTerm1  = \Schema::hasColumn($lpjTable, 'ppl_document_path_termin1');

        // ====== ROUTE MAP ======
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

        $notifications->each(function ($notification) use (
            $hasPplColumn,
            $hasPplTerm1,
            $adminMap,
            $publicMap,
            $user
        ) {
            try {
                // ===== BASIC FLAGS =====
                $notification->isLhppAvailable = (bool) $notification->lhpp;
                $notification->isLpjAvailable  = (bool) $notification->lpj;

                $isLpp = false;
                if ($notification->lpj) {
                    if ($hasPplColumn && !empty($notification->lpj->ppl_document_path)) {
                        $isLpp = true;
                    }
                    if ($hasPplTerm1 && !empty($notification->lpj->ppl_document_path_termin1)) {
                        $isLpp = true;
                    }
                }
                $notification->isLppAvailable = $isLpp;

                // ===== DOKUMEN =====
                $docs = collect($notification->dokumenOrders ?? []);
                $types = $docs->pluck('jenis_dokumen')->map(fn ($v) => strtolower(trim($v)));

                $notification->isAbnormalAvailable = $types->contains(fn ($t) => str_contains($t, 'abnormal'));
                $notification->isGambarTeknikAvailable = $types->contains(fn ($t) => str_contains($t, 'gambar'));
                $notification->isScopeOfWorkAvailable =
                    !empty($notification->scopeOfWork) ||
                    $types->contains(fn ($t) => str_contains($t, 'scope'));

                $notification->isSpkAvailable = (bool) $notification->spk;

                // ===== ADMIN CHECK =====
                $isAdmin =
                    $user &&
                    (
                        (method_exists($user, 'hasRole') && $user->hasRole('admin')) ||
                        !empty($user->is_admin) ||
                        (($user->usertype ?? null) === 'admin')
                    );

                // ===============================
                // HPP — SATU PINTU (HELPER)
                // ===============================
                $this->resolveHppForNotification(
                    $notification,
                    $adminMap,
                    $publicMap,
                    $isAdmin
                );

            } catch (\Throwable $e) {
                \Log::warning("jobWaiting transform failed {$notification->notification_number}: {$e->getMessage()}");
            }
        });

        // ===== BUANG YANG SUDAH SELESAI =====
        $filtered = $notifications->reject(fn ($n) =>
            $n->isLhppAvailable && $n->isLpjAvailable && $n->isLppAvailable
        )->values();

        // ===== PAGINATION =====
        $page = (int) $request->input('page', 1);
        $perPage = 10;

        $paginated = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('pkm.jobwaiting', [
            'notifications' => $paginated
        ]);

    } catch (\Throwable $e) {
        \Log::error("jobWaiting error: {$e->getMessage()}");
        abort(500, 'Terjadi kesalahan saat memuat Job Waiting.');
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
        $query = Notification::with(['lhpp', 'lpj', 'hpp1', 'purchaseOrder', 'dokumenOrders' , 'garansi']);

        // filter by notification number (partial)
        $query->when($notificationNumber, fn($q) =>
            $q->where('notification_number', 'like', "%{$notificationNumber}%")
        );

        // ambil semua kandidat
        $notifications = $query->orderBy('created_at', 'desc')->get();

        // ========== helper checks (TIDAK DIUBAH) ==========
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
        // =================================================

$notifications = $notifications->map(function ($notification) use (
    $hasPplColumn,
    $hasPplTerm1,
    $adminMap,
    $publicMap,
    $user
) {

    // ===== GARANSI (AMBIL DARI TABEL GARANSIS) =====
    $notification->garansi_start = null;
    $notification->garansi_end   = null;

    $garansi = $notification->garansi ?? null;

    if ($garansi) {
        if (!empty($garansi->start_date)) {
            $notification->garansi_start = $garansi->start_date->toDateString();
        }

        if (!empty($garansi->end_date)) {
            $notification->garansi_end = $garansi->end_date->toDateString();
        }
    }


            /* =====================================================
             * HPP — SATU-SATUNYA BAGIAN YANG DIPERBARUI
             * ===================================================== */
            $isAdmin =
                $user &&
                (
                    (method_exists($user, 'hasRole') && $user->hasRole('admin')) ||
                    !empty($user->is_admin) ||
                    (($user->usertype ?? null) === 'admin')
                );

            // reset flag HPP (AMAN)
            $notification->isHppAvailable = false;
            $notification->hpp_file_path = null;
            $notification->hpp_file_exists = false;
            $notification->download_route_name = null;
            $notification->has_hpp_fallback = false;

            /** @var \App\Models\Hpp1|null $hpp */
            $hpp = $notification->hpp1;
            if ($hpp) {
                $notification->isHppAvailable = true;
                $notification->source_form = $hpp->source_form ?? $notification->source_form ?? '-';
                $notification->total_amount = $hpp->total_amount ?? 0;

                // PRIORITAS 1: FILE UPLOAD DIREKTUR (PRIVATE)
                if (
                    in_array($hpp->source_form, ['createhpp1', 'createhpp3'], true) &&
                    !empty($hpp->director_uploaded_file)
                ) {
                    $path = ltrim($hpp->director_uploaded_file, '/');

                    if (\Storage::disk('private')->exists($path)) {
                        // blade akan pakai route download khusus
                        $notification->download_route_name = 'pkm.hpp.download_director';
                        $notification->has_hpp_fallback = true;
                        return $notification; // STOP, jangan jatuh ke PDF
                    }
                }

                // PRIORITAS 2: PDF ROUTE
                if (in_array($hpp->source_form, ['createhpp1','createhpp2','createhpp3','createhpp4'], true)) {
                    $notification->download_route_name = $isAdmin
                        ? ($adminMap[$hpp->source_form] ?? null)
                        : ($publicMap[$hpp->source_form] ?? null);

                    $notification->has_hpp_fallback = true;
                }
            }
            /* =====================================================
             * AKHIR PERUBAHAN HPP
             * ===================================================== */

            // ===== LOGIC LAIN: TIDAK DIUBAH SAMA SEKALI =====

            $lhpp = $notification->lhpp;
            $notification->has_lhpp = (bool) $lhpp;
            $notification->total_biaya = (float) ($lhpp->total_biaya ?? $notification->total_amount ?? 0);

            $po = $notification->purchaseOrder;
            $notification->has_po_document = !empty($po->po_document_path);

            $lpj = $notification->lpj;
            
            // termin 1
            $notification->lpj_path_termin1 = $lpj->lpj_document_path_termin1 ?? null;
            $notification->ppl_path_termin1 = $lpj->ppl_document_path_termin1 ?? null;

            // termin 2 (jika ada)
            $notification->lpj_path_termin2 = $lpj->lpj_document_path_termin2 ?? null;
            $notification->ppl_path_termin2 = $lpj->ppl_document_path_termin2 ?? null;

            $notification->isLppAvailable = false;
            if ($lpj) {
                if ($hasPplColumn && !empty($lpj->ppl_document_path)) {
                    $notification->isLppAvailable = true;
                }
                if ($hasPplTerm1 && !empty($lpj->ppl_document_path_termin1)) {
                    $notification->isLppAvailable = true;
                }
            }

            $notification->is_complete = (
                $notification->isHppAvailable &&
                $notification->has_po_document &&
                $notification->has_lhpp &&
                $notification->isLppAvailable
            );

            return $notification;
        });

        // APPLY STATUS FILTER
        if ($status === 'complete') {
            $notifications = $notifications->filter(fn($n) => $n->is_complete)->values();
        } elseif ($status === 'incomplete') {
            $notifications = $notifications->reject(fn($n) => $n->is_complete)->values();
        }

        // PAGINATION
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