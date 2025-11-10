<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_number';
    public $incrementing = false;
    protected $keyType = 'string';

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
        'approval_target',
        'approval_note',    // ✅ Catatan kecil saat PO disetujui/tidak disetujui
        'catatan_pkm',      // ✅ Catatan umum PKM (pengelola)
        'update_date',
    ];

    protected $casts = [
        'approve_manager' => 'boolean',
        'approve_senior_manager' => 'boolean',
        'approve_general_manager' => 'boolean',
        'approve_direktur_operasional' => 'boolean',
        'update_date' => 'datetime',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }
}
