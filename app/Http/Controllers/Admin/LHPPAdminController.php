<?php

namespace App\Http\Controllers\Admin;

use Barryvdh\DomPDF\Facade\Pdf; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LHPP;
use App\Models\User; 
use App\Models\Garansi;
use Illuminate\Support\Facades\Http; // Untuk API Fonnte
use Illuminate\Support\Facades\Log; // Untuk logging
use Carbon\Carbon;           // <<-- tambah
use Illuminate\Support\Facades\Auth;

class LHPPAdminController extends Controller
{
    /**
     * Tampilkan daftar LHPP di admin.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Jika ada pencarian, filter berdasarkan nomor order atau unit kerja peminta
        $lhpps = LHPP::when($search, function ($query) use ($search) {
            return $query->where('notification_number', 'LIKE', "%{$search}%")
                         ->orWhere('unit_kerja', 'LIKE', "%{$search}%");
        })->paginate(10);

        return view('admin.lhpp', compact('lhpps'));
    }

    /**
     * Menampilkan detail LHPP berdasarkan nomor notifikasi.
     */
    public function show($notification_number)
    {
        $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();
    
        
        // Cek apakah field adalah string sebelum json_decode
        $lhpp->material_description = is_string($lhpp->material_description) ? json_decode($lhpp->material_description) : $lhpp->material_description;
        $lhpp->material_volume = is_string($lhpp->material_volume) ? json_decode($lhpp->material_volume) : $lhpp->material_volume;
        $lhpp->material_harga_satuan = is_string($lhpp->material_harga_satuan) ? json_decode($lhpp->material_harga_satuan) : $lhpp->material_harga_satuan;
        $lhpp->material_jumlah = is_string($lhpp->material_jumlah) ? json_decode($lhpp->material_jumlah) : $lhpp->material_jumlah;
    
        $lhpp->consumable_description = is_string($lhpp->consumable_description) ? json_decode($lhpp->consumable_description) : $lhpp->consumable_description;
        $lhpp->consumable_volume = is_string($lhpp->consumable_volume) ? json_decode($lhpp->consumable_volume) : $lhpp->consumable_volume;
        $lhpp->consumable_harga_satuan = is_string($lhpp->consumable_harga_satuan) ? json_decode($lhpp->consumable_harga_satuan) : $lhpp->consumable_harga_satuan;
        $lhpp->consumable_jumlah = is_string($lhpp->consumable_jumlah) ? json_decode($lhpp->consumable_jumlah) : $lhpp->consumable_jumlah;
    
        $lhpp->upah_description = is_string($lhpp->upah_description) ? json_decode($lhpp->upah_description) : $lhpp->upah_description;
        $lhpp->upah_volume = is_string($lhpp->upah_volume) ? json_decode($lhpp->upah_volume) : $lhpp->upah_volume;
        $lhpp->upah_harga_satuan = is_string($lhpp->upah_harga_satuan) ? json_decode($lhpp->upah_harga_satuan) : $lhpp->upah_harga_satuan;
        $lhpp->upah_jumlah = is_string($lhpp->upah_jumlah) ? json_decode($lhpp->upah_jumlah) : $lhpp->upah_jumlah;

        // ✅ Decode JSON untuk `images`
        $lhpp->images = is_string($lhpp->images) ? json_decode($lhpp->images, true) : $lhpp->images;
    
        return view('pkm.lhpp.show', compact('lhpp'));
    }
   /**
 * Approve LHPP dari Admin.
 */
/**
 * Simpan / update / hapus garansi yang dipicu dari halaman LHPP.
 *
 * - Jika input garansi_months kosong => hapus record garansi (jika ada)
 * - Jika ada nilai (0..12) => createOrUpdate garansi dan hitung end_date bila memungkinkan
 */
public function storeGaransi(Request $request, $notification_number)
{
    $request->validate([
        'garansi_months' => 'nullable|integer|min:0|max:120', // allow up to 120 bulan jika dimau
        'garansi_label'  => 'nullable|string|max:255',
        'lpj_number'     => 'nullable|string', // hidden field dari blade (tidak wajib)
        'redirect_to'    => 'nullable|string',
    ]);

    try {
        $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();

        // Ambil value; treat empty string as null (karena select bisa mengirim "")
        $monthsRaw = $request->input('garansi_months', null);
        $garansiMonths = ($monthsRaw === '' ? null : (is_null($monthsRaw) ? null : (int)$monthsRaw));
        $garansiLabel  = $request->input('garansi_label', null);

        // Jika tidak ada nilai garansi (user memilih kosong) -> hapus record garansi (jika ada)
        if ($garansiMonths === null) {
            $deleted = Garansi::where('notification_number', $notification_number)->delete();
            $msg = $deleted ? 'Garansi dihapus.' : 'Tidak ada data garansi untuk dihapus.';
            $redirect = $request->input('redirect_to') ?? route('admin.lhpp.index');
            return redirect($redirect)->with('success', $msg);
        }

        // Tentukan start_date: ambil dari LHPP.tanggal_selesai bila tersedia
        $startDate = null;
        if (!empty($lhpp->tanggal_selesai)) {
            // Jika LHPP->tanggal_selesai string, biar Carbon parsing
            try {
                $startDate = Carbon::parse($lhpp->tanggal_selesai)->startOfDay();
            } catch (\Throwable $e) {
                $startDate = null;
            }
        }

        // Hitung end_date kalau start_date ada; jika tidak ada, end_date tetap null
        $endDate = null;
        if ($startDate) {
            // jika months = 0, kita set end_date = startDate (sesuai kebijakan)
            try {
                $endDate = (clone $startDate)->addMonthsNoOverflow($garansiMonths);
            } catch (\Throwable $e) {
                // fallback
                try {
                    $endDate = (clone $startDate)->addMonths($garansiMonths);
                } catch (\Throwable $e2) {
                    $endDate = null;
                }
            }
        }

        // tentukan status
        $status = 'belum_dimulai';
        if ($startDate && $endDate) {
            $now = Carbon::now()->startOfDay();
            $status = $now->lessThanOrEqualTo($endDate) ? 'masih_berlaku' : 'habis';
        } elseif ($startDate && $garansiMonths === 0) {
            // months == 0 treated as same-day end -> masih_berlaku jika today == startDate
            $now = Carbon::now()->startOfDay();
            $status = $now->lessThanOrEqualTo($startDate) ? 'masih_berlaku' : 'habis';
        }

        // simpan ke tabel garansis (create or update by notification_number)
        $garansi = Garansi::updateOrCreate(
            ['notification_number' => $notification_number],
            [
                'garansi_months' => $garansiMonths,
                'garansi_label'  => $garansiLabel,
                'start_date'     => $startDate ? $startDate->toDateString() : null,
                'end_date'       => $endDate ? $endDate->toDateString() : null,
                'status'         => $status,
                'created_by'     => Auth::id() ?? null,
                'updated_by'     => Auth::id() ?? null,
            ]
        );

        $redirect = $request->input('redirect_to') ?? route('admin.lhpp.index');
        return redirect($redirect)->with('success', 'Data garansi berhasil disimpan.');

    } catch (\Exception $e) {
        Log::error('storeGaransi failed for '.$notification_number, [
            'err' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);

        $redirect = $request->input('redirect_to') ?? route('admin.lhpp.index');
        return redirect($redirect)->with('error', 'Gagal menyimpan data garansi. Cek log.');
    }
}

public function approve($notification_number)
{
    $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();

    // Update status approval oleh Admin
    $lhpp->status_approve = 'Approved'; 
    $lhpp->save();

    // Kirim notifikasi WhatsApp ke Manager setelah dokumen disetujui oleh Admin
    $this->sendWhatsAppToManager($lhpp);

    return redirect()->route('admin.lhpp.index')->with('success', 'LHPP telah disetujui oleh Admin.');
}

/**
 * Mengirimkan notifikasi WhatsApp ke Manager setelah Admin menyetujui LHPP.
 */
private function sendWhatsAppToManager($lhpp)
{
    // Kirim notifikasi WhatsApp ke Manager dengan unit_work "Unit Of Workshop"
    $managers = User::where('unit_work', $lhpp->unit_kerja)
    ->where('jabatan', 'Manager')
    ->get();

    foreach ($managers as $manager) {
        try {
            $message = "Permintaan Approval Pembuatan LHPP:\nNomor Notifikasi: {$lhpp->notification_number}\nDeskripsi: {$lhpp->description_notifikasi}\nUnit Kerja: {$lhpp->unit_kerja}\n\nSilakan login untuk melihat detailnya:\nhttps://sectionofworkshop.com/approval/lhpp";

            Http::withHeaders([
                'Authorization' => 'KBTe2RszCgc6aWhYapcv' // API key Fonnte Anda
            ])->post('https://api.fonnte.com/send', [
                'target' => $manager->whatsapp_number,
                'message' => $message,
            ]);

            \Log::info("WhatsApp notification sent to Manager: " . $manager->whatsapp_number);
        } catch (\Exception $e) {
            \Log::error("Gagal mengirim WhatsApp ke {$manager->whatsapp_number}: " . $e->getMessage());
        }
    }
}


public function reject(Request $request, $notification_number)
{
    $request->validate([
        'rejection_reason' => 'required|string|max:255',
    ]);

    $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();

    // Update status reject oleh Admin sesuai ENUM yang valid
    $lhpp->status_approve = 'Rejected'; // GANTI dari 'Rejected by Admin' ke 'Rejected'
    $lhpp->rejection_reason = $request->rejection_reason;
    $lhpp->save();

    return redirect()->route('admin.lhpp.index')->with('error', 'LHPP ditolak dengan alasan: ' . $request->rejection_reason);
}

public function downloadPDF($notification_number)
{
    $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();

    // Pastikan direktori signatures ada
    $signaturePath = storage_path("app/public/signatures/");
    if (!file_exists($signaturePath)) {
        mkdir($signaturePath, 0777, true); // Buat direktori jika belum ada
    }

    // Konversi tanda tangan dari Base64 ke file sementara
    $signatures = [
        'manager_signature' => $lhpp->manager_signature,
        'manager_signature_requesting' => $lhpp->manager_signature_requesting,
        'manager_pkm_signature' => $lhpp->manager_pkm_signature,
    ];

    foreach ($signatures as $key => $signature) {
        if (!empty($signature) && str_starts_with($signature, 'data:image')) {
            $imageData = substr($signature, strpos($signature, ',') + 1);
            $imagePath = $signaturePath . "{$key}_{$notification_number}.png";

            // Simpan gambar Base64 ke penyimpanan sementara
            file_put_contents($imagePath, base64_decode($imageData));

            // Update path untuk digunakan dalam Blade
            $lhpp->$key = $imagePath;
        }
    }

    // Load view untuk PDF
    $pdf = Pdf::loadView('pkm.lhpp.lhpppdf', compact('lhpp'));

    return $pdf->stream("LHPP_{$notification_number}.pdf");
}


}
