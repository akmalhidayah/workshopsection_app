<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\HPP;
use App\Models\LHPP;
use App\Models\UnitWork;
use App\Models\KawatLas;              // <--- tambah
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;    // <--- tambah

class DashboardUserController extends Controller
{
    public function index(Request $request)
    {
        // Summary counts
        $jumlahNotifikasi = Notification::count();
        $jumlahDiproses   = Notification::where('status', 'Pending')->count();
        $jumlahDiterima   = Notification::where('status', 'Approved')->count();

        // Main query for listing notifications (with filters/pagination)
        $query = Notification::with([
            'hpp1',
            'purchaseOrder',
            'lhpp',
            'lpj',
            'verifikasiAnggaran',
             'garansi' 
        ]);

        if ($request->filled('notification_number')) {
            $q = trim($request->input('notification_number'));
            $query->where('notification_number', 'like', "%{$q}%");
        }

        if ($request->filled('unit_work')) {
            $unit = $request->input('unit_work');
            $query->where('unit_work', $unit);
        }

        $sort = $request->get('sortOrder', 'latest');
        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $allowed = [10,25,50,100];
        $perPage = (int) $request->get('entries', 10);
        if (!in_array($perPage, $allowed)) $perPage = 10;

        $notifications = $query->paginate($perPage)->withQueryString();

        // Units for dropdown
        $units = UnitWork::orderBy('name')->pluck('name');

        // Chart data for Notifications (top 10 unit_work by Approved notifications)
        $chartDataRaw = Notification::select('unit_work', DB::raw('COUNT(*) as jumlah_notifikasi'))
            ->where('status', 'Approved')
            ->groupBy('unit_work')
            ->orderByDesc('jumlah_notifikasi')
            ->take(10)
            ->get();

        $totalBiaya = \App\Models\LHPP::select('unit_kerja', DB::raw('SUM(total_biaya) as total_biaya'))
            ->groupBy('unit_kerja')
            ->get()
            ->keyBy('unit_kerja');

        $chartData = [
            'labels' => $chartDataRaw->pluck('unit_work'),
            'notification_counts' => $chartDataRaw->pluck('jumlah_notifikasi'),
            'total_biaya' => $chartDataRaw->map(fn($item) => $totalBiaya[$item->unit_work]->total_biaya ?? 0),
        ];

        // ===== NEW: KAWAT LAS statistics (only Good Issue) =====
        $kawatRaw = KawatLas::select('unit_work', DB::raw('COUNT(*) as jumlah'))
            ->where('status', 'Good Issue')
            ->groupBy('unit_work')
            ->orderByDesc('jumlah')
            ->take(10)
            ->get();

        $chartKawat = [
            'labels' => $kawatRaw->pluck('unit_work'),
            'counts' => $kawatRaw->pluck('jumlah'),
        ];

        // total KawatLas Good Issue (for a small card or summary)
        $jumlahGoodIssue = KawatLas::where('status', 'Good Issue')->count();

        return view('dashboard', [
            'notifications' => $notifications,
            'jumlahNotifikasi' => $jumlahNotifikasi,
            'jumlahDiproses' => $jumlahDiproses,
            'jumlahDiterima' => $jumlahDiterima,
            'chartData' => $chartData,
            'chartKawat' => $chartKawat,          // <-- kirim ke blade
            'jumlahGoodIssue' => $jumlahGoodIssue,// <-- kirim ke blade
            'units' => $units,
        ]);
    }
}
