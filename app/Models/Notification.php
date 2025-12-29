<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /* =====================================================
     |  BASIC CONFIG
     ===================================================== */
    protected $primaryKey = 'notification_number';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'notification_number',
        'job_name',
        'unit_work',
        'seksi',
        'priority',
        'input_date',
        'status',
        'catatan',
        'user_id',
        'usage_plan_date',
        'update_date',
    ];

    protected $casts = [
        'update_date' => 'datetime',
    ];

    /* =====================================================
     |  STATUS ENUM (APPROVAL)
     ===================================================== */
    public const STATUS_PENDING                = 'pending';
    public const STATUS_REJECT                 = 'reject';
    public const STATUS_APPROVED_WORKSHOP      = 'approved_workshop';
    public const STATUS_APPROVED_JASA          = 'approved_jasa';
    public const STATUS_APPROVED_WORKSHOP_JASA = 'approved_workshop_jasa';

    public const WORKSHOP_NOTES = [
        'Regu Fabrikasi',
        'Regu Bengkel (Refurbish)',
    ];

    public const JASA_NOTES = [
        'Jasa Fabrikasi',
        'Jasa Konstruksi',
        'Jasa Pengerjaan Mesin',
    ];

    /* =====================================================
     |  STATUS HELPERS
     ===================================================== */
    public static function approvedStatuses(): array
    {
        return [
            self::STATUS_APPROVED_WORKSHOP,
            self::STATUS_APPROVED_JASA,
            self::STATUS_APPROVED_WORKSHOP_JASA,
        ];
    }

    public function isApproved(): bool
    {
        return in_array($this->status, self::approvedStatuses(), true);
    }

    /* =====================================================
     |  ACCESSORS (UI META)
     ===================================================== */

    /** PRIORITY BADGE */
    public function getPriorityBadgeAttribute(): array
    {
        return match ($this->priority) {
            'Urgently' => ['label' => 'Emergency', 'class' => 'bg-red-600 text-white'],
            'Hard'     => ['label' => 'High',      'class' => 'bg-orange-500 text-white'],
            'Medium'   => ['label' => 'Medium',    'class' => 'bg-yellow-400 text-black'],
            default    => ['label' => 'Low',       'class' => 'bg-green-600 text-white'],
        };
    }

    /** STATUS BADGE */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            self::STATUS_PENDING => [
                'label' => 'Pending',
                'class' => 'bg-yellow-500 text-white',
            ],
            self::STATUS_REJECT => [
                'label' => 'Reject',
                'class' => 'bg-red-500 text-white',
            ],
            self::STATUS_APPROVED_WORKSHOP,
            self::STATUS_APPROVED_JASA,
            self::STATUS_APPROVED_WORKSHOP_JASA => [
                'label' => 'Approved',
                'class' => 'bg-green-600 text-white',
            ],
            default => [
                'label' => 'Unknown',
                'class' => 'bg-gray-500 text-white',
            ],
        };
    }

    /** STATUS LABEL (TEXT ONLY) */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING                => 'Pending',
            self::STATUS_REJECT                 => 'Reject',
            self::STATUS_APPROVED_WORKSHOP      => 'Approved Workshop',
            self::STATUS_APPROVED_JASA          => 'Approved Jasa',
            self::STATUS_APPROVED_WORKSHOP_JASA => 'Approved Workshop + Jasa',
            default                             => $this->status ?? '-',
        };
    }


    /* =====================================================
     |  CROSS-MODEL BUSINESS HELPERS
     ===================================================== */

    /**
     * Apakah form E-KORIN boleh ditampilkan?
     * - Order Bengkel Waiting Budget
     * - ATAU Verifikasi Anggaran Tidak Tersedia
     */
    public function canShowEkorinForm(): bool
    {
        if ($this->orderBengkel && $this->orderBengkel->isWaitingBudget()) {
            return true;
        }

        if ($this->verifikasiAnggaran && $this->verifikasiAnggaran->isUnavailable()) {
            return true;
        }

        return false;
    }

    /* =====================================================
     |  RELATIONSHIPS
     ===================================================== */

    public function dokumenOrders()
    {
        return $this->hasMany(DokumenOrder::class, 'notification_number', 'notification_number');
    }

public function scopeOfWork()
{
    return $this->hasOne(ScopeOfWork::class, 'notification_number', 'notification_number');
}


    public function verifikasiAnggaran()
    {
        return $this->hasOne(VerifikasiAnggaran::class, 'notification_number', 'notification_number');
    }

    public function orderBengkel()
    {
        return $this->hasOne(OrderBengkel::class, 'notification_number', 'notification_number');
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'notification_number', 'notification_number');
    }

    public function hpp1()
    {
        return $this->hasOne(Hpp1::class, 'notification_number', 'notification_number');
    }

    public function lhpp()
    {
        return $this->hasOne(LHPP::class, 'notification_number', 'notification_number');
    }

    public function spk()
    {
        return $this->hasOne(SPK::class, 'notification_number', 'notification_number');
    }

    public function lpj()
    {
        return $this->hasOne(Lpj::class, 'notification_number', 'notification_number');
    }

    public function garansi()
    {
        return $this->hasOne(Garansi::class, 'notification_number', 'notification_number');
    }

    /* =====================================================
     |  QUERY SCOPES
     ===================================================== */

    /** Digunakan untuk Job Waiting */
    public function scopeNotApprovedWorkshop($query)
    {
        return $query->where('status', '!=', self::STATUS_APPROVED_WORKSHOP);
    }

    /* =====================================================
     |  LEGACY HELPERS
     ===================================================== */

    public function hasAbnormalitas(): bool
    {
        return $this->dokumenOrders()
            ->where('jenis_dokumen', 'abnormalitas')
            ->exists();
    }

    public function hasGambarTeknik(): bool
    {
        return $this->dokumenOrders()
            ->where('jenis_dokumen', 'gambar_teknik')
            ->exists();
    }
    /* =====================================================
 |  DOKUMEN ORDER HELPERS
 ===================================================== */
/**
 * Apakah dokumen order sudah lengkap
 */
public function isDokumenOrderLengkap(): bool
{
    // pastikan relasi siap
    $this->loadMissing(['dokumenOrders', 'scopeOfWork']);

    // 1. Abnormalitas wajib
    $hasAbnormalitas = $this->dokumenOrders
        ->where('jenis_dokumen', 'abnormalitas')
        ->isNotEmpty();

    // 2. Gambar Teknik wajib
    $hasGambarTeknik = $this->dokumenOrders
        ->where('jenis_dokumen', 'gambar_teknik')
        ->isNotEmpty();

    // 3. Scope of Work (modul terpisah)
    $hasScopeOfWork = $this->scopeOfWork !== null;

    return $hasAbnormalitas && $hasGambarTeknik && $hasScopeOfWork;
}

/**
 * Tentukan apakah tombol "Lengkapi Dokumen" ditampilkan
 */
public function shouldShowLengkapiDokumenButton(): bool
{
    // kalau sudah lengkap → jangan tampilkan
    return !$this->isDokumenOrderLengkap();
}


}
