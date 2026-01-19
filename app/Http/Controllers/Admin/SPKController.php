<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SPK;
use App\Models\UnitWork;
use App\Models\SPKApprovalToken;
use App\Services\SPKApprovalLinkService;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SPKController extends Controller
{
/* =/* =====================================================
 * ISSUE TOKEN PERTAMA
 * → MANAGER WORKSHOP (fixed ke Unit of Workshop & Design)
 * ===================================================== */
private function issueFirstTokenForSPK(SPK $spk): void
{
    // Optional: untuk logging saja
    $notification = Notification::where('notification_number', $spk->notification_number)->first();

    if (! $notification) {
        Log::warning('[SPK] Notification tidak ditemukan saat issueFirstToken', [
            'nomor_spk' => $spk->nomor_spk,
            'notif'     => $spk->notification_number,
        ]);
        // tetap kita stop di sini, karena ini kasus aneh
        return;
    }

    // 👉 TIDAK memakai $notification->unit_work dan $notification->seksi lagi
    // Selalu pakai Unit of Workshop & Design + Section of Machine Workshop

    $unitWork = UnitWork::where('name', 'Unit of Workshop & Design')->first();

    if (! $unitWork) {
        Log::warning('[SPK] UnitWork Workshop (Unit of Workshop & Design) tidak ditemukan', [
            'nomor_spk' => $spk->nomor_spk,
            'notif'     => $spk->notification_number,
        ]);
        return;
    }

    /** @var \App\Models\UnitWorkSection|null $section */
    $section = $unitWork->sections()
        ->where('name', 'Section of Machine Workshop')
        ->first();

    if (! $section) {
        Log::warning('[SPK] Section of Machine Workshop tidak ditemukan', [
            'nomor_spk' => $spk->nomor_spk,
            'notif'     => $spk->notification_number,
            'unit_work' => $unitWork->name,
        ]);
        return;
    }

    $manager = $section->manager;

    if (! $manager) {
        Log::warning('[SPK] Manager Section of Machine Workshop belum di-set', [
            'nomor_spk' => $spk->nomor_spk,
            'notif'     => $spk->notification_number,
            'seksi'     => $section->name,
        ]);
        return;
    }

    // Cek apakah token aktif sudah ada untuk SPK + user ini
    $exists = SPKApprovalToken::where('nomor_spk', $spk->nomor_spk)
        ->where('user_id', $manager->id)
        ->where('sign_type', 'manager')
        ->whereNull('used_at')
        ->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        })
        ->exists();

    if ($exists) {
        Log::info('[SPK] Token manager sudah ada', [
            'nomor_spk' => $spk->nomor_spk,
            'user_id'   => $manager->id,
        ]);
        return;
    }

    $svc = app(SPKApprovalLinkService::class);

    $svc->issue(
        nomorSpk: $spk->nomor_spk,
        notificationNumber: $spk->notification_number,
        signType: 'manager',
        userId: $manager->id,
        minutes: 60 * 24 // 1 hari
    );

    Log::info('[SPK] First approval token issued (Manager Workshop)', [
        'nomor_spk' => $spk->nomor_spk,
        'notif'     => $spk->notification_number,
        'user_id'   => $manager->id,
    ]);
}


    /* =====================================================
     * FORM CREATE SPK
     * ===================================================== */
    public function create(string $notificationNumber)
    {
        $notification = Notification::where('notification_number', $notificationNumber)
            ->firstOrFail();

        $now   = now();
        $month = $now->format('m');
        $year  = $now->format('Y');

        $lastSpk = SPK::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderByDesc('created_at')
            ->first();

        $nextNumber = '001';
        if ($lastSpk && preg_match('/^(\d{3})\//', $lastSpk->nomor_spk, $m)) {
            $nextNumber = str_pad(((int) $m[1]) + 1, 3, '0', STR_PAD_LEFT);
        }

        $nomorSpkOtomatis = sprintf('%s/IW/25.10/%s-%s', $nextNumber, $month, $year);

        return view('admin.inputspk.createspk', compact(
            'notification',
            'nomorSpkOtomatis'
        ));
    }

    /* =====================================================
     * STORE SPK
     * ===================================================== */
public function store(Request $request)
{
    $validated = $request->validate([
        'perihal'              => 'required|string|max:255',
        'tanggal_spk'          => 'required|date',
        'notification_number'  => 'required|exists:notifications,notification_number',
        // 'unit_work'         => 'required|string|max:255', // ❌ dihapus

        'functional_location'  => 'required|array',
        'scope_pekerjaan'      => 'required|array',
        'qty'                  => 'required|array',
        'stn'                  => 'required|array',
        'keterangan'           => 'required|array',

        'keterangan_pekerjaan' => 'nullable|string|max:2000',
    ]);

DB::beginTransaction();

try {
    $now   = now();
    $month = $now->format('m');
    $year  = $now->format('Y');

    $lastSpk = SPK::whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->orderByDesc('created_at')
        ->first();

    $nextNumber = '001';
    if ($lastSpk && preg_match('/^(\d{3})\//', $lastSpk->nomor_spk, $m)) {
        $nextNumber = str_pad(((int) $m[1]) + 1, 3, '0', STR_PAD_LEFT);
    }

    $nomorSpk = sprintf('%s/IW/25.10/%s-%s', $nextNumber, $month, $year);

    // 🔹 Ambil unit_work dari Notification supaya konsisten
    $notification = Notification::where('notification_number', $validated['notification_number'])
        ->firstOrFail();

    $spk = SPK::create([
        'nomor_spk'            => $nomorSpk,
        'perihal'              => $validated['perihal'],
        'tanggal_spk'          => $validated['tanggal_spk'],
        'notification_number'  => $validated['notification_number'],
        'unit_work'            => $notification->unit_work, // 🔥 sumber tunggal

        'functional_location'  => $validated['functional_location'],
        'scope_pekerjaan'      => $validated['scope_pekerjaan'],
        'qty'                  => $validated['qty'],
        'stn'                  => $validated['stn'],
        'keterangan'           => $validated['keterangan'],

        'keterangan_pekerjaan' => $validated['keterangan_pekerjaan'] ?? '-',
    ]);

    DB::commit();

    $this->issueFirstTokenForSPK($spk);

    return redirect()
        ->route('notifikasi.index')
        ->with('success_spk', 'SPK berhasil dibuat.');

} catch (\Throwable $e) {
    DB::rollBack();

    Log::error('[SPK] Store gagal', [
        'error' => $e->getMessage(),
    ]);

    return back()
        ->withInput()
        ->with('error_spk', 'Terjadi kesalahan saat menyimpan SPK.');
}
}

    /* =====================================================
     * VIEW / PDF SPK
     * ===================================================== */
    public function show(string $notification_number)
    {
        $spk = SPK::with([
            'managerSignatureUser',
            'seniorManagerSignatureUser',
            'notification',
        ])->where('notification_number', $notification_number)
          ->firstOrFail();

        $pdf = Pdf::loadView('admin.inputspk.viewspk', [
                'spk' => $spk,
            ])
            ->setPaper('A4', 'portrait');

        $filename = 'SPK-' . str_replace(['/', '\\'], '-', $spk->nomor_spk) . '.pdf';

        return $pdf->stream($filename);
    }

    public function view(string $notification_number)
    {
        return $this->show($notification_number);
    }
}
