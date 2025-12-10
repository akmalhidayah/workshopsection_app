<?php

namespace App\Http\Controllers\Admin;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Hpp1;
use App\Models\Notification;
use App\Models\User;
use App\Jobs\SendWhatsAppMessage;
use App\Services\WhatsAppCloudService;
use Illuminate\Support\Facades\Http;
use App\Services\HppApprovalLinkService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\Services\SignatureService;
use Illuminate\Support\Facades\Auth;



class Hpp1Controller extends BaseHppController
{public function index()
{
    return parent::index();
}


    public function create()
    {
        try {
            $notifications = Notification::whereNotIn('notification_number', function ($query) {
                    $query->select('notification_number')->from('hpp1');
                })
                ->with('dokumenOrders', 'scopeOfWork')
                ->get();

            $source_form = 'createhpp1';
            $currentOA   = $this->getCurrentOA();

            return view('admin.inputhpp.createhpp1', compact('notifications', 'source_form', 'currentOA'));
        } catch (\Throwable $e) {
            Log::error('Gagal membuka form create HPP1: '.$e->getMessage());
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat membuka form create.');
        }
    }
public function store(Request $request)
{
    try {
        // 1) VALIDASI HEADER + NESTED FIELDS (uraian_pekerjaan TIDAK LAGI diwajibkan)
        $data = $request->validate([
            'notification_number' => [
                'required','string','max:255',
                Rule::exists('notifications','notification_number'),
                Rule::unique('hpp1','notification_number'),
            ],
            'source_form'         => 'nullable|string|max:100',
            'cost_centre'         => 'nullable|string|max:100',
            'description'         => 'required|string',
            'requesting_unit'     => 'nullable|string|max:100',
            'controlling_unit'    => 'nullable|string|max:100',
            'outline_agreement'   => 'nullable|string|max:100',

            // item [g][i] - menggunakan nama_item sebagai acuan group
            'jenis_item'          => 'nullable|array',
            'jenis_item.*'        => 'array',
            'jenis_item.*.*'      => 'nullable|string|max:50',

            'nama_item'           => 'required|array|min:1',
            'nama_item.*'         => 'array|min:1',
            'nama_item.*.*'       => 'nullable|string|max:255',

            'jumlah_item'         => 'nullable|array',        // <<-- tambah ini
            'jumlah_item.*'       => 'array',
            'jumlah_item.*.*'     => 'nullable|string|max:255',

            'qty'                 => 'required|array|min:1',
            'qty.*'               => 'array|min:1',
            'qty.*.*'             => 'nullable|numeric|min:0',

            'satuan'              => 'required|array|min:1',
            'satuan.*'            => 'array|min:1',
            'satuan.*.*'          => 'nullable|string|max:50',

            'harga_satuan'        => 'required|array|min:1',
            'harga_satuan.*'      => 'array|min:1',
            'harga_satuan.*.*'    => 'nullable|numeric|min:0',

            'harga_total'         => 'nullable|array',
            'harga_total.*'       => 'array',
            'harga_total.*.*'     => 'nullable|numeric|min:0',

            'keterangan'          => 'nullable|array',
            'keterangan.*'        => 'array',
            'keterangan.*.*'      => 'nullable|string|max:255',

            'status'              => 'nullable|string|in:draft,submitted,rejected,approved_manager,approved_sm,approved_gm,approved_dir',
            'rejection_reason'    => 'nullable|string|max:255',
            'controlling_notes'   => 'nullable|string',
            'requesting_notes'    => 'nullable|string',
        ]);

        // default & lock requesting_unit
        $data['source_form'] = $data['source_form'] ?? 'createhpp1';
        $notif = Notification::where('notification_number', $data['notification_number'])->firstOrFail();
        $data['requesting_unit'] = $notif->unit_work;
        $data['cost_centre'] = $notif->cost_centre ?? ($data['cost_centre'] ?? null);

        // NORMALISASI PANJANG ARRAY + HITUNG GRAND TOTAL
        $grand     = 0.0;
        $itemCols  = ['jenis_item','jumlah_item','nama_item','qty','satuan','harga_satuan','harga_total','keterangan'];

        // pastikan nama_item ada sebagai acuan group
        if (!isset($data['nama_item']) || !is_array($data['nama_item']) || empty($data['nama_item'])) {
            $data['nama_item'] = [['']]; // fallback 1 group kosong
        }

        // gunakan index kelompok dari nama_item
        $groupIndices = array_keys($data['nama_item']);

        foreach ($groupIndices as $g) {
            $n = count($data['nama_item'][$g] ?? []);

            foreach ($itemCols as $col) {
                $row = $data[$col][$g] ?? [];
                $row = is_array($row) ? array_values($row) : [];
                $data[$col][$g] = array_values(array_pad(array_slice($row, 0, $n), $n, null));
            }

            for ($i = 0; $i < $n; $i++) {
                $qty   = (float)($data['qty'][$g][$i] ?? 0);
                $harga = (float)($data['harga_satuan'][$g][$i] ?? 0);
                $data['harga_total'][$g][$i] = round($qty * $harga, 2);
                $grand += $data['harga_total'][$g][$i];
            }
        }
        $data['total_amount'] = $grand;

        // Tentukan status berdasarkan tombol
        $action = $request->input('action'); // 'draft' | 'submit'
        $data['status'] = $action === 'submit' ? 'submitted' : 'draft';

        if ($data['status'] === 'submitted' && $data['total_amount'] <= 0) {
            return back()
                ->withInput()
                ->with('error', 'Total keseluruhan harus lebih dari 0 untuk diajukan.');
        }

        // SIMPAN (filter hanya field yang ada di model -> mencegah field tak diinginkan tersimpan)
        $hppModel = new Hpp1();
        $saveData = Arr::only($data, $hppModel->getFillable());
        $saveData['total_amount'] = number_format((float)($saveData['total_amount'] ?? 0), 2, '.', '');

        $hpp = Hpp1::create($saveData);

        // Jika submitted, issue first token
        if ($hpp->status === 'submitted') {
            $this->issueFirstToken($hpp);
        }

        return redirect()
            ->route('admin.inputhpp.index')
            ->with('success', 'HPP berhasil dibuat.')
            ->setStatusCode(Response::HTTP_CREATED);

    } catch (\Throwable $e) {
        \Log::error('Gagal menyimpan HPP1 (store): '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan data HPP.');
    }
}
private function issueFirstToken(Hpp1 $hpp): void
{
    $roleLower   = 'manager';
    $unitPattern = 'unit of workshop%';

    $user = User::query()
        ->whereRaw('LOWER(jabatan) = ?', [$roleLower])
        ->whereRaw('LOWER(unit_work) LIKE ?', [$unitPattern])
        ->first();

    if (!$user) {
        Log::warning('[HPP] Approver awal tidak ditemukan', [
            'notif' => $hpp->notification_number,
            'filter' => ['jabatan' => $roleLower, 'unit_like' => $unitPattern],
        ]);
        return;
    }

    $linkSvc = app(\App\Services\HppApprovalLinkService::class);
    $tok     = $linkSvc->issue($hpp->notification_number, 'manager', $user->id, 60*24);
    $url     = $linkSvc->url($tok);

    // sanitize nomor
    $to = preg_replace('/[^0-9]/', '', (string)$user->whatsapp_number);

    if (empty($to) || strlen($to) < 8) {
        Log::warning('[HPP] Nomor WhatsApp approver invalid', [
            'user' => $user->id, 'whatsapp_number' => $user->whatsapp_number
        ]);
        return;
    }

    $message = "✍️ *Permintaan Tanda Tangan HPP*\n".
               "No: {$hpp->notification_number}\n".
               "Role: Manager\n".
               "Klik untuk menandatangani:\n{$url}\n\n".
               "_Link berlaku 24 jam & hanya untuk Anda_";

    // prepare WA payload (template or text)
    $payload = [
        'messaging_product' => 'whatsapp',
        'to' => $to,
        'type' => 'text',
        'text' => ['body' => $message],
    ];

    try {
        // dispatch job (non-blocking)
        SendWhatsAppMessage::dispatch($payload);

        Log::info('[HPP] WA job dispatched', [
            'notif' => $hpp->notification_number,
            'user' => $user->id,
            'to' => $to,
        ]);
    } catch (\Throwable $e) {
        Log::error('[HPP] Gagal dispatch WA job: ' . $e->getMessage(), [
            'notif' => $hpp->notification_number,
            'user' => $user->id,
        ]);
    }
}
    public function edit($notification_number)
    {
        try {
            $hpp = Hpp1::where('notification_number', $notification_number)->firstOrFail();
            // Model sudah casts → langsung array; tidak perlu json_decode.
            return view('admin.inputhpp.edit', compact('hpp'));
        } catch (\Throwable $e) {
            Log::error('Gagal membuka halaman edit HPP1: '.$e->getMessage());
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat memuat data untuk edit.');
        }
    }
public function update(Request $request, $notification_number)
{
    try {
        // VALIDASI (uraian_pekerjaan tidak diwajibkan)
        $data = $request->validate([
            'description'       => 'required|string',
            'requesting_unit'   => 'required|string',
            'controlling_unit'  => 'nullable|string',
            'outline_agreement' => 'nullable|string',

            'jenis_item'        => 'nullable|array',
            'jenis_item.*'      => 'array',
            'jenis_item.*.*'    => 'nullable|string|max:50',

            'nama_item'         => 'required|array|min:1',
            'nama_item.*'       => 'array|min:1',
            'nama_item.*.*'     => 'nullable|string|max:255',

            'jumlah_item'         => 'nullable|array',        // <<-- tambah ini
            'jumlah_item.*'       => 'array',
            'jumlah_item.*.*'     => 'nullable|string|max:255',

            'qty'               => 'required|array|min:1',
            'qty.*'             => 'array|min:1',
            'qty.*.*'           => 'nullable|numeric|min:0',

            'satuan'            => 'required|array|min:1',
            'satuan.*'          => 'array|min:1',
            'satuan.*.*'        => 'nullable|string|max:50',

            'harga_satuan'      => 'required|array|min:1',
            'harga_satuan.*'    => 'array|min:1',
            'harga_satuan.*.*'  => 'nullable|numeric|min:0',

            'harga_total'       => 'nullable|array',
            'harga_total.*'     => 'array',
            'harga_total.*.*'   => 'nullable|numeric|min:0',

            'keterangan'        => 'nullable|array',
            'keterangan.*'      => 'array',
            'keterangan.*.*'    => 'nullable|string|max:255',

            'status'            => 'nullable|string|in:draft,submitted,rejected,approved_manager,approved_sm,approved_gm,approved_dir',
        ]);

        $hpp = Hpp1::where('notification_number', $notification_number)->firstOrFail();

        // NORMALISASI + HITUNG ULANG (sama seperti store)
        $grand     = 0.0;
        $itemCols  = ['jenis_item','jumlah_item','nama_item','qty','satuan','harga_satuan','harga_total','keterangan'];

        if (!isset($data['nama_item']) || !is_array($data['nama_item']) || empty($data['nama_item'])) {
            $data['nama_item'] = [['']];
        }

        $groupIndices = array_keys($data['nama_item']);

        foreach ($groupIndices as $g) {
            $n = count($data['nama_item'][$g] ?? []);

            foreach ($itemCols as $col) {
                $row = $data[$col][$g] ?? [];
                $row = is_array($row) ? array_values($row) : [];
                $data[$col][$g] = array_values(array_pad(array_slice($row, 0, $n), $n, null));
            }

            for ($i = 0; $i < $n; $i++) {
                $qty   = (float)($data['qty'][$g][$i] ?? 0);
                $harga = (float)($data['harga_satuan'][$g][$i] ?? 0);
                $data['harga_total'][$g][$i] = round($qty * $harga, 2);
                $grand += $data['harga_total'][$g][$i];
            }
        }
        $data['total_amount'] = $grand;

        // FILTER & UPDATE (hanya field fillable)
        $saveData = Arr::only($data, (new Hpp1())->getFillable());
        $saveData['total_amount'] = number_format((float)($saveData['total_amount'] ?? 0), 2, '.', '');
        $hpp->update($saveData);

        return redirect()
            ->route('admin.inputhpp.index')
            ->with('success', 'Data berhasil diperbarui.');

    } catch (\Throwable $e) {
        \Log::error('Gagal memperbarui HPP1: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat memperbarui data HPP.');
    }
}

/**
     * Terima upload file HPP Direktur (manual upload oleh admin/direksi)
     *
     * POST /admin/inputhpp/director-upload/{notification_number}
     */
public function storeDirectorUpload(Request $request, $notification_number)
{
    $request->validate([
        'hpp_file' => 'required|file|mimes:pdf|max:10240', // 10MB
    ]);

    // DEBUG: catat request masuk agar jelas controller terpanggil
    Log::info('[HPP DEBUG] storeDirectorUpload dipanggil', [
        'notification_number' => $notification_number,
        'user_id' => auth()->id(),
        'request_inputs' => $request->except(['hpp_file']),
    ]);

    try {
        $hpp = Hpp1::where('notification_number', $notification_number)->firstOrFail();

        /**
         * Accept upload jika:
         * - status === 'approved_gm'
         * OR
         * - ada tanda tangan GM (general_manager_signature) — toleran jika status belum di-update
         */
        $gmSigned = !empty($hpp->general_manager_signature);
        $gmStatusOk = $hpp->status === 'approved_gm';

        if (! $gmStatusOk && ! $gmSigned) {
            Log::warning('[HPP] Upload ditolak: GM belum tanda tangan / status belum approved_gm', [
                'notif' => $hpp->notification_number,
                'status' => $hpp->status,
                'general_manager_signature' => $hpp->general_manager_signature ? 'exists' : null,
                'user_attempt' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.inputhpp.index')
                ->with('error', 'Upload hanya diizinkan setelah General Manager menyetujui HPP.');
        }

        // simpan file ke disk private
        $file = $request->file('hpp_file');
        $path = $file->store("hpp/{$notification_number}", 'private');

        // hapus file lama jika ada (best-effort)
        try {
            $svc = app(SignatureService::class);
            if (!empty($hpp->director_uploaded_file)) {
                $svc->deleteIfExists($hpp->director_uploaded_file);
            }
        } catch (\Throwable $e) {
            Log::warning("[HPP] Gagal menghapus file direktur lama: " . $e->getMessage(), [
                'notif' => $notification_number,
            ]);
        }

        // update model (tidak mengubah status approval otomatis)
        $hpp->update([
            'director_uploaded_file' => $path,
            'director_uploaded_at'   => now(),
            'director_uploaded_by'   => Auth::id() ?? (Auth::user()->name ?? null),
        ]);

        Log::info("[HPP] File direktur diupload", ['notif' => $notification_number, 'path' => $path, 'by' => auth()->id()]);

        return redirect()
            ->route('admin.inputhpp.index')
            ->with('success', 'File HPP Direktur berhasil diupload.');

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('[HPP] storeDirectorUpload: HPP tidak ditemukan', ['notif' => $notification_number]);
        return redirect()
            ->route('admin.inputhpp.index')
            ->with('error', 'Dokumen HPP tidak ditemukan.');
    } catch (\Throwable $e) {
        Log::error('[HPP] Gagal menyimpan upload direktur: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return redirect()
            ->route('admin.inputhpp.index')
            ->with('error', 'Terjadi kesalahan saat meng-upload file.');
    }
}


    /**
     * Download file direktur (jika ada)
     *
     * GET /admin/inputhpp/director-download/{notification_number}
     */
    public function downloadDirector($notification_number)
    {
        try {
            $hpp = Hpp1::where('notification_number', $notification_number)->firstOrFail();

            if (empty($hpp->director_uploaded_file)) {
                abort(Response::HTTP_NOT_FOUND, 'File direktur tidak ditemukan.');
            }

            // download dari disk private
            return Storage::disk('private')->download($hpp->director_uploaded_file, "HPP-{$notification_number}-director.pdf");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(Response::HTTP_NOT_FOUND, 'Data HPP tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('[HPP] Gagal mendownload file direktur: ' . $e->getMessage());
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat mengunduh file.');
        }
    }


  public function downloadPDF($notification_number)
{
try {
$hpp = Hpp1::where('notification_number', $notification_number)->firstOrFail();
$data = $this->preparePdfData($hpp);


$pdf = PDF::loadView('admin.inputhpp.hpppdf1', $data)
->setPaper('a4', 'landscape');


return $pdf->stream("HPP-{$hpp->notification_number}.pdf");
} catch (\Throwable $e) {
Log::error('Gagal membuat PDF HPP1: '.$e->getMessage());
abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat membuat PDF.');
}
}
    public function destroy($notification_number)
    {
        try {
            $hpp = Hpp1::where('notification_number', $notification_number)->firstOrFail();
            $hpp->delete();

            return redirect()
                ->route('admin.inputhpp.index')
                ->with('success', 'Dokumen HPP berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(Response::HTTP_NOT_FOUND, 'Data HPP tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus HPP1: '.$e->getMessage());
            return redirect()
                ->route('admin.inputhpp.index')
                ->with('error', 'Gagal menghapus dokumen HPP: '.$e->getMessage());
        }
    }
}
