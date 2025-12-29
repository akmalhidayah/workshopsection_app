<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SPK;
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
    /* =====================================================
     * ISSUE TOKEN PERTAMA
     * → MANAGER WORKSHOP
     * ===================================================== */
    private function issueFirstTokenForSPK(SPK $spk): void
    {
        $manager = User::query()
            ->whereRaw('LOWER(jabatan) = ?', ['manager'])
            ->where(function ($q) {
                $q->whereRaw('LOWER(unit_work) LIKE ?', ['%workshop%'])
                  ->orWhereRaw('LOWER(unit_work) LIKE ?', ['%bengkel%']);
            })
            ->first();

        if (! $manager) {
            Log::warning('[SPK] Manager Workshop tidak ditemukan', [
                'nomor_spk' => $spk->nomor_spk,
            ]);
            return;
        }

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
            minutes: 60 * 24
        );

        Log::info('[SPK] First approval token issued', [
            'nomor_spk' => $spk->nomor_spk,
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
            'unit_work'            => 'required|string|max:255',

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

            $spk = SPK::create([
                'nomor_spk'            => $nomorSpk,
                'perihal'              => $validated['perihal'],
                'tanggal_spk'          => $validated['tanggal_spk'],
                'notification_number'  => $validated['notification_number'],
                'unit_work'            => $validated['unit_work'],

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
