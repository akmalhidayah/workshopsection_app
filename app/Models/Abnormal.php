<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abnormal extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'notification_number',
        'files',
    ];

    protected $casts = [
        'files' => 'array',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }
}

