<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LHPPApprovalToken extends Model
{
    // Kalau mau pakai tabel khusus:
    protected $table = 'lhpp_approval_tokens';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'notification_number',
        'sign_type',      // misal: manager_pkm
        'user_id',
        'expires_at',
        'used_at',
        'issued_by',
        'issued_channel',
        'ip_issued',
        'ua_issued',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (! $m->id) {
                $m->id = (string) Str::uuid();
            }
        });
    }
}
