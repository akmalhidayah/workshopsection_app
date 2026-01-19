<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemNotification;
use App\Models\LHPP;
use App\Models\Notification;
use App\Models\User;
use App\Models\LHPPApprovalToken;
use App\Services\LHPPApprovalLinkService;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class LHPPController extends Controller
{
    public function index(Request $request)
    {
        // Ambil filter dari query string
        $search       = $request->query('search');
        $filterUnit   = $request->query('unit_kerja');
        $filterPo     = $request->query('purchase_order_number');
        $filterTermin = $request->query('termin_status'); // values: all, t1_paid, t1_unpaid, t2_paid, t2_unpaid

        // Build query: ambil LHPP + left join ke LPJ untuk status termin
        $query = DB::table('lhpp as l')
            ->leftJoin('lpjs as p', 'l.notification_number', '=', 'p.notification_number')
            ->leftJoin('notifications as n', 'l.notification_number', '=', 'n.notification_number')
            ->select('l.*', 'p.termin1', 'p.termin2', 'n.seksi');

        // Search (search di notification_number, purchase_order_number, unit_kerja)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('l.notification_number', 'like', "%{$search}%")
                    ->orWhere('l.purchase_order_number', 'like', "%{$search}%")
                    ->orWhere('l.unit_kerja', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($filterUnit) {
            $query->where('l.unit_kerja', $filterUnit);
        }

        if ($filterPo) {
            $query->where('l.purchase_order_number', $filterPo);
        }

        if ($filterTermin && $filterTermin !== 'all') {
            switch ($filterTermin) {
                case 't1_paid':
                    $query->where('p.termin1', 'sudah');
                    break;
                case 't1_unpaid':
                    $query->where(function ($q) {
                        $q->whereNull('p.termin1')->orWhere('p.termin1', '!=', 'sudah');
                    });
                    break;
                case 't2_paid':
                    $query->where('p.termin2', 'sudah');
                    break;
                case 't2_unpaid':
                    $query->where(function ($q) {
                        $q->whereNull('p.termin2')->orWhere('p.termin2', '!=', 'sudah');
                    });
                    break;
            }
        }

        // Pagination (gunakan 12 per halaman supaya rapi)
        $lhppRows = $query->orderBy('l.created_at', 'desc')
            ->paginate(12)
            ->appends($request->query());

        // Ambil daftar distinct untuk options dropdown
        $units = DB::table('lhpp')->select('unit_kerja')->distinct()->orderBy('unit_kerja')->pluck('unit_kerja');
        $pos   = DB::table('lhpp')->select('purchase_order_number')->distinct()->orderBy('purchase_order_number')->pluck('purchase_order_number');

        // ====== Token approval aktif untuk setiap LHPP (opsional untuk index Blade) ======
        $activeTokens = collect();
        try {
            if ($lhppRows->count() > 0) {
                $notifNumbers = $lhppRows->pluck('notification_number')->filter()->unique()->values()->all();

                if (!empty($notifNumbers)) {
                    $activeTokens = LHPPApprovalToken::whereIn('notification_number', $notifNumbers)
                        ->whereNull('used_at')
                        ->where(function ($q) {
                            $q->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                        })
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->groupBy('notification_number')
                        ->map(function ($group) {
                            // jika ada beberapa token, ambil yang terbaru
                            return $group->first();
                        });
                }
            }
        } catch (\Throwable $e) {
            Log::error('[LHPP] Gagal mengambil activeTokens: ' . $e->getMessage());
            // kalau error, jangan ganggu halaman index
            $activeTokens = collect();
        }

        // Kirim ke view
        return view('pkm.lhpp.index', [
            'lhpps'        => $lhppRows,
            'units'        => $units,
            'pos'          => $pos,
            'activeTokens' => $activeTokens,
            'filters'      => [
                'search'                => $search,
                'unit_kerja'            => $filterUnit,
                'purchase_order_number' => $filterPo,
                'termin_status'         => $filterTermin ?? 'all',
            ],
        ]);
    }

    public function create()
    {
        $notifications = Notification::whereNotIn('notification_number', function ($query) {
                $query->select('notification_number')->from('lhpp');
            })
            ->whereHas('hpp1')
            ->whereHas('purchaseOrder', function ($q) {
                $q->whereNotNull('po_document_path');
            })
            ->get();

        return view('pkm.lhpp.create', compact('notifications'));
    }
public function store(Request $request)
{
    $validated = $request->validate([
        'notification_number'   => 'required|string|max:255',
        'nomor_order'           => 'nullable|string|max:255',
        'description_notifikasi'=> 'nullable|string',
        'purchase_order_number' => 'required|string|max:255',
        'unit_kerja'            => 'required|string|max:255',
        'tanggal_selesai'       => 'required|date',
        'waktu_pengerjaan'      => 'required|integer',

        // material
        'material_description'  => 'nullable|array',
        'material_volume'       => 'nullable|array',
        'material_harga_satuan' => 'nullable|array',
        'material_jumlah'       => 'nullable|array',

        // upah
        'upah_description'      => 'nullable|array',
        'upah_volume'           => 'nullable|array',
        'upah_harga_satuan'     => 'nullable|array',
        'upah_jumlah'           => 'nullable|array',

        // subtotals & total
        'material_subtotal'     => 'nullable|numeric',
        'upah_subtotal'         => 'nullable|numeric',
        'total_biaya'           => 'nullable|numeric',

        'kontrak_pkm'           => 'required|string|in:Fabrikasi,Konstruksi,Pengerjaan Mesin',

        'images'                => 'nullable|array',
        'images.*'              => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        'image_descriptions'    => 'nullable|array',
    ]);

    // Simpan multiple images + keterangan gambar sebagai array (karena model cast => array)
    $imageData = [];
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $key => $image) {
            $path        = $image->store('lhpp_images', 'public');
            $description = $request->image_descriptions[$key] ?? null;
            $imageData[] = [
                'path'        => $path,
                'description' => $description,
            ];
        }
    }

    try {
        // SIMPAN LHPP (logic lama tidak diubah)
        $lhpp = LHPP::create([
            'notification_number'      => $validated['notification_number'],
            'nomor_order'              => $validated['nomor_order'] ?? null,
            'description_notifikasi'   => $validated['description_notifikasi'] ?? null,
            'purchase_order_number'    => $validated['purchase_order_number'],
            'unit_kerja'               => $validated['unit_kerja'],
            'tanggal_selesai'          => $validated['tanggal_selesai'],
            'waktu_pengerjaan'         => $validated['waktu_pengerjaan'],

            // material
            'material_description'     => $validated['material_description'] ?? null,
            'material_volume'          => $validated['material_volume'] ?? null,
            'material_harga_satuan'    => $validated['material_harga_satuan'] ?? null,
            'material_jumlah'          => $validated['material_jumlah'] ?? null,

            // upah
            'upah_description'         => $validated['upah_description'] ?? null,
            'upah_volume'              => $validated['upah_volume'] ?? null,
            'upah_harga_satuan'        => $validated['upah_harga_satuan'] ?? null,
            'upah_jumlah'              => $validated['upah_jumlah'] ?? null,

            'material_subtotal'        => $validated['material_subtotal'] ?? 0,
            'upah_subtotal'            => $validated['upah_subtotal'] ?? 0,
            'total_biaya'              => $validated['total_biaya'] ?? 0,

            'kontrak_pkm'              => $validated['kontrak_pkm'],

            // store array (Eloquent will cast to JSON jika model casts)
            'images'                   => count($imageData) > 0 ? $imageData : null,
        ]);

        // 🔔 === BUAT NOTIFIKASI ADMIN ===
        $this->notifyAdminNewLhpp($lhpp);

        // === ISSUE TOKEN PERTAMA UNTUK MANAGER USER (via Struktur Organisasi) ===
        try {
            $this->issueFirstTokenForLHPP($lhpp);
        } catch (\Throwable $e) {
            Log::error('[LHPP] Gagal issue first token: '.$e->getMessage(), [
                'notif' => $lhpp->notification_number,
            ]);
        }

        return redirect()
            ->route('pkm.lhpp.index')
            ->with('success', 'Data LHPP berhasil disimpan.');

    } catch (\Throwable $e) {
        Log::error("[LHPP] Error saving LHPP: ".$e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);

        return redirect()
            ->back()
            ->with('error', 'Terjadi kesalahan saat menyimpan data LHPP. Harap mengisi dengan benar.')
            ->withInput();
    }
}
private function issueFirstTokenForLHPP(LHPP $lhpp): void
{
    if (! $lhpp->notification_number) {
        Log::warning('[LHPP] Tidak bisa issue token: notification_number kosong', [
            'lhpp_id' => $lhpp->id,
        ]);
        return;
    }

    $notification = \App\Models\Notification::where(
        'notification_number',
        $lhpp->notification_number
    )->first();

    if (! $notification || ! $notification->unit_work || ! $notification->seksi) {
        Log::warning('[LHPP] Data notification/unit_work/seksi tidak lengkap saat issue token pertama', [
            'notif'     => $lhpp->notification_number,
            'unit_work' => $notification->unit_work ?? null,
            'seksi'     => $notification->seksi ?? null,
        ]);
        return;
    }

    $unitWork = \App\Models\UnitWork::where('name', $notification->unit_work)->first();
    if (! $unitWork) {
        Log::warning('[LHPP] UnitWork tidak ditemukan saat issue token pertama', [
            'notif'     => $lhpp->notification_number,
            'unit_work' => $notification->unit_work,
        ]);
        return;
    }

    $section = $unitWork->sections()
        ->where('name', $notification->seksi)
        ->first();

    if (! $section || ! $section->manager) {
        Log::warning('[LHPP] Section/Manager tidak ditemukan saat issue token pertama', [
            'notif'     => $lhpp->notification_number,
            'seksi'     => $notification->seksi,
        ]);
        return;
    }

    $manager  = $section->manager;
    $signType = 'manager_user';

    $exists = \App\Models\LHPPApprovalToken::where('notification_number', $lhpp->notification_number)
        ->where('user_id', $manager->id)
        ->where('sign_type', $signType)
        ->whereNull('used_at')
        ->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        })
        ->exists();

    if ($exists) {
        Log::info('[LHPP] Token aktif sudah ada, skip re-issue', [
            'notif' => $lhpp->notification_number,
            'user'  => $manager->id,
        ]);
        return;
    }

    $linkSvc = app(\App\Services\LHPPApprovalLinkService::class);

    $tokenId = $linkSvc->issue(
        $lhpp->notification_number,
        $signType,
        $manager->id,
        60 * 24 // misal 24 jam
    );

    Log::info('[LHPP] First token issued for Manager User (Struktur Org)', [
        'notif' => $lhpp->notification_number,
        'user'  => $manager->id,
        'token' => $tokenId,
    ]);
}
private function notifyAdminNewLhpp(LHPP $lhpp): void
{
    try {
        // Ambil primary key LHPP (bisa id, bisa notification_number tergantung model)
        $entityId = $lhpp->getKey();

        // Kalau masih kosong/null, fallback ke notification_number
        if (empty($entityId)) {
            $entityId = is_numeric($lhpp->notification_number)
                ? (int) $lhpp->notification_number
                : 0;
        }

        SystemNotification::create([
            'title'       => 'LHPP baru dibuat',
            'description' => sprintf(
                'LHPP %s untuk Unit Kerja %s telah dibuat dan menunggu proses approval.',
                $lhpp->notification_number,
                $lhpp->unit_kerja ?? '-'
            ),
          'url'         => route('admin.lhpp.index'),
            'target_role' => 'admin',

            'entity_type' => 'lhpp',
            'entity_id'   => $entityId,
            'action'      => 'created',   // ✅ WAJIB: isi kolom action
            'priority'    => 'high',
            'is_read'     => false,
        ]);
    } catch (\Throwable $e) {
        Log::error('[LHPP] Gagal membuat SystemNotification admin', [
            'notif' => $lhpp->notification_number,
            'err'   => $e->getMessage(),
        ]);
    }
}


    public function getPurchaseOrder($notificationNumber)
    {
        try {
            $notification = Notification::with('purchaseOrder')
                ->where('notification_number', $notificationNumber)
                ->first();

            if (!$notification || !$notification->purchaseOrder) {
                Log::error("Purchase Order tidak ditemukan untuk notification_number: $notificationNumber");
                return response()->json(['error' => 'Purchase Order tidak ditemukan'], 404);
            }

            return response()->json([
                'purchase_order_number' => $notification->purchaseOrder->purchase_order_number ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error("Error fetching purchase order: " . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan di server'], 500);
        }
    }

    public function getNomorOrder($notificationNumber)
    {
        try {
            $nomorOrder = Notification::where('notification_number', $notificationNumber)
                ->whereHas('hpp1')
                ->whereHas('purchaseOrder', function ($query) {
                    $query->whereNotNull('po_document_path');
                })
                ->first();

            if (!$nomorOrder) {
                Log::error("Nomor Order tidak ditemukan atau belum memiliki HPP dan PO untuk notification_number: $notificationNumber");
                return response()->json(['error' => 'Nomor Order tidak ditemukan atau belum memiliki HPP dan PO'], 404);
            }

            return response()->json([
                'nomor_order' => $nomorOrder->nomor_order ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error("Error fetching nomor order: " . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan di server'], 500);
        }
    }

    public function getJobName($notificationNumber)
    {
        try {
            $notification = Notification::where('notification_number', $notificationNumber)->first();

            if (!$notification) {
                Log::error("Job name tidak ditemukan untuk notification_number: $notificationNumber");
                return response()->json(['error' => 'Job name tidak ditemukan'], 404);
            }

            return response()->json([
                'job_name' => $notification->job_name ?? '-',
            ]);
        } catch (\Throwable $e) {
            Log::error("Error fetching job name: " . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan di server'], 500);
        }
    }

    public function calculateWorkDuration($notificationNumber, $tanggalSelesai)
    {
        $notification = Notification::where('notification_number', $notificationNumber)
            ->with('purchaseOrder')
            ->first();

        if ($notification && $notification->purchaseOrder && $notification->purchaseOrder->update_date) {
            $updateDate = new \DateTime($notification->purchaseOrder->update_date ?? null);
            $selesai    = new \DateTime($tanggalSelesai);
            $diff       = $updateDate->diff($selesai)->days;
            return response()->json(['waktu_pengerjaan' => $diff]);
        }

        return response()->json(['waktu_pengerjaan' => 0]);
    }

    public function show($notification_number)
    {
        $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();

        // Decode JSON jika masih string; kalau null tetap []
        $lhpp->material_description  = is_string($lhpp->material_description) ? json_decode($lhpp->material_description, true) : ($lhpp->material_description ?? []);
        $lhpp->material_volume       = is_string($lhpp->material_volume) ? json_decode($lhpp->material_volume, true) : ($lhpp->material_volume ?? []);
        $lhpp->material_harga_satuan = is_string($lhpp->material_harga_satuan) ? json_decode($lhpp->material_harga_satuan, true) : ($lhpp->material_harga_satuan ?? []);
        $lhpp->material_jumlah       = is_string($lhpp->material_jumlah) ? json_decode($lhpp->material_jumlah, true) : ($lhpp->material_jumlah ?? []);

        $lhpp->upah_description      = is_string($lhpp->upah_description) ? json_decode($lhpp->upah_description, true) : ($lhpp->upah_description ?? []);
        $lhpp->upah_volume           = is_string($lhpp->upah_volume) ? json_decode($lhpp->upah_volume, true) : ($lhpp->upah_volume ?? []);
        $lhpp->upah_harga_satuan     = is_string($lhpp->upah_harga_satuan) ? json_decode($lhpp->upah_harga_satuan, true) : ($lhpp->upah_harga_satuan ?? []);
        $lhpp->upah_jumlah           = is_string($lhpp->upah_jumlah) ? json_decode($lhpp->upah_jumlah, true) : ($lhpp->upah_jumlah ?? []);

        $lhpp->material_harga_satuan = array_map('floatval', $lhpp->material_harga_satuan ?? []);
        $lhpp->material_jumlah       = array_map('floatval', $lhpp->material_jumlah ?? []);
        $lhpp->upah_harga_satuan     = array_map('floatval', $lhpp->upah_harga_satuan ?? []);
        $lhpp->upah_jumlah           = array_map('floatval', $lhpp->upah_jumlah ?? []);

        $lhpp->images                = is_string($lhpp->images) ? json_decode($lhpp->images, true) : ($lhpp->images ?? []);

        return view('pkm.lhpp.show', compact('lhpp'));
    }

    public function edit($id)
    {
        $lhpp = LHPP::findOrFail($id);

        $lhpp->material_description  = is_string($lhpp->material_description) ? json_decode($lhpp->material_description, true) : ($lhpp->material_description ?? []);
        $lhpp->material_volume       = is_string($lhpp->material_volume) ? json_decode($lhpp->material_volume, true) : ($lhpp->material_volume ?? []);
        $lhpp->material_harga_satuan = is_string($lhpp->material_harga_satuan) ? json_decode($lhpp->material_harga_satuan, true) : ($lhpp->material_harga_satuan ?? []);
        $lhpp->material_jumlah       = is_string($lhpp->material_jumlah) ? json_decode($lhpp->material_jumlah, true) : ($lhpp->material_jumlah ?? []);

        $lhpp->upah_description      = is_string($lhpp->upah_description) ? json_decode($lhpp->upah_description, true) : ($lhpp->upah_description ?? []);
        $lhpp->upah_volume           = is_string($lhpp->upah_volume) ? json_decode($lhpp->upah_volume, true) : ($lhpp->upah_volume ?? []);
        $lhpp->upah_harga_satuan     = is_string($lhpp->upah_harga_satuan) ? json_decode($lhpp->upah_harga_satuan, true) : ($lhpp->upah_harga_satuan ?? []);
        $lhpp->upah_jumlah           = is_string($lhpp->upah_jumlah) ? json_decode($lhpp->upah_jumlah, true) : ($lhpp->upah_jumlah ?? []);

        $lhpp->images                = is_string($lhpp->images) ? json_decode($lhpp->images, true) : ($lhpp->images ?? []);

        return view('pkm.lhpp.edit', compact('lhpp'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nomor_order'           => 'nullable|string|max:255',
            'description_notifikasi'=> 'nullable|string',
            'purchase_order_number' => 'required|string|max:255',
            'unit_kerja'            => 'required|string|max:255',
            'tanggal_selesai'       => 'required|date',
            'waktu_pengerjaan'      => 'required|integer',

            // material
            'material_description'  => 'nullable|array',
            'material_volume'       => 'nullable|array',
            'material_harga_satuan' => 'nullable|array',
            'material_jumlah'       => 'nullable|array',

            // upah
            'upah_description'      => 'nullable|array',
            'upah_volume'           => 'nullable|array',
            'upah_harga_satuan'     => 'nullable|array',
            'upah_jumlah'           => 'nullable|array',

            'material_subtotal'     => 'nullable|numeric',
            'upah_subtotal'         => 'nullable|numeric',
            'total_biaya'           => 'nullable|numeric',

            'kontrak_pkm'           => 'required|string|in:Fabrikasi,Konstruksi,Pengerjaan Mesin',

            'new_images.*'          => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $lhpp = LHPP::findOrFail($id);

        // Hapus gambar yang dipilih untuk dihapus
        if ($request->has('delete_images')) {
            $deletedPaths = $request->delete_images;
            $images       = is_string($lhpp->images) ? json_decode($lhpp->images, true) : ($lhpp->images ?? []);

            foreach ($deletedPaths as $path) {
                Storage::disk('public')->delete($path);
                $images = array_filter($images, fn ($image) => $image['path'] !== $path);
            }

            $lhpp->images = array_values($images);
        }

        // Tambah gambar baru jika ada
        if ($request->hasFile('new_images')) {
            $images = is_string($lhpp->images) ? json_decode($lhpp->images, true) : ($lhpp->images ?? []);
            foreach ($request->file('new_images') as $image) {
                $path     = $image->store('lhpp_images', 'public');
                $images[] = ['path' => $path, 'description' => ''];
            }
            $lhpp->images = $images;
        }

        // Update data lainnya
        $lhpp->update($validated);

        return redirect()->route('pkm.lhpp.index')->with('success', 'Data LHPP berhasil diperbarui.');
    }

    public function deleteImage(Request $request)
    {
        $request->validate([
            'image_path' => 'required|string',
            'lhpp_id'    => 'required|string', // notification_number, bukan integer
        ]);

        $lhpp = LHPP::where('notification_number', $request->lhpp_id)->firstOrFail();

        $images      = is_string($lhpp->images) ? json_decode($lhpp->images, true) : ($lhpp->images ?? []);
        $imageExists = array_filter($images, fn ($image) => $image['path'] === $request->image_path);

        if (empty($imageExists)) {
            return response()->json(['success' => false, 'message' => 'Gambar tidak ditemukan dalam database'], 404);
        }

        if (Storage::disk('public')->exists($request->image_path)) {
            Storage::disk('public')->delete($request->image_path);
        }

        $images = array_filter($images, fn ($image) => $image['path'] !== $request->image_path);

        $lhpp->update([
            'images' => array_values($images),
        ]);

        return response()->json(['success' => true, 'message' => 'Gambar berhasil dihapus']);
    }

    public function destroy($notification_number)
    {
        $lhpp = LHPP::findOrFail($notification_number);
        $lhpp->delete();
        return redirect()->route('pkm.lhpp.index')->with('success', 'Data berhasil dihapus.');
    }

    public function downloadPDF($notification_number)
    {
        $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();

        // Pastikan storage:link telah dijalankan (php artisan storage:link) agar asset('storage/...') bisa diakses dari public
        $signaturePath = storage_path("app/public/signatures/lhpp/");
        if (!file_exists($signaturePath)) {
            mkdir($signaturePath, 0777, true);
        }

        $signatures = [
            'manager_signature'             => $lhpp->manager_signature,
            'manager_signature_requesting'  => $lhpp->manager_signature_requesting,
            'manager_pkm_signature'        => $lhpp->manager_pkm_signature,
        ];

        foreach ($signatures as $key => $signature) {
            if (!empty($signature) && str_starts_with($signature, 'data:image')) {
                $imageData = substr($signature, strpos($signature, ',') + 1);
                $imagePath = $signaturePath . "{$key}_{$notification_number}.png";
                file_put_contents($imagePath, base64_decode($imageData));
                $lhpp->$key = asset("storage/signatures/lhpp/{$key}_{$notification_number}.png");
            }
        }

        $pdf = Pdf::loadView('pkm.lhpp.lhpppdf', compact('lhpp'));

        return $pdf->stream("LHPP_{$notification_number}.pdf");
    }
}
