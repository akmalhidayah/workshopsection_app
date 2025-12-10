<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_number';
    public $incrementing = false;
    protected $keyType = 'string';

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
        'update_date' => 'datetime', // Memastikan update_date dianggap sebagai datetime
    ];

    /**
     * ==========================
     *  STATUS ENUM (APPROVAL)
     * ==========================
     * kolom "status" akan diisi dengan nilai ini:
     * - pending
     * - reject
     * - approved_workshop
     * - approved_jasa
     * - approved_workshop_jasa
     */
    public const STATUS_PENDING                = 'pending';
    public const STATUS_REJECT                 = 'reject';
    public const STATUS_APPROVED_WORKSHOP      = 'approved_workshop';
    public const STATUS_APPROVED_JASA          = 'approved_jasa';
    public const STATUS_APPROVED_WORKSHOP_JASA = 'approved_workshop_jasa';

    /**
     * Catatan yang dipakai untuk kelompok WORKSHOP / JASA
     */
    public const WORKSHOP_NOTES = [
        'Regu Fabrikasi',
        'Regu Bengkel (Refurbish)',
    ];

    public const JASA_NOTES = [
        'Jasa Fabrikasi',
        'Jasa Konstruksi',
        'Jasa Pengerjaan Mesin',
    ];

    /**
     * Helper: daftar semua status yg termasuk "Approved"
     */
    public static function approvedStatuses(): array
    {
        return [
            self::STATUS_APPROVED_WORKSHOP,
            self::STATUS_APPROVED_JASA,
            self::STATUS_APPROVED_WORKSHOP_JASA,
        ];
    }

    /**
     * Helper: cek apakah notifikasi ini sudah approved (tipe apapun)
     */
    public function isApproved(): bool
    {
        return in_array($this->status, self::approvedStatuses(), true);
    }

    /**
     * Accessor: label status yg enak dibaca di Blade
     * pakai: {{ $notification->status_label }}
     */
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

    // ==========================
    //  RELASI & HELPER LAMA
    // ==========================

    public function hasAbnormalitas()
    {
        return $this->dokumenOrders()->where('jenis_dokumen', 'abnormalitas')->exists();
    }

    public function hasGambarTeknik()
    {
        return $this->dokumenOrders()->where('jenis_dokumen', 'gambar_teknik')->exists();
    }

    public function dokumenOrders()
    {
        return $this->hasMany(DokumenOrder::class, 'notification_number', 'notification_number');
    }

    public function scopeOfWork()
    {
        return $this->hasOne(\App\Models\ScopeOfWork::class, 'notification_number', 'notification_number');
    }

    public function verifikasiAnggaran()
    {
        return $this->hasOne(VerifikasiAnggaran::class, 'notification_number', 'notification_number');
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

    // Relasi dengan model LPJ
    public function lpj()
    {
        return $this->hasOne(Lpj::class, 'notification_number', 'notification_number');
    }

    // Relasi Order Bengkel
    public function orderBengkel()
    {
        return $this->hasOne(\App\Models\OrderBengkel::class, 'notification_number', 'notification_number');
    }

public function garansi() { return $this->hasOne(\App\Models\Garansi::class, 'notification_number', 'notification_number'); }

}
