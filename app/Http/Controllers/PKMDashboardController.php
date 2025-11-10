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
        // 🎯 Ambil filter dari request
        $priority = $request->input('priority');
        $search   = $request->input('search');

        // 🔍 Ambil semua notifikasi dengan PurchaseOrder yang sudah disetujui manager
        $query = Notification::with('purchaseOrder')
            ->whereHas('purchaseOrder', function ($q) {
                $q->whereNotNull('approval_target')
                  ->where('approve_manager', true);
            });

        // 🔹 Filter prioritas
        if (!empty($priority)) {
            $query->where('priority', $priority);
        }

        // 🔹 Filter pencarian
        if (!empty($search)) {
            $query->where('notification_number', 'like', "%{$search}%");
        }

        // 🔹 Ambil data notifikasi
        $notifications = $query->orderBy('created_at', 'desc')->get();

        // 🔧 Iterasi & tambahkan atribut dokumen tambahan
        $notifications->each(function ($notification) {
            try {
                // LHPP, LPJ, LPP
                $notification->isLhppAvailable = LHPP::where('notification_number', $notification->notification_number)->exists();
                $notification->isLpjAvailable  = Lpj::where('notification_number', $notification->notification_number)->exists();
                $notification->isLppAvailable  = Lpj::where('notification_number', $notification->notification_number)
                    ->whereNotNull('ppl_document_path')
                    ->exists();

                // Abnormalitas → DokumenOrder
                $notification->isAbnormalAvailable = DokumenOrder::where('notification_number', $notification->notification_number)
                    ->where('jenis_dokumen', 'abnormalitas')
                    ->exists();

                // Scope of Work → model khusus
                $notification->isScopeOfWorkAvailable = \App\Models\ScopeOfWork::where('notification_number', $notification->notification_number)
                    ->exists();

                // Gambar Teknik → DokumenOrder
                $notification->isGambarTeknikAvailable = DokumenOrder::where('notification_number', $notification->notification_number)
                    ->where('jenis_dokumen', 'gambar_teknik')
                    ->exists();

                // HPP
                $hpp = Hpp1::where('notification_number', $notification->notification_number)->first();
                if ($hpp) {
                    $notification->isHppAvailable = true;
                    $notification->source_form    = $hpp->source_form;
                    $notification->total_amount   = $hpp->total_amount;
                } else {
                    $notification->isHppAvailable = false;
                }

                // SPK
                $notification->isSpkAvailable = SPK::where('notification_number', $notification->notification_number)->exists();

            } catch (\Exception $e) {
                \Log::error("Error saat cek dokumen notification {$notification->notification_number}: " . $e->getMessage());
            }
        });

        // 🚫 Filter hanya yang belum lengkap LHPP, LPJ, LPP
        $filtered = $notifications->reject(fn($n) =>
            $n->isLhppAvailable && $n->isLpjAvailable && $n->isLppAvailable
        );

        // 📄 Pagination manual
        $page    = $request->input('page', 1);
        $perPage = 10;
        $paginated = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // 🎨 Return view biasa
        if (!$request->ajax()) {
            return view('pkm.jobwaiting', ['notifications' => $paginated]);
        }

        // 🔁 Return JSON jika AJAX
        return response()->json([
            'status' => 'success',
            'data'   => $paginated,
        ], 200);

    } catch (\Illuminate\Database\QueryException $e) {
        \Log::error("Database error: " . $e->getMessage());
        return response()->json([
            'status'  => 'error',
            'message' => 'Terjadi kesalahan database saat mengambil data.',
            'error'   => $e->getMessage()
        ], 500);

    } catch (\Exception $e) {
        \Log::error("Unexpected error: " . $e->getMessage());
        return response()->json([
            'status'  => 'error',
            'message' => 'Terjadi kesalahan tidak terduga.',
            'error'   => $e->getMessage()
        ], 500);
    }
}

/**
 * 🔽 Download file HPP sesuai source_form (HPP1 / HPP2 / HPP3)
 */
public function downloadHpp($notification_number)
{
    try {
        // Ambil data HPP berdasarkan notification_number
        $hpp = Hpp1::where('notification_number', $notification_number)->first();

        if (!$hpp) {
            return back()->with('error', 'Data HPP tidak ditemukan.');
        }

        // Pastikan kolom file_path atau pdf_path ada
        $filePath = $hpp->file_path ?? $hpp->pdf_path ?? null;

        if (!$filePath || !\Storage::exists('public/' . $filePath)) {
            return back()->with('error', 'File HPP belum tersedia di server.');
        }

        // Buat nama file yang rapi berdasarkan source_form
        $source = strtoupper(str_replace('create', '', $hpp->source_form ?? 'HPP'));
        $fileName = "{$source}_{$notification_number}.pdf";

        return \Storage::download('public/' . $filePath, $fileName);

    } catch (\Exception $e) {
        \Log::error("Error download HPP: " . $e->getMessage());
        return back()->with('error', 'Terjadi kesalahan saat mengunduh dokumen HPP.');
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

        // base query: eager load relations yang dibutuhkan
        $query = Notification::with(['lhpp', 'lpj', 'hpp1', 'purchaseOrder']);

        // filter by notification number (partial)
        $query->when($notificationNumber, fn($q) => $q->where('notification_number', 'like', "%{$notificationNumber}%"));

        // tetap persyaratan dasar: HPP ada, PurchaseOrder punya dokumen, dan LHPP ada
        $query->whereHas('hpp1')
              ->whereHas('purchaseOrder', function ($q) {
                  $q->whereNotNull('po_document_path');
              })
              ->whereHas('lhpp');

        // status filter: complete = punya LPJ+PPL; incomplete = tidak memenuhi semua syarat complete
        if ($status === 'complete') {
            $query->whereHas('lpj', function ($q) {
                $q->whereNotNull('lpj_document_path')
                  ->whereNotNull('ppl_document_path');
            });
        } elseif ($status === 'incomplete') {
            // jika salah satu komponen lengkap belum terpenuhi -> termasuk incomplete
            $query->where(function ($q) {
                $q->whereDoesntHave('lpj', function ($qq) {
                        $qq->whereNotNull('lpj_document_path')
                           ->whereNotNull('ppl_document_path');
                    })
                    ->orWhereDoesntHave('purchaseOrder', function ($qq) {
                        $qq->whereNotNull('po_document_path');
                    })
                    ->orWhereDoesntHave('lhpp')
                    ->orWhereDoesntHave('hpp1');
            });
        }

        // paginate dan jaga query string
        $notifications = $query->orderBy('created_at', 'desc')
                               ->paginate(10)
                               ->appends($request->query());

        // transform supaya blade tidak melakukan query ulang
        $notifications->transform(function ($notification) {
            $hpp = $notification->hpp1;
            $notification->isHppAvailable = (bool) $hpp;
            $notification->source_form    = $hpp->source_form ?? '-';
            $notification->total_amount   = $hpp->total_amount ?? 0;

            $po = $notification->purchaseOrder;
            $notification->has_po_document = !empty($po->po_document_path);

            $notification->has_lhpp = (bool) $notification->lhpp;

            $lpj = $notification->lpj;
            $notification->has_lpj = !empty($lpj->lpj_document_path);
            $notification->has_ppl = !empty($lpj->ppl_document_path);

            return $notification;
        });

        return view('pkm.laporan', compact('notifications'));
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