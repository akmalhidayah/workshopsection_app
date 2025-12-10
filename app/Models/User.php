<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

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

    /**
     * Mutator: normalisasi nomor WhatsApp saat diset.
     * Menyimpan format E.164 Indonesia tanpa plus, contoh: 6281234567890
     */
    public function setWhatsappNumberAttribute($value)
    {
        if (is_null($value) || $value === '') {
            $this->attributes['whatsapp_number'] = null;
            return;
        }

        // Hilangkan semua non-digit
        $onlyDigits = preg_replace('/\D+/', '', (string) $value);

        // Jika dimulai dengan 0 -> ubah ke 62...
        if (Str::startsWith($onlyDigits, '0')) {
            $onlyDigits = '62' . ltrim($onlyDigits, '0');
        }
        // Jika langsung mulai 8 (mis '82123...') -> tambahkan 62
        elseif (Str::startsWith($onlyDigits, '8')) {
            $onlyDigits = '62' . $onlyDigits;
        }
        // Jika sudah mulai 62 -> biarkan
        // Untuk nomor internasional lain, biarkan apa adanya (tidak diubah)

        $this->attributes['whatsapp_number'] = $onlyDigits;
    }

    /**
     * Helper: kembalikan nomor untuk API (string E.164 tanpa plus).
     * Gunakan saat melakukan panggilan ke WA Cloud API.
     */
    public function whatsappNumberForApi(): ?string
    {
        return $this->whatsapp_number ?: null;
    }

    /**
     * Optional accessor tampil (format sederhana, hanya untuk UI).
     * Tidak mempengaruhi nilai tersimpan.
     */
    public function getWhatsappNumberDisplayAttribute(): ?string
    {
        $v = $this->whatsapp_number;
        if (empty($v)) return null;

        // contoh format tampil: +62 81234567890 (sederhana)
        return '+' . $v;
    }

    public function hppDocuments()
    {
        return $this->hasMany(Hpp1::class, 'notification_number', 'notification_number');
    }

    public function getDisplayTitleAttribute()
    {
        $name = $this->name;

        switch (strtolower($this->jabatan)) {

            case 'manager':
                return "{$name} (Manager — {$this->seksi})";

            case 'senior manager':
                return "{$name} (Senior Manager — {$this->unit_work})";

            case 'general manager':
                return "{$name} (General Manager — {$this->departemen})";

            case 'operational director':
            case 'operational direction':
            case 'director':
                return "{$name} (Operational Director — PT Semen Tonasa)";

            default:
                // fallback
                return "{$name} ({$this->jabatan})";
        }
    }

    /**
     * Scope: Cocokkan unit dengan unit utama / related units
     */
    public function scopeMatchUnit($q, string $unit)
    {
        $unit = strtolower(trim($unit));

        return $q->where(function ($q) use ($unit) {
            // Unit utama
            $q->whereRaw('LOWER(unit_work) LIKE ?', [$unit.'%'])

              // Related units JSON array
              ->orWhereJsonContains('related_units', $unit);
        });
    }
}
