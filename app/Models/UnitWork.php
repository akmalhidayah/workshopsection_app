<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitWork extends Model
{
    use HasFactory;

    protected $table = 'unit_work'; // optional jika nama model beda dengan tabel

    protected $fillable = ['name']; // ✅ penting!
}
