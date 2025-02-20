<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hpp1 extends Model
{
    protected $table = 'hpp1'; // Nama tabel yang terkait dengan model ini

    protected $primaryKey = 'notification_number'; // Primary key

    public $incrementing = false; // Primary key bukan auto-increment

    protected $keyType = 'string'; // Tipe primary key adalah string

    protected $fillable = [
        'notification_number',
        'cost_centre',
        'description',
        'usage_plan',
        'completion_target',
        'requesting_unit',
        'controlling_unit',
        'outline_agreement',
        'uraian_pekerjaan',
        'jenis_material',
        'qty',
        'satuan',
        'volume_satuan',
        'jumlah_volume_satuan',
        'harga_material',
        'harga_consumable',
        'harga_upah',
        'jumlah_harga_material',
        'jumlah_harga_consumable',
        'jumlah_harga_upah',
        'harga_total',
        'keterangan',
        'total_amount',
        'manager_signature',
        'senior_manager_signature',
        'general_manager_signature',
        'director_signature',
        'manager_signature_requesting_unit',
        'senior_manager_signature_requesting_unit',
        'general_manager_signature_requesting_unit',
    ];

    protected $casts = [
        'uraian_pekerjaan' => 'array',
        'jenis_material' => 'array',
        'qty' => 'array',
        'satuan' => 'array',
        'volume_satuan' => 'array',
        'jumlah_volume_satuan' => 'array',
        'harga_material' => 'array',
        'harga_consumable' => 'array',
        'harga_upah' => 'array',
        'jumlah_harga_material' => 'array',
        'jumlah_harga_consumable' => 'array',
        'jumlah_harga_upah' => 'array',
        'harga_total' => 'array',
        'keterangan' => 'array',
    ];

// Pada model Hpp1 tambahkan fungsi relationship
public function notification()
{
    return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
}
 // Relasi dengan tabel purchase_orders
 public function purchaseOrder()
 {
     return $this->hasOne(PurchaseOrder::class, 'notification_number', 'notification_number');
 }

public function managerSignatureRequestingUser()
{
    return $this->belongsTo(User::class, 'manager_signature_requesting_user_id');
}
public function generalManagerRequestingUser()
{
    return $this->belongsTo(User::class, 'general_manager_signature_requesting_user_id');
}

public function seniorManagerRequestingUser()
{
    return $this->belongsTo(User::class, 'senior_manager_signature_requesting_user_id');
}
public function managerSignatureUser()
{
    return $this->belongsTo(User::class, 'manager_signature_user_id');
}

public function seniorManagerSignatureUser()
{
    return $this->belongsTo(User::class, 'senior_manager_signature_user_id');
}

public function generalManagerSignatureUser()
{
    return $this->belongsTo(User::class, 'general_manager_signature_user_id');
}

public function directorSignatureUser()
{
    return $this->belongsTo(User::class, 'director_signature_user_id');
}

}

