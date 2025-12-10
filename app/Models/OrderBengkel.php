<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderBengkel extends Model
{
    use HasFactory;

    protected $table = 'order_bengkels';

    // PK adalah notification_number (string)
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

    // Relasi: OrderBengkel BELONGS TO Notification
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }
    public function getShowMaterialAttribute() 
    { 
        return $this->konfirmasi_anggaran === 'Material Ready';
     }

}
