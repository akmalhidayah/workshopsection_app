<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifikasiAnggaran extends Model
{
    use HasFactory;

    protected $table = 'verifikasi_anggarans';

    protected $fillable = [
        'notification_number',
        'status_anggaran',
        'cost_element',
        'kategori_biaya',
        'kategori_item',      // ✅ baru
        'nomor_e_korin',      // ✅ baru
        'status_e_korin',     // ✅ baru
        'catatan',
        'tanggal_verifikasi',
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** Relasi ke Notification */
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }

    /** Scope filter status verifikasi anggaran */
    public function scopeStatus($query, $status)
    {
        return $query->where('status_anggaran', $status);
    }

    /** Scope filter kategori biaya */
    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori_biaya', $kategori);
    }

    /** ✅ Scope filter kategori item (Spare Part / Jasa) */
    public function scopeKategoriItem($query, $item)
    {
        return $query->where('kategori_item', $item);
    }

    /** ✅ Scope filter status E-KORIN */
    public function scopeStatusEkorin($query, $status)
    {
        return $query->where('status_e_korin', $status);
    }

    /** Scope filter tanggal update */
    public function scopeVerifiedBetween($query, $start, $end)
    {
        return $query->whereBetween('tanggal_verifikasi', [$start, $end]);
    }
    /* =====================================================
 |  BUSINESS LOGIC (INSTANCE METHODS)
 ===================================================== */

/**
 * Anggaran tidak tersedia
 */
public function isUnavailable(): bool
{
    return $this->status_anggaran === 'Tidak Tersedia';
}

/**
 * Anggaran masih menunggu
 */
public function isWaiting(): bool
{
    return $this->status_anggaran === 'Menunggu';
}

/**
 * Anggaran tersedia
 */
public function isAvailable(): bool
{
    return $this->status_anggaran === 'Tersedia';
}

}
