<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitWorkSection extends Model
{
    use HasFactory;

    protected $table = 'unit_work_sections';

    protected $fillable = [
        'unit_work_id',
        'name',
        'manager_id',
    ];

    /**
     * Seksi ini milik unit kerja apa.
     */
    public function unitWork()
    {
        return $this->belongsTo(UnitWork::class, 'unit_work_id');
    }

    /**
     * User yang menjabat sebagai Manager di seksi ini.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
