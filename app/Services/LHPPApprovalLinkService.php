<?php

namespace App\Services;

use App\Models\LHPPApprovalToken;   // ← sesuaikan dengan nama model tokenmu
use Carbon\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LHPPApprovalLinkService
{
    /**
     * Issue token LHPP.
     *
     * @param string   $notificationNumber  nomor notifikasi
     * @param string   $signType            jenis penanda tangan (misal: manager_user|manager_workshop|manager_pkm)
     * @param int      $userId              user yang dituju
     * @param int|null $minutes             masa berlaku (menit). null = tidak kedaluwarsa.
     * @return string                       token id (UUID)
     */
    public function issue(string $notificationNumber, string $signType, int $userId, ?int $minutes = 60*24*30): string
    {
        $tok = new LHPPApprovalToken();
        $tok->id                  = (string) Str::uuid();
        $tok->notification_number = $notificationNumber;
        $tok->sign_type           = $signType;
        $tok->user_id             = $userId;
        $tok->issued_by           = auth()->id() ?? 0;
        $tok->issued_channel      = 'system';
        $tok->ip_issued           = request()->ip();
        $tok->ua_issued           = request()->userAgent();

        // default: 30 hari; kalau $minutes null => permanent
        $tok->expires_at = is_null($minutes) ? null : Carbon::now()->addMinutes($minutes);

        $tok->save();

        return $tok->id;
    }

    /**
     * Kembalikan full URL ke halaman approve LHPP (GET).
     * PENTING: pakai nama route LHPP, bukan HPP.
     */
    public function url(string $token): string
    {
        return route('approval.lhpp.sign', ['token' => $token]);
    }

    /**
     * Validasi token: ada, belum dipakai, belum expired.
     */
    public function validate(string $token): LHPPApprovalToken
    {
        /** @var LHPPApprovalToken|null $link */
        $link = LHPPApprovalToken::find($token);

        if (! $link) {
            abort(Response::HTTP_NOT_FOUND, 'Token tidak ditemukan.');
        }
        if ($link->used_at) {
            abort(Response::HTTP_FORBIDDEN, 'Token sudah digunakan.');
        }
        if ($link->expires_at && $link->expires_at->isPast()) {
            abort(Response::HTTP_FORBIDDEN, 'Token telah kedaluwarsa.');
        }

        return $link;
    }

    /**
     * Tandai token sudah digunakan.
     */
    public function markUsed(LHPPApprovalToken $token): void
    {
        $token->used_at = now();
        $token->save();
    }
}
