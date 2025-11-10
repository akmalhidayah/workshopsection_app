<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LHPP;
use App\Models\Notification;
use App\Models\User;
use App\Models\Abnormal;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;


class LHPPController extends Controller
{
    public function index()
    {
        // Ambil data LHPP dengan pagination
        $lhpps = LHPP::paginate(10); // Ambil 10 data per halaman
        
        // Kirim data ke view
        return view('pkm.lhpp.index', compact('lhpps'));
    }
    
    /**
     * Menampilkan form LHPP
     */
    public function create()
    {
        // Ambil notifikasi yang belum ada di tabel 'lhpp' DAN sudah memiliki HPP & PO
        $notifications = Notification::whereNotIn('notification_number', function ($query) {
            $query->select('notification_number')->from('lhpp');
        })
        ->whereHas('hpp1') // harus sudah punya HPP
        ->whereHas('purchaseOrder', function ($q) {
            $q->whereNotNull('po_document_path'); // PO wajib ada dan ada filenya
        })
        ->get();
    
        return view('pkm.lhpp.create', compact('notifications'));
    }
    
    

    /**
     * Menyimpan data dari form LHPP
     */
    public function store(Request $request)
    {
        // Validasi input form
        $validated = $request->validate([
            'notification_number' => 'required|string|max:255',
            'nomor_order' => 'nullable|string|max:255',
            'description_notifikasi' => 'nullable|string',
            'purchase_order_number' => 'required|string|max:255',
            'unit_kerja' => 'required|string|max:255',
            'tanggal_selesai' => 'required|date',
            'waktu_pengerjaan' => 'required|integer',

             // Validasi array untuk material
        'material_description' => 'nullable|array',
        'material_volume' => 'nullable|array',
        'material_harga_satuan' => 'nullable|array',
        'material_jumlah' => 'nullable|array',

        // Validasi array untuk consumable
        'consumable_description' => 'nullable|array',
        'consumable_volume' => 'nullable|array',
        'consumable_harga_satuan' => 'nullable|array',
        'consumable_jumlah' => 'nullable|array',

        // Validasi array untuk upah kerja
        'upah_description' => 'nullable|array',
        'upah_volume' => 'nullable|array',
        'upah_harga_satuan' => 'nullable|array',
        'upah_jumlah' => 'nullable|array',

        // Subtotal dan total
        'material_subtotal' => 'nullable|numeric',
        'consumable_subtotal' => 'nullable|numeric',
        'upah_subtotal' => 'nullable|numeric',
        'total_biaya' => 'nullable|numeric',


            // Kontrak PKM
            'kontrak_pkm' => 'required|string|in:Fabrikasi,Konstruksi,Pengerjaan Mesin',

            // Validasi gambar
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_descriptions' => 'nullable|array',
            
         
     ]);
 
     // Simpan multiple images + keterangan gambar
     $imageData = [];
     if ($request->hasFile('images')) {
         foreach ($request->file('images') as $key => $image) {
             $path = $image->store('lhpp_images', 'public');
             $description = $request->image_descriptions[$key] ?? null;
             
             $imageData[] = [
                 'path' => $path,
                 'description' => $description
             ];
         }
     }

        try {
            // Simpan data ke database
            LHPP::create([
                'notification_number' => $validated['notification_number'],
                'nomor_order' => $validated['nomor_order'],
                'description_notifikasi' => $validated['description_notifikasi'],
                'purchase_order_number' => $validated['purchase_order_number'],
                'unit_kerja' => $validated['unit_kerja'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'waktu_pengerjaan' => $validated['waktu_pengerjaan'],

                // Material data
                'material_description' => $validated['material_description'],
                'material_volume' => $validated['material_volume'],
                'material_harga_satuan' => $validated['material_harga_satuan'],
                'material_jumlah' => $validated['material_jumlah'],

                // Consumable data
                'consumable_description' => $validated['consumable_description'],
                'consumable_volume' => $validated['consumable_volume'],
                'consumable_harga_satuan' => $validated['consumable_harga_satuan'],
                'consumable_jumlah' => $validated['consumable_jumlah'],

                // Upah data
                'upah_description' => $validated['upah_description'],
                'upah_volume' => $validated['upah_volume'],
                'upah_harga_satuan' => $validated['upah_harga_satuan'],
                'upah_jumlah' => $validated['upah_jumlah'],

                // Subtotal dan total
                'material_subtotal' => $validated['material_subtotal'],
                'consumable_subtotal' => $validated['consumable_subtotal'],
                'upah_subtotal' => $validated['upah_subtotal'],
                'total_biaya' => $validated['total_biaya'],

                // Kontrak PKM
                'kontrak_pkm' => $validated['kontrak_pkm'],
                

            // Simpan path gambar dalam JSON
            'images' => count($imageData) > 0 ? json_encode($imageData) : null,
            ]);
            

            // Kirim notifikasi WhatsApp ke Manager PKM berdasarkan kontrak PKM yang dipilih
            $managers = User::where('unit_work', $validated['kontrak_pkm'])
                            ->where('jabatan', 'Manager')
                            ->get();

            foreach ($managers as $manager) {
                try {
                    $message = "Permintaan Approval Pembuatan LHPP:\nNomor Notifikasi: {$validated['notification_number']}\nDeskripsi: {$validated['description_notifikasi']}\nUnit Kerja: {$validated['unit_kerja']}\n\nSilakan login untuk melihat detailnya:\nhttps://sectionofworkshop.com/approval/lhpp";

                    Http::withHeaders([
                        'Authorization' => 'KBTe2RszCgc6aWhYapcv' // API key Fonnte Anda
                    ])->post('https://api.fonnte.com/send', [
                        'target' => $manager->whatsapp_number,
                        'message' => $message,
                    ]);

                    \Log::info("WhatsApp notification sent to Manager PKM: " . $manager->whatsapp_number);
                } catch (\Exception $e) {
                    \Log::error("Gagal mengirim WhatsApp ke {$manager->whatsapp_number}: " . $e->getMessage());
                }
            }

            // Cek apakah Manager PKM sudah tanda tangan, jika ya kirim WA ke Admin
            $lhpp = LHPP::where('notification_number', $validated['notification_number'])->first();

            if (!empty($lhpp) && !empty($lhpp->manager_pkm_signature)) {
                $this->sendWhatsAppToAdmin($lhpp);
            }

            return redirect()->route('pkm.lhpp.index')->with('success', 'Data LHPP berhasil disimpan.');
        } catch (\Exception $e) {
            \Log::error("Error saving LHPP: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data LHPP Harap Mengisi dengan Benar.')->withInput();
        }
    }
    private function sendWhatsAppToAdmin($lhpp)
{
    $admins = User::where('usertype', 'admin')->get();

    foreach ($admins as $admin) {
        try {
            $message = "Review LHPP Diperlukan:\nNomor Notifikasi: {$lhpp->notification_number}\nDeskripsi: {$lhpp->description_notifikasi}\nUnit Kerja: {$lhpp->unit_kerja}\n\nSilakan login untuk melakukan review:\nhttps://sectionofworkshop.com/admin/lhpp";

            Http::withHeaders([
                'Authorization' => 'KBTe2RszCgc6aWhYapcv',
            ])->post('https://api.fonnte.com/send', [
                'target' => $admin->whatsapp_number,
                'message' => $message,
            ]);

            \Log::info("WhatsApp notification sent to Admin: " . $admin->whatsapp_number);
        } catch (\Exception $e) {
            \Log::error("Gagal mengirim WhatsApp ke Admin {$admin->whatsapp_number}: " . $e->getMessage());
        }
    }
}

public function getPurchaseOrder($notificationNumber)
{
    try {
        $notification = Notification::with('purchaseOrder')
            ->where('notification_number', $notificationNumber)
            ->first();

        if (!$notification || !$notification->purchaseOrder) {
            \Log::error("Purchase Order tidak ditemukan untuk notification_number: $notificationNumber");
            return response()->json(['error' => 'Purchase Order tidak ditemukan'], 404);
        }

        return response()->json([
            'purchase_order_number' => $notification->purchaseOrder->purchase_order_number ?? null
        ]);
    } catch (\Exception $e) {
        \Log::error("Error fetching purchase order: " . $e->getMessage());
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
            \Log::error("Nomor Order tidak ditemukan atau belum memiliki HPP dan PO untuk notification_number: $notificationNumber");
            return response()->json(['error' => 'Nomor Order tidak ditemukan atau belum memiliki HPP dan PO'], 404);
        }

        return response()->json([
            'nomor_order' => $nomorOrder->nomor_order ?? null
        ]);
    } catch (\Exception $e) {
        \Log::error("Error fetching nomor order: " . $e->getMessage());
        return response()->json(['error' => 'Terjadi kesalahan di server'], 500);
    }
}

public function getJobName($notificationNumber)
{
    try {
        $notification = \App\Models\Notification::where('notification_number', $notificationNumber)->first();

        if (!$notification) {
            \Log::error("Job name tidak ditemukan untuk notification_number: $notificationNumber");
            return response()->json(['error' => 'Job name tidak ditemukan'], 404);
        }

        return response()->json([
            'job_name' => $notification->job_name ?? '-'
        ]);
    } catch (\Exception $e) {
        \Log::error("Error fetching job name: " . $e->getMessage());
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
            $selesai = new \DateTime($tanggalSelesai);
    
            // Hitung selisih hari antara update PO dan tanggal selesai
            $diff = $updateDate->diff($selesai)->days;
    
            return response()->json(['waktu_pengerjaan' => $diff]);
        }
    
        return response()->json(['waktu_pengerjaan' => 0]);
    }
    

    public function show($notification_number)
    {
        $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();
    
        // Decode JSON jika masih dalam bentuk string
        $lhpp->material_description = is_string($lhpp->material_description) ? json_decode($lhpp->material_description, true) : $lhpp->material_description;
        $lhpp->material_volume = is_string($lhpp->material_volume) ? json_decode($lhpp->material_volume, true) : $lhpp->material_volume;
        $lhpp->material_harga_satuan = is_string($lhpp->material_harga_satuan) ? json_decode($lhpp->material_harga_satuan, true) : $lhpp->material_harga_satuan;
        $lhpp->material_jumlah = is_string($lhpp->material_jumlah) ? json_decode($lhpp->material_jumlah, true) : $lhpp->material_jumlah;
    
        $lhpp->consumable_description = is_string($lhpp->consumable_description) ? json_decode($lhpp->consumable_description, true) : $lhpp->consumable_description;
        $lhpp->consumable_volume = is_string($lhpp->consumable_volume) ? json_decode($lhpp->consumable_volume, true) : $lhpp->consumable_volume;
        $lhpp->consumable_harga_satuan = is_string($lhpp->consumable_harga_satuan) ? json_decode($lhpp->consumable_harga_satuan, true) : $lhpp->consumable_harga_satuan;
        $lhpp->consumable_jumlah = is_string($lhpp->consumable_jumlah) ? json_decode($lhpp->consumable_jumlah, true) : $lhpp->consumable_jumlah;
    
        $lhpp->upah_description = is_string($lhpp->upah_description) ? json_decode($lhpp->upah_description, true) : $lhpp->upah_description;
        $lhpp->upah_volume = is_string($lhpp->upah_volume) ? json_decode($lhpp->upah_volume, true) : $lhpp->upah_volume;
        $lhpp->upah_harga_satuan = is_string($lhpp->upah_harga_satuan) ? json_decode($lhpp->upah_harga_satuan, true) : $lhpp->upah_harga_satuan;
        $lhpp->upah_jumlah = is_string($lhpp->upah_jumlah) ? json_decode($lhpp->upah_jumlah, true) : $lhpp->upah_jumlah;
    
        // ✅ Pastikan semua nilai harga dan jumlah dikonversi ke float agar tidak error saat number_format()
        $lhpp->material_harga_satuan = array_map('floatval', $lhpp->material_harga_satuan);
        $lhpp->material_jumlah = array_map('floatval', $lhpp->material_jumlah);
        $lhpp->consumable_harga_satuan = array_map('floatval', $lhpp->consumable_harga_satuan);
        $lhpp->consumable_jumlah = array_map('floatval', $lhpp->consumable_jumlah);
        $lhpp->upah_harga_satuan = array_map('floatval', $lhpp->upah_harga_satuan);
        $lhpp->upah_jumlah = array_map('floatval', $lhpp->upah_jumlah);
    
        // ✅ Decode JSON untuk `images`
        $lhpp->images = is_string($lhpp->images) ? json_decode($lhpp->images, true) : $lhpp->images;
    
        return view('pkm.lhpp.show', compact('lhpp'));
    }
    
    public function edit($id)
    {
        // Ambil data LHPP berdasarkan id/notification_number
        $lhpp = LHPP::findOrFail($id);
    
        // Pastikan semua JSON field di-decode ke array
        $lhpp->material_description = is_string($lhpp->material_description) ? json_decode($lhpp->material_description, true) : ($lhpp->material_description ?? []);
        $lhpp->material_volume = is_string($lhpp->material_volume) ? json_decode($lhpp->material_volume, true) : ($lhpp->material_volume ?? []);
        $lhpp->material_harga_satuan = is_string($lhpp->material_harga_satuan) ? json_decode($lhpp->material_harga_satuan, true) : ($lhpp->material_harga_satuan ?? []);
        $lhpp->material_jumlah = is_string($lhpp->material_jumlah) ? json_decode($lhpp->material_jumlah, true) : ($lhpp->material_jumlah ?? []);
    
        // **Pastikan images selalu berupa array**
        $lhpp->images = is_string($lhpp->images) ? json_decode($lhpp->images, true) : ($lhpp->images ?? []);
    
        return view('pkm.lhpp.edit', compact('lhpp'));
    }
    
    public function update(Request $request, $id)
{
    // Validasi input form
    $validated = $request->validate([
        'nomor_order' => 'required|string|max:255',
        'description_notifikasi' => 'nullable|string',
        'purchase_order_number' => 'required|string|max:255',
        'unit_kerja' => 'required|string|max:255',
        'tanggal_selesai' => 'required|date',
        'waktu_pengerjaan' => 'required|integer',

        // Validasi array untuk material
        'material_description' => 'nullable|array',
        'material_volume' => 'nullable|array',
        'material_harga_satuan' => 'nullable|array',
        'material_jumlah' => 'nullable|array',

        // Validasi array untuk consumable
        'consumable_description' => 'nullable|array',
        'consumable_volume' => 'nullable|array',
        'consumable_harga_satuan' => 'nullable|array',
        'consumable_jumlah' => 'nullable|array',

        // Validasi array untuk upah kerja
        'upah_description' => 'nullable|array',
        'upah_volume' => 'nullable|array',
        'upah_harga_satuan' => 'nullable|array',
        'upah_jumlah' => 'nullable|array',

        // Subtotal dan total
        'material_subtotal' => 'nullable|numeric',
        'consumable_subtotal' => 'nullable|numeric',
        'upah_subtotal' => 'nullable|numeric',
        'total_biaya' => 'nullable|numeric',

        // Validasi gambar baru (opsional)
        'new_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Cari data LHPP yang akan di-update
    $lhpp = LHPP::findOrFail($id);

    // **1. Hapus gambar yang dipilih untuk dihapus**
    if ($request->has('delete_images')) {
        $deletedPaths = $request->delete_images;

        // Decode images jika masih dalam bentuk string
        $images = is_string($lhpp->images) ? json_decode($lhpp->images, true) : ($lhpp->images ?? []);

        // Hapus gambar dari storage dan filter array
        foreach ($deletedPaths as $path) {
            Storage::disk('public')->delete($path);
            $images = array_filter($images, fn($image) => $image['path'] !== $path);
        }

        // Simpan kembali daftar gambar yang tersisa
        $lhpp->images = json_encode(array_values($images));
    }

    // **2. Tambahkan gambar baru jika ada**
    if ($request->hasFile('new_images')) {
        $images = is_string($lhpp->images) ? json_decode($lhpp->images, true) : ($lhpp->images ?? []);

        foreach ($request->file('new_images') as $image) {
            $path = $image->store('lhpp_images', 'public');
            $images[] = ['path' => $path, 'description' => '']; // Bisa tambahkan input untuk deskripsi gambar
        }

        $lhpp->images = json_encode($images);
    }

    // Update data dengan input baru
    $lhpp->update($validated);

    return redirect()->route('pkm.lhpp.index')->with('success', 'Data LHPP berhasil diperbarui.');
}

public function deleteImage(Request $request)
{
    $request->validate([
        'image_path' => 'required|string',
        'lhpp_id' => 'required|integer'
    ]);

    $lhpp = LHPP::findOrFail($request->lhpp_id);

    // Decode JSON jika masih dalam bentuk string dan pastikan tidak null
    $images = is_string($lhpp->images) ? json_decode($lhpp->images, true) : ($lhpp->images ?? []);

    // Pastikan gambar yang ingin dihapus ada dalam database
    $imageExists = array_filter($images, fn($image) => $image['path'] === $request->image_path);

    if (empty($imageExists)) {
        return response()->json(['success' => false, 'message' => 'Gambar tidak ditemukan dalam database'], 404);
    }

    // Hapus gambar dari storage
    if (Storage::disk('public')->exists($request->image_path)) {
        Storage::disk('public')->delete($request->image_path);
    }

    // Filter array untuk menyimpan hanya gambar yang tidak dihapus
    $images = array_filter($images, fn($image) => $image['path'] !== $request->image_path);

    // Update database tanpa gambar yang dihapus
    $lhpp->update([
        'images' => json_encode(array_values($images))
    ]);

    return response()->json(['success' => true, 'message' => 'Gambar berhasil dihapus']);
}

public function destroy($notification_number)
{
    // Cari data berdasarkan notification_number
    $lhpp = LHPP::findOrFail($notification_number);

    // Hapus data
    $lhpp->delete();

    // Redirect kembali ke halaman index dengan pesan sukses
    return redirect()->route('pkm.lhpp.index')->with('success', 'Data berhasil dihapus.');
}

public function downloadPDF($notification_number)
{
    $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();

    // Pastikan direktori signatures/lhpp ada
    $signaturePath = storage_path("app/public/signatures/lhpp/");
    if (!file_exists($signaturePath)) {
        mkdir($signaturePath, 0777, true); // Buat direktori jika belum ada
    }

    // Konversi tanda tangan dari Base64 ke file sementara di dalam folder /lhpp/
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
            $lhpp->$key = asset("storage/signatures/lhpp/{$key}_{$notification_number}.png");
        }
    }

    // Load view untuk PDF
    $pdf = Pdf::loadView('pkm.lhpp.lhpppdf', compact('lhpp'));

    return $pdf->stream("LHPP_{$notification_number}.pdf");
}

    }
