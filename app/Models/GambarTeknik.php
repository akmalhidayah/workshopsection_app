<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GambarTeknik extends Model
{
    protected $primaryKey = 'notification_number'; // Set notification_number sebagai primary key
    public $incrementing = false; // Matikan auto increment
    protected $keyType = 'string'; // Set tipe primary key sebagai string

    protected $fillable = ['notification_number', 'file_path'];

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }
}
