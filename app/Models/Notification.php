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
        'seksi',
        'priority',
        'input_date',
        'status',
        'catatan',
        'user_id',
        'usage_plan_date',
        'update_date', 
    ];

    protected $casts = [
        'update_date' => 'datetime', // Memastikan update_date dianggap sebagai datetime
    ];

public function hasAbnormalitas()
{
    return $this->dokumenOrders()->where('jenis_dokumen', 'abnormalitas')->exists();
}

public function hasGambarTeknik()
{
    return $this->dokumenOrders()->where('jenis_dokumen', 'gambar_teknik')->exists();
}

 public function dokumenOrders()
{
    return $this->hasMany(DokumenOrder::class, 'notification_number', 'notification_number');
}
public function scopeOfWork()
{
    return $this->hasOne(\App\Models\ScopeOfWork::class, 'notification_number', 'notification_number');
}
public function verifikasiAnggaran()
{
    return $this->hasOne(VerifikasiAnggaran::class, 'notification_number', 'notification_number');
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
