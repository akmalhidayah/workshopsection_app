<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderBengkel;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderBengkelController extends Controller
{
  public function index(Request $request)
{
    $perPage = (int) $request->input('perPage', 10);
    $page    = (int) $request->input('page', 1);

    // ✅ pakai konstanta dari Notification, bukan hardcode lagi
    $workshopNotes = Notification::WORKSHOP_NOTES;

    try {
        Log::info('OrderBengkelController@index called', $request->all());

        // ✅ Base query:
        //   - hanya status: approved_workshop ATAU approved_workshop_jasa
        //   - tidak perlu lagi whereIn('catatan', $workshopNotes) di sini,
        //     karena status sudah memisahkan target bengkel/jasa
        $notificationsQuery = Notification::with(['dokumenOrders', 'scopeOfWork', 'verifikasiAnggaran'])
            ->whereIn('status', [
                Notification::STATUS_APPROVED_WORKSHOP,
                Notification::STATUS_APPROVED_WORKSHOP_JASA,
            ])
            ->orderBy('input_date', 'desc');

        // 🔍 optional search (TIDAK DIUBAH)
        if ($search = trim($request->input('search', ''))) {
            $notificationsQuery->where(function ($q) use ($search) {
                $q->where('notification_number', 'like', "%{$search}%")
                  ->orWhere('job_name', 'like', "%{$search}%")
                  ->orWhere('unit_work', 'like', "%{$search}%")
                  ->orWhere('seksi', 'like', "%{$search}%");
            });
        }

        // 🔍 optional filter progress (TIDAK DIUBAH)
        if ($progress = $request->input('progress')) {
            $notificationsQuery->whereHas('orderBengkel', function ($q) use ($progress) {
                $q->where('progress_status', $progress);
            });
        }

        // 🔍 optional filter regu (tetap pakai catatan)
        if ($regu = $request->input('regu')) {
            $notificationsQuery->where('catatan', $regu);
        }

        // ⬇️ sisanya (paginate, mapping ke $combined, paginator, return view)
        //     BIARKAN seperti kode kamu sebelumnya, tidak perlu diubah
        $notificationsPaginated = $notificationsQuery->paginate($perPage, ['*'], 'page', $page);

        $notificationNumbers = $notificationsPaginated->getCollection()->pluck('notification_number')->toArray();

        $orderBengkels = collect();
        if (!empty($notificationNumbers)) {
            $orderBengkels = OrderBengkel::whereIn('notification_number', $notificationNumbers)
                ->get()
                ->keyBy('notification_number');
        }

        $combined = $notificationsPaginated->getCollection()->map(function ($notif) use ($orderBengkels) {
            $orderModel = $orderBengkels->get($notif->notification_number);

            $konf            = $orderModel->konfirmasi_anggaran ?? null;
            $status_material = $orderModel->status_material ?? null;

            $showMaterial = false;
            $showProgress = false;
            $showEkorin   = false;

            if ($konf === 'Material Ready') {
                $showMaterial = true;
                $showProgress = true;
                $showEkorin   = false;
            } elseif ($konf === 'Material Not Ready') {
                $showMaterial = false;
                $showProgress = true;
                $showEkorin   = true;
            }

            return (object) [
                'notification'          => $notif,
                'notification_number'   => $notif->notification_number,
                'konfirmasi_anggaran'   => $orderModel->konfirmasi_anggaran ?? '',
                'keterangan_konfirmasi' => $orderModel->keterangan_konfirmasi ?? '',
                'status_anggaran'       => $orderModel->status_anggaran ?? null,
                'keterangan_anggaran'   => $orderModel->keterangan_anggaran ?? '',
                'status_material'       => $orderModel->status_material ?? null,
                'keterangan_material'   => $orderModel->keterangan_material ?? '',
                'progress_status'       => $orderModel->progress_status ?? null,
                'keterangan_progress'   => $orderModel->keterangan_progress ?? '',
                'catatan_order'         => $orderModel->catatan ?? $notif->catatan ?? '-',
                'nomor_e_korin'         => $orderModel->nomor_e_korin ?? null,
                'status_e_korin'        => $orderModel->status_e_korin ?? null,
                'show_material'         => $showMaterial,
                'show_progress'         => $showProgress,
                'show_ekorin'           => $showEkorin,
                'order_bengkel'         => $orderModel ? $orderModel->toArray() : null,
            ];
        });

        $paginator = new LengthAwarePaginator(
            $combined->values(),
            $notificationsPaginated->total(),
            $notificationsPaginated->perPage(),
            $notificationsPaginated->currentPage(),
            [
                'path'  => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        return view('admin.order_bengkel', [
            'orders'      => $paginator,
            'reguOptions' => $workshopNotes,
        ]);
    } catch (\Throwable $e) {
        Log::error('OrderBengkelController@index error: '.$e->getMessage(), ['exception' => $e, 'request' => $request->all()]);
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data order bengkel. Cek log.'], 500);
        }
        return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat mengambil data order bengkel. Silakan cek log.']);
    }
}


    // update method stays same (no change required)...


    public function update(Request $request, $notification_number)
    {
        try {
            $payload = $request->all();
            Log::info('OrderBengkelController@update called', ['notification' => $notification_number, 'payload' => $payload]);

            $request->validate([
                'konfirmasi_anggaran'    => 'nullable|string|max:100',
                'keterangan_konfirmasi'  => 'nullable|string|max:2000',
                'status_anggaran'        => 'nullable|string|max:100',
                'keterangan_anggaran'    => 'nullable|string|max:2000',
                'status_material'        => 'nullable|string|max:100',
                'keterangan_material'    => 'nullable|string|max:2000',
                'progress_status'        => 'nullable|string|max:50',
                'keterangan_progress'    => 'nullable|string|max:2000',
                'catatan'                => 'nullable|string|max:2000',
                'nomor_e_korin'          => 'nullable|string|max:191',
                'status_e_korin'         => 'nullable|in:waiting_korin,waiting_approval,waiting_transfer,complete_transfer',
            ]);

            $order = OrderBengkel::firstOrNew(['notification_number' => $notification_number]);

            $updatable = [
                'konfirmasi_anggaran','keterangan_konfirmasi',
                'status_anggaran','keterangan_anggaran',
                'status_material','keterangan_material',
                'progress_status','keterangan_progress',
                'catatan',
                'nomor_e_korin','status_e_korin',
            ];

            foreach ($updatable as $field) {
                if ($request->has($field)) {
                    $order->$field = $request->input($field);
                }
            }

            // BUSINESS RULES (keamanan, jadi dijalankan di controller)
            // 1) Jika konfirmasi = Material Not Ready -> remove status_material & keterangan_material
            if (isset($order->konfirmasi_anggaran) && $order->konfirmasi_anggaran === 'Material Not Ready') {
                $order->status_material = null;
                $order->keterangan_material = null;
            }

            // 2) Jika konfirmasi = Material Ready -> we keep material fields (no change)
            // 3) Jika konfirmasi empty -> hide progress & material fields by controller flags (no DB changes required)

            $order->notification_number = $notification_number;
            $order->save();

            Log::info("OrderBengkel saved for {$notification_number}", ['order_id' => $order->notification_number]);

            return response()->json([
                'message' => 'Status order bengkel berhasil diperbarui.',
                'updated' => $order->toArray()
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['errors' => $ve->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('OrderBengkelController@update error: '.$e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan. Cek log.'], 500);
        }
    }
}
