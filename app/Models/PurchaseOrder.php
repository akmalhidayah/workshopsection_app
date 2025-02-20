<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    // Menentukan bahwa primary key adalah 'notification_number' dan bukan 'id'
    protected $primaryKey = 'notification_number';
    public $incrementing = false; // Menyatakan bahwa primary key bukan auto-increment
    protected $keyType = 'string'; // Tipe data primary key adalah string

    protected $fillable = [
        'notification_number',
        'purchase_order_number',
        'po_document_path',
        'approve_manager',
        'approve_senior_manager',
        'approve_general_manager',
        'approve_direktur_operasional',
        'progress_pekerjaan',
        'catatan',
        'target_penyelesaian',
        'update_date',
    ];

    protected $casts = [
        'approve_manager' => 'boolean',
        'approve_senior_manager' => 'boolean',
        'approve_general_manager' => 'boolean',
        'approve_direktur_operasional' => 'boolean',
        'update_date' => 'datetime',
    ];

    // Relasi ke model Notification
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }
}
