<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Hpp1;
use App\Models\HppApprovalToken;
use App\Models\LHPP;
use App\Models\LHPPApprovalToken;
use App\Services\HppApprovalLinkService;
use App\Services\LHPPApprovalLinkService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UserObserver
{
    /**
     * When a user is created.
     */
    public function created(User $user)
    {
        $this->maybeIssueTokensForUser($user);
    }

    /**
     * When a user is updated.
     */
    public function updated(User $user)
    {
        if ($user->wasChanged(['jabatan', 'unit_work', 'related_units'])) {
            $this->maybeIssueTokensForUser($user);
        }
    }

    /**
     * Entry point: cek semua kebutuhan token untuk user ini (HPP + LHPP).
     */
    protected function maybeIssueTokensForUser(User $user): void
    {
        $this->maybeIssueHppTokens($user);   // logika lama HPP dipindah ke sini
        $this->maybeIssueLhppTokens($user);  // logika baru LHPP PKM
    }

   protected function maybeIssueHppTokens(User $user): void
{
    $svc = app(\App\Services\HppApprovalLinkService::class);

    // Normalize user's jabatan & unit
    $userJabatan = strtolower(trim($user->jabatan ?? ''));
    $userUnit    = trim($user->unit_work ?? '');

    if ($userJabatan === '') {
        \Log::debug('[HPP-AUTO] user jabatan kosong, skip', ['user'=>$user->id]);
        return;
    }

    // Map jabatan -> sign_type HPP
    $jabatanToSignType = [
        'manager'             => 'manager',
        'senior manager'      => 'sm',
        'sr manager'          => 'sm',
        'senior mgr'          => 'sm',
        'general manager'     => 'gm',
        'gm'                  => 'gm',
        'director'            => 'dir',
        'director of operation' => 'dir',
        'operation director'  => 'dir',
        'operational director'=> 'dir',
    ];

    $possibleSignTypes = [];
    foreach ($jabatanToSignType as $needle => $signType) {
        if (str_contains($userJabatan, $needle)) {
            $possibleSignTypes[] = $signType;
        }
    }
    if (empty($possibleSignTypes)) {
        \Log::debug('[HPP-AUTO] no possibleSignTypes', ['user'=>$user->id,'jabatan'=>$user->jabatan]);
        return;
    }

    // Status -> expected sign_type mapping (mirror controller)
    $statusToExpected = [
        'submitted'            => 'manager',
        'approved_manager'     => 'sm',
        'approved_sm'          => 'mgr_req',
        'approved_manager_req' => 'sm_req',
        'approved_sm_req'      => 'gm',
        'approved_gm'          => 'gm_req',
        'approved_gm_req'      => 'dir',
    ];

    $statuses = array_keys($statusToExpected);
    $candidates = Hpp1::whereIn('status', $statuses)->get();

    foreach ($candidates as $hpp) {
        $expected = $statusToExpected[$hpp->status] ?? null;
        if (!$expected) continue;
        if (! in_array($expected, $possibleSignTypes)) continue;

        // Determine target unit (improved)
        $nextType = $this->signTypeToNextType($expected);
        if ($nextType === 'request') {
            $targetUnit = !empty($hpp->requesting_unit) ? $hpp->requesting_unit : optional($hpp->notification)->unit_work;
        } else {
            $targetUnit = $hpp->controlling_unit ?: null;
        }
        if (!$targetUnit) {
            \Log::debug('[HPP-AUTO] targetUnit empty, skip', ['notif'=>$hpp->notification_number,'expected'=>$expected]);
            continue;
        }

        $targetUnitNormalized = strtolower(trim((string) $targetUnit));
        $userUnitNormalized = strtolower(trim((string) $userUnit));

        // matching: prefix, related_units (array), reverse prefix
        $unitMatches = false;
        if ($userUnitNormalized !== '' && str_starts_with($targetUnitNormalized, $userUnitNormalized)) {
            $unitMatches = true;
        } else {
            $related = $user->related_units ?? null;
            if (is_array($related) && !empty($related)) {
                foreach ($related as $ru) {
                    $ruClean = strtolower(trim((string)$ru));
                    if ($ruClean !== '' && str_starts_with($targetUnitNormalized, $ruClean)) {
                        $unitMatches = true; break;
                    }
                }
            }
            // reverse prefix: handle cases target is shorter
            if (!$unitMatches && $userUnitNormalized !== '' && str_starts_with($userUnitNormalized, $targetUnitNormalized)) {
                $unitMatches = true;
            }
        }

        if (!$unitMatches) {
            \Log::debug('[HPP-AUTO] unit not match', [
                'notif' => $hpp->notification_number,
                'target' => $targetUnitNormalized,
                'userUnit' => $userUnitNormalized
            ]);
            continue;
        }

        // Check existing active token for notif+sign_type
        $exists = HppApprovalToken::where('notification_number', $hpp->notification_number)
            ->where('sign_type', $expected)
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->exists();

        if ($exists) {
            \Log::debug('[HPP-AUTO] token exists, skip', ['notif'=>$hpp->notification_number,'sign'=>$expected]);
            continue;
        }

        try {
            $tokId = $svc->issue($hpp->notification_number, $expected, $user->id);
            \Log::info('[HPP-AUTO] Issued token', ['notif'=>$hpp->notification_number,'sign'=>$expected,'user'=>$user->id,'tok'=>$tokId]);
        } catch (\Throwable $e) {
            \Log::error('[HPP-AUTO] Failed to issue token', ['notif'=>$hpp->notification_number,'err'=>$e->getMessage()]);
        }
    }
}


    /* =======================================================
     *  LHPP PKM: auto-issue token QC Manager PKM
     * ======================================================= */
 /* =======================================================
 *  LHPP: auto-issue token berdasarkan TTD (bukan status_approve)
 * ======================================================= */
protected function maybeIssueLhppTokens(User $user): void
{
    $svc = app(LHPPApprovalLinkService::class);

    $jabatan  = strtolower(trim($user->jabatan ?? ''));
    $unitWork = trim($user->unit_work ?? '');

    // kalau user belum jelas jabatannya / unit-nya, skip
    if ($jabatan === '' || $unitWork === '') {
        return;
    }

    // Ambil semua LHPP yang BELUM lengkap 3 tanda tangan
    // (pakai helper hasAllSignatures() di model LHPP)
    $candidates = LHPP::all()->filter(function (LHPP $lhpp) {
        return ! $lhpp->hasAllSignatures();
    });

    foreach ($candidates as $lhpp) {
        // ========= Tentukan TTD mana yang belum terisi (urutan approval) =========
        $userSigned = !empty($lhpp->manager_signature_requesting) ||
                      !empty($lhpp->manager_signature_requesting_user_id);

        $workshopSigned = !empty($lhpp->manager_signature) ||
                          !empty($lhpp->manager_signature_user_id);

        $pkmSigned = !empty($lhpp->manager_pkm_signature) ||
                     !empty($lhpp->manager_pkm_signature_user_id);

        // urutan:
        // 1) manager_user
        // 2) manager_workshop
        // 3) manager_pkm
        if (! $userSigned) {
            $signType = 'manager_user';
        } elseif (! $workshopSigned) {
            $signType = 'manager_workshop';
        } elseif (! $pkmSigned) {
            $signType = 'manager_pkm';
        } else {
            // sebenarnya sudah ke-cover hasAllSignatures, tapi buat jaga-jaga
            continue;
        }

        // ========= Cek apakah user ini memang calon approver untuk signType tsb =========
        $isApprover = false;

        switch ($signType) {
            case 'manager_user':
                // Manager User: jabatan mengandung "manager" + unit_work == unit_kerja LHPP
                $isApprover =
                    str_contains($jabatan, 'manager') &&
                    strcasecmp($user->unit_work ?? '', $lhpp->unit_kerja ?? '') === 0;
                break;

            case 'manager_workshop':
                // Manager Workshop: jabatan Manager + unit mengandung "workshop" / "bengkel"
                if (! str_contains($jabatan, 'manager')) {
                    $isApprover = false;
                    break;
                }
                $u = strtolower($user->unit_work ?? '');
                $isApprover = str_contains($u, 'workshop') || str_contains($u, 'bengkel');
                break;
            case 'manager_pkm':
                // Manager PKM: usertype approval + jabatan mengandung "manager" + unit_work == kontrak_pkm
                $isApprover =
                    strcasecmp($user->usertype ?? '', 'approval') === 0 &&
                    str_contains($jabatan, 'manager') &&
                    strcasecmp($user->unit_work ?? '', $lhpp->kontrak_pkm ?? '') === 0;
                break;

        }

        if (! $isApprover) {
            continue;
        }

        // ========= Cek apakah token aktif untuk user & sign_type ini sudah ada =========
        $exists = LHPPApprovalToken::where('notification_number', $lhpp->notification_number)
            ->where('sign_type', $signType)
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', Carbon::now());
            })
            ->exists();

        if ($exists) {
            continue;
        }

        // ========= Issue token baru =========
        try {
            $tokId = $svc->issue(
                $lhpp->notification_number,
                $signType,
                $user->id,
                60 * 24 * 30 // 30 hari
            );

            Log::info('[LHPP-AUTO] Issued token', [
                'notif'     => $lhpp->notification_number,
                'sign_type' => $signType,
                'user'      => $user->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('[LHPP-AUTO] Failed to issue token', [
                'notif' => $lhpp->notification_number,
                'user'  => $user->id,
                'err'   => $e->getMessage(),
            ]);
        }
    }
}


    /**
     * Heuristic: given expected sign_type ('manager','sm','gm','dir','mgr_req',...),
     * return whether the next approver unit is 'controller' or 'request'.
     */
    protected function signTypeToNextType(string $signType): string
    {
        return in_array($signType, ['mgr_req','sm_req','gm_req'])
            ? 'request'
            : 'controller';
    }
}
