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
        'priority',
        'input_date',
        'status',
        'catatan',
        'user_id',
        'jenis_kontrak',
        'nama_kontrak', 
        'usage_plan_date',
        'status_anggaran',
        'update_date', // Tambahkan kolom update_date ke dalam fillable
    ];

    protected $casts = [
        'update_date' => 'datetime', // Memastikan update_date dianggap sebagai datetime
    ];

    // Relasi one-to-one dengan Abnormalitas
    public function abnormal()
    {
        return $this->hasOne(Abnormal::class, 'notification_number', 'notification_number');
    }

    // Relasi one-to-one dengan ScopeOfWork
    public function scopeOfWork()
    {
        return $this->hasOne(ScopeOfWork::class, 'notification_number', 'notification_number');
    }

    // Relasi one-to-one atau one-to-many dengan GambarTeknik
    public function gambarTeknik()
    {
        return $this->hasOne(GambarTeknik::class, 'notification_number', 'notification_number');
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
    
    
}
