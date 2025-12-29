<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderBengkel extends Model
{
    use HasFactory;

    protected $table = 'order_bengkels';

    protected $primaryKey = 'notification_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'notification_number',
        'konfirmasi_anggaran',
        'keterangan_konfirmasi',
        'status_anggaran',
        'keterangan_anggaran',
        'nomor_e_korin',
        'status_e_korin',
        'status_material',
        'keterangan_material',
        'progress_status',
        'keterangan_progress',
        'catatan',
    ];

    /* =========================
     |  RELATIONSHIP
     ========================= */
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }

    /* =========================
     |  BUSINESS LOGIC (INI YANG KURANG)
     ========================= */

    /**
     * Order Bengkel sedang menunggu anggaran
     */
    public function isWaitingBudget(): bool
    {
        return $this->status_anggaran === 'Waiting Budget';
    }

    /**
     * Anggaran tidak tersedia
     */
    public function isUnavailable(): bool
    {
        return $this->status_anggaran === 'Tidak Tersedia';
    }

    /* =========================
     |  UI HELPERS
     ========================= */
    public function getShowMaterialAttribute()
    {
        return $this->konfirmasi_anggaran === 'Material Ready';
    }
}
