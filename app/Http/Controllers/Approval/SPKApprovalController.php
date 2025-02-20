<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SPK;
use App\Models\User;
use App\Models\Lpj;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SPKApprovalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
    
        // Ambil daftar SPK yang sudah memiliki LPJ & PPL lengkap
        $lpjCompletedNotifications = DB::table('lpjs')
            ->whereNotNull('lpj_number')
            ->whereNotNull('ppl_number')
            ->whereNotNull('lpj_document_path')
            ->whereNotNull('ppl_document_path')
            ->pluck('notification_number')
            ->toArray();

        // Jika user bukan dari unit "Unit Of Workshop", tampilkan halaman kosong
        if ($user->unit_work !== 'Unit Of Workshop') {
            return view('approval.spk.index', ['spks' => []]);
        }

        // Ambil dokumen SPK berdasarkan status penandatanganan, kecuali yang LPJ & PPL-nya sudah ada
        $spks = SPK::whereNotIn('notification_number', $lpjCompletedNotifications) // ✅ Filter SPK yang sudah memiliki LPJ & PPL
                    ->where(function ($query) use ($user) {
                        if ($user->jabatan === 'Manager') {
                            $query->whereNull('manager_signature')
                                  ->orWhereNotNull('manager_signature');
                        } elseif ($user->jabatan === 'Senior Manager') {
                            $query->whereNotNull('manager_signature')
                                  ->orWhereNotNull('senior_manager_signature');
                        }
                    })
                    ->orderByRaw("CASE 
                        WHEN manager_signature IS NULL THEN 0 
                        WHEN senior_manager_signature IS NULL THEN 1 
                        ELSE 2 
                    END")
                    ->get();
    
        return view('approval.spk.index', compact('spks'));
    }

    
    public function saveSignature(Request $request, $signType, $nomorSpk)
    {
        try {
            \Log::info('Received signType: ' . $signType);
            \Log::info('SPK Number: ' . $nomorSpk);
            \Log::info('Signature: ' . $request->tanda_tangan);
    
            $request->validate([
                'tanda_tangan' => 'required',
            ]);
    
            // Ambil data SPK berdasarkan nomor SPK
            $spk = SPK::where('nomor_spk', $nomorSpk)->firstOrFail();
    
            if ($signType === 'manager') {
                $spk->manager_signature = $request->tanda_tangan;
                $spk->save();
    
                // Kirim notifikasi WhatsApp ke Senior Manager setelah Manager menandatangani
                $seniorManagers = User::where('unit_work', 'Unit Of Workshop')
                                      ->where('jabatan', 'Senior Manager')
                                      ->get();
    
                foreach ($seniorManagers as $seniorManager) {
                    $message = "Permintaan Approval Dokumen SPK:\nNomor SPK: {$spk->nomor_spk}\nPerihal: {$spk->perihal}\nTanggal SPK: {$spk->tanggal_spk}\nUnit Kerja: {$spk->unit_work}\n\nSilakan login untuk melihat dan menandatangani dokumen:\nhttps://sectionofworkshop.com/approval/spk";
    
                    Http::withHeaders([
                        'Authorization' => 'KBTe2RszCgc6aWhYapcv' // API key Fonnte Anda
                    ])->post('https://api.fonnte.com/send', [
                        'target' => $seniorManager->whatsapp_number,
                        'message' => $message,
                    ]);
    
                    \Log::info('WhatsApp notification sent to Senior Manager: ' . $seniorManager->whatsapp_number);
                }
            } elseif ($signType === 'senior_manager') {
                $spk->senior_manager_signature = $request->tanda_tangan;
                $spk->save();
            }
    
            return response()->json(['message' => 'Signature saved successfully!'], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan tanda tangan', 'details' => $e->getMessage()], 500);
        }
    }
}    
