<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hpp1 extends Model
{
    use HasFactory;

    protected $table = 'hpp1';
    protected $primaryKey = 'notification_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'notification_number',
        'cost_centre',
        'description',
        'requesting_unit',
        'controlling_unit',
        'outline_agreement',

        // data bertingkat per-kelompok [g][i]
        'uraian_pekerjaan',   // array 1D judul kelompok
        'jenis_item',    
        'nama_item',       // array 2D: material/consumable/upah
        'qty',                // array 2D
        'satuan',             // array 2D
        'harga_satuan',       // array 2D
        'harga_total',        // array 2D (qty * harga_satuan)
        'keterangan',         

        // total & status
        'total_amount',
        'status',             // draft|submitted|approved_*|rejected

        // notes & meta
        'source_form',
        'rejection_reason',
        'controlling_notes',
        'requesting_notes',

        // signature paths (bukan base64)
        'manager_signature',
        'senior_manager_signature',
        'general_manager_signature',
        'director_signature',

        // (opsional) signature dari sisi requesting unit
        'manager_signature_requesting_unit',
        'senior_manager_signature_requesting_unit',
        'general_manager_signature_requesting_unit',

          'manager_signed_at',
    'senior_manager_signed_at',
    'general_manager_signed_at',
    'director_signed_at',
    'manager_requesting_signed_at',
    'senior_manager_requesting_signed_at',
    'general_manager_requesting_signed_at',

        
        // penanda tangan (FK users)
        'manager_signature_user_id',
        'senior_manager_signature_user_id',
        'general_manager_signature_user_id',
        'director_signature_user_id',
        'manager_signature_requesting_user_id',
        'senior_manager_signature_requesting_user_id',
        'general_manager_signature_requesting_user_id',
    ];
 // 🔒 default "[]": supaya cast TIDAK pernah menghasilkan null
    protected $attributes = [
        'uraian_pekerjaan' => '[]',
        'jenis_item'       => '[]',
        'nama_item'        => '[]',
        'qty'              => '[]',
        'satuan'           => '[]',
        'harga_satuan'     => '[]',
        'harga_total'      => '[]',
        'keterangan'       => '[]',
        'requesting_notes'  => '[]',
        'controlling_notes' => '[]',
    ];
    protected $casts = [
        'uraian_pekerjaan' => 'array',
        'jenis_item'       => 'array',
        'nama_item'        => 'array',  
        'qty'              => 'array',
        'satuan'           => 'array',
        'harga_satuan'     => 'array',
        'harga_total'      => 'array',
        'keterangan'       => 'array',
        'total_amount'     => 'decimal:2',
        'requesting_notes'  => 'array',
        'controlling_notes' => 'array',
        'manager_signed_at'                      => 'datetime',
        'senior_manager_signed_at'               => 'datetime',
        'general_manager_signed_at'              => 'datetime',
        'director_signed_at'                     => 'datetime',
        'manager_requesting_signed_at'           => 'datetime',
        'senior_manager_requesting_signed_at'    => 'datetime',
        'general_manager_requesting_signed_at'   => 'datetime',
    ];
protected static function booted()
{
    static::deleting(function (self $hpp) {
        $svc = app(\App\Services\SignatureService::class);
        foreach ([
            'manager_signature',
            'senior_manager_signature',
            'general_manager_signature',
            'director_signature',
            'manager_signature_requesting_unit',
            'senior_manager_signature_requesting_unit',
            'general_manager_signature_requesting_unit',
        ] as $f) {
            // path baru berbentuk 'hpp/...' (private disk)
            if (!empty($hpp->$f) && !str_starts_with($hpp->$f, 'storage/')) {
                $svc->deleteIfExists($hpp->$f);
            }
        }
    });
}

    // ===== RELATIONS =====
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'notification_number', 'notification_number');
    }

    public function managerSignatureUser()
    {
        return $this->belongsTo(User::class, 'manager_signature_user_id');
    }

    public function seniorManagerSignatureUser()
    {
        return $this->belongsTo(User::class, 'senior_manager_signature_user_id');
    }

    public function generalManagerSignatureUser()
    {
        return $this->belongsTo(User::class, 'general_manager_signature_user_id');
    }

    public function directorSignatureUser()
    {
        return $this->belongsTo(User::class, 'director_signature_user_id');
    }

    public function managerSignatureRequestingUser()
    {
        return $this->belongsTo(User::class, 'manager_signature_requesting_user_id');
    }

    public function seniorManagerSignatureRequestingUser()
    {
        return $this->belongsTo(User::class, 'senior_manager_signature_requesting_user_id');
    }

    public function generalManagerSignatureRequestingUser()
    {
        return $this->belongsTo(User::class, 'general_manager_signature_requesting_user_id');
    }
}
