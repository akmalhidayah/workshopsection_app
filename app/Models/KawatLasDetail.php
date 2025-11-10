<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KawatLasDetail extends Model
{
    use HasFactory;

    protected $table = 'kawat_las_details';

    protected $fillable = [
        'kawat_las_id',
        'jenis_kawat',
        'jumlah',
    ];

    /**
     * Relasi balik ke order utama
     */
    public function kawatLas()
    {
        return $this->belongsTo(KawatLas::class, 'kawat_las_id');
    }
    public function jenis()
{
    return $this->belongsTo(JenisKawatLas::class, 'jenis_kawat', 'kode');
}

}
