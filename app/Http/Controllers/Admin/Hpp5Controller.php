<?php

namespace App\Http\Controllers\Admin;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Hpp1;
use App\Models\Notification;
use App\Services\HppApprovalLinkService;
use App\Services\HppApproverResolver;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;



class Hpp5Controller extends BaseHppController
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

            $source_form = 'createhpp5';
            $currentOA   = $this->getCurrentOA();

            return view('admin.inputhpp.createhpp5', compact('notifications', 'source_form', 'currentOA'));
        } catch (\Throwable $e) {
            Log::error('Gagal membuka form create HPP5: '.$e->getMessage());
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
        $data['source_form'] = $data['source_form'] ?? 'createhpp5';
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
        \Log::error('Gagal menyimpan HPP5 (store): '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat menyimpan data HPP.');
    }
}

// di dalam class Hpp1Controller
private function issueFirstToken(Hpp1 $hpp): void
{
    $user = app(HppApproverResolver::class)->resolveApprover($hpp, 'manager');

    if (!$user) {
        Log::warning('[HPP] Approver awal tidak ditemukan (struktur org)', [
            'notif' => $hpp->notification_number,
        ]);
        return;
    }

    $linkSvc = app(HppApprovalLinkService::class);
    $tok     = $linkSvc->issue($hpp->notification_number, 'manager', $user->id, 60 * 24);
    $url     = $linkSvc->url($tok);

    Log::info('[HPP] First token issued', [
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
            Log::error('Gagal membuka halaman edit HPP5: '.$e->getMessage());
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
        \Log::error('Gagal memperbarui HPP5: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat memperbarui data HPP.');
    }
}



    public function downloadPDF($notification_number)
    {
        try {
            $hpp  = Hpp1::where('notification_number', $notification_number)->firstOrFail();
            $data = $this->preparePdfData($hpp);

            $pdf = PDF::loadView('admin.inputhpp.hpppdf5', $data)
                ->setPaper('a4', 'landscape', 'none');

            return $pdf->stream("HPP-{$hpp->notification_number}.pdf");
        } catch (\Throwable $e) {
            Log::error('Gagal membuat PDF HPP5: '.$e->getMessage());
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
            Log::error('Gagal menghapus HPP5: '.$e->getMessage());
            return redirect()
                ->route('admin.inputhpp.index')
                ->with('error', 'Gagal menghapus dokumen HPP: '.$e->getMessage());
        }
    }
}
