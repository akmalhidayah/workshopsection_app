<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SPK;
use App\Models\User;
use App\Models\DokumenOrder;
use App\Models\SPKApprovalToken;
use App\Services\SPKApprovalLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SPKApprovalController extends Controller
{
    /* =====================================================
     * INDEX
     * ===================================================== */
    public function index()
    {
        $spks = SPK::latest()
            ->get()
            ->filter(fn (SPK $spk) => ! $spk->isFullyApproved());

        return view('admin.spk.approval_index', compact('spks'));
    }

    /* =====================================================
     * SHOW (TOKEN)
     * ===================================================== */
    public function show(string $token, SPKApprovalLinkService $svc)
    {
        $link = $svc->validate($token);
        $user = auth()->user();

        if ((int) $link->user_id !== (int) $user->id) {
            abort(Response::HTTP_FORBIDDEN, 'Token bukan milik Anda.');
        }

        $spk = SPK::findOrFail($link->nomor_spk);

        if (! $this->canSignForType($user, $link->sign_type)) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak berhak sign SPK ini.');
        }

        if (! $this->canProceed($spk, $link->sign_type)) {
            abort(Response::HTTP_FORBIDDEN, 'Urutan approval belum sesuai.');
        }
        $dokumenAbnormalitas = DokumenOrder::where('notification_number', $spk->notification_number)
    ->where('jenis_dokumen', 'abnormalitas')
    ->first();


        return view('admin.inputspk.approval', [
            'spk'       => $spk,
            'token'     => $token,
            'sign_type' => $link->sign_type,
            'signTypeLabel' => $this->label($link->sign_type),
                'dokumenAbnormalitas' => $dokumenAbnormalitas,
        ]);
    }

    /* =====================================================
     * SIGN
     * ===================================================== */
    public function sign(string $token, Request $request, SPKApprovalLinkService $svc)
    {
        $request->validate([
            'signature_base64' => 'required|string',
        ]);

        $link = $svc->validate($token);
        $user = auth()->user();

        if ((int) $link->user_id !== (int) $user->id) {
            abort(Response::HTTP_FORBIDDEN, 'Token bukan milik Anda.');
        }

        $spk = SPK::findOrFail($link->nomor_spk);

        if (! $this->canSignForType($user, $link->sign_type)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if (! $this->canProceed($spk, $link->sign_type)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        /* ===============================
         * SIMPAN FILE TTD
         * =============================== */
        $path = $this->storeSignature(
            $request->signature_base64,
            $spk->nomor_spk,
            $link->sign_type
        );

        if ($link->sign_type === 'manager') {
            $spk->update([
                'manager_signature'         => $path,
                'manager_signature_user_id' => $user->id,
                'manager_signed_at'         => now(),
            ]);

            // ISSUE TOKEN SENIOR MANAGER
            $this->issueNextToken($spk, 'senior_manager');
        }

        if ($link->sign_type === 'senior_manager') {
            $spk->update([
                'senior_manager_signature'         => $path,
                'senior_manager_signature_user_id' => $user->id,
                'senior_manager_signed_at'         => now(),
            ]);
        }

        $svc->markUsed($link);

       return redirect()
    ->route('spk.show', $spk->notification_number)
    ->with('success', 'SPK berhasil ditandatangani.');

    }

    /* =====================================================
     * SIGNATURE STORAGE
     * ===================================================== */
    private function storeSignature(string $base64, string $nomorSpk, string $type): string
    {
        $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
        $binary = base64_decode($base64);

        $filename = sprintf(
            'spk/%s_%s_%s.png',
            str_replace('/', '-', $nomorSpk),
            $type,
            now()->format('YmdHis')
        );

        Storage::disk('public')->put($filename, $binary);

        return $filename;
    }

    /* =====================================================
     * ISSUE NEXT TOKEN
     * ===================================================== */
    private function issueNextToken(SPK $spk, string $type): void
    {
        $user = User::query()
            ->whereRaw('LOWER(jabatan) LIKE ?', ['%senior%'])
            ->whereRaw('LOWER(unit_work) LIKE ?', ['%workshop%'])
            ->first();

        if (! $user) {
            Log::warning('[SPK] Senior Manager tidak ditemukan');
            return;
        }

        app(SPKApprovalLinkService::class)->issue(
            nomorSpk: $spk->nomor_spk,
            notificationNumber: $spk->notification_number,
            signType: $type,
            userId: $user->id,
            minutes: 60 * 24
        );
    }

    /* =====================================================
     * HELPERS
     * ===================================================== */
    private function canSignForType(User $user, string $type): bool
    {
        $jabatan = strtolower($user->jabatan ?? '');
        $unit    = strtolower($user->unit_work ?? '');

        return match ($type) {
            'manager' =>
                str_contains($jabatan, 'manager') && str_contains($unit, 'workshop'),

            'senior_manager' =>
                str_contains($jabatan, 'senior') && str_contains($unit, 'workshop'),

            default => false,
        };
    }

    private function canProceed(SPK $spk, string $type): bool
    {
        return match ($type) {
            'manager' =>
                ! $spk->isManagerSigned(),

            'senior_manager' =>
                $spk->isManagerSigned() && ! $spk->isSeniorManagerSigned(),

            default => false,
        };
    }

    private function label(string $type): string
    {
        return match ($type) {
            'manager'        => 'Manager Workshop',
            'senior_manager' => 'Senior Manager Workshop',
            default          => ucfirst($type),
        };
    }
}
