<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LHPP extends Model
{
    use HasFactory;

    protected $table = 'lhpp';
    protected $primaryKey = 'notification_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'notification_number',
        'nomor_order',
        'description_notifikasi',
        'purchase_order_number',
        'unit_kerja',
        'tanggal_selesai',
        'waktu_pengerjaan',
        'material_description',
        'material_volume',
        'material_harga_satuan',
        'material_jumlah',
        'upah_description',
        'upah_volume',
        'upah_harga_satuan',
        'upah_jumlah',
        'material_subtotal',
        'upah_subtotal',
        'total_biaya',
        'images',
        'kontrak_pkm',

        // TTD
        'manager_signature',                   // Manager Workshop
        'manager_signature_requesting',        // Manager Peminta (Manager User)
        'manager_pkm_signature',               // Manager PKM

        'controlling_notes',                   // Catatan QC / PKM
        'requesting_notes',                    // Catatan unit peminta

        'manager_signature_user_id',
        'manager_signature_requesting_user_id',
        'manager_pkm_signature_user_id',
        'status_approve',                      // approved / rejected / pending
    ];

    protected $casts = [
        'material_description' => 'array',
        'material_volume'      => 'array',
        'material_harga_satuan'=> 'array',
        'material_jumlah'      => 'array',
        'upah_description'     => 'array',
        'upah_volume'          => 'array',
        'upah_harga_satuan'    => 'array',
        'upah_jumlah'          => 'array',
        'images'               => 'array',

        // notes kalau mau bentuk array
        'controlling_notes' => 'array',
        'requesting_notes' => 'array',
    ];

    // relasi user (sudah benar)
    // ...

    public function hasAllSignatures(): bool
    {
        $a = !empty($this->manager_signature) || !empty($this->manager_signature_user_id);
        $b = !empty($this->manager_signature_requesting) || !empty($this->manager_signature_requesting_user_id);
        $c = !empty($this->manager_pkm_signature) || !empty($this->manager_pkm_signature_user_id);

        return $a && $b && $c;
    }
}
