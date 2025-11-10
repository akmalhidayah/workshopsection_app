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
use Illuminate\Support\Facades\Http;
use App\Services\HppApprovalLinkService;


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
        // 1) VALIDASI HEADER + NESTED FIELDS
        $data = $request->validate([
            'notification_number' => [
                'required','string','max:255',
                Rule::exists('notifications','notification_number'),
                Rule::unique('hpp1','notification_number'),
            ],
            'source_form'         => 'nullable|string|max:100',
            'cost_centre'         => 'nullable|string|max:100',
            'description'         => 'required|string',
            'requesting_unit'     => 'nullable|string|max:100', // akan dioverride
            'controlling_unit'    => 'nullable|string|max:100',
            'outline_agreement'   => 'nullable|string|max:100',

            // kelompok [g]
            'uraian_pekerjaan'    => 'required|array|min:1',
            'uraian_pekerjaan.*'  => 'nullable|string|max:255',

            // item [g][i]
            'jenis_item'          => 'nullable|array',
            'jenis_item.*'        => 'array',
            'jenis_item.*.*'      => 'nullable|string|max:50',

            'nama_item'           => 'required|array|min:1',
            'nama_item.*'         => 'array|min:1',
            'nama_item.*.*'       => 'nullable|string|max:255',

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

        // default
        $data['source_form'] = $data['source_form'] ?? 'createhpp1';

        // 2) LOCK unit kerja dari notifikasi (abaikan input user)
        $notif = Notification::where('notification_number', $data['notification_number'])->firstOrFail();
        $data['requesting_unit'] = $notif->unit_work;

        // 3) NORMALISASI PANJANG ARRAY + HITUNG GRAND TOTAL
        $grand     = 0.0;
        $itemCols  = ['jenis_item','nama_item','qty','satuan','harga_satuan','harga_total','keterangan'];

        foreach (array_keys($data['uraian_pekerjaan']) as $g) {
            // acuan panjang = nama_item[g]
            $n = count($data['nama_item'][$g] ?? []);

            foreach ($itemCols as $col) {
                $row = array_values($data[$col][$g] ?? []);
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

        // 3.1) Tentukan status berdasarkan tombol
        $action = $request->input('action'); // 'draft' | 'submit'
        $data['status'] = $action === 'submit' ? 'submitted' : 'draft';

        // Jika diajukan, pastikan total > 0
        if ($data['status'] === 'submitted' && $data['total_amount'] <= 0) {
            return back()
                ->withInput()
                ->with('error', 'Total keseluruhan harus lebih dari 0 untuk diajukan.');
        }

        // 4) SIMPAN
        $hpp = Hpp1::create($data);

        // 5) Jika submitted, issue token approver pertama
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
// di dalam class Hpp1Controller
private function issueFirstToken(Hpp1 $hpp): void
{
    // tahap pertama: Manager Unit of Workshop (toleran variasi penamaan)
    $roleLower   = 'manager';
    $unitPattern = 'unit of workshop%'; // contoh: "Unit of Workshop", "Unit of Workshop & Design", dst.

    // cari user approver pertama (lebih toleran & jelas saat gagal)
    $user = User::query()
        ->whereRaw('LOWER(jabatan) = ?', [$roleLower])
        ->whereRaw('LOWER(unit_work) LIKE ?', [$unitPattern])
        ->first();

    if (!$user) {
        \Log::warning('[HPP] Approver awal tidak ditemukan', [
            'notif' => $hpp->notification_number,
            'filter' => ['jabatan' => $roleLower, 'unit_like' => $unitPattern],
        ]);
        return;
    }

    // terbitkan token + buat URL
    $linkSvc = app(HppApprovalLinkService::class);
    $tok     = $linkSvc->issue($hpp->notification_number, 'manager', $user->id, 60*24);
    $url     = $linkSvc->url($tok);

    // kirim WA (opsional; error tidak memblokir proses)
    try {
        Http::withHeaders(['Authorization' => 'KBTe2RszCgc6aWhYapcv'])
            ->post('https://api.fonnte.com/send', [
                'target'  => $user->whatsapp_number,
                'message' =>
                    "✍️ *Permintaan Tanda Tangan HPP*\n".
                    "No: {$hpp->notification_number}\n".
                    "Role: Manager\n".
                    "Klik untuk menandatangani:\n{$url}\n\n".
                    "_Link berlaku 24 jam & hanya untuk Anda_",
            ]);
    } catch (\Throwable $e) {
        \Log::error('[HPP] Gagal kirim WA first token', [
            'notif' => $hpp->notification_number,
            'user'  => $user->id,
            'error' => $e->getMessage(),
        ]);
    }

    \Log::info('[HPP] First token issued', [
        'notif'     => $hpp->notification_number,
        'sign_type' => 'manager',
        'user'      => $user->id,
        'url'       => $url,
    ]);
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
        // 1) VALIDASI (jenis opsional; acuan panjang = nama_item)
        $data = $request->validate([
            'description'       => 'required|string',
            'requesting_unit'   => 'required|string',
            'controlling_unit'  => 'nullable|string',
            'outline_agreement' => 'nullable|string',

            'uraian_pekerjaan'  => 'required|array|min:1',
            'uraian_pekerjaan.*'=> 'nullable|string|max:255',

            'jenis_item'        => 'nullable|array',
            'jenis_item.*'      => 'array',
            'jenis_item.*.*'    => 'nullable|string|max:50',

            'nama_item'         => 'required|array|min:1',
            'nama_item.*'       => 'array|min:1',
            'nama_item.*.*'     => 'nullable|string|max:255',

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

        // 2) NORMALISASI + HITUNG ULANG
        $grand     = 0.0;
        $itemCols  = ['jenis_item','nama_item','qty','satuan','harga_satuan','harga_total','keterangan'];

        foreach (array_keys($data['uraian_pekerjaan']) as $g) {
            $n = count($data['nama_item'][$g] ?? []);

            foreach ($itemCols as $col) {
                $row = array_values($data[$col][$g] ?? []);
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

        // 3) UPDATE
        $hpp->update($data);

        return redirect()
            ->route('admin.inputhpp.index')
            ->with('success', 'Data berhasil diperbarui.');

    } catch (\Throwable $e) {
        \Log::error('Gagal memperbarui HPP1: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat memperbarui data HPP.');
    }
}


    public function downloadPDF($notification_number)
    {
        try {
            $hpp  = Hpp1::where('notification_number', $notification_number)->firstOrFail();
            $data = $this->preparePdfData($hpp);

            $pdf = PDF::loadView('admin.inputhpp.hpppdf1', $data)
                ->setPaper('a4', 'landscape', 'none');

            return $pdf->stream('HPP1.pdf');
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
