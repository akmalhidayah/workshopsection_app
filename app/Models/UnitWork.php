<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitWork extends Model
{
    use HasFactory;

    protected $table = 'unit_work';

    protected $fillable = ['name', 'seksi'];

    // penting: otomatis cast JSON <-> array
    protected $casts = [
        'seksi' => 'array',
    ];

    // Relasi bantu: cocokkan Notification.unit_work (string) ke UnitWork.name
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'unit_work', 'name');
    }

    // Scope pencarian: name + item di dalam JSON 'seksi' (MySQL)
    public function scopeSearch($query, ?string $q)
    {
        if (!$q) return $query;

        // Cari di name
        $query->where('name', 'like', "%{$q}%");

        // Tambahkan OR: cari elemen seksi yang mengandung keyword (JSON_SEARCH mendukung wildcard %)
        // Catatan: butuh MySQL/MariaDB. Untuk SQLite/PostgreSQL, butuh penyesuaian.
        return $query->orWhereRaw("JSON_SEARCH(seksi, 'one', ?) IS NOT NULL", ["%{$q}%"]);
    }

    // Helper: daftar seksi yang sudah dirapikan (trim & buang kosong)
    public function getSeksiListAttribute(): array
    {
        $arr = is_array($this->seksi) ? $this->seksi : [];
        return array_values(array_filter(array_map('trim', $arr), fn ($v) => $v !== ''));
    }

    // Helper: cek apakah suatu seksi ada pada unit ini (case-insensitive)
    public function hasSeksi(string $name): bool
    {
        $needle = mb_strtolower(trim($name));
        foreach ($this->seksi_list as $s) {
            if (mb_strtolower($s) === $needle) return true;
        }
        return false;
    }
}
