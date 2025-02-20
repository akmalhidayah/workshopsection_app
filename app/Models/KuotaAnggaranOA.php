<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KuotaAnggaranOA extends Model
{
    protected $table = 'kuota_anggaran_oa';
    protected $primaryKey = 'outline_agreement';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'unit_work',
        'outline_agreement',
        'jenis_kontrak',
        'nama_kontrak',
        'nilai_kontrak',
        'tambahan_kuota_kontrak',
        'total_kuota_kontrak',
        'periode_kontrak_start',
        'periode_kontrak_end',
        'adendum_end',
        'periode_kontrak_final'
    ];

    public function calculateTotalKuota()
    {
        return ($this->nilai_kontrak ?? 0) + ($this->tambahan_kuota_kontrak ?? 0);
    }
}

