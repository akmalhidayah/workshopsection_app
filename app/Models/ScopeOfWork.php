<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScopeOfWork extends Model
{
    protected $primaryKey = 'notification_number';
    public $incrementing = false; // Because the primary key is not an integer
    protected $keyType = 'string';

    protected $fillable = [
        'notification_number', 
        'nama_pekerjaan', 
        'unit_kerja', 
        'tanggal_dokumen', 
        'tanggal_pemakaian',
        'scope_pekerjaan', 
        'qty', 
        'satuan', 
        'keterangan', 
        'catatan',
        'nama_penginput',
        'tanda_tangan'
    ];

    protected $casts = [
        'scope_pekerjaan' => 'array',
        'qty' => 'array',
        'satuan' => 'array',
        'keterangan' => 'array',
    ];
}

