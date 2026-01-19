<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Hpp1;
use App\Models\HppApprovalToken;
use App\Models\KuotaAnggaranOA;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseHppController extends Controller
{

public function index()
{
    // =========================
    // FILTER INPUT
    // =========================
    $search    = request('search');
    $jenisHpp  = request('jenis_hpp');   // createhpp1|createhpp2|createhpp3|createhpp4
    $unitKerja = request('unit_kerja');

    // =========================
    // QUERY UTAMA HPP
    // =========================
    $query = Hpp1::with('notification')
        ->orderByDesc('created_at');

    if (!empty($jenisHpp)) {
        $query->where('source_form', $jenisHpp);
    }

    if (!empty($unitKerja)) {
        $query->where('requesting_unit', $unitKerja);
    }

    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('notification_number', 'like', "%{$search}%")
              ->orWhere('requesting_unit', 'like', "%{$search}%")
              ->orWhere('outline_agreement', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // =========================
    // PAGINATION
    // =========================
    $hpp = $query->paginate(5)->withQueryString();

    // =========================
    // DROPDOWN UNIT KERJA
    // =========================
    $unitKerjaOptions = Hpp1::whereNotNull('requesting_unit')
        ->distinct()
        ->orderBy('requesting_unit')
        ->pluck('requesting_unit')
        ->toArray();

    // =========================
    // STATISTIK TOTAL PER UNIT
    // =========================
    $unitKerjaHppData = Hpp1::selectRaw('requesting_unit, SUM(total_amount) as total')
        ->when($jenisHpp, fn ($q) => $q->where('source_form', $jenisHpp))
        ->when($unitKerja, fn ($q) => $q->where('requesting_unit', $unitKerja))
        ->when($search, function ($q) use ($search) {
            $q->where(function ($s) use ($search) {
                $s->where('notification_number', 'like', "%{$search}%")
                  ->orWhere('requesting_unit', 'like', "%{$search}%")
                  ->orWhere('outline_agreement', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        })
        ->groupBy('requesting_unit')
        ->orderByDesc('total')
        ->take(10)
        ->get();

// =========================
// 🔥 TOKEN APPROVAL AKTIF (SEMUA USER, SESUAI PERILAKU LAMA)
// =========================

// Ambil notification_number yang tampil di halaman ini saja
$notifNumbers = $hpp->getCollection()
    ->pluck('notification_number')
    ->map(fn ($v) => (string) $v)
    ->all();

$activeTokens = HppApprovalToken::whereIn('notification_number', $notifNumbers)
    ->whereNull('used_at')
    ->where(function ($q) {
        $q->whereNull('expires_at')
          ->orWhere('expires_at', '>', now());
    })
    ->orderByDesc('created_at')
    ->get()
    // kalau ada lebih dari satu token per notif → ambil yang terbaru
    ->unique('notification_number')
    // key harus STRING supaya match Blade
    ->keyBy(fn ($t) => (string) $t->notification_number);

    // =========================
    // RETURN VIEW
    // =========================
    return view('admin.inputhpp.index', compact(
        'hpp',
        'unitKerjaOptions',
        'unitKerjaHppData',
        'activeTokens'
    ));
}

 // sebelum: protected function cleanData(array $dataArray = null): array
protected function cleanData(?array $dataArray = null): array
{
    if (!is_array($dataArray)) return [];
    return array_map(function ($item) {
        $s = trim((string) ($item ?? ''));
        return ($s === '' || $s === '-') ? null : $item;
    }, $dataArray);
}

// (boleh tetap tanpa type-hint, atau sekalian buat nullable untuk konsistensi)
protected function cleanData2D(?array $arr2d): array
{
    if (!is_array($arr2d)) return [];
    return array_map(fn($row) => $this->cleanData(is_array($row) ? $row : []), $arr2d);
}

    /**
     * Mengambil Outline Agreement aktif berdasarkan tanggal saat ini.
     */
    protected function getCurrentOA()
    {
        try {
            $currentDate = now()->format('Y-m-d');

            return KuotaAnggaranOA::where('periode_kontrak_start', '<=', $currentDate)
                ->where('periode_kontrak_end', '>=', $currentDate)
                ->first();
        } catch (\Throwable $e) {
            Log::error('Gagal mengambil data Outline Agreement: ' . $e->getMessage());
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat mengambil Outline Agreement.');
        }
    }


    /**
     * ✅ Menyiapkan data PDF untuk HPP (digunakan oleh downloadPDF)
     * Disesuaikan dengan struktur baru: memakai casts array di model.
     */
    protected function preparePdfData($hpp)
    {
        try {
            // Dengan casts di model, kolom sudah berupa array. Kita bersihkan saja.
            $hpp->jenis_item       = $this->cleanData2D($hpp->jenis_item);
             $hpp->nama_item       = $this->cleanData2D($hpp->nama_item);
            $hpp->qty              = $this->cleanData2D($hpp->qty);
            $hpp->satuan           = $this->cleanData2D($hpp->satuan);
            $hpp->harga_satuan     = $this->cleanData2D($hpp->harga_satuan);
            $hpp->harga_total      = $this->cleanData2D($hpp->harga_total);
            $hpp->keterangan       = $this->cleanData2D($hpp->keterangan);

            // Ambil OA aktif (bisa null)
            $currentOA = $this->getCurrentOA();

            // Susun data untuk dikirim ke view PDF
            return [
                'hpp' => $hpp,
                'currentOA' => $currentOA,
                'tanggal_cetak' => now()->format('d/m/Y H:i'),
                'judul' => 'Dokumen HPP',
            ];
        } catch (\Throwable $e) {
            \Log::error('❌ Gagal menyiapkan data PDF HPP: ' . $e->getMessage());
            abort(\Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat memproses data PDF.');
        }
    }
}
