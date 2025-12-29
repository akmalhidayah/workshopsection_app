<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPKApprovalToken extends Model
{
    protected $table = 'spk_approval_tokens';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',

        // 🔑 RELASI UTAMA
        'nomor_spk',

        // 🔗 KONTEKS ASAL
        'notification_number',

        // approval meta
        'sign_type',        // manager | senior_manager
        'user_id',

        // audit trail
        'issued_by',
        'issued_channel',
        'ip_issued',
        'ua_issued',

        // lifecycle
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* =========================
       RELATIONS
       ========================= */

    public function spk()
    {
        return $this->belongsTo(SPK::class, 'nomor_spk', 'nomor_spk');
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* =========================
       HELPERS
       ========================= */

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return ! is_null($this->used_at);
    }
}
