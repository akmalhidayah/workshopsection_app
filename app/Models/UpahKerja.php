<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpahKerja extends Model
{
    use HasFactory;

    protected $fillable = [
        'lhpp_id',
        'description',
        'volume',
        'harga_satuan',
        'jumlah',
    ];

    public function lhpp()
    {
        return $this->belongsTo(LHPP::class);
    }
}
