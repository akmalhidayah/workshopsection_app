<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Notification;
use App\Models\UnitWork;
use App\Models\UnitWorkSection;
use App\Models\Hpp1;
use App\Models\HppApprovalToken;
use App\Models\LHPP;
use App\Models\LHPPApprovalToken;
use App\Models\SPK;
use App\Models\SPKApprovalToken;
use App\Services\SPKApprovalLinkService;
use App\Services\HppApprovalLinkService;
use App\Services\HppApproverResolver;
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
         $this->maybeIssueSpkTokens($user);  // logika baru SPK
    }

   protected function maybeIssueHppTokens(User $user): void
{
    $svc = app(\App\Services\HppApprovalLinkService::class);
    $resolver = app(\App\Services\HppApproverResolver::class);

    $statuses = $resolver->pendingStatuses();
    $candidates = Hpp1::whereIn('status', $statuses)
        ->with('notification')
        ->get();

    foreach ($candidates as $hpp) {
        $map = $resolver->statusMapForSourceForm($hpp->source_form ?? null);
        $expected = $map[$hpp->status] ?? null;
        if (! $expected) {
            continue;
        }

        $expectedUser = $resolver->resolveApprover($hpp, $expected);
        if (! $expectedUser || (int) $expectedUser->id != (int) $user->id) {
            continue;
        }

        $exists = HppApprovalToken::where('notification_number', $hpp->notification_number)
            ->where('sign_type', $expected)
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($exists) {
            continue;
        }

        try {
            $tokId = $svc->issue($hpp->notification_number, $expected, $user->id);
            \Log::info('[HPP-AUTO] Issued token', [
                'notif' => $hpp->notification_number,
                'sign'  => $expected,
                'user'  => $user->id,
                'tok'   => $tokId,
            ]);
        } catch (\Throwable $e) {
            \Log::error('[HPP-AUTO] Failed to issue token', [
                'notif' => $hpp->notification_number,
                'err'   => $e->getMessage(),
            ]);
        }
    }
}

/* =======================================================
 *  LHPP: auto-issue token berdasarkan struktur organisasi
 *  (Manager User, Manager Workshop, Manager PKM)
 * ======================================================= */
protected function maybeIssueLhppTokens(User $user): void
{
    $svc = app(LHPPApprovalLinkService::class);

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

        // ========= Tentukan siapa APPROVER seharusnya menurut struktur organisasi =========
        $expectedUser = $this->resolveLhppApproverUser($signType, $lhpp);

        // kalau struktur organisasi belum lengkap / approver belum di-set → skip
        if (! $expectedUser) {
            continue;
        }

        // User ini bukan approver yang diharapkan → skip
        if ((int) $expectedUser->id !== (int) $user->id) {
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
     * Auto-issue tokens untuk SPK (Manager -> Senior Manager).
     *
     * Rules (heuristik):
     *  - SPK perlu dua tanda tangan: manager (unit peminta) lalu senior_manager (workshop/senior)
     *  - Manager: jabatan mengandung 'manager' && unit_work == spk.unit_work
     *  - Senior Manager: jabatan mengandung 'senior' OR (jabatan mengandung 'manager' && unit_work mengandung 'workshop'/'bengkel' OR equals 'Unit Of Workshop')
     *
     * NOTE: Sesuaikan nama model SpkApprovalToken / SpkApprovalLinkService jika berbeda.
     */
    /**
 * Tentukan user approver untuk LHPP berdasarkan sign_type & struktur organisasi.
 */
protected function resolveLhppApproverUser(string $signType, LHPP $lhpp): ?User
{
    return match ($signType) {
        'manager_user'      => $this->getLhppManagerUser($lhpp),
        'manager_workshop'  => $this->getLhppManagerWorkshop($lhpp),
        'manager_pkm'       => $this->getLhppManagerPkm($lhpp),
        default             => null,
    };
}

/**
 * Manager User:
 * LHPP -> Notification (unit_work + seksi) -> UnitWork -> UnitWorkSection -> manager
 */
protected function getLhppManagerUser(LHPP $lhpp): ?User
{
    $notification = Notification::where('notification_number', $lhpp->notification_number)->first();

    if (! $notification) {
        Log::warning('[LHPP-AUTO][Resolver] Notification tidak ditemukan', [
            'notif' => $lhpp->notification_number,
        ]);
        return null;
    }

    if (! $notification->unit_work || ! $notification->seksi) {
        Log::warning('[LHPP-AUTO][Resolver] unit_work/seksi Notification kosong', [
            'notif'      => $lhpp->notification_number,
            'unit_work'  => $notification->unit_work,
            'seksi'      => $notification->seksi,
        ]);
        return null;
    }

    $unitWork = UnitWork::where('name', $notification->unit_work)->first();

    if (! $unitWork) {
        Log::warning('[LHPP-AUTO][Resolver] UnitWork tidak ditemukan untuk Manager User', [
            'notif'     => $lhpp->notification_number,
            'unit_work' => $notification->unit_work,
        ]);
        return null;
    }

    /** @var UnitWorkSection|null $section */
    $section = $unitWork->sections()
        ->where('name', $notification->seksi)
        ->first();

    if (! $section) {
        Log::warning('[LHPP-AUTO][Resolver] Section (seksi) tidak ditemukan untuk Manager User', [
            'notif'     => $lhpp->notification_number,
            'unit_work' => $notification->unit_work,
            'seksi'     => $notification->seksi,
        ]);
        return null;
    }

    if (! $section->manager) {
        Log::warning('[LHPP-AUTO][Resolver] Manager untuk seksi peminta belum di-set', [
            'notif'  => $lhpp->notification_number,
            'seksi'  => $section->name,
        ]);
        return null;
    }

    return $section->manager;
}

/**
 * Manager Workshop:
 * Selalu Manager dari seksi "Section of Machine Workshop"
 * pada unit "Unit of Workshop & Design".
 */
protected function getLhppManagerWorkshop(LHPP $lhpp): ?User
{
    $unitWork = UnitWork::where('name', 'Unit of Workshop & Design')->first();

    if (! $unitWork) {
        Log::warning('[LHPP-AUTO][Resolver] UnitWork Workshop tidak ditemukan', [
            'notif' => $lhpp->notification_number,
        ]);
        return null;
    }

    /** @var UnitWorkSection|null $section */
    $section = $unitWork->sections()
        ->where('name', 'Section of Machine Workshop')
        ->first();

    if (! $section) {
        Log::warning('[LHPP-AUTO][Resolver] Section of Machine Workshop tidak ditemukan', [
            'notif' => $lhpp->notification_number,
        ]);
        return null;
    }

    if (! $section->manager) {
        Log::warning('[LHPP-AUTO][Resolver] Manager Section of Machine Workshop belum di-set', [
            'notif' => $lhpp->notification_number,
        ]);
        return null;
    }

    return $section->manager;
}

/**
 * Manager PKM:
 * Unit "PT. Prima Karya Manunggal" + seksi sama dengan `lhpp.kontrak_pkm`
 * (Fabrikasi / Konstruksi / Pengerjaan Mesin).
 */
protected function getLhppManagerPkm(LHPP $lhpp): ?User
{
    if (! $lhpp->kontrak_pkm) {
        Log::warning('[LHPP-AUTO][Resolver] kontrak_pkm kosong', [
            'notif' => $lhpp->notification_number,
        ]);
        return null;
    }

    $pkmUnit = UnitWork::where('name', 'PT. Prima Karya Manunggal')->first();

    if (! $pkmUnit) {
        Log::warning('[LHPP-AUTO][Resolver] UnitWork PKM tidak ditemukan', [
            'notif' => $lhpp->notification_number,
        ]);
        return null;
    }

    /** @var UnitWorkSection|null $section */
    $section = $pkmUnit->sections()
        ->where('name', $lhpp->kontrak_pkm)
        ->first();

    if (! $section) {
        Log::warning('[LHPP-AUTO][Resolver] Section PKM tidak ditemukan untuk kontrak_pkm', [
            'notif'       => $lhpp->notification_number,
            'kontrak_pkm' => $lhpp->kontrak_pkm,
        ]);
        return null;
    }

    if (! $section->manager) {
        Log::warning('[LHPP-AUTO][Resolver] Manager PKM untuk kontrak_pkm belum di-set', [
            'notif'       => $lhpp->notification_number,
            'kontrak_pkm' => $lhpp->kontrak_pkm,
        ]);
        return null;
    }

    return $section->manager;
}

/* =======================================================
     *  SPK: auto-issue token berdasarkan struktur WORKSHOP
     * ======================================================= */
    protected function maybeIssueSpkTokens(User $user): void
    {
        $svc = app(SPKApprovalLinkService::class);

        // Ambil semua SPK yang belum lengkap tanda tangan
        $spks = SPK::all()->filter(fn (SPK $spk) => ! $spk->isFullyApproved());

        foreach ($spks as $spk) {
            // Tentukan step approval berikutnya
            $signType = ! $spk->isManagerSigned()
                ? 'manager'
                : (! $spk->isSeniorManagerSigned() ? 'senior_manager' : null);

            if (! $signType) {
                continue; // sudah lengkap
            }

            // ========= Tentukan approver seharusnya (struktur Workshop tetap) =========
            $expectedUser = $this->resolveSpkApproverUser($signType, $spk);

            if (! $expectedUser) {
                continue;
            }

            // User ini bukan approver yang diharapkan → skip
            if ((int) $expectedUser->id !== (int) $user->id) {
                continue;
            }

            // ========= Cek token aktif untuk user & sign_type ini =========
            $exists = SPKApprovalToken::where('nomor_spk', $spk->nomor_spk)
                ->where('sign_type', $signType)
                ->where('user_id', $user->id)
                ->whereNull('used_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->exists();

            if ($exists) {
                continue;
            }

            // ========= Issue token baru =========
            try {
                $tokId = $svc->issue(
                    $spk->nomor_spk,
                    $spk->notification_number,
                    $signType,
                    $user->id,
                    60 * 24 // 1 hari
                );

                Log::info('[SPK-AUTO] Issued token', [
                    'spk'       => $spk->nomor_spk,
                    'notif'     => $spk->notification_number,
                    'sign_type' => $signType,
                    'user'      => $user->id,
                    'token_id'  => $tokId,
                ]);
            } catch (\Throwable $e) {
                Log::error('[SPK-AUTO] Failed issue token', [
                    'spk'   => $spk->nomor_spk,
                    'notif' => $spk->notification_number,
                    'err'   => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Tentukan user approver untuk SPK berdasarkan sign_type & struktur Workshop.
     */
    protected function resolveSpkApproverUser(string $signType, SPK $spk): ?User
    {
        return match ($signType) {
            'manager'        => $this->getSpkManagerUser($spk),
            'senior_manager' => $this->getSpkSeniorManagerUser($spk),
            default          => null,
        };
    }

    /**
     * Manager Workshop untuk SPK:
     * Selalu Manager dari:
     *   UnitWork.name = 'Unit of Workshop & Design'
     *   Section.name  = 'Section of Machine Workshop'
     */
    protected function getSpkManagerUser(SPK $spk): ?User
    {
        // hanya untuk logging, bukan penentu struktur
        $notification = Notification::where('notification_number', $spk->notification_number)->first();

        $unitWork = UnitWork::where('name', 'Unit of Workshop & Design')->first();

        if (! $unitWork) {
            Log::warning('[SPK-AUTO][Resolver] UnitWork Workshop (Unit of Workshop & Design) tidak ditemukan', [
                'spk'            => $spk->nomor_spk,
                'notif'          => $spk->notification_number,
                'notif_unitwork' => $notification?->unit_work,
            ]);
            return null;
        }

        /** @var UnitWorkSection|null $section */
        $section = $unitWork->sections()
            ->where('name', 'Section of Machine Workshop')
            ->first();

        if (! $section) {
            Log::warning('[SPK-AUTO][Resolver] Section of Machine Workshop tidak ditemukan', [
                'spk'       => $spk->nomor_spk,
                'notif'     => $spk->notification_number,
                'unit_work' => $unitWork->name,
            ]);
            return null;
        }

        if (! $section->manager) {
            Log::warning('[SPK-AUTO][Resolver] Manager Section of Machine Workshop belum di-set', [
                'spk'   => $spk->nomor_spk,
                'notif' => $spk->notification_number,
                'seksi' => $section->name,
            ]);
            return null;
        }

        return $section->manager;
    }

    /**
     * Senior Manager Workshop untuk SPK:
     * UnitWork 'Unit of Workshop & Design' -> relasi seniorManager
     */
    protected function getSpkSeniorManagerUser(SPK $spk): ?User
    {
        // hanya buat logging
        $notification = Notification::where('notification_number', $spk->notification_number)->first();

        $unitWork = UnitWork::where('name', 'Unit of Workshop & Design')->first();

        if (! $unitWork) {
            Log::warning('[SPK-AUTO][Resolver] UnitWork Workshop (Unit of Workshop & Design) tidak ditemukan untuk Senior Manager', [
                'spk'            => $spk->nomor_spk,
                'notif'          => $spk->notification_number,
                'notif_unitwork' => $notification?->unit_work,
            ]);
            return null;
        }

        if (! $unitWork->seniorManager) {
            Log::warning('[SPK-AUTO][Resolver] Senior Manager untuk Unit of Workshop & Design belum di-set', [
                'spk'       => $spk->nomor_spk,
                'notif'     => $spk->notification_number,
                'unit_work' => $unitWork->name,
            ]);
            return null;
        }

        return $unitWork->seniorManager;
    }

    /**
     * Heuristic: given expected sign_type ('manager','sm','gm','dir','mgr_req',...),
     * return whether the next approver unit is 'controller' or 'request'.
     */
    protected function signTypeToNextType(string $signType): string
    {
        return in_array($signType, ['mgr_req', 'sm_req', 'gm_req'])
            ? 'request'
            : 'controller';
    }
}

/**
 * Helper kecil: cek apakah kolom ada di DB tanpa melempar exception bila tabel/kolom belum dibuat.
 */
if (! function_exists('SchemaHasColumnSafe')) {
    function SchemaHasColumnSafe(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            \Log::warning('[SchemaHasColumnSafe] gagal cek kolom', [
                'table'  => $table,
                'column' => $column,
                'err'    => $e->getMessage(),
            ]);
            return false;
        }
    }
}
