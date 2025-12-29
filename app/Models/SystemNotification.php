<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $fillable = [
        'target_role',
        'entity_type',
        'entity_id',
        'action',
        'title',
        'description',
        'url',
        'priority',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}
