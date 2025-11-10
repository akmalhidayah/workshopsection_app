<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenOrder extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_number';
    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected $fillable = [
        'notification_number',
        'jenis_dokumen',
        'file_path',
        'keterangan',
        'nama_pekerjaan',
        'unit_kerja',
        'tanggal_pemakaian',
        'tanggal_dokumen',
        'scope_pekerjaan',
        'qty',
        'satuan',
        'keterangan_pekerjaan',
        'catatan',
        'nama_penginput',
        'tanda_tangan',
    ];

    protected $casts = [
        'scope_pekerjaan' => 'array',
        'qty' => 'array',
        'satuan' => 'array',
        'keterangan_pekerjaan' => 'array',
    ];

    // Relasi ke Notification
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }
}
