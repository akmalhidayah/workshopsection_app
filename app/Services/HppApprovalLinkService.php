<?php

namespace App\Services;

use App\Models\HppApprovalToken;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class HppApprovalLinkService
{
    public function issue(string $notificationNumber, string $signType, int $userId, int $minutes = 60): string
    {
        $tok = new HppApprovalToken();
        $tok->id                  = (string) Str::uuid();
        $tok->notification_number = $notificationNumber;
        $tok->sign_type           = $signType;           // manager|sm|gm|dir
        $tok->user_id             = $userId;
        $tok->issued_by           = auth()->id() ?? 0;
        $tok->issued_channel      = 'system';
        $tok->ip_issued           = request()->ip();
        $tok->ua_issued           = request()->userAgent();
        $tok->expires_at          = Carbon::now()->addMinutes($minutes);
        $tok->save();

        return $tok->id;
    }

    /** Kembalikan full URL ke halaman approve (GET) */
    public function url(string $token): string
    {
        // ⇩⇩ INI YANG PENTING: pakai nama rute BARU
        return route('approval.hpp.sign', ['token' => $token]);
    }

    /** Validasi token: ada, belum dipakai, belum expired */
    public function validate(string $token): HppApprovalToken
    {
        /** @var HppApprovalToken|null $link */
        $link = HppApprovalToken::find($token);

        if (!$link) {
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

    public function markUsed(HppApprovalToken $token): void
    {
        $token->used_at = now();
        $token->save();
    }
}
