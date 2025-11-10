<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lpj extends Model
{
    use HasFactory;

    protected $table = 'lpjs';
    protected $primaryKey = 'notification_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'notification_number',
        'lpj_number',
        'lpj_document_path',
        'ppl_number',
        'ppl_document_path',
        'termin1',
        'termin2',
        'update_date',
        // baru:
        'garansi_months',
        'garansi_label',
    ];

    protected $casts = [
        'update_date' => 'datetime',
        'garansi_months' => 'integer',
    ];

    // ---------- Termin helpers ----------
    public function isTermin1Paid(): bool
    {
        return $this->termin1 === 'sudah';
    }

    public function isTermin2Paid(): bool
    {
        return $this->termin2 === 'sudah';
    }

    public function getTermin1LabelAttribute(): string
    {
        return $this->termin1 === 'sudah' ? 'Sudah Dibayar' : 'Belum Dibayar';
    }

    public function getTermin2LabelAttribute(): string
    {
        return $this->termin2 === 'sudah' ? 'Sudah Dibayar' : 'Belum Dibayar';
    }

    // ---------- Garansi helpers ----------
    /**
     * Kembalikan label garansi, mis: "1 Bulan", "6 Bulan", atau '-' jika null
     */
 public function getGaransiLabelAttribute()
    {
        if (! $this->garansi_months) return '-';
        return $this->garansi_months . ' Bulan';
    }

    /**
     * Shortcut: apakah garansi pernah dipilih (ada nilai)
     */
    public function hasGaransi(): bool
    {
        return (bool) $this->garansi_months;
    }
     public function calculateGaransiEndDate(?Carbon $startDate): ?Carbon
    {
        if (! $startDate) {
            return null;
        }

        $months = (int) ($this->garansi_months ?? 0);
        if ($months <= 0) {
            return null;
        }

        // gunakan addMonthsNoOverflow supaya tanggal tetap valid (mis. 31 Jan + 1 bln => 28/29 Feb)
        return (clone $startDate)->addMonthsNoOverflow($months);
    }
    
}
