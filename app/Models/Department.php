<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'name',
        'general_manager_id', // user yang menjabat sebagai General Manager
    ];

    /**
     * Department has many Unit Work
     */
    public function unitWorks()
    {
        return $this->hasMany(UnitWork::class, 'department_id');
    }

    /**
     * User yang menjabat sebagai General Manager departemen ini.
     */
    public function generalManager()
    {
        return $this->belongsTo(User::class, 'general_manager_id');
    }
}
