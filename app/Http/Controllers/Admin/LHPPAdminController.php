<?php

namespace App\Http\Controllers\Admin;

use Barryvdh\DomPDF\Facade\Pdf; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LHPP;
use App\Models\Garansi;
use Illuminate\Support\Facades\Log; // Untuk logging
use Carbon\Carbon;           // <<-- tambah
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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

        $notificationNumbers = $lhpps->pluck('notification_number')->unique()->values();
        $garansiMap = Garansi::whereIn('notification_number', $notificationNumbers)
            ->get()
            ->keyBy('notification_number');

        return view('admin.lhpp', compact('lhpps', 'garansiMap'));
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
    public function approve($notification_number)
    {
        $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();

        // Update status approval oleh Admin
        $lhpp->status_approve = 'Approved';
        $lhpp->save();

        return redirect()->route('admin.lhpp.index')->with('success', 'LHPP telah disetujui oleh Admin.');
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

public function storeGaransi(Request $request, $notification_number)
{
    $validated = $request->validate([
        'garansi_months' => 'nullable|integer|min:0|max:12',
        'garansi_label'  => 'nullable|string|max:255',
        'redirect_to'    => 'nullable|string',
    ]);

    $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();

    $garansiMonths = $validated['garansi_months'] ?? null;
    $redirectTo = $validated['redirect_to'] ?? route('admin.lhpp.index');

    if ($garansiMonths === null) {
        Garansi::where('notification_number', $notification_number)->delete();
        return redirect($redirectTo)->with('success', 'Garansi berhasil dihapus.');
    }

    $hasAllSignatures = false;
    if (method_exists($lhpp, 'hasAllSignatures')) {
        $hasAllSignatures = (bool) $lhpp->hasAllSignatures();
    } else {
        $a = !empty($lhpp->manager_signature) || !empty($lhpp->manager_signature_user_id);
        $b = !empty($lhpp->manager_signature_requesting) || !empty($lhpp->manager_signature_requesting_user_id);
        $c = !empty($lhpp->manager_pkm_signature) || !empty($lhpp->manager_pkm_signature_user_id);
        $hasAllSignatures = ($a && $b && $c);
    }

    $startDate = null;
    if ($hasAllSignatures && !empty($lhpp->tanggal_selesai)) {
        try {
            $startDate = Carbon::parse($lhpp->tanggal_selesai)->startOfDay();
        } catch (\Throwable $e) {
            $startDate = null;
        }
    }

    $endDate = null;
    $status = 'belum_dimulai';

    if ($startDate !== null) {
        if ((int) $garansiMonths === 0) {
            $endDate = (clone $startDate);
            $status = 'habis';
        } else {
            try {
                $endDate = (clone $startDate)->addMonthsNoOverflow((int) $garansiMonths);
            } catch (\Throwable $e) {
                $endDate = (clone $startDate)->addMonths((int) $garansiMonths);
            }

            if ($endDate) {
                $status = Carbon::now()->startOfDay()->lessThanOrEqualTo($endDate)
                    ? 'masih_berlaku'
                    : 'habis';
            }
        }
    }

    $payload = [
        'garansi_months' => (int) $garansiMonths,
        'start_date'     => $startDate ? $startDate->toDateString() : null,
        'end_date'       => $endDate ? $endDate->toDateString() : null,
        'status'         => $status,
    ];

    if (Schema::hasColumn('garansis', 'garansi_label')) {
        $payload['garansi_label'] = $validated['garansi_label'] ?? null;
    }

    Garansi::updateOrCreate(
        ['notification_number' => $notification_number],
        $payload
    );

    return redirect($redirectTo)->with('success', 'Garansi berhasil disimpan.');
}

}
