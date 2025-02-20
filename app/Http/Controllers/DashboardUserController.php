<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\HPP;
use App\Models\LHPP;
use Illuminate\Http\Request;

class DashboardUserController extends Controller
{
    public function index()
    {
        // Menghitung jumlah total notifikasi
        $jumlahNotifikasi = Notification::count();

        // Mengambil semua notifikasi untuk tabel
        $notifications = Notification::all();

        // Menghitung jumlah notifikasi yang sedang diproses (status Pending)
        $jumlahDiproses = Notification::where('status', 'Pending')->count();

        // Menghitung jumlah notifikasi yang diterima (status Approved atau Diterima)
        $jumlahDiterima = Notification::where('status', 'Approved')->count();

        $notifications = Notification::with(['hpp1'])->get();

        // Ambil notifikasi dengan relasi LHPP
        $notifications = Notification::with('lhpp')->paginate(10);

        return view('dashboard', [
            'notifications' => $notifications,
            'jumlahNotifikasi' => $jumlahNotifikasi,
            'jumlahDiproses' => $jumlahDiproses,
            'jumlahDiterima' => $jumlahDiterima
        ]);
    }
}
