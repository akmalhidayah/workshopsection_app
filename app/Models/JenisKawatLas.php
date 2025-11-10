<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisKawatLas extends Model
{
    use HasFactory;

    protected $table = 'jenis_kawat_las';

    protected $fillable = [
        'kode',
        'deskripsi',
        'gambar',
        'stok',         // jumlah stok kawat
        'harga',        // ✅ harga per unit kawat
        'cost_element', // ✅ kode cost element
    ];

    /**
     * Relasi ke detail order kawat
     * Satu jenis kawat bisa ada di banyak detail order
     */
    public function details()
    {
        return $this->hasMany(KawatLasDetail::class, 'jenis_kawat', 'kode');
    }
}
