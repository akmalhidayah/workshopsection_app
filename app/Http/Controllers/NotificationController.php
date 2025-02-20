<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Abnormal;
use App\Models\ScopeOfWork;
use App\Models\GambarTeknik;
use App\Models\Notification;
use Illuminate\Database\QueryException;

class NotificationController extends Controller
{
    public function index()
{
    // Jika pengguna adalah admin, ambil semua notifikasi
    if (auth()->user()->usertype == 'admin') {
        $notifications = Notification::orderBy('created_at', 'desc')->get();
    } else {
        // Jika bukan admin, ambil notifikasi milik user yang login
        $notifications = Notification::where('user_id', auth()->id())
                                     ->orderBy('created_at', 'desc')
                                     ->get();
    }

    return view('notifications.index', compact('notifications'));
}

public function store(Request $request)
    {
        // Validasi input dari form termasuk jenis_kontrak dan nama_kontrak
        $request->validate([
            'notification_number' => [
                'required',
                \Illuminate\Validation\Rule::unique('notifications'), // Validasi unique di seluruh tabel
            ],
            'job_name' => 'required',
            'unit_work' => 'required',
            'priority' => 'required',
            'input_date' => 'required|date',
            'usage_plan_date' => 'required|date',
            'jenis_kontrak' => 'required',
            'nama_kontrak' => 'required',
        ]);
    
        try {
            // Simpan notifikasi dengan user_id, jenis_kontrak, dan nama_kontrak
            Notification::create([
                'notification_number' => $request->notification_number,
                'job_name' => $request->job_name,
                'unit_work' => $request->unit_work,
                'priority' => $request->priority,
                'input_date' => $request->input_date,
                'usage_plan_date' => $request->usage_plan_date,
                'jenis_kontrak' => $request->jenis_kontrak,
                'nama_kontrak' => $request->nama_kontrak,
                'user_id' => auth()->id(), // Menyimpan ID user yang membuat notifikasi
            ]);
    
            return redirect()->route('notifications.index')->with('success', 'Notifikasi berhasil dibuat.');
    
        } catch (QueryException $e) {
            // Menangani error duplikasi entry (pelanggaran primary key)
            if($e->errorInfo[1] == 1062) {
                // Kode error 1062 menunjukkan pelanggaran unique constraint (primary key duplikat)
                return redirect()->back()->withErrors(['notification_number' => 'Nomor notifikasi ini sudah digunakan oleh user lain.']);
            }
    
            // Menangani error lain yang mungkin terjadi
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi.']);
        }
    }
    
    public function destroy($notification_number)
    {
        // Dapatkan data notifikasi berdasarkan notification_number dan user yang login
        $notification = Notification::where('notification_number', $notification_number)
                                    ->where('user_id', auth()->id()) // Hanya notifikasi milik user yang login
                                    ->firstOrFail();

        // Hapus semua data terkait di tabel abnormals
        Abnormal::where('notification_number', $notification->notification_number)->delete();

        // Hapus semua data terkait di tabel scope of work
        ScopeOfWork::where('notification_number', $notification->notification_number)->delete();


        // Hapus notifikasi itu sendiri
        $notification->delete();

        return redirect()->route('notifications.index')->with('success', 'Data berhasil dihapus beserta data terkait.');
    }

    public function update(Request $request, $notification_number)
{
    $request->validate([
        'status' => 'required|string',
        'catatan' => 'nullable|string',
    ]);

    // Jika admin, jangan batasi berdasarkan user_id
    if (auth()->user()->usertype == 'admin') {
        $notification = Notification::where('notification_number', $notification_number)->firstOrFail();
    } else {
        // Batasi hanya notifikasi milik user yang login
        $notification = Notification::where('notification_number', $notification_number)
                                    ->where('user_id', auth()->id())
                                    ->firstOrFail();
    }

    // Update status dan catatan
    $notification->status = $request->input('status');
    $notification->catatan = $request->input('catatan');
    $notification->save();

    return back()->with('success_status', 'Status berhasil diperbarui');
}

public function updatePriority(Request $request, $notification_number)
{
    $request->validate([
        'priority' => 'required|string|in:Urgently,Hard,Medium,Low',
    ]);

    // Jika admin, jangan batasi berdasarkan user_id
    if (auth()->user()->usertype == 'admin') {
        $notification = Notification::where('notification_number', $notification_number)->firstOrFail();
    } else {
        // Batasi hanya notifikasi milik user yang login
        $notification = Notification::where('notification_number', $notification_number)
                                    ->where('user_id', auth()->id())
                                    ->firstOrFail();
    }

    // Update prioritas
    $notification->priority = $request->priority;
    $notification->save();

    return back()->with('success_priority', 'Priority berhasil diperbarui');
}
public function updateStatusAnggaran(Request $request, $notification_number)
{
    $request->validate([
        'status_anggaran' => 'required|string|in:Tersedia,Tidak Tersedia',
    ]);

    // Temukan notifikasi berdasarkan nomor notifikasi
    $notification = Notification::where('notification_number', $notification_number)->firstOrFail();
    
    // Update status anggaran dan set update_date ke waktu saat ini
    $notification->status_anggaran = $request->input('status_anggaran');
    $notification->update_date = now(); // Menyimpan waktu update terakhir
    $notification->save();

    return redirect()->back()->with('success', 'Status anggaran berhasil diperbarui.');
}

public function edit($notification_number)
{
    // Jika admin, jangan batasi berdasarkan user_id
    if (auth()->user()->usertype == 'admin') {
        $notification = Notification::where('notification_number', $notification_number)->firstOrFail();
    } else {
        // Batasi hanya notifikasi milik user yang login
        $notification = Notification::where('notification_number', $notification_number)
                                    ->where('user_id', auth()->id())
                                    ->firstOrFail();
    }

    return response()->json($notification); // Pastikan ini mengembalikan data dalam format JSON
}


}
