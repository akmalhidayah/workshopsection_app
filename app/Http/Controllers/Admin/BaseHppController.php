<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Hpp1;
use App\Models\HppApprovalToken;
use App\Models\KuotaAnggaranOA;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseHppController extends Controller
{
public function index()
{
    // ambil parameter filter dari query string
    $search    = request('search');
    $jenisHpp  = request('jenis_hpp');   // createhpp1|createhpp2|createhpp3
    $unitKerja = request('unit_kerja');

    // query dasar + eager load notification (buat usage_plan_date di table)
    $query = Hpp1::with('notification')->orderBy('created_at', 'desc');

    // filter: jenis hpp -> source_form
    if (!empty($jenisHpp)) {
        $query->where('source_form', $jenisHpp);
    }

    // filter: unit kerja
    if (!empty($unitKerja)) {
        $query->where('requesting_unit', $unitKerja);
    }

    // filter: search (nomor order / unit / outline / deskripsi)
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('notification_number', 'like', "%{$search}%")
              ->orWhere('requesting_unit', 'like', "%{$search}%")
              ->orWhere('outline_agreement', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // data untuk tabel (paginate)
    $hpp = $query->paginate(5)->withQueryString();

    // opsi dropdown unit kerja (distinct, diurutkan)
    $unitKerjaOptions = Hpp1::whereNotNull('requesting_unit')
        ->distinct()
        ->orderBy('requesting_unit')
        ->pluck('requesting_unit')
        ->toArray();

    // statistik total per unit kerja (menghormati filter jenis_hpp & search/unit jika kamu mau)
    $statsQuery = Hpp1::selectRaw('requesting_unit, SUM(total_amount) as total')
        ->when(!empty($jenisHpp), fn($q) => $q->where('source_form', $jenisHpp))
        ->when(!empty($unitKerja), fn($q) => $q->where('requesting_unit', $unitKerja))
        ->when(!empty($search), function ($q) use ($search) {
            $q->where(function ($s) use ($search) {
                $s->where('notification_number', 'like', "%{$search}%")
                  ->orWhere('requesting_unit', 'like', "%{$search}%")
                  ->orWhere('outline_agreement', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        })
        ->groupBy('requesting_unit')
        ->orderBy('total', 'desc')
        ->take(10);

    $unitKerjaHppData = $statsQuery->get();

    // ambil nomor dari koleksi paginator → pastikan STRING
$notifNumbers = $hpp->getCollection()
    ->pluck('notification_number')
    ->map(fn ($v) => (string) $v)
    ->all();

$activeTokens = HppApprovalToken::whereIn('notification_number', $notifNumbers)
    ->whereNull('used_at')
    ->where('expires_at', '>', now())
    ->orderByDesc('created_at')
    ->get()
    ->unique('notification_number')
    // >>> key pakai STRING, agar cocok dengan $data->notification_number di Blade
    ->keyBy(fn ($t) => (string) $t->notification_number);


return view('admin.inputhpp.index', compact(
    'hpp', 'unitKerjaHppData', 'unitKerjaOptions', 'activeTokens'
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
     * ✅ Encode otomatis field JSON dalam satu loop
     * Catatan: disimpan untuk kompatibilitas lama; jika dipakai,
     * sekarang lebih aman mengoper array apa adanya (model sudah casts).
     */
    protected function mapJsonFields(Request $request, array $fields): array
    {
        try {
            $mapped = [];
            foreach ($fields as $field) {
                // Tetap kembalikan array (bukan string JSON), mengikuti casts di model
                $mapped[$field] = $request->input($field, []);
            }
            return $mapped;
        } catch (\Throwable $e) {
            Log::error('❌ Gagal memetakan field JSON: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Gagal memproses data JSON.');
        }
    }

    /**
     * ✅ Mengambil Outline Agreement aktif berdasarkan tanggal saat ini
     */
    protected function getCurrentOA()
    {
        try {
            $currentDate = now()->format('Y-m-d');

            $currentOA = KuotaAnggaranOA::where('periode_kontrak_start', '<=', $currentDate)
                ->where('periode_kontrak_end', '>=', $currentDate)
                ->first();

            return $currentOA;
        } catch (\Throwable $e) {
            Log::error('❌ Gagal mengambil data Outline Agreement: ' . $e->getMessage());
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat mengambil Outline Agreement.');
        }
    }

    /**
     * ✅ Mengirim notifikasi WhatsApp ke seluruh Manager Workshop (Fonnte)
     * (Biarkan token hardcoded dulu agar tidak mengubah alur lama)
     */
    protected function sendWhatsappToManagers($hpp)
    {
        try {
            $managers = User::where('unit_work', 'Unit Of Workshop')
                ->where('jabatan', 'Manager')
                ->get();

            if ($managers->isEmpty()) {
                Log::warning('⚠️ Tidak ada manager ditemukan untuk pengiriman WA.');
                return;
            }

            foreach ($managers as $manager) {
                try {
                    Http::withHeaders([
                        'Authorization' => 'KBTe2RszCgc6aWhYapcv', // API key Fonnte (lama)
                    ])->timeout(10)->post('https://api.fonnte.com/send', [
                        'target' => $manager->whatsapp_number,
                        'message' => "📄 *Permintaan Approval HPP Baru*\n"
                            . "Nomor Notifikasi: {$hpp->notification_number}\n"
                            . "Unit Kerja: {$hpp->controlling_unit}\n"
                            . "Deskripsi: {$hpp->description}\n\n"
                            . "Silakan login untuk melihat detail:\n"
                            . "https://sectionofworkshop.com/approval/hpp",
                    ]);

                    Log::info("✅ WhatsApp notification sent to {$manager->whatsapp_number}");
                } catch (\Throwable $e) {
                    Log::error("❌ Gagal kirim WhatsApp ke {$manager->whatsapp_number}: " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::error('❌ Terjadi kesalahan saat proses pengiriman WhatsApp: ' . $e->getMessage());
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Gagal mengirim notifikasi WhatsApp.');
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
            $hpp->uraian_pekerjaan = $this->cleanData($hpp->uraian_pekerjaan);
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
