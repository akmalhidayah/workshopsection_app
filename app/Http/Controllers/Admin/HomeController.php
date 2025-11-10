<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Hpp1;
use App\Models\ScopeOfWork; 
use App\Models\JenisKawatLas;
use App\Models\KawatLas;
use App\Models\KawatLasDetail;
use App\Models\VerifikasiAnggaran;
use App\Models\Lpj;
use App\Models\KuotaAnggaranOA;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Cache;


class HomeController extends Controller
{
    public function index()
    {
        // Ambil semua notifikasi dengan eager loading
        $notifications = Notification::with(['dokumenOrders', 'scopeOfWork'])->get();


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
    $abnormal = $notification->dokumenOrders->where('jenis_dokumen', 'abnormalitas')->first();
    $gambarTeknik = $notification->dokumenOrders->where('jenis_dokumen', 'gambar_teknik')->first();

    return $abnormal !== null &&
           $gambarTeknik !== null &&
           $notification->scopeOfWork !== null &&
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
    
        // Ambil target biaya pemeliharaan dari OA terakhir
        $targetPemeliharaan = optional($latestKuotaAnggaran)->target_biaya_pemeliharaan ?? null;
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
            'totalRealisasiBiaya',
            'targetPemeliharaan'
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
/**
 * Helper filter notifikasi (reusable).
 * - $context === 'verifikasi'  -> filter unit dari notifications.unit_work & tanggal dari va.tanggal_verifikasi
 * - selain itu                  -> filter default dari notifications.created_at
 */
private function filterNotifications(Request $request, $withHpp = false, $withDate = true, $context = null)
{
    // pagination & basic filters
    $entries     = (int) $request->input('entries', 10);
    $search      = $request->input('search', '');
    $statusNotif = $request->input('status', '');     // dipakai saat context != 'verifikasi'
    $regu        = $request->input('regu');
    $startDate   = $request->input('start_date');
    $endDate     = $request->input('end_date');

    // filter khusus verifikasi anggaran
    $unit         = $request->input('unit');               // notifications.unit_work
    $statusVA     = $request->input('status_va');          // va.status_anggaran (opsional)
    $kategoriItem = $request->input('kategori_item');      // 'spare part' | 'jasa'
    $statusEKorin = $request->input('status_e_korin');     // 'waiting_korin' | 'waiting_transfer' | 'complete_transfer'

    $query = Notification::query();

    // eager-load relasi untuk view
    $with = ['dokumenOrders', 'scopeOfWork'];
    if ($withHpp) $with[] = 'hpp1';
    $query->with($with);

    // cari berdasarkan nomor notifikasi
    $query->when($search, fn($q) => $q->where('notification_number', 'like', "%{$search}%"));

    if ($context !== 'verifikasi') {
        // ===== MODE NON-VERIFIKASI =====
        $query->when($statusNotif, fn($q) => $q->where('status', $statusNotif))
              ->when($regu,        fn($q) => $q->where('catatan', $regu));

        if ($withDate) {
            $query->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
                  ->when($endDate,   fn($q) => $q->whereDate('created_at', '<=', $endDate));
        }

        return $query->orderBy('created_at', 'desc')
                     ->paginate($entries)
                     ->appends($request->all());
    }

    // ===== MODE VERIFIKASI =====
    $query->leftJoin('verifikasi_anggarans as va', 'va.notification_number', '=', 'notifications.notification_number')
          ->select('notifications.*'); // penting: hindari kolom bentrok saat join

    // unit dari notifications.unit_work
    $query->when($unit, fn($q) => $q->where('notifications.unit_work', $unit));

    // filter kolom VA
    $query->when($statusVA,     fn($q) => $q->where('va.status_anggaran', $statusVA))
          ->when($kategoriItem, fn($q) => $q->where('va.kategori_item', $kategoriItem))
          ->when($statusEKorin, fn($q) => $q->where('va.status_e_korin', $statusEKorin));

    // periode by tanggal_verifikasi (bukan created_at)
    if ($withDate) {
        $query->when($startDate, fn($q) => $q->whereDate('va.tanggal_verifikasi', '>=', $startDate))
              ->when($endDate,   fn($q) => $q->whereDate('va.tanggal_verifikasi', '<=', $endDate));
    }

    return $query->orderBy('notifications.created_at', 'desc')
                 ->paginate($entries)
                 ->appends($request->all());
}

public function notifikasi(Request $request)
{
    // ✅ Panggil helper biar konsisten filter & pagination
    $notifications = $this->filterNotifications($request, false, false);

    foreach ($notifications as $notification) {
        $notification->isAbnormalAvailable = $notification->dokumenOrders
            ->where('jenis_dokumen', 'abnormalitas')
            ->isNotEmpty();

        $notification->isGambarTeknikAvailable = $notification->dokumenOrders
            ->where('jenis_dokumen', 'gambar_teknik')
            ->isNotEmpty();

        $notification->isScopeOfWorkAvailable = $notification->scopeOfWork !== null;

        $notification->isAbnormalSigned = false;
    }

    $outstandingNotifications = $notifications->filter(function ($notification) {
        return $notification->isAbnormalAvailable &&
               $notification->isScopeOfWorkAvailable &&
               $notification->isGambarTeknikAvailable;
    })->count();

    // 🔥 ambil order kawat las + jenis list
    $kawatLasOrders      = KawatLas::with('details')->orderBy('created_at', 'desc')->paginate(10);
    $jumlahOrderKawatLas = $kawatLasOrders->total(); // biar konsisten dengan pagination
    $jenisList           = JenisKawatLas::orderBy('kode')->get();

    // 🔥 distinct Unit untuk filter dropdown
    $units = KawatLas::select('unit_work')
        ->distinct()
        ->orderBy('unit_work')
        ->pluck('unit_work');

    return view('admin.notifikasi', compact(
        'notifications',
        'outstandingNotifications',
        'jumlahOrderKawatLas',
        'kawatLasOrders',
        'jenisList',
        'units' // 👈 tambahan ini
    ));
}
public function inputHppIndex(Request $request)
{
    $query = \App\Models\Hpp1::query()->orderBy('created_at', 'desc');

    // 🔍 Filter berdasarkan pencarian (nomor order / unit kerja)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('notification_number', 'like', "%{$search}%")
              ->orWhere('requesting_unit', 'like', "%{$search}%");
        });
    }

    // 🧾 Filter berdasarkan jenis HPP (source_form)
    if ($request->filled('jenis_hpp')) {
        $query->where('source_form', $request->jenis_hpp);
    }

    // 🏢 Filter berdasarkan unit kerja
    if ($request->filled('unit_kerja')) {
        $query->where('requesting_unit', $request->unit_kerja);
    }

    // 🔹 Ambil data hasil filter + pagination
    $hpp = $query->paginate(10)->appends($request->all());

    // 🔹 Ambil daftar unit kerja unik untuk pilihan filter
    $unitKerjaOptions = \App\Models\Hpp1::select('requesting_unit')
        ->distinct()
        ->pluck('requesting_unit')
        ->sort()
        ->values();

    // 🔹 Data tambahan (grafik / analisis)
    $unitKerjaHppData = \App\Models\Hpp1::selectRaw('requesting_unit, SUM(total_amount) as total')
        ->groupBy('requesting_unit')
        ->orderBy('total', 'desc')
        ->take(10)
        ->get();

    return view('admin.inputhpp.index', compact('hpp', 'unitKerjaHppData', 'unitKerjaOptions'));
}

public function verifikasiAnggaran(Request $request)
{
    try {
        // aktifkan mode 'verifikasi' agar join & filter VA berjalan
        $notifications = $this->filterNotifications($request, true, true, 'verifikasi');

        // ambil semua nomor notifikasi di halaman ini untuk bulk-ambil VA
        $notifNumbers = $notifications->pluck('notification_number')->all();

        $verifMap = VerifikasiAnggaran::whereIn('notification_number', $notifNumbers)
            ->get()
            ->keyBy('notification_number');

        foreach ($notifications as $notification) {
            // status dokumen
            $notification->isAbnormalAvailable     = $notification->dokumenOrders->where('jenis_dokumen', 'abnormalitas')->isNotEmpty();
            $notification->isGambarTeknikAvailable = $notification->dokumenOrders->where('jenis_dokumen', 'gambar_teknik')->isNotEmpty();
            $notification->isScopeOfWorkAvailable  = $notification->scopeOfWork !== null;

            // HPP
            if ($notification->hpp1) {
                $notification->isHppAvailable = true;
                $notification->source_form  = $notification->hpp1->source_form;
                $notification->total_amount = $notification->hpp1->total_amount;
            } else {
                $notification->isHppAvailable = false;
            }

            // Merge Verifikasi Anggaran ke objek notif (untuk Blade)
            $v = $verifMap->get($notification->notification_number);
            $notification->status_anggaran    = $v->status_anggaran    ?? 'Menunggu';
            $notification->cost_element       = $v->cost_element       ?? '';
            $notification->kategori_biaya     = $v->kategori_biaya     ?? null;
            $notification->kategori_item      = $v->kategori_item      ?? null;   // NEW
            $notification->nomor_e_korin      = $v->nomor_e_korin      ?? null;   // NEW
            $notification->status_e_korin     = $v->status_e_korin     ?? null;   // NEW
            $notification->catatan            = $v->catatan            ?? '';
            $notification->tanggal_verifikasi = $v->tanggal_verifikasi ?? null;
        }

        // Dropdown Unit → dari notifications.unit_work (bukan KawatLas / requesting_unit)
        $units = Notification::select('unit_work')
            ->whereNotNull('unit_work')
            ->distinct()
            ->orderBy('unit_work')
            ->pluck('unit_work');

        return view('admin.verifikasianggaran', compact('notifications', 'units'));

    } catch (\Exception $e) {
        \Log::error('Error di verifikasiAnggaran(): ' . $e->getMessage());
        abort(500, 'Terjadi kesalahan saat memproses data Verifikasi Anggaran.');
    }
}

public function updateVerifikasiAnggaran(Request $request, $notification_number)
{
    try {
        // Validasi sesuai schema baru
        $validated = $request->validate([
            'status_anggaran' => 'nullable|string|in:Tersedia,Tidak Tersedia,Menunggu',
            'cost_element'    => 'nullable|string|max:255',
            'kategori_biaya'  => 'nullable|in:pemeliharaan,non pemeliharaan,capex',
            'kategori_item'   => 'nullable|in:spare part,jasa',
            'nomor_e_korin'   => 'nullable|string|max:255',
            'status_e_korin'  => 'nullable|in:waiting_korin,waiting_transfer,complete_transfer',
            'catatan'         => 'nullable|string|max:1000',
        ]);

        // Ambil data lama (jika ada)
        $verifikasi = VerifikasiAnggaran::where('notification_number', $notification_number)->first();

        $old = [
            'status_anggaran'    => $verifikasi->status_anggaran    ?? 'Menunggu',
            'cost_element'       => $verifikasi->cost_element       ?? null,
            'kategori_biaya'     => $verifikasi->kategori_biaya     ?? null,
            'kategori_item'      => $verifikasi->kategori_item      ?? null,
            'nomor_e_korin'      => $verifikasi->nomor_e_korin      ?? null,
            'status_e_korin'     => $verifikasi->status_e_korin     ?? null,
            'catatan'            => $verifikasi->catatan            ?? null,
            'tanggal_verifikasi' => $verifikasi->tanggal_verifikasi ?? null,
        ];

        // Susun nilai baru (fallback ke lama jika kosong)
        $new = [
            'status_anggaran' => $validated['status_anggaran'] ?? $old['status_anggaran'],
            'cost_element'    => $validated['cost_element']    ?? $old['cost_element'],
            'kategori_biaya'  => $validated['kategori_biaya']  ?? $old['kategori_biaya'],
            'kategori_item'   => $validated['kategori_item']   ?? $old['kategori_item'],
            'nomor_e_korin'   => $validated['nomor_e_korin']   ?? $old['nomor_e_korin'],
            'status_e_korin'  => $validated['status_e_korin']  ?? $old['status_e_korin'],
            'catatan'         => $validated['catatan']         ?? $old['catatan'],
        ];

        // Tentukan apakah ada perubahan (update timestamp jika ada)
        $changed = array_filter($new, fn($k) => ($new[$k] ?? null) !== ($old[$k] ?? null), ARRAY_FILTER_USE_KEY);
        $tanggalUpdate = !empty($changed) ? now() : $old['tanggal_verifikasi'];

        VerifikasiAnggaran::updateOrCreate(
            ['notification_number' => $notification_number],
            $new + ['tanggal_verifikasi' => $tanggalUpdate]
        );

        return redirect()->back()->with('success', '✅ Data verifikasi anggaran berhasil diperbarui.');

    } catch (\Illuminate\Validation\ValidationException $ve) {
        return back()->withErrors($ve->errors())->withInput();
    } catch (\Exception $e) {
        \Log::error('Gagal update Verifikasi Anggaran: ' . $e->getMessage());
        return back()->with('error', '❌ Terjadi kesalahan saat memperbarui data.');
    }
}


public function lpj(Request $request)
{
    try {
        // Hanya cari by notification_number (simple)
        $search  = $request->input('search', null);
        $entries = (int) $request->input('entries', 10);

        $query = \App\Models\Notification::with(['dokumenOrders', 'scopeOfWork']);

        if ($search) {
            // hanya cari pada kolom notification_number
            $query->where('notification_number', 'like', "%{$search}%");
        }

        // ambil notifications dengan pagination
        $notifications = $query->orderBy('created_at', 'desc')
                               ->paginate($entries)
                               ->appends($request->all());

        // ambil notification_number pada page ini untuk mengambil LPJ & LHPP sekaligus
        $notificationNumbersOnPage = $notifications->pluck('notification_number')->toArray();

        $lpjMap = \App\Models\Lpj::whereIn('notification_number', $notificationNumbersOnPage)
                    ->get()
                    ->keyBy('notification_number');

        $lhppMap = \App\Models\LHPP::whereIn('notification_number', $notificationNumbersOnPage)
                    ->get()
                    ->keyBy('notification_number');

        return view('admin.lpj', [
            'notifications' => $notifications,
            'lpjMap' => $lpjMap,
            'lhppMap' => $lhppMap,
            'request' => $request,
        ]);
    } catch (\Exception $e) {
        Log::error('Error di lpj(): ' . $e->getMessage());
        return response()->view('errors.500', [
            'message' => 'Terjadi kesalahan saat memuat data LPJ.'
        ], 500);
    }
}
public function updateLpj(Request $request, $notification_number)
{
    // short-lived lock key untuk mencegah double processing (5 detik)
    $lockKey = "lpj_lock_{$notification_number}";

    // kalau sudah ada lock, tolak request kedua
    if (! Cache::add($lockKey, true, 5)) {
        Log::warning("Duplicate LPJ request blocked for {$notification_number}", ['ip' => $request->ip()]);
        return back()->with('error', 'Permintaan sudah dikirim — tunggu sebentar dan cek kembali.')->setStatusCode(429);
    }

    // log awal (debug)
    Log::info('LPJ update request received', [
        'notification' => $notification_number,
        'ip' => $request->ip(),
        'time' => now()->toDateTimeString(),
        'has_lpj_file' => $request->hasFile('lpj_document') ? 'yes' : 'no',
        'has_ppl_file' => $request->hasFile('ppl_document') ? 'yes' : 'no',
    ]);

    try {
        $request->validate([
            'lpj_number'      => 'required|string',
            'lpj_document'    => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'ppl_number'      => 'nullable|string',
            'ppl_document'    => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'termin1'         => 'nullable|in:belum,sudah',
            'termin2'         => 'nullable|in:belum,sudah',
            'garansi_months'  => 'nullable|integer|min:1|max:12',
            'garansi_label'   => 'nullable|string|max:255',
        ]);

        // ambil model (atau buat baru, belum disimpan)
        $lpj = Lpj::firstOrNew(['notification_number' => $notification_number]);

        // handle LPJ file: hanya simpan jika ada file baru
        if ($request->hasFile('lpj_document')) {
            if ($lpj->lpj_document_path && \Storage::disk('public')->exists($lpj->lpj_document_path)) {
                \Storage::disk('public')->delete($lpj->lpj_document_path);
            }
            $lpjExtension = $request->file('lpj_document')->getClientOriginalExtension();
            $lpjFileName  = "LPJ_BMS_{$notification_number}." . $lpjExtension;
            $lpj->lpj_document_path = $request->file('lpj_document')->storeAs('lpj_documents', $lpjFileName, 'public');
        }

        // handle PPL file: hanya simpan jika ada file baru
        if ($request->hasFile('ppl_document')) {
            if ($lpj->ppl_document_path && \Storage::disk('public')->exists($lpj->ppl_document_path)) {
                \Storage::disk('public')->delete($lpj->ppl_document_path);
            }
            $pplExtension = $request->file('ppl_document')->getClientOriginalExtension();
            $pplFileName  = "PPL_BMS_{$notification_number}." . $pplExtension;
            $lpj->ppl_document_path = $request->file('ppl_document')->storeAs('ppl_documents', $pplFileName, 'public');
        }

        // set atribut lain
        $lpj->lpj_number     = $request->lpj_number;
        $lpj->ppl_number     = $request->ppl_number ?? $lpj->ppl_number;
        $lpj->termin1        = $request->input('termin1', $lpj->termin1 ?? 'belum');
        $lpj->termin2        = $request->input('termin2', $lpj->termin2 ?? 'belum');
        $lpj->garansi_months = $request->input('garansi_months');
        $lpj->garansi_label  = $request->input('garansi_label');
        $lpj->update_date    = now();

        // simpan model sekali
        $lpj->save();

        return redirect()->route('admin.lpj')->with('success', 'Data LPJ berhasil diupdate!');
    } catch (\Exception $e) {
        Log::error('Error di updateLpj(): ' . $e->getMessage(), ['notif' => $notification_number]);
        return back()->with('error', 'Gagal memperbarui LPJ.')->setStatusCode(500);
    } finally {
        // pastikan lock dibersihkan agar tidak terkunci selamanya
        Cache::forget($lockKey);
    }
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
        'outline_agreement'       => 'required|string',
        'unit_work'               => 'required|string',
        'jenis_kontrak'           => 'required|string',
        'nama_kontrak'            => 'required|string',
        'nilai_kontrak'           => 'required|numeric',
        'tambahan_kuota_kontrak'  => 'nullable|numeric',
        'total_kuota_kontrak'     => 'required|numeric',
        'periode_kontrak_start'   => 'required|date',
        'periode_kontrak_end'     => 'required|date',
        'adendum_end'             => 'nullable|date',
        'target_biaya_pemeliharaan' => 'nullable|numeric', // ⬅️ baru
    ]);

    KuotaAnggaranOA::updateOrCreate(
        ['outline_agreement' => $request->outline_agreement],
        [
            'unit_work'               => $request->unit_work,
            'jenis_kontrak'           => $request->jenis_kontrak,
            'nama_kontrak'            => $request->nama_kontrak,
            'nilai_kontrak'           => $request->nilai_kontrak,
            'tambahan_kuota_kontrak'  => $request->tambahan_kuota_kontrak,
            // tetap pakai perhitungan lama:
            'total_kuota_kontrak'     => $request->nilai_kontrak + ($request->tambahan_kuota_kontrak ?? 0),
            'periode_kontrak_start'   => $request->periode_kontrak_start,
            'periode_kontrak_end'     => $request->periode_kontrak_end,
            'adendum_end'             => $request->adendum_end,
            'periode_kontrak_final'   => $request->periode_kontrak_final ?? $request->periode_kontrak_end,
            'target_biaya_pemeliharaan' => $request->target_biaya_pemeliharaan, // ⬅️ baru
        ]
    );

    return redirect()->route('admin.updateoa')->with('success', 'Data Outline Agreement berhasil disimpan!');
}

}


