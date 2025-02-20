<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LHPP extends Model
{
    use HasFactory;

    protected $table = 'lhpp'; // Nama tabel di database

    // Menentukan bahwa 'notification_number' adalah primary key dan tipe datanya adalah string
    protected $primaryKey = 'notification_number';
    public $incrementing = false; // Karena 'notification_number' bukan auto-increment
    protected $keyType = 'string'; // Tipe data dari primary key adalah string

    protected $fillable = [
        'notification_number', // Primary key
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
        'consumable_description',
        'consumable_volume',
        'consumable_harga_satuan',
        'consumable_jumlah',
        'upah_description',
        'upah_volume',
        'upah_harga_satuan',
        'upah_jumlah',
        'material_subtotal',
        'consumable_subtotal',
        'upah_subtotal',
        'total_biaya',
        'images',
        'kontrak_pkm',
        'manager_signature', // Tanda tangan Manager
        'manager_signature_requesting', // Tanda tangan Manager Peminta
        'manager_pkm_signature', // Tanda tangan Manager PKM
        'controlling_notes', // Catatan Pengendali
        'requesting_notes', // Catatan Peminta
        'manager_signature_user_id', // ID User yang bertanda tangan sebagai Manager
        'manager_signature_requesting_user_id', // ID User yang bertanda tangan sebagai Manager Peminta
        'manager_pkm_signature_user_id', // ID User yang bertanda tangan sebagai Manager PKM
        'status_approve' // Status Approve
    ];

    // Cast fields that store JSON data to arrays
    protected $casts = [
        'material_description' => 'array',
        'material_volume' => 'array',
        'material_harga_satuan' => 'array',
        'material_jumlah' => 'array',
        'consumable_description' => 'array',
        'consumable_volume' => 'array',
        'consumable_harga_satuan' => 'array',
        'consumable_jumlah' => 'array',
        'upah_description' => 'array',
        'upah_volume' => 'array',
        'upah_harga_satuan' => 'array',
        'upah_jumlah' => 'array',
        'images' => 'array'
    ];

    // Menambahkan relasi ke tabel users
    public function managerSignatureUser()
    {
        return $this->belongsTo(User::class, 'manager_signature_user_id');
    }

    public function managerSignatureRequestingUser()
    {
        return $this->belongsTo(User::class, 'manager_signature_requesting_user_id');
    }

    public function managerPKMSignatureUser()
    {
        return $this->belongsTo(User::class, 'manager_pkm_signature_user_id');
    }
}
