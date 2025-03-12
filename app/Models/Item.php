<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'notification_number',
        'deskripsi_pekerjaan',
        'total_hpp',
        'materials',
        'harga',
        'total',
        'total_margin',
        'approved_by',
    ];

    protected $casts = [
        'materials' => 'array',
        'harga' => 'array',
        'total_hpp' => 'integer', // Menghilangkan .00 dari total_hpp
        'total' => 'integer', // Menghilangkan .00 dari total
        'total_margin' => 'integer', // Jika perlu juga bisa diubah
    ];

    // Relasi ke Notification
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }

    // Mengambil deskripsi pekerjaan dari Abnormalitas
    public function abnormal()
    {
        return $this->belongsTo(Abnormal::class, 'notification_number', 'notification_number');
    }

    // Mengambil total HPP dari HPP
    public function hpp()
    {
        return $this->belongsTo(Hpp1::class, 'notification_number', 'notification_number');
    }
}
