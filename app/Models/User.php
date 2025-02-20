<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'initials', // Menambahkan kolom initials
        'email',
        'password',
        'usertype',
        'departemen',
        'unit_work',
        'seksi',
        'jabatan',
        'whatsapp_number',
        'related_units',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'related_units' => 'array', 
    ];

    /**
     * Set default value for related_units during model creation.
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (is_null($model->related_units)) {
                $model->related_units = [];
            }
        });
    }

    public function hppDocuments()
    {
        return $this->hasMany(Hpp1::class, 'notification_number', 'notification_number');
    }
}
