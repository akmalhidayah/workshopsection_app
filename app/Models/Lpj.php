<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Lpj extends Model
{
    use HasFactory;

    protected $table = 'lpjs';
    protected $primaryKey = 'notification_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'notification_number',

        // legacy single-number (tetap disimpan untuk backward compatibility)
        'lpj_number',
        'ppl_number',

        // --- tambahan: nomor per-termin (baru) ---
        'lpj_number_termin1',
        'ppl_number_termin1',
        'lpj_number_termin2',
        'ppl_number_termin2',

        // legacy file paths (tetap)
        'lpj_document_path',
        'ppl_document_path',

        // explicit per-termin file columns
        'lpj_document_path_termin1',
        'ppl_document_path_termin1',
        'lpj_document_path_termin2',
        'ppl_document_path_termin2',

        // status termin
        'termin1',
        'termin2',

        'update_date',

        // tambahkan garansi_months agar bisa diisi/dibaca lewat model
        'garansi_months',
    ];

    protected $casts = [
        'update_date' => 'datetime',
        'garansi_months' => 'integer',
    ];

    // ---------- Termin helpers (existing) ----------
    public function isTermin1Paid(): bool
    {
        return $this->termin1 === 'sudah';
    }

    public function isTermin2Paid(): bool
    {
        return $this->termin2 === 'sudah';
    }

    public function getTermin1LabelAttribute(): string
    {
        return $this->termin1 === 'sudah' ? 'Sudah Dibayar' : 'Belum Dibayar';
    }

    public function getTermin2LabelAttribute(): string
    {
        return $this->termin2 === 'sudah' ? 'Sudah Dibayar' : 'Belum Dibayar';
    }

    // ---------- File path helpers (prioritas & kompatibilitas) (existing) ----------
    /**
     * Get LPJ file path for a given termin (1 or 2).
     * Prioritas:
     *  - termin-specific column (lpj_document_path_terminX)
     *  - legacy lpj_document_path (treated as termin1)
     */
    public function getLpjPathForTermin(int $termin = 1): ?string
    {
        if ($termin === 1) {
            return $this->lpj_document_path_termin1 ?? $this->lpj_document_path ?? null;
        }

        return $this->lpj_document_path_termin2 ?? null;
    }

    /**
     * Get PPL file path for a given termin (1 or 2).
     * Prioritas: termin-specific, lalu legacy.
     */
    public function getPplPathForTermin(int $termin = 1): ?string
    {
        if ($termin === 1) {
            return $this->ppl_document_path_termin1 ?? $this->ppl_document_path ?? null;
        }

        return $this->ppl_document_path_termin2 ?? null;
    }

    // ---------- New: nomor per-termin helpers (tidak mengubah logic lama) ----------
    public function getLpjNumberForTermin(int $termin = 1): ?string
    {
        if ($termin === 1) {
            return $this->lpj_number_termin1 ?? $this->lpj_number ?? null;
        }
        return $this->lpj_number_termin2 ?? null;
    }

    public function getPplNumberForTermin(int $termin = 1): ?string
    {
        if ($termin === 1) {
            return $this->ppl_number_termin1 ?? $this->ppl_number ?? null;
        }
        return $this->ppl_number_termin2 ?? null;
    }

    /**
     * Generate safe filename mengikuti format:
     *   {TYPE}_BMS_{NOTIF}_T{termin}_{NUMBER}.{ext}
     */
    public function generateDocumentFilename(string $type, int $termin = 1, string $extension = 'pdf'): string
    {
        $typeUpper = strtoupper($type) === 'PPL' ? 'PPL' : 'LPJ';
        $notif = $this->notification_number;
        $number = $typeUpper === 'LPJ' ? $this->getLpjNumberForTermin($termin) : $this->getPplNumberForTermin($termin);
        $cleanNumber = $number ? preg_replace('/[^A-Za-z0-9\-_]/', '_', $number) : 'NONUM';
        $filename = "{$typeUpper}_BMS_{$notif}_T{$termin}_{$cleanNumber}.{$extension}";

        // limit length to be safe for filesystems
        return Str::limit($filename, 200, '');
    }

    /**
     * Shortcut: apakah termin sudah dibayar
     */
    public function isTerminPaid(int $termin = 1): bool
    {
        return $termin === 1 ? $this->isTermin1Paid() : $this->isTermin2Paid();
    }

    /**
     * Hitung tanggal berakhir garansi berdasarkan tanggal mulai.
     *
     * @param  \Carbon\Carbon|string|null  $start
     * @return \Carbon\Carbon|null
     */
    public function calculateGaransiEndDate($start): ?Carbon
    {
        if (empty($start)) {
            return null;
        }

        try {
            $startDate = $start instanceof Carbon ? $start : Carbon::parse($start);
        } catch (\Exception $e) {
            return null;
        }

        $months = (int) ($this->garansi_months ?? 0);

        if ($months <= 0) {
            return null;
        }

        // default: berakhir = start + months - 1 hari
        return $startDate->copy()->addMonths($months)->subDay();
    }
}
