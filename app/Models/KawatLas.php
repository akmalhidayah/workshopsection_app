<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KawatLas extends Model
{
    use HasFactory;

    protected $table = 'kawat_las';

    protected $fillable = [
        'order_number',
        'tanggal',
        'unit_work',
        'seksi',
            'status',
    'catatan',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relasi ke detail kawat
     * 1 order -> banyak detail
     */
    public function details()
    {
        return $this->hasMany(KawatLasDetail::class, 'kawat_las_id');
    }

    /**
     * Relasi ke user pembuat order
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
