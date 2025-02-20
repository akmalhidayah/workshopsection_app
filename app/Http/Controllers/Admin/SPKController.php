<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SPK;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use App\Mail\SPKNotification;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Http;



class SPKController extends Controller
{
    public function create($notificationNumber)
    {
        // Mengambil data notifikasi berdasarkan nomor notifikasi
        $notification = Notification::where('notification_number', $notificationNumber)->firstOrFail();

        // Mengirimkan data notifikasi ke view
        return view('admin.inputspk.createspk', compact('notification'));
    }
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nomor_spk' => 'required|unique:spks',
            'perihal' => 'required',
            'tanggal_spk' => 'required|date',
            'notification_number' => 'required|exists:notifications,notification_number',
            'unit_work' => 'required',
    
            // Validasi array
            'functional_location' => 'required|array',
            'scope_pekerjaan' => 'required|array',
            'qty' => 'required|array',
            'stn' => 'required|array',
            'keterangan' => 'required|array',
        ]);
    
        // Simpan data ke dalam model SPK langsung dalam bentuk array
        $spk = SPK::create([
            'nomor_spk' => $request->input('nomor_spk'),
            'perihal' => $request->input('perihal'),
            'tanggal_spk' => $request->input('tanggal_spk'),
            'notification_number' => $request->input('notification_number'),
            'unit_work' => $request->input('unit_work'),
    
            // Simpan data array secara langsung
            'functional_location' => $request->input('functional_location'),
            'scope_pekerjaan' => $request->input('scope_pekerjaan'),
            'qty' => $request->input('qty'),
            'stn' => $request->input('stn'),
            'keterangan' => $request->input('keterangan'),
    
            // Keterangan Pengerjaan
            'keterangan_pekerjaan' => $request->input('keterangan_pekerjaan', '-'),
        ]);
    
        // Kirim email notifikasi ke Manager dengan unit_work "Unit Of Workshop"
        $managers = User::where('unit_work', 'Unit Of Workshop')
        ->where('jabatan', 'Manager')
        ->get();

        foreach ($managers as $manager) {
            try {
                // Kirim notifikasi WhatsApp
                Http::withHeaders([
                    'Authorization' => 'KBTe2RszCgc6aWhYapcv' // API key Fonnte Anda
                ])->post('https://api.fonnte.com/send', [
                    'target' => $manager->whatsapp_number,
                    'message' => "Permintaan Approval Pembuatan SPK:\nNomor SPK: {$spk->nomor_spk}\nPerihal: {$spk->perihal}\nTanggal SPK: " . $spk->created_at->format('d-m-Y') . "\nUnit Kerja: {$spk->unit_work}\n\nSilakan login untuk melihat detailnya:\nhttps://sectionofworkshop.com/approval/spk",
                ]);
        
                \Log::info("WhatsApp notification sent to Manager: " . $manager->whatsapp_number);
            } catch (\Exception $e) {
                \Log::error("Gagal mengirim WhatsApp ke {$manager->whatsapp_number}: " . $e->getMessage());
            }
        
            // Komentar email notifikasi
            /*
            try {
                Mail::to($manager->email)->send(new SPKNotification($spk, $manager));
            } catch (\Exception $e) {
                \Log::error("Gagal mengirim email ke {$manager->email}: " . $e->getMessage());
            }
            */
        }
        
        return redirect()->route('notifikasi.index')->with('success_spk', 'SPK berhasil dibuat.');
    }        
    

public function show($notification_number)
{
    // Mengambil data SPK berdasarkan nomor notifikasi
    $spk = SPK::where('notification_number', $notification_number)->firstOrFail();

    // Decode JSON menjadi array, jika belum berbentuk array
    if (!is_array($spk->functional_location)) {
        $spk->functional_location = json_decode($spk->functional_location, true);
    }
    if (!is_array($spk->scope_pekerjaan)) {
        $spk->scope_pekerjaan = json_decode($spk->scope_pekerjaan, true);
    }
    if (!is_array($spk->qty)) {
        $spk->qty = json_decode($spk->qty, true);
    }
    if (!is_array($spk->stn)) {
        $spk->stn = json_decode($spk->stn, true);
    }
    if (!is_array($spk->keterangan)) {
        $spk->keterangan = json_decode($spk->keterangan, true);
    }

    return view('admin.inputspk.viewspk', compact('spk'));
}

}



