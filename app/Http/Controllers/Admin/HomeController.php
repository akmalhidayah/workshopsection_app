<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Hpp1;
use App\Models\SPKApprovalToken;
use App\Models\ScopeOfWork; 
use App\Models\JenisKawatLas;
use App\Models\KawatLas;
use App\Models\KawatLasDetail;
use App\Models\VerifikasiAnggaran;
use App\Models\Lpj;
use App\Models\KuotaAnggaranOA;
use App\Models\UnitWork;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;




class HomeController extends Controller
{
   public function index()
{
    // Ambil semua notifikasi dengan eager loading
    $notifications = Notification::with(['dokumenOrders', 'scopeOfWork', 'spk'])->get();

    // Ambil semua notifikasi yang sudah memiliki dokumen HPP
    $notificationsWithHPP = DB::table('hpp1')->pluck('notification_number')->toArray();

    /**
     * ===========================================================
     * LPJ + PPL FIX (mendukung kolom baru / termin)
     * ===========================================================
     */
    $lpjCols = [];
    $pplCols = [];

    $tbl = 'lpjs';

    // cek kolom LPJ
    foreach (['lpj_number', 'lpj_number_termin1', 'lpj_number_termin2'] as $col) {
        if (Schema::hasColumn($tbl, $col)) $lpjCols[] = $col;
    }

    // cek kolom PPL
    foreach (['ppl_number', 'ppl_number_termin1', 'ppl_number_termin2'] as $col) {
        if (Schema::hasColumn($tbl, $col)) $pplCols[] = $col;
    }

    if (empty($lpjCols) || empty($pplCols)) {
        $lpjNotifications = [];
    } else {
        $lpjQuery = DB::table($tbl);

        $lpjQuery->where(function ($q) use ($lpjCols) {
            foreach ($lpjCols as $col) {
                $q->orWhereNotNull($col);
            }
        });

        $lpjQuery->where(function ($q) use ($pplCols) {
            foreach ($pplCols as $col) {
                $q->orWhereNotNull($col);
            }
        });

        $lpjNotifications = $lpjQuery->pluck('notification_number')->toArray();
    }

    // Hitung total realisasi biaya berdasarkan total_amount dari tabel HPP
    $totalRealisasiBiaya = DB::table('hpp1')
        ->whereIn('notification_number', $lpjNotifications)
        ->sum('total_amount');

    // Approval Process (HPP)
    $approvalNotifications = DB::table('hpp1')
        ->whereNotNull('general_manager_signature_requesting_unit')
        ->pluck('notification_number')
        ->toArray();

    // Purchase Order
    $poNotifications = DB::table('purchase_orders')
        ->whereNotNull('purchase_order_number')
        ->pluck('notification_number')
        ->toArray();

    // LHPP
    $lhppNotificationNumbers = DB::table('lhpp')->pluck('notification_number')->toArray();

    /**
     * ================= JML DATA =================
     */
    $outstandingNotifications = $notifications->filter(function ($notification) use (
        $notificationsWithHPP,
        $approvalNotifications,
        $poNotifications
    ) {
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

    /**
     * ================= TOTAL AMOUNT =================
     */
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

    $urgentNotificationNumbers = DB::table('notifications')
        ->where('priority', 'Urgently')
        ->pluck('notification_number')
        ->toArray();

    $urgentAmount = DB::table('hpp1')
        ->whereIn('notification_number', $urgentNotificationNumbers)
        ->sum('total_amount');

    $documentPRPOAmount = DB::table('hpp1')
        ->whereIn('notification_number', $lhppNotificationNumbers)
        ->whereNotIn('notification_number', $urgentNotificationNumbers)
        ->sum('total_amount');

    if ($documentPRPOAmount > 0) {
        $documentOnProcessPOAmount = DB::table('hpp1')
            ->whereIn('notification_number', $poNotifications)
            ->whereNotIn('notification_number', $lhppNotificationNumbers)
            ->sum('total_amount');
    }

    $totalAmount1 = $documentOnProcessHPPAmount + $approvalProcessHPPAmount + $documentOnProcessPOAmount;
    $totalAmount2 = $documentPRPOAmount + $urgentAmount;
    $totalSeluruhAmount = $totalAmount1 + $totalAmount2;


    // ================= Kuota OA =================
    $latestKuotaAnggaran = DB::table('kuota_anggaran_oa')->latest('created_at')->first();

    if ($latestKuotaAnggaran) {
        if (is_string($latestKuotaAnggaran->target_biaya_pemeliharaan)) {
            $decoded = json_decode($latestKuotaAnggaran->target_biaya_pemeliharaan, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $latestKuotaAnggaran->target_biaya_pemeliharaan = $decoded;
            }
        }

        if (is_string($latestKuotaAnggaran->tahun)) {
            $decodedYears = json_decode($latestKuotaAnggaran->tahun, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $latestKuotaAnggaran->tahun = $decodedYears;
            }
        }
    }

    $totalKuotaKontrak = $latestKuotaAnggaran->total_kuota_kontrak ?? 0;

    $periodeKontrak = [
        'start' => $latestKuotaAnggaran->periode_kontrak_start ?? null,
        'end' => $latestKuotaAnggaran->periode_kontrak_end ?? null,
        'adendum' => $latestKuotaAnggaran->adendum_end ?? null,
    ];

    $sisaKuotaKontrak = $totalKuotaKontrak - $totalRealisasiBiaya;
    $targetPemeliharaan = optional($latestKuotaAnggaran)->target_biaya_pemeliharaan ?? null;

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
        $request->validate([
            'startYear' => 'required|numeric',
            'endYear' => 'required|numeric|gte:startYear',
            'startMonth' => 'nullable|numeric|min:1|max:12',
            'endMonth' => 'nullable|numeric|min:1|max:12|gte:startMonth',
        ]);

        $startYear = $request->startYear;
        $endYear = $request->endYear;
        $startMonth = $request->startMonth;
        $endMonth = $request->endMonth;

        // ===== LPJ FILTER FIX =====
        $tbl = 'lpjs';
        $lpjCols = [];
        $pplCols = [];

        foreach (['lpj_number', 'lpj_number_termin1', 'lpj_number_termin2'] as $c)
            if (Schema::hasColumn($tbl, $c)) $lpjCols[] = $c;

        foreach (['ppl_number', 'ppl_number_termin1', 'ppl_number_termin2'] as $c)
            if (Schema::hasColumn($tbl, $c)) $pplCols[] = $c;

        if (empty($lpjCols) || empty($pplCols)) {
            return response()->json([]);
        }

        $lpjQuery = DB::table($tbl)
            ->whereBetween(DB::raw('YEAR(update_date)'), [$startYear, $endYear]);

        if ($startMonth && $endMonth) {
            $lpjQuery->whereBetween(DB::raw('MONTH(update_date)'), [$startMonth, $endMonth]);
        }

        $lpjQuery->where(function ($q) use ($lpjCols) {
            foreach ($lpjCols as $col) $q->orWhereNotNull($col);
        });

        $lpjQuery->where(function ($q) use ($pplCols) {
            foreach ($pplCols as $col) $q->orWhereNotNull($col);
        });

        $lpjNumbers = $lpjQuery->pluck('notification_number')->toArray();

        if (empty($lpjNumbers)) return response()->json([]);

        // hasil realisasi
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
    $with = ['dokumenOrders', 'scopeOfWork','verifikasiAnggaran', 'spk'];
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
    // ===============================
// ACTIVE TOKEN SPK (untuk index)
// ===============================
$activeSpkTokens = collect();

try {
    $notifNumbers = $notifications
        ->pluck('notification_number')
        ->filter()
        ->unique()
        ->values()
        ->all();

    if (!empty($notifNumbers)) {
        $activeSpkTokens = SPKApprovalToken::whereIn('notification_number', $notifNumbers)
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('notification_number')
            ->map(fn ($g) => $g->first()); // token aktif terbaru
    }
} catch (\Throwable $e) {
    Log::error('[SPK] gagal ambil active token', [
        'error' => $e->getMessage()
    ]);
}


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
        'units',
        'activeSpkTokens'
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

            $notification->status_anggaran    = $v?->status_anggaran    ?? 'Menunggu';
            $notification->cost_element       = $v?->cost_element       ?? '';
            $notification->kategori_biaya     = $v?->kategori_biaya     ?? null;
            $notification->kategori_item      = $v?->kategori_item      ?? null;
            $notification->nomor_e_korin      = $v?->nomor_e_korin      ?? null;
            $notification->status_e_korin     = $v?->status_e_korin     ?? null;
            $notification->catatan            = $v?->catatan            ?? '';
            $notification->tanggal_verifikasi = $v?->tanggal_verifikasi ?? null;

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
   'status_e_korin'  => 'nullable|in:waiting_korin,waiting_approval,waiting_transfer,complete_transfer',
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

// ---------------------------
// function lpj (tidak banyak diubah, hanya konsisten)
// ---------------------------
// di App\Http\Controllers\Admin\HomeController (method lpj)
public function lpj(Request $request)
{
    try {
        $search  = $request->input('search', null);
        $poFilter = $request->input('po', null);
        $entries = (int) $request->input('entries', 10);

        $query = \App\Models\Notification::with(['dokumenOrders', 'scopeOfWork']);

        // If user searches by notification_number OR we will filter by PO below
        if ($search) {
            $search = trim($search);
            $query->where('notification_number', 'like', "%{$search}%");
        }

        // If filter by PO number requested -> find notifications that have that PO
        if ($poFilter) {
            $poMatches = \App\Models\PurchaseOrder::where('purchase_order_number', $poFilter)
                            ->pluck('notification_number')
                            ->toArray();

            if (!empty($poMatches)) {
                $query->whereIn('notification_number', $poMatches);
            } else {
                // no matches -> return empty paginator
                $notifications = collect([])->paginate($entries);
                // but simplest: make query match nothing
                $query->whereRaw('0 = 1');
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')
                               ->paginate($entries)
                               ->appends($request->all());

        // batch-load maps for per-row lookups (LPJ, LHPP, PO) to avoid N+1
        $notificationNumbersOnPage = $notifications->pluck('notification_number')->toArray();

        $lpjMap = \App\Models\Lpj::whereIn('notification_number', $notificationNumbersOnPage)
                    ->get()
                    ->keyBy('notification_number');

        $lhppMap = \App\Models\LHPP::whereIn('notification_number', $notificationNumbersOnPage)
                    ->get()
                    ->keyBy('notification_number');

        $poMap = \App\Models\PurchaseOrder::whereIn('notification_number', $notificationNumbersOnPage)
                    ->get()
                    ->keyBy('notification_number');

        // --- NEW: options for PO dropdown (distinct purchase_order_number)
        $poOptions = \App\Models\PurchaseOrder::select('purchase_order_number')
                      ->whereNotNull('purchase_order_number')
                      ->distinct()
                      ->orderBy('purchase_order_number')
                      ->pluck('purchase_order_number');

        return view('admin.lpj', [
            'notifications' => $notifications,
            'lpjMap' => $lpjMap,
            'lhppMap' => $lhppMap,
            'poMap' => $poMap,
            'poOptions' => $poOptions,
            'request' => $request,
        ]);
    } catch (\Exception $e) {
        \Log::error('Error di lpj(): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->view('errors.500', [
            'message' => 'Terjadi kesalahan saat memuat data LPJ.'
        ], 500);
    }
}
public function updateLpj(Request $request, $notification_number)
{
    $request->validate([
        'lpj_number' => 'required|string',
        'ppl_number' => 'nullable|string',
        'selected_termin' => 'required|in:1,2',

        // FILE PER TERMIN
        'lpj_document' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        'ppl_document' => 'nullable|file|mimes:pdf,doc,docx|max:2048',

        'lpj_document_termin2' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        'ppl_document_termin2' => 'nullable|file|mimes:pdf,doc,docx|max:2048',

        // pembayaran
        'termin1' => 'nullable|in:belum,sudah',
        'termin2' => 'nullable|in:belum,sudah',
    ]);

    // Ambil atau buat LPJ record
    $lpj = Lpj::firstOrNew(['notification_number' => $notification_number]);

    $termin = $request->selected_termin;

    // =============================
    // 1. SIMPAN NOMOR PER TERMIN
    // =============================

    if ($termin == 1) {
        $lpj->lpj_number_termin1 = $request->lpj_number;
        $lpj->ppl_number_termin1 = $request->ppl_number;
    } else {
        $lpj->lpj_number_termin2 = $request->lpj_number;
        $lpj->ppl_number_termin2 = $request->ppl_number;
    }

    // =============================
    // 2. UPLOAD FILE PER TERMIN
    // =============================

    /* --- TERMIN 1 --- */
    if ($termin == 1) {

        // LPJ T1
        if ($request->hasFile('lpj_document')) {
            if ($lpj->lpj_document_path_termin1 && Storage::disk('public')->exists($lpj->lpj_document_path_termin1)) {
                Storage::disk('public')->delete($lpj->lpj_document_path_termin1);
            }

            $ext = $request->file('lpj_document')->getClientOriginalExtension();
            $filename = "LPJ_T1_{$notification_number}.".$ext;

            $lpj->lpj_document_path_termin1 =
                $request->file('lpj_document')->storeAs('lpj_documents', $filename, 'public');
        }

        // PPL T1
        if ($request->hasFile('ppl_document')) {
            if ($lpj->ppl_document_path_termin1 && Storage::disk('public')->exists($lpj->ppl_document_path_termin1)) {
                Storage::disk('public')->delete($lpj->ppl_document_path_termin1);
            }

            $ext = $request->file('ppl_document')->getClientOriginalExtension();
            $filename = "PPL_T1_{$notification_number}.".$ext;

            $lpj->ppl_document_path_termin1 =
                $request->file('ppl_document')->storeAs('ppl_documents', $filename, 'public');
        }
    }

    /* --- TERMIN 2 --- */
    if ($termin == 2) {

        // LPJ T2
        if ($request->hasFile('lpj_document_termin2')) {
            if ($lpj->lpj_document_path_termin2 && Storage::disk('public')->exists($lpj->lpj_document_path_termin2)) {
                Storage::disk('public')->delete($lpj->lpj_document_path_termin2);
            }

            $ext = $request->file('lpj_document_termin2')->getClientOriginalExtension();
            $filename = "LPJ_T2_{$notification_number}.".$ext;

            $lpj->lpj_document_path_termin2 =
                $request->file('lpj_document_termin2')->storeAs('lpj_documents', $filename, 'public');
        }

        // PPL T2
        if ($request->hasFile('ppl_document_termin2')) {
            if ($lpj->ppl_document_path_termin2 && Storage::disk('public')->exists($lpj->ppl_document_path_termin2)) {
                Storage::disk('public')->delete($lpj->ppl_document_path_termin2);
            }

            $ext = $request->file('ppl_document_termin2')->getClientOriginalExtension();
            $filename = "PPL_T2_{$notification_number}.".$ext;

            $lpj->ppl_document_path_termin2 =
                $request->file('ppl_document_termin2')->storeAs('ppl_documents', $filename, 'public');
        }
    }

    // =============================
    // 3. PEMBAYARAN (GARANSI DIHAPUS)
    // =============================

    $lpj->termin1 = $request->termin1 ?? $lpj->termin1 ?? 'belum';
    $lpj->termin2 = $request->termin2 ?? $lpj->termin2 ?? 'belum';

    $lpj->update_date = now();

    $lpj->save();

    return back()->with('success', 'LPJ berhasil diperbarui!');
}


    public function updateOA(Request $request)
{
    // Cek apakah ada parameter 'new' untuk membuat OA baru
    $latestData = $request->has('new') ? null : KuotaAnggaranOA::latest()->first();


    $unitWorks = UnitWork::orderBy('name')->get();

    return view('admin.updateoa', compact('latestData', 'unitWorks'));

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
        'periode_kontrak_start'   => 'required|date',
        'periode_kontrak_end'     => 'required|date',
        'adendum_end'             => 'nullable|date',
        'target_biaya_pemeliharaan' => 'nullable|array', // karena sudah JSON array
        'tahun'                     => 'nullable|array', // baru
    ]);

    // hitung total otomatis
    $total = ($request->nilai_kontrak ?? 0) + ($request->tambahan_kuota_kontrak ?? 0);

    KuotaAnggaranOA::updateOrCreate(
        ['outline_agreement' => $request->outline_agreement],
        [
            'unit_work'               => $request->unit_work,
            'jenis_kontrak'           => $request->jenis_kontrak,
            'nama_kontrak'            => $request->nama_kontrak,
            'nilai_kontrak'           => $request->nilai_kontrak,
            'tambahan_kuota_kontrak'  => $request->tambahan_kuota_kontrak,
            'total_kuota_kontrak'     => $total,
            'periode_kontrak_start'   => $request->periode_kontrak_start,
            'periode_kontrak_end'     => $request->periode_kontrak_end,
            'adendum_end'             => $request->adendum_end,
            'periode_kontrak_final'   => $request->periode_kontrak_final ?? $request->periode_kontrak_end,
            'target_biaya_pemeliharaan' => $request->target_biaya_pemeliharaan,
            'tahun'                     => $request->tahun,
        ]
    );

    return redirect()->route('admin.updateoa')->with('success', 'Data Outline Agreement berhasil disimpan!');
}

}


