<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abnormal extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_number';  // Set primary key ke notification_number
    public $incrementing = false; // Karena notification_number bukan integer, matikan auto increment

    protected $fillable = [
        'notification_number', 
        'abnormal_title', 
        'unit_kerja', 
        'abnormal_date', 
        'problem_description', 
        'root_cause', 
        'immediate_actions', 
        'summary', 
        'manager_signature',         // Kolom untuk tanda tangan manager
        'senior_manager_signature',
        'actions',
        'risks',
        'files'
    ];

    protected $casts = [
        'actions' => 'array',
        'risks' => 'array',
        'files' => 'array',
    ];

    // Relasi one-to-one balik ke Notification (optional, jika perlu)
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }
    // Relasi ke model User untuk mengidentifikasi pengguna yang menandatangani dokumen
    public function user()
    {
        return $this->belongsTo(User::class);
    }

        public function managerUser()
    {
        return $this->belongsTo(User::class, 'manager_signature_user_id');
    }

    public function seniorManagerUser()
    {
        return $this->belongsTo(User::class, 'senior_manager_signature_user_id');
    }

}
