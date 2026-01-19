<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitWork extends Model
{
    use HasFactory;

    protected $table = 'unit_work';

    /**
     * =========================
     * MASS ASSIGNMENT
     * =========================
     */
    protected $fillable = [
        'department_id',
        'senior_manager_id', // user yang menjabat sebagai Senior Manager unit ini
        'name',
        'seksi',
    ];

    /**
     * =========================
     * CASTS
     * =========================
     * Otomatis JSON <-> array
     */
    protected $casts = [
        'seksi' => 'array',
    ];

    /**
     * =========================
     * RELATIONS
     * =========================
     */

    // UnitWork belongs to Department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * User yang menjabat sebagai Senior Manager untuk unit kerja ini.
     */
    public function seniorManager()
    {
        return $this->belongsTo(User::class, 'senior_manager_id');
    }

    /**
     * Daftar seksi (section) formal yang terkait dengan unit ini,
     * masing-masing bisa punya Manager (User).
     */
    public function sections()
    {
        return $this->hasMany(UnitWorkSection::class, 'unit_work_id');
    }

    // Relasi bantu: cocokkan Notification.unit_work (string) ke UnitWork.name
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'unit_work', 'name');
    }

    /**
     * =========================
     * SCOPES
     * =========================
     */

    /**
     * Scope pencarian:
     * - name
     * - isi JSON seksi
     */
    public function scopeSearch($query, ?string $q)
    {
        if (!$q) return $query;

        return $query->where(function ($sub) use ($q) {
            $sub->where('name', 'like', "%{$q}%")
                ->orWhereRaw(
                    "JSON_SEARCH(seksi, 'one', ?) IS NOT NULL",
                    ["%{$q}%"]
                );
        });
    }

    /**
     * =========================
     * ACCESSORS / HELPERS
     * =========================
     */

    /**
     * Daftar seksi yang sudah dirapikan:
     * - trim
     * - buang kosong
     * - reset index
     */
    public function getSeksiListAttribute(): array
    {
        $arr = is_array($this->seksi) ? $this->seksi : [];

        return array_values(
            array_filter(
                array_map('trim', $arr),
                fn ($v) => $v !== ''
            )
        );
    }

    /**
     * Cek apakah suatu seksi ada (case-insensitive)
     */
    public function hasSeksi(string $name): bool
    {
        $needle = mb_strtolower(trim($name));

        foreach ($this->seksi_list as $s) {
            if (mb_strtolower($s) === $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * (Opsional) Helper: ambil Manager (User) untuk nama seksi tertentu
     * jika sudah di-mapping ke UnitWorkSection.
     */
    public function managerForSeksi(string $seksiName): ?User
    {
        $seksiName = trim($seksiName);

        $section = $this->sections
            ->firstWhere('name', $seksiName);

        return $section?->manager;
    }
}
