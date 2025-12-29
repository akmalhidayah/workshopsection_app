<?php

namespace App\Services;

use App\Models\SPK;
use App\Models\SPKApprovalToken;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SPKApprovalLinkService
{
    /**
     * Issue token approval SPK.
     *
     * @param string      $nomorSpk
     * @param string      $notificationNumber
     * @param string      $signType              manager | senior_manager
     * @param int         $userId
     * @param int|null    $minutes               null = no expiry
     *
     * @return string     token UUID
     */
    public function issue(
        string $nomorSpk,
        string $notificationNumber,
        string $signType,
        int $userId,
        ?int $minutes = 1440 // default 24 jam
    ): string {
        // Pastikan SPK valid (guard)
        $spk = SPK::where('nomor_spk', $nomorSpk)->firstOrFail();

        $token = new SPKApprovalToken();
        $token->id                  = (string) Str::uuid();
        $token->nomor_spk           = $spk->nomor_spk;
        $token->notification_number = $notificationNumber;
        $token->sign_type           = $signType;
        $token->user_id             = $userId;

        // ===== AUDIT TRAIL =====
        $token->issued_by      = auth()->id();
        $token->issued_channel = 'system';
        $token->ip_issued      = request()->ip();
        $token->ua_issued      = request()->userAgent();

        // ===== EXPIRY =====
        $token->expires_at = $minutes === null
            ? null
            : Carbon::now()->addMinutes($minutes);

        $token->save();

        return $token->id;
    }

    /**
     * Generate URL approval SPK.
     */
    public function url(string $token): string
    {
        return route('approval.spk.sign', $token);
    }

    /**
     * Validasi token SPK.
     */
    public function validate(string $token): SPKApprovalToken
    {
        $link = SPKApprovalToken::find($token);

        if (! $link) {
            abort(Response::HTTP_NOT_FOUND, 'Token SPK tidak ditemukan.');
        }

        if ($link->used_at !== null) {
            abort(Response::HTTP_FORBIDDEN, 'Token SPK sudah digunakan.');
        }

        if ($link->expires_at && $link->expires_at->isPast()) {
            abort(Response::HTTP_FORBIDDEN, 'Token SPK telah kedaluwarsa.');
        }

        return $link;
    }

    /**
     * Tandai token sebagai sudah digunakan.
     */
    public function markUsed(SPKApprovalToken $token): void
    {
        $token->update([
            'used_at' => now(),
        ]);
    }
}
    