<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lpj;
use App\Models\LHPP;
use App\Models\User;
use Illuminate\Support\Facades\Http; // Untuk API Fonnte
use Illuminate\Support\Facades\Log; // Untuk logging
use Illuminate\Support\Facades\DB;

class LHPPApprovalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
    
        // Ambil daftar dokumen LHPP yang sudah memiliki LPJ & PPL lengkap
        $lpjCompletedNotifications = DB::table('lpjs')
            ->whereNotNull('lpj_number')
            ->whereNotNull('ppl_number')
            ->whereNotNull('lpj_document_path')
            ->whereNotNull('ppl_document_path')
            ->pluck('notification_number')
            ->toArray();
    
        // 🔹 1️⃣ Manager PKM hanya melihat LHPP yang belum ditandatangani oleh Manager PKM
        if (in_array($user->unit_work, ['Fabrikasi', 'Konstruksi', 'Pengerjaan Mesin'])) {
            $lhppDocuments = LHPP::where(function ($query) use ($user, $lpjCompletedNotifications) {
                    $query->whereNull('manager_pkm_signature') // Manager PKM belum tanda tangan
                          ->whereNull('manager_signature_requesting') // Manager Requesting belum tanda tangan
                          ->whereNull('manager_signature') // Manager Workshop belum tanda tangan
                          ->where('kontrak_pkm', $user->unit_work) // Kontrak PKM cocok dengan unit kerja user
                          ->whereNotIn('notification_number', $lpjCompletedNotifications); // ❌ Hapus yang sudah ada LPJ & PPL
                })
                ->orderByRaw("CASE 
                    WHEN manager_pkm_signature_user_id IS NULL THEN 0 
                    WHEN manager_signature_requesting_user_id IS NULL THEN 1 
                    WHEN manager_signature_user_id IS NULL THEN 2 
                    ELSE 3 
                END")
                ->get();
        } 
        // 🔹 2️⃣ Manager Requesting hanya melihat LHPP setelah Manager PKM tanda tangan, tetapi Manager Workshop belum
        elseif ($user->unit_work === auth()->user()->unit_work) {
            $lhppDocuments = LHPP::where('unit_kerja', $user->unit_work)
                ->where(function ($query) use ($lpjCompletedNotifications) {
                    $query->whereNotNull('manager_pkm_signature') // Manager PKM sudah tanda tangan
                          ->whereNull('manager_signature_requesting') // Manager Peminta belum tanda tangan
                          ->whereNull('manager_signature') // Manager Workshop belum tanda tangan
                          ->whereNotIn('notification_number', $lpjCompletedNotifications); // ❌ Hapus yang sudah ada LPJ & PPL
                })
                ->orderByRaw("CASE 
                    WHEN manager_pkm_signature_user_id IS NULL THEN 0 
                    WHEN manager_signature_requesting_user_id IS NULL THEN 1 
                    WHEN manager_signature_user_id IS NULL THEN 2 
                    ELSE 3 
                END")
                ->get();
        }
        // 🔹 3️⃣ Manager Workshop hanya melihat LHPP setelah Manager PKM & Manager Requesting tanda tangan
        elseif ($user->unit_work === 'Unit Of Workshop') {
            $lhppDocuments = LHPP::whereNotNull('manager_pkm_signature') // Manager PKM sudah tanda tangan
                ->whereNotNull('manager_signature_requesting') // Manager Requesting sudah tanda tangan
                ->whereNull('manager_signature') // Manager Workshop belum tanda tangan
                ->whereNotIn('notification_number', $lpjCompletedNotifications) // ❌ Hapus yang sudah ada LPJ & PPL
                ->orderByRaw("CASE 
                    WHEN manager_pkm_signature_user_id IS NULL THEN 0 
                    WHEN manager_signature_requesting_user_id IS NULL THEN 1 
                    WHEN manager_signature_user_id IS NULL THEN 2 
                    ELSE 3 
                END")
                ->get();
        } else {
            // Jika user tidak masuk dalam kategori manajer di atas
            $lhppDocuments = collect();
        }
    
        // Kirim data LHPP ke view
        return view('approval.lhpp.index', compact('lhppDocuments'));
    }
    
    
    
public function saveSignature(Request $request, $signType, $notificationNumber)
{
    try {
        $request->validate([
            'tanda_tangan' => 'required',
        ]);

        // Ambil dokumen LHPP berdasarkan nomor notifikasi
        $lhpp = LHPP::where('notification_number', $notificationNumber)->firstOrFail();

        // 🔹 Urutan: Manager PKM → Admin → Manager Peminta → Manager Workshop
        if ($signType === 'manager_pkm') {
            $lhpp->manager_pkm_signature = $request->tanda_tangan;
            $lhpp->manager_pkm_signature_user_id = auth()->user()->id;

            // ✅ Kirim WhatsApp ke Admin setelah Manager PKM tanda tangan
            $this->sendWhatsAppNotification($lhpp, 'admin');

            // ✅ Kirim WhatsApp ke Manager Peminta setelah Admin menerima notifikasi
            $this->sendWhatsAppNotification($lhpp, 'manager_requesting');

        } elseif ($signType === 'manager_requesting') {
            $lhpp->manager_signature_requesting = $request->tanda_tangan;
            $lhpp->manager_signature_requesting_user_id = auth()->user()->id;

            // ✅ Kirim WhatsApp ke Manager Workshop setelah Manager Peminta tanda tangan
            $this->sendWhatsAppNotification($lhpp, 'manager');

        } elseif ($signType === 'manager') {
            $lhpp->manager_signature = $request->tanda_tangan;
            $lhpp->manager_signature_user_id = auth()->user()->id;

            // ✅ Semua proses selesai, tidak perlu kirim WA lagi setelah Manager Workshop tanda tangan
        }

        // Simpan ke database
        $lhpp->save();

        return response()->json(['message' => 'Signature saved successfully!'], 200);
    } catch (\Exception $e) {
        Log::error($e->getMessage());
        return response()->json(['message' => 'Failed to save signature'], 500);
    }
}


private function sendWhatsAppNotification($lhpp)
{
    $role = 'Manager'; // Jabatan tetap sebagai "Manager"
    $unitWork = 'Unit Of Workshop'; // Kirim ke Manager Workshop

    Log::info("Sending WhatsApp Notification to $role in Unit Work: $unitWork");

    $managers = User::where('jabatan', $role)
                    ->where('unit_work', $unitWork)
                    ->get();

    if ($managers->isEmpty()) {
        Log::warning("No managers found for role $role in unit $unitWork.");
        return; // Jika tidak ada Manager, hentikan proses
    }

    foreach ($managers as $manager) {
        try {
            $message = "Permintaan Approval Dokumen LHPP:\nNomor Notifikasi: {$lhpp->notification_number}\nDeskripsi: {$lhpp->description_notifikasi}\nUnit Kerja: {$lhpp->unit_kerja}\n\nSilakan login untuk melihat detailnya:\nhttps://sectionofworkshop.com/approval/lhpp";

            $response = Http::withHeaders([
                'Authorization' => 'KBTe2RszCgc6aWhYapcv',
            ])->post('https://api.fonnte.com/send', [
                'target' => $manager->whatsapp_number,
                'message' => $message,
            ]);

            Log::info("Fonnte Response for {$manager->whatsapp_number}: ", $response->json());
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp to {$manager->whatsapp_number}: " . $e->getMessage());
        }
    }
}

     public function reject(Request $request, $signType, $notificationNumber)
    {
        // Validasi alasan penolakan
        $request->validate([
            'reason' => 'required|string',
        ]);
    
        // Cari dokumen LHPP berdasarkan nomor notifikasi
        $lhpp = LHPP::where('notification_number', $notificationNumber)->firstOrFail();
    
        // Simpan alasan penolakan dan tandai dokumen ditolak
        $lhpp->rejection_reason = $request->reason;
    
        // Update status reject berdasarkan tipe tanda tangan
        if ($signType === 'manager') {
            $lhpp->manager_signature = 'rejected';
            $lhpp->manager_signature_user_id = null;
        } elseif ($signType === 'manager_requesting') {
            $lhpp->manager_signature_requesting = 'rejected';
            $lhpp->manager_signature_requesting_user_id = null;
        } elseif ($signType === 'manager_pkm') {
            $lhpp->manager_pkm_signature = 'rejected';
            $lhpp->manager_pkm_signature_user_id = null;
        }
    
        // Simpan perubahan di database
        $lhpp->save();
    
        return response()->json(['message' => 'Document rejected successfully!'], 200);
    }

    public function saveNotes(Request $request, $notification_number, $type)
    {
        $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();
        
        // Debug: Log isi request dan isi kolom sebelum di-update
        \Log::info("Incoming Request", $request->all());
        \Log::info("Existing Controlling Notes", [$lhpp->controlling_notes]);
        \Log::info("Existing Requesting Notes", [$lhpp->requesting_notes]);
    
        if ($type == 'controlling') {
            $existingNotes = $lhpp->controlling_notes ? json_decode($lhpp->controlling_notes, true) : [];
            $newNotes = $request->input('controlling_notes');
            
            foreach (array_filter($newNotes) as $note) {
                $existingNotes[] = [
                    'note' => $note,
                    'user_id' => auth()->user()->id,
                ];
            }
            $lhpp->controlling_notes = json_encode($existingNotes);
        } elseif ($type == 'requesting') {
            $existingNotes = $lhpp->requesting_notes ? json_decode($lhpp->requesting_notes, true) : [];
            $newNotes = $request->input('requesting_notes');
            
            foreach (array_filter($newNotes) as $note) {
                $existingNotes[] = [
                    'note' => $note,
                    'user_id' => auth()->user()->id,
                ];
            }
            $lhpp->requesting_notes = json_encode($existingNotes);
        }
        
        // Debug: Log nilai setelah diperbarui
        \Log::info("Updated Controlling Notes", [$lhpp->controlling_notes]);
        \Log::info("Updated Requesting Notes", [$lhpp->requesting_notes]);
    
        $lhpp->save();
        
        return redirect()->back()->with('success', 'Catatan berhasil disimpan.');
    }
    public function updateStatus(Request $request, $notification_number)
{
    // Validasi request untuk status approval
    $request->validate([
        'status_approve' => 'required|in:Approved,Rejected'
    ]);

    // Cari dokumen LHPP berdasarkan nomor notifikasi
    $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();

    // Update status approval
    $lhpp->status_approve = $request->status_approve;
    $lhpp->save();

    // Redirect dengan pesan sukses
    return redirect()->back()->with('success', 'Status approval berhasil diperbarui.');
}

public function show($notification_number)
{
    $lhpp = LHPP::where('notification_number', $notification_number)->firstOrFail();
    return view('approval.lhpp.show', compact('lhpp'));
}


}
