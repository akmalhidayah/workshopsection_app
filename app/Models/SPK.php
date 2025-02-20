<?php

namespace App\Models;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPK extends Model
{
    use HasFactory;

    protected $table = 'spks';

    // Field yang dapat diisi
    protected $fillable = [
        'nomor_spk', 'perihal', 'tanggal_spk', 'notification_number', 'unit_work', 
        'functional_location', 'scope_pekerjaan', 'qty', 'stn', 'keterangan', 'keterangan_pekerjaan','manager_signature', 'senior_manager_signature'
    ];

    // Menentukan primary key adalah 'nomor_spk'
    protected $primaryKey = 'nomor_spk';

    // Menonaktifkan auto-increment karena 'nomor_spk' adalah string
    public $incrementing = false;

    // Menentukan tipe primary key sebagai string
    protected $keyType = 'string';

    // Mengubah JSON menjadi array
    protected $casts = [
        'functional_location' => 'array',
        'scope_pekerjaan' => 'array',
        'qty' => 'array',
        'stn' => 'array',
        'keterangan' => 'array'
    ];
    
    public function user()
{
    return $this->belongsTo(User::class);
}

}

