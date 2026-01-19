<?php

namespace App\Services;

use App\Models\Hpp1;
use App\Models\Notification;
use App\Models\UnitWork;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class HppApproverResolver
{
    private const WORKSHOP_UNIT = 'Unit of Workshop & Design';
    private const WORKSHOP_SECTION = 'Section of Machine Workshop';

    public function statusMapForSourceForm(?string $sourceForm): array
    {
        $default = [
            'submitted'             => 'manager',
            'approved_manager'      => 'sm',
            'approved_sm'           => 'mgr_req',
            'approved_manager_req'  => 'sm_req',
            'approved_sm_req'       => 'gm_req',
            'approved_gm_req'       => 'gm',
        ];

        $maps = [
            'createhpp1' => $default,
            'createhpp2' => $default,
            'createhpp5' => $default,
            'createhpp6' => $default,
            'createhpp3' => [
                'submitted'        => 'manager',
                'approved_manager' => 'sm',
                'approved_sm'      => 'gm',
            ],
            'createhpp4' => [
                'submitted'        => 'manager',
                'approved_manager' => 'sm',
                'approved_sm'      => 'gm',
            ],
        ];

        $source = $this->normalizeSourceForm($sourceForm);

        return $maps[$source] ?? $default;
    }

    public function expectedSignTypeForStatus(?string $status, ?Hpp1 $hpp = null): string
    {
        $source = $hpp?->source_form;
        $mapping = $this->statusMapForSourceForm($source);
        $default = $this->statusMapForSourceForm(null);

        return $mapping[$status] ?? ($default[$status] ?? 'manager');
    }

    public function pendingStatuses(): array
    {
        $maps = [
            $this->statusMapForSourceForm('createhpp1'),
            $this->statusMapForSourceForm('createhpp2'),
            $this->statusMapForSourceForm('createhpp3'),
            $this->statusMapForSourceForm('createhpp4'),
            $this->statusMapForSourceForm('createhpp5'),
            $this->statusMapForSourceForm('createhpp6'),
        ];

        $statuses = [];
        foreach ($maps as $map) {
            $statuses = array_merge($statuses, array_keys($map));
        }

        return array_values(array_unique($statuses));
    }

    public function resolveApprover(Hpp1 $hpp, string $signType): ?User
    {
        return match ($signType) {
            'manager'  => $this->controllingManager($hpp),
            'sm'       => $this->controllingSeniorManager($hpp),
            'gm'       => $this->controllingGeneralManager($hpp),
            'mgr_req'  => $this->isSpecialRequestingFlow($hpp) ? $this->requestingManagerSpecial($hpp) : $this->requestingManager($hpp),
            'sm_req'   => $this->isSpecialRequestingFlow($hpp) ? $this->requestingSeniorManagerSpecial($hpp) : $this->requestingSeniorManager($hpp),
            'gm_req'   => $this->isSpecialRequestingFlow($hpp) ? $this->requestingGeneralManagerSpecial($hpp) : $this->requestingGeneralManager($hpp),
            'dir'      => $this->directorFallback(),
            default    => null,
        };
    }

    private function requestingManager(Hpp1 $hpp): ?User
    {
        $notification = $this->notificationForHpp($hpp);
        if (! $notification) {
            return null;
        }

        $unitWork = $this->unitWorkByName($notification->unit_work);
        if (! $unitWork) {
            return null;
        }

        $seksi = $this->cleanString($notification->seksi);
        if ($seksi === '') {
            return null;
        }

        return $unitWork->sections()
            ->where('name', $seksi)
            ->first()
            ?->manager;
    }

    private function requestingSeniorManager(Hpp1 $hpp): ?User
    {
        $unitWork = $this->requestingUnitWork($hpp);

        return $unitWork?->seniorManager;
    }

    private function requestingGeneralManager(Hpp1 $hpp): ?User
    {
        $unitWork = $this->requestingUnitWork($hpp);

        return $unitWork?->department?->generalManager;
    }

    /**
     * Special flow for createhpp5/6: tolerate missing org-structure data on requesting side.
     * Fallback chain is ONLY applied for requesting approvers (mgr_req/sm_req/gm_req).
     */
    private function requestingManagerSpecial(Hpp1 $hpp): ?User
    {
        $candidates = [
            'requestingManager'         => $this->requestingManager($hpp),
            'requestingSeniorManager'   => $this->requestingSeniorManager($hpp),
            'requestingGeneralManager'  => $this->requestingGeneralManager($hpp),
        ];

        foreach ($candidates as $label => $user) {
            if ($user) {
                if ($label !== 'requestingManager') {
                    Log::info('[HPP] Special requesting fallback used', [
                        'source_form' => $hpp->source_form,
                        'notif'       => $hpp->notification_number,
                        'sign_type'   => 'mgr_req',
                        'fallback'    => $label,
                        'user_id'     => $user->id,
                    ]);
                }
                return $user;
            }
        }

        return null;
    }

    private function requestingSeniorManagerSpecial(Hpp1 $hpp): ?User
    {
        $candidates = [
            'requestingSeniorManager'   => $this->requestingSeniorManager($hpp),
            'requestingGeneralManager'  => $this->requestingGeneralManager($hpp),
        ];

        foreach ($candidates as $label => $user) {
            if ($user) {
                if ($label !== 'requestingSeniorManager') {
                    Log::info('[HPP] Special requesting fallback used', [
                        'source_form' => $hpp->source_form,
                        'notif'       => $hpp->notification_number,
                        'sign_type'   => 'sm_req',
                        'fallback'    => $label,
                        'user_id'     => $user->id,
                    ]);
                }
                return $user;
            }
        }

        return null;
    }

    private function requestingGeneralManagerSpecial(Hpp1 $hpp): ?User
    {
        $candidates = [
            'requestingGeneralManager'  => $this->requestingGeneralManager($hpp),
        ];

        foreach ($candidates as $label => $user) {
            if ($user) {
                if ($label !== 'requestingGeneralManager') {
                    Log::info('[HPP] Special requesting fallback used', [
                        'source_form' => $hpp->source_form,
                        'notif'       => $hpp->notification_number,
                        'sign_type'   => 'gm_req',
                        'fallback'    => $label,
                        'user_id'     => $user->id,
                    ]);
                }
                return $user;
            }
        }

        return null;
    }

    private function controllingManager(Hpp1 $hpp): ?User
    {
        $unitWork = $this->controllingUnitWork($hpp);
        if (! $unitWork) {
            return null;
        }

        $sectionName = self::WORKSHOP_SECTION;
        $section = $unitWork->sections()
            ->where('name', $sectionName)
            ->first();

        return $section?->manager;
    }

    private function controllingSeniorManager(Hpp1 $hpp): ?User
    {
        $unitWork = $this->controllingUnitWork($hpp);

        $seniorManager = $unitWork?->seniorManager;
        if ($seniorManager) {
            return $seniorManager;
        }

        if ($this->shouldFallbackSmToGm($hpp)) {
            return $unitWork?->department?->generalManager;
        }

        return null;
    }

    private function controllingGeneralManager(Hpp1 $hpp): ?User
    {
        $unitWork = $this->controllingUnitWork($hpp);

        $generalManager = $unitWork?->department?->generalManager;
        if ($generalManager) {
            return $generalManager;
        }

        if ($this->shouldFallbackGmToDirector($hpp)) {
            return $this->directorFallback();
        }

        return null;
    }

    private function requestingUnitWork(Hpp1 $hpp): ?UnitWork
    {
        $notification = $this->notificationForHpp($hpp);
        $unitName = $notification?->unit_work ?? $hpp->requesting_unit;

        return $this->unitWorkByName($unitName);
    }

    private function controllingUnitWork(Hpp1 $hpp): ?UnitWork
    {
        $unitName = $this->cleanString($hpp->controlling_unit);
        if ($unitName === '') {
            $unitName = self::WORKSHOP_UNIT;
        }

        return $this->unitWorkByName($unitName);
    }

    private function unitWorkByName(?string $name): ?UnitWork
    {
        $name = $this->cleanString($name);
        if ($name === '') {
            return null;
        }

        return UnitWork::where('name', $name)->first();
    }

    private function notificationForHpp(Hpp1 $hpp): ?Notification
    {
        return $hpp->relationLoaded('notification')
            ? $hpp->notification
            : Notification::where('notification_number', $hpp->notification_number)->first();
    }

    private function cleanString(?string $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function normalizeSourceForm(?string $value): string
    {
        return strtolower(trim((string) ($value ?? '')));
    }

    private function isSpecialRequestingFlow(Hpp1 $hpp): bool
    {
        $source = $this->normalizeSourceForm($hpp->source_form ?? null);

        return in_array($source, ['createhpp5', 'createhpp6'], true);
    }

    private function shouldFallbackSmToGm(Hpp1 $hpp): bool
    {
        $source = $this->normalizeSourceForm($hpp->source_form ?? null);

        return in_array($source, ['createhpp5', 'createhpp6'], true);
    }

    private function shouldFallbackGmToDirector(Hpp1 $hpp): bool
    {
        $source = $this->normalizeSourceForm($hpp->source_form ?? null);

        return in_array($source, ['createhpp5', 'createhpp6'], true);
    }

    private function directorFallback(): ?User
    {
        return User::query()
            ->whereRaw('LOWER(jabatan) LIKE ?', ['%director%'])
            ->first();
    }
}
