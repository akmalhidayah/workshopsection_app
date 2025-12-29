<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPK extends Model
{
    use HasFactory;

    protected $table = 'spks';

    protected $primaryKey = 'nomor_spk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nomor_spk',
        'perihal',
        'tanggal_spk',
        'notification_number',
        'unit_work',
        'keterangan_pekerjaan',

        // JSON fields
        'functional_location',
        'scope_pekerjaan',
        'qty',
        'stn',
        'keterangan',

        // signatures
        'manager_signature',
        'manager_signature_user_id',
        'manager_signed_at',

        'senior_manager_signature',
        'senior_manager_signature_user_id',
        'senior_manager_signed_at',

        // status approval
        'status',
    ];

    protected $casts = [
        'functional_location' => 'array',
        'scope_pekerjaan'     => 'array',
        'qty'                 => 'array',
        'stn'                 => 'array',
        'keterangan'          => 'array',
        'tanggal_spk'         => 'date',
        'manager_signed_at'   => 'datetime',
        'senior_manager_signed_at' => 'datetime',
    ];

    /* ================================
       RELATIONS
    =================================*/

    // SPK -> Notification
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_number', 'notification_number');
    }

    // Manager signer
    public function managerSignatureUser()
    {
        return $this->belongsTo(User::class, 'manager_signature_user_id');
    }

    // Senior Manager signer
    public function seniorManagerSignatureUser()
    {
        return $this->belongsTo(User::class, 'senior_manager_signature_user_id');
    }

    /* ================================
       APPROVAL HELPERS
    =================================*/

    // Cek apakah manager sudah tanda tangan
    public function isManagerSigned(): bool
    {
        return !empty($this->manager_signature) || !empty($this->manager_signature_user_id);
    }

    // Cek apakah senior manager sudah tanda tangan
    public function isSeniorManagerSigned(): bool
    {
        return !empty($this->senior_manager_signature) || !empty($this->senior_manager_signature_user_id);
    }

    // Cek apakah semua tanda tangan sudah lengkap
    public function isFullyApproved(): bool
    {
        return $this->isManagerSigned() && $this->isSeniorManagerSigned();
    }
    public function approvalStatusLabel(): string
{
    if ($this->isFullyApproved()) {
        return '✅ Fully Approved';
    }

    if ($this->isManagerSigned()) {
        return '✍️ Signed by Manager';
    }

    return '⏳ Belum Ditandatangani';
}

}
