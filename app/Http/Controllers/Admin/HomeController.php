<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Hpp1;
use App\Models\Abnormal; 
use App\Models\ScopeOfWork; 
use App\Models\GambarTeknik; 
use App\Models\Lpj;
use App\Models\KuotaAnggaranOA;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 



class HomeController extends Controller
{
    public function index()
    {
        // Ambil semua notifikasi dengan eager loading
        $notifications = Notification::with(['abnormal', 'scopeOfWork', 'gambarTeknik'])->get();
    
        // Ambil semua notifikasi yang sudah memiliki dokumen HPP
        $notificationsWithHPP = DB::table('hpp1')->pluck('notification_number')->toArray();
    
         // Ambil semua notification_number dari tabel LPJ yang memiliki LPJ dan PPL
            $lpjNotifications = DB::table('lpjs')
            ->whereNotNull('lpj_number') // LPJ tersedia
            ->whereNotNull('ppl_number') // PPL tersedia
            ->pluck('notification_number')
            ->toArray();

        // Hitung total realisasi biaya berdasarkan total_amount dari tabel HPP
        $totalRealisasiBiaya = DB::table('hpp1')
            ->whereIn('notification_number', $lpjNotifications) // Ambil data HPP yang sesuai dengan LPJ
            ->sum('total_amount'); // Hitung total_amount dari HPP

        // Ambil semua notifikasi yang sudah masuk kategori Approval Process (HPP)
        $approvalNotifications = DB::table('hpp1')
            ->whereNotNull('general_manager_signature_requesting_unit') // TTD General Manager Peminta
            ->pluck('notification_number')
            ->toArray();
    
        // Ambil semua notifikasi yang sudah memiliki Purchase Order
        $poNotifications = DB::table('purchase_orders')
            ->whereNotNull('purchase_order_number') // Cek jika purchase_order_number sudah terisi
            ->pluck('notification_number')
            ->toArray();
    
        // Ambil semua notification_number yang ada di tabel LHPP
        $lhppNotificationNumbers = DB::table('lhpp')->pluck('notification_number')->toArray();
    
        // **Jumlah data**
        $outstandingNotifications = $notifications->filter(function ($notification) use ($notificationsWithHPP, $approvalNotifications, $poNotifications) {
            return $notification->abnormal !== null &&
                   $notification->abnormal->manager_signature !== null &&
                   $notification->abnormal->senior_manager_signature !== null &&
                   $notification->scopeOfWork !== null &&
                   $notification->gambarTeknik !== null &&
                   $notification->status !== 'Approved' &&
                   !in_array($notification->notification_number, $notificationsWithHPP) &&
                   !in_array($notification->notification_number, $approvalNotifications) &&
                   !in_array($notification->notification_number, $poNotifications);
        })->count();
    
        $pendingProcessJasa = Notification::where('status', 'Approved')
            ->whereNotIn('notification_number', $notificationsWithHPP)
            ->whereNotIn('notification_number', $approvalNotifications)
            ->whereNotIn('notification_number', $poNotifications)
            ->count();
    
        $documentOnProcessHPPCount = DB::table('hpp1')
            ->whereNull('general_manager_signature_requesting_unit')
            ->whereNotIn('notification_number', $poNotifications)
            ->count();
    
        $approvalProcessHPPCount = DB::table('hpp1')
            ->whereNotNull('general_manager_signature_requesting_unit')
            ->whereNotIn('notification_number', $poNotifications)
            ->count();
    
        $documentOnProcessPOCount = count($poNotifications);
    
        // **Total amount**
        $documentOnProcessHPPAmount = DB::table('hpp1')
            ->whereNull('general_manager_signature_requesting_unit')
            ->whereNotIn('notification_number', $poNotifications)
            ->sum('total_amount');
    
        $approvalProcessHPPAmount = DB::table('hpp1')
            ->whereNotNull('general_manager_signature_requesting_unit')
            ->whereNotIn('notification_number', $poNotifications)
            ->sum('total_amount');
    
        $documentOnProcessPOAmount = DB::table('hpp1')
            ->whereIn('notification_number', $poNotifications)
            ->sum('total_amount');
    
        // Ambil semua notification_number yang memiliki priority "Urgent"
        $urgentNotificationNumbers = DB::table('notifications')
            ->where('priority', 'Urgently') // Sesuaikan dengan priority di database
            ->pluck('notification_number')
            ->toArray();

        // Hitung total amount untuk Pekerjaan Urgent
        $urgentAmount = DB::table('hpp1')
            ->whereIn('notification_number', $urgentNotificationNumbers)
            ->sum('total_amount');

        // Hitung total amount untuk Document PR/PO tanpa melibatkan notifikasi Urgent
        $documentPRPOAmount = DB::table('hpp1')
            ->whereIn('notification_number', $lhppNotificationNumbers) // Sudah ada di LHPP
            ->whereNotIn('notification_number', $urgentNotificationNumbers) // Exclude Urgent notifications
            ->sum('total_amount');

        // Jika Document PR/PO sudah terpenuhi, nolkan hanya Document On Process PO jika tidak masuk LHPP
        if ($documentPRPOAmount > 0) {
            $documentOnProcessPOAmount = DB::table('hpp1')
                ->whereIn('notification_number', $poNotifications)
                ->whereNotIn('notification_number', $lhppNotificationNumbers) // Exclude PR/PO in LHPP
                ->sum('total_amount');
        }

        // Total seluruh amount1
        $totalAmount1 = $documentOnProcessHPPAmount + $approvalProcessHPPAmount + $documentOnProcessPOAmount;

        // Total seluruh amount2
        $totalAmount2 = $documentPRPOAmount + $urgentAmount;

        // Total Keseluruhan amount
        $totalSeluruhAmount = $totalAmount1 + $totalAmount2;
    
        // Ambil data kuota anggaran terakhir dari tabel
        $latestKuotaAnggaran = DB::table('kuota_anggaran_oa')->latest('created_at')->first();
    
        // Hitung Total Kuota Kontrak
        $totalKuotaKontrak = $latestKuotaAnggaran->total_kuota_kontrak ?? 0;
    
        // Periode Kontrak
        $periodeKontrak = [
            'start' => $latestKuotaAnggaran->periode_kontrak_start ?? null,
            'end' => $latestKuotaAnggaran->periode_kontrak_end ?? null,
            'adendum' => $latestKuotaAnggaran->adendum_end ?? null,
        ];

        // Hitung Sisa Kuota Kontrak
        $sisaKuotaKontrak = $totalKuotaKontrak - $totalRealisasiBiaya;
    
        // Passing data ke view
        return view('admin.dashboard', compact(
            'outstandingNotifications',
            'pendingProcessJasa',
            'documentOnProcessHPPCount',
            'approvalProcessHPPCount',
            'documentOnProcessPOCount',
            'documentOnProcessHPPAmount',
            'approvalProcessHPPAmount',
            'documentOnProcessPOAmount',
            'totalAmount1',
            'totalAmount2',
            'documentPRPOAmount',
            'urgentAmount',
            'totalSeluruhAmount',
            'totalKuotaKontrak',
            'sisaKuotaKontrak',
            'periodeKontrak',
            'totalRealisasiBiaya'
        ));
    }
    public function getYears()
    {
        // Ambil semua tahun dari tabel LPJ berdasarkan kolom update_date
        $years = DB::table('lpjs')
            ->selectRaw('YEAR(update_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    
        return response()->json($years);
    }
    
    public function realisasiBiaya(Request $request)
    {
        try {
            // Validasi input tahun dan bulan
            $request->validate([
                'startYear' => 'required|numeric',
                'endYear' => 'required|numeric|gte:startYear',
                'startMonth' => 'nullable|numeric|min:1|max:12',
                'endMonth' => 'nullable|numeric|min:1|max:12|gte:startMonth',
            ]);
    
            $startYear = $request->input('startYear');
            $endYear = $request->input('endYear');
            $startMonth = $request->input('startMonth');
            $endMonth = $request->input('endMonth');
    
            // Ambil data notification_number berdasarkan tahun dan bulan
            $lpjs = DB::table('lpjs')
                ->whereNotNull('lpj_number')
                ->whereNotNull('ppl_number')
                ->whereBetween(DB::raw('YEAR(update_date)'), [$startYear, $endYear]);
    
            if ($startMonth && $endMonth) {
                $lpjs->whereBetween(DB::raw('MONTH(update_date)'), [$startMonth, $endMonth]);
            }
    
            $lpjNumbers = $lpjs->pluck('notification_number')->toArray();
    
            // Hitung total realisasi biaya berdasarkan filter
            $realisasiBiaya = DB::table('hpp1')
                ->join('lpjs', 'hpp1.notification_number', '=', 'lpjs.notification_number')
                ->whereIn('hpp1.notification_number', $lpjNumbers)
                ->select(
                    DB::raw('YEAR(lpjs.update_date) as year'),
                    DB::raw('MONTH(lpjs.update_date) as month'),
                    DB::raw('SUM(hpp1.total_amount) as total')
                )
                ->groupBy(DB::raw('YEAR(lpjs.update_date), MONTH(lpjs.update_date)'))
                ->get();
    
            return response()->json($realisasiBiaya);
        } catch (\Exception $e) {
            Log::error('Error di realisasiBiaya: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat memproses data.'], 500);
        }
    }
    
    public function getMonths()
{
    // Ambil semua bulan dari tabel LPJ berdasarkan kolom update_date
    $months = DB::table('lpjs')
        ->selectRaw('MONTH(update_date) as month')
        ->distinct()
        ->orderBy('month', 'asc')
        ->pluck('month')
        ->map(function ($month) {
            return [
                'number' => $month,
                'name' => \Carbon\Carbon::create()->month($month)->format('F')
            ];
        });

    return response()->json($months);
}

    
    public function notifikasi(Request $request)
    {
        // Default pagination size
        $entries = $request->input('entries', 10); // Default 10 entries per page
        $searchQuery = $request->input('search', ''); // Ambil input pencarian
    
        // Mengambil data notifications dengan eager loading
        $notifications = Notification::with(['abnormal', 'scopeOfWork', 'gambarTeknik'])
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where('notification_number', 'LIKE', "%{$searchQuery}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($entries);
    
        // Iterasi untuk menandai notifikasi yang memiliki dokumen lengkap
        foreach ($notifications as $notification) {
            $notification->isAbnormalAvailable = $notification->abnormal !== null;
            $notification->isScopeOfWorkAvailable = $notification->scopeOfWork !== null;
            $notification->isGambarTeknikAvailable = $notification->gambarTeknik !== null;
            
            // Tambahkan pengecekan tanda tangan manager dan senior manager
            $notification->isAbnormalSigned = $notification->abnormal !== null &&
                                              $notification->abnormal->manager_signature !== null &&
                                              $notification->abnormal->senior_manager_signature !== null;
        }
    
        // Hitung jumlah notifikasi yang sudah lengkap dokumennya
        $outstandingNotifications = $notifications->filter(function ($notification) {
            return $notification->isAbnormalAvailable &&
                   $notification->isAbnormalSigned && // Pastikan tanda tangan manager dan senior manager sudah ada
                   $notification->isScopeOfWorkAvailable &&
                   $notification->isGambarTeknikAvailable;
        })->count();
    
        return view('admin.notifikasi', compact('notifications', 'outstandingNotifications'));
    }
    public function verifikasiAnggaran(Request $request)
    {
        $entries = $request->input('entries', 10); // Default 5 entries per page
        $search = $request->input('search'); // Untuk pencarian
    
        // Query untuk mendapatkan data notifications
        $query = Notification::query();
    
        // Jika ada input search, lakukan pencarian berdasarkan nomor notifikasi
        if ($search) {
            $query->where('notification_number', 'LIKE', "%{$search}%");
        }
    
        // Lanjutkan dengan pagination dan order
        $notifications = $query->orderBy('created_at', 'desc')->paginate($entries);
    
        // Iterasi untuk cek apakah dokumen abnormalitas, scope of work, gambar teknik, dan HPP sudah ada
        foreach ($notifications as $notification) {
            $notification->isAbnormalAvailable = Abnormal::where('notification_number', $notification->notification_number)->exists();
            $notification->isScopeOfWorkAvailable = ScopeOfWork::where('notification_number', $notification->notification_number)->exists();
            $notification->isGambarTeknikAvailable = GambarTeknik::where('notification_number', $notification->notification_number)->exists();
        
            // ✅ Tambahkan pengecekan tanda tangan
            $abnormal = Abnormal::where('notification_number', $notification->notification_number)->first();
            $notification->isAbnormalSigned = $abnormal && $abnormal->manager_signature && $abnormal->senior_manager_signature;
        
            // ✅ Perluas logika AbnormalAvailable
            $notification->isAbnormalAvailable = $notification->isAbnormalAvailable && $notification->isAbnormalSigned;
        
            // ✅ HPP
            $hpp = Hpp1::where('notification_number', $notification->notification_number)->first();
            if ($hpp) {
                $notification->isHppAvailable = true;
                $notification->source_form = $hpp->source_form;
                $notification->total_amount = $hpp->total_amount;
            } else {
                $notification->isHppAvailable = false;
            }
        }
        
    
        return view('admin.verifikasianggaran', compact('notifications', 'search', 'entries'));
    }
    

public function purchaseRequest(Request $request)
{
    // Default pagination size
    $entries = $request->input('entries', 5); // Default 5 entries per page
    
    // Mengambil data notifications dengan pagination
    $notifications = Notification::orderBy('created_at', 'desc')->paginate($entries);
    
    // Iterasi untuk cek apakah dokumen abnormalitas, scope of work, gambar teknik, dan HPP sudah ada
    foreach ($notifications as $notification) {
        $notification->isAbnormalAvailable = Abnormal::where('notification_number', $notification->notification_number)->exists();
        $notification->isScopeOfWorkAvailable = ScopeOfWork::where('notification_number', $notification->notification_number)->exists();
        $notification->isGambarTeknikAvailable = GambarTeknik::where('notification_number', $notification->notification_number)->exists();
        
        // Ambil data HPP dan source_form
        $hpp = Hpp1::where('notification_number', $notification->notification_number)->first();
        if ($hpp) {
            $notification->isHppAvailable = true;
            $notification->source_form = $hpp->source_form;
        } else {
            $notification->isHppAvailable = false;
        }
    }

    return view('admin.purchaserequest', compact('notifications'));
}

public function purchaseOrder(Request $request)
{
    // Ambil data dari tabel notifications
    $notifications = Notification::orderBy('created_at', 'desc')->get(); // Gunakan get() untuk filtering manual

    // Filter hanya notifikasi dengan dokumen lengkap
    $filteredNotifications = $notifications->filter(function ($notification) {
        $hasAbnormal = Abnormal::where('notification_number', $notification->notification_number)->exists();
        $hasScopeOfWork = ScopeOfWork::where('notification_number', $notification->notification_number)->exists();
        $hasGambarTeknik = GambarTeknik::where('notification_number', $notification->notification_number)->exists();

        // Periksa keberadaan HPP dan ambil detail
        $hpp = Hpp1::where('notification_number', $notification->notification_number)->first();
        if ($hpp) {
            $notification->isHppAvailable = true;
            $notification->source_form = $hpp->source_form;
            $notification->total_amount = $hpp->total_amount;

            // Perbarui update_date jika semua dokumen tersedia
            if ($hasAbnormal && $hasScopeOfWork && $hasGambarTeknik) {
                $notification->update_date = now();
                $notification->save();
                return true;
            }
        }

        return false;
    });

    // Return view dengan data filteredNotifications
    return view('admin.purchaseorder', [
        'notifications' => $filteredNotifications->paginate(5) // Paginasi setelah filter
    ]);
}


public function lpj()
    {
        // Ambil data notifikasi untuk LPJ
        $notifications = Notification::paginate(10); // Sesuaikan dengan kebutuhan

        return view('admin.lpj', compact('notifications'));
    }

    // Fungsi untuk memperbarui LPJ
    public function updateLpj(Request $request, $notification_number)
    {
        // Validasi data
        $request->validate([
            'lpj_number' => 'required|string',
            'lpj_document' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'ppl_number' => 'nullable|string',
            'ppl_document' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);
    
        // Proses dokumen LPJ
        if ($request->hasFile('lpj_document')) {
            $lpjExtension = $request->file('lpj_document')->getClientOriginalExtension();
            $lpjFileName = 'LPJ BMS ' . $notification_number . '.' . $lpjExtension;
            $lpjPath = $request->file('lpj_document')->storeAs('lpj_documents', $lpjFileName, 'public');
        } else {
            $lpjPath = Lpj::where('notification_number', $notification_number)->value('lpj_document_path');
        }
    
        // Proses dokumen PPL
        if ($request->hasFile('ppl_document')) {
            Log::info('PPL document uploaded', ['notification_number' => $notification_number]);
    
            $pplExtension = $request->file('ppl_document')->getClientOriginalExtension();
            $pplFileName = 'PPL BMS ' . $notification_number . '.' . $pplExtension;
            $pplPath = $request->file('ppl_document')->storeAs('ppl_documents', $pplFileName, 'public');
        } else {
            $pplPath = Lpj::where('notification_number', $notification_number)->value('ppl_document_path');
            if (!$pplPath) {
                Log::warning('No previous PPL document found', ['notification_number' => $notification_number]);
                $pplPath = null;
            }
        }
    
        // Update atau simpan data LPJ
        Lpj::updateOrCreate(
            ['notification_number' => $notification_number],
            [
                'lpj_number' => $request->lpj_number,
                'lpj_document_path' => $lpjPath,
                'ppl_number' => $request->ppl_number,
                'ppl_document_path' => $pplPath,
                'update_date' => now(),
            ]
        );
    
        return redirect()->route('admin.lpj')->with('success', 'Data LPJ berhasil diupdate!');
    }
    
    public function updateOA(Request $request)
    {
        // Cek apakah ada parameter 'new' untuk membuat OA baru
        $latestData = $request->has('new') ? null : KuotaAnggaranOA::latest()->first();
    
        return view('admin.updateoa', compact('latestData'));
    }
    
    public function storeOA(Request $request)
    {
        $request->validate([
            'outline_agreement' => 'required|string',
            'unit_work' => 'required|string',
            'jenis_kontrak' => 'required|string',
            'nama_kontrak' => 'required|string',
            'nilai_kontrak' => 'required|numeric',
            'tambahan_kuota_kontrak' => 'nullable|numeric',
            'total_kuota_kontrak' => 'required|numeric',
            'periode_kontrak_start' => 'required|date',
            'periode_kontrak_end' => 'required|date',
            'adendum_end' => 'nullable|date',
        ]);
    
        KuotaAnggaranOA::updateOrCreate(
            ['outline_agreement' => $request->outline_agreement],
            [
                'unit_work' => $request->unit_work,
                'jenis_kontrak' => $request->jenis_kontrak,
                'nama_kontrak' => $request->nama_kontrak,
                'nilai_kontrak' => $request->nilai_kontrak,
                'tambahan_kuota_kontrak' => $request->tambahan_kuota_kontrak,
                'total_kuota_kontrak' => $request->nilai_kontrak + ($request->tambahan_kuota_kontrak ?? 0),
                'periode_kontrak_start' => $request->periode_kontrak_start,
                'periode_kontrak_end' => $request->periode_kontrak_end,
                'adendum_end' => $request->adendum_end,
                'periode_kontrak_final' => $request->periode_kontrak_final ?? $request->periode_kontrak_end,
            ]
        );
    
        return redirect()->route('admin.updateoa')->with('success', 'Data Outline Agreement berhasil disimpan!');
    }
    
    
}


