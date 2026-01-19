<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SPK;
use App\Models\User;
use App\Models\DokumenOrder;
use App\Models\SPKApprovalToken;
use App\Models\Notification;
use App\Models\UnitWork;
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

        if (! $user || (int) $link->user_id !== (int) $user->id) {
            abort(Response::HTTP_FORBIDDEN, 'Token bukan milik Anda.');
        }

        $spk = SPK::findOrFail($link->nomor_spk);

        // cek hak sign berdasarkan struktur Workshop (fixed Unit of Workshop & Design)
        if (! $this->canSignForType($user, $link->sign_type, $spk)) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak berhak sign SPK ini.');
        }

        // cek urutan approval (manager -> senior_manager)
        if (! $this->canProceed($spk, $link->sign_type)) {
            abort(Response::HTTP_FORBIDDEN, 'Urutan approval belum sesuai.');
        }

        $dokumenAbnormalitas = DokumenOrder::where('notification_number', $spk->notification_number)
            ->where('jenis_dokumen', 'abnormalitas')
            ->first();

        return view('admin.inputspk.approval', [
            'spk'                 => $spk,
            'token'               => $token,
            'sign_type'           => $link->sign_type,
            'signTypeLabel'       => $this->label($link->sign_type),
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

        if (! $user || (int) $link->user_id !== (int) $user->id) {
            abort(Response::HTTP_FORBIDDEN, 'Token bukan milik Anda.');
        }

        $spk = SPK::findOrFail($link->nomor_spk);

        // cek hak sign berdasarkan struktur Workshop
        if (! $this->canSignForType($user, $link->sign_type, $spk)) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak berhak sign SPK ini.');
        }

        // cek urutan
        if (! $this->canProceed($spk, $link->sign_type)) {
            abort(Response::HTTP_FORBIDDEN, 'Urutan approval belum sesuai.');
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

            // ISSUE TOKEN SENIOR MANAGER (berdasarkan struktur Unit of Workshop & Design)
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
     * ISSUE NEXT TOKEN (SENIOR MANAGER)
     * ===================================================== */
    private function issueNextToken(SPK $spk, string $type): void
    {
        // ambil Senior Manager dari struktur Unit of Workshop & Design
        $user = $this->getSpkSeniorManagerUser($spk);

        if (! $user) {
            Log::warning('[SPK] Senior Manager Workshop tidak ditemukan (struktur org)', [
                'nomor_spk' => $spk->nomor_spk,
            ]);
            return;
        }

        app(SPKApprovalLinkService::class)->issue(
            nomorSpk: $spk->nomor_spk,
            notificationNumber: $spk->notification_number,
            signType: $type,
            userId: $user->id,
            minutes: 60 * 24
        );

        Log::info('[SPK] Token untuk Senior Manager Workshop issued', [
            'nomor_spk' => $spk->nomor_spk,
            'user_id'   => $user->id,
        ]);
    }

    /* =====================================================
     * HELPERS: APPROVAL FLOW
     * ===================================================== */

    /**
     * Cek apakah user ini boleh sign SPK untuk sign_type tertentu
     * berdasarkan struktur Workshop (Unit of Workshop & Design).
     */
    private function canSignForType(User $user, string $type, SPK $spk): bool
    {
        $expected = match ($type) {
            'manager'        => $this->getSpkManagerUser($spk),
            'senior_manager' => $this->getSpkSeniorManagerUser($spk),
            default          => null,
        };

        if (! $expected) {
            Log::warning('[SPK] Expected approver tidak ditemukan (struktur org belum lengkap)', [
                'nomor_spk' => $spk->nomor_spk,
                'sign_type' => $type,
            ]);
            return false;
        }

        return (int) $expected->id === (int) $user->id;
    }

    /**
     * Cek urutan approval:
     * - manager        : boleh kalau manager belum sign
     * - senior_manager : boleh kalau manager sudah sign dan senior_manager belum sign
     */
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

    /* =====================================================
     * RESOLVER APPROVER BERDASAR STRUKTUR WORKSHOP
     * ===================================================== */

    /**
     * Manager Workshop untuk SPK:
     * Selalu Manager dari:
     *  - UnitWork.name  = 'Unit of Workshop & Design'
     *  - Section.name   = 'Section of Machine Workshop'
     */
    private function getSpkManagerUser(SPK $spk): ?User
    {
        // notification hanya dipakai untuk logging bila perlu
        $notification = Notification::where('notification_number', $spk->notification_number)->first();

        $unitWork = UnitWork::where('name', 'Unit of Workshop & Design')->first();

        if (! $unitWork) {
            Log::warning('[SPK][Resolver] UnitWork Workshop (Unit of Workshop & Design) tidak ditemukan', [
                'nomor_spk' => $spk->nomor_spk,
                'notif'     => $spk->notification_number,
                'notif_unit_work' => $notification?->unit_work,
            ]);
            return null;
        }

        $section = $unitWork->sections()
            ->where('name', 'Section of Machine Workshop')
            ->first();

        if (! $section) {
            Log::warning('[SPK][Resolver] Section of Machine Workshop tidak ditemukan', [
                'nomor_spk' => $spk->nomor_spk,
                'notif'     => $spk->notification_number,
                'unit_work' => $unitWork->name,
            ]);
            return null;
        }

        if (! $section->manager) {
            Log::warning('[SPK][Resolver] Manager Section of Machine Workshop belum di-set', [
                'nomor_spk' => $spk->nomor_spk,
                'notif'     => $spk->notification_number,
                'seksi'     => $section->name,
            ]);
            return null;
        }

        return $section->manager;
    }

    /**
     * Senior Manager Workshop untuk SPK:
     * UnitWork 'Unit of Workshop & Design' -> relasi seniorManager
     */
    private function getSpkSeniorManagerUser(SPK $spk): ?User
    {
        // notification lagi-lagi cuma buat logging
        $notification = Notification::where('notification_number', $spk->notification_number)->first();

        $unitWork = UnitWork::where('name', 'Unit of Workshop & Design')->first();

        if (! $unitWork) {
            Log::warning('[SPK][Resolver] UnitWork Workshop (Unit of Workshop & Design) tidak ditemukan untuk Senior Manager', [
                'nomor_spk' => $spk->nomor_spk,
                'notif'     => $spk->notification_number,
                'notif_unit_work' => $notification?->unit_work,
            ]);
            return null;
        }

        if (! $unitWork->seniorManager) {
            Log::warning('[SPK][Resolver] Senior Manager untuk Unit of Workshop & Design belum di-set', [
                'nomor_spk'  => $spk->nomor_spk,
                'notif'      => $spk->notification_number,
                'unit_work'  => $unitWork->name,
            ]);
            return null;
        }

        return $unitWork->seniorManager;
    }
}
