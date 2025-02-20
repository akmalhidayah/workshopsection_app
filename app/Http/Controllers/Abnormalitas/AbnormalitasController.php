<?php

namespace App\Http\Controllers\Abnormalitas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Abnormal;

class AbnormalitasController extends Controller
{
    public function index(Request $request)
    {
        // Ambil nilai dari search dan filter
        $search = $request->input('search');
        $sortOrder = $request->input('sortOrder', 'latest'); // Default ke 'latest'
        $entries = $request->input('entries', 10); // Default ke 10 entries
    
        // Query dasar, hanya notifikasi milik user yang sedang login
        $query = Notification::where('user_id', auth()->id())
                             ->with('abnormal', 'scopeOfWork', 'gambarTeknik');
    
        // Jika ada input search, tambahkan kondisi pencarian
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('notification_number', 'like', "%{$search}%")
                      ->orWhere('job_name', 'like', "%{$search}%");
            });
        }
    
        // Sortir berdasarkan order yang dipilih
        $query->orderBy('created_at', $sortOrder === 'latest' ? 'desc' : 'asc');
    
        // Ambil jumlah entries sesuai pilihan user
        $abnormalitas = $query->paginate($entries);
    
        // Mengirim data ke view
        return view('abnormalitas.index', compact('abnormalitas', 'search', 'sortOrder', 'entries'));
    }
    

    public function edit($notificationNumber)
    {
        $abnormal = Abnormal::where('notification_number', $notificationNumber)->firstOrFail();
        $notification = Notification::where('notification_number', $notificationNumber)->firstOrFail();
    
        return view('abnormal.edit', compact('abnormal', 'notification'));
    }
    

    public function update(Request $request, $id)
    {
        // Validasi data yang diterima dari form
        $request->validate([
            'notification_number' => 'required|unique:notifications,notification_number,' . $id,
            'job_name' => 'required',
            'unit_work' => 'required',
            'input_date' => 'required|date',
        ]);

        // Mengambil data yang akan diupdate berdasarkan ID
        $abnormalitas = Notification::findOrFail($id);

        // Mengupdate data di database
        $abnormalitas->update($request->all());

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('abnormalitas.index')->with('success', 'Abnormalitas updated successfully.');
    }

    public function destroy($id)
    {
        // Menghapus data berdasarkan ID
        $abnormalitas = Notification::findOrFail($id);
        $abnormalitas->delete();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('abnormalitas.index')->with('success', 'Abnormalitas deleted successfully.');
    }
    public function show($notificationNumber)
{
    $abnormal = Abnormal::where('notification_number', $notificationNumber)->firstOrFail();
    $notification = Notification::where('notification_number', $notificationNumber)->firstOrFail();

    return view('abnormal.view', compact('abnormal', 'notification'));
}

}
