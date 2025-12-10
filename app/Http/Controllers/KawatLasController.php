<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KawatLas;
use App\Models\KawatLasDetail;
use App\Models\JenisKawatLas;
use App\Models\UnitWork;
use Illuminate\Support\Facades\DB;

class KawatLasController extends Controller
{
    /**
     * Menampilkan daftar kawat las dengan filter, pencarian, dan pagination
     */
    public function index(Request $request)
    {
        $query = KawatLas::with('details', 'user');

        // 🔒 Filter user (non-admin hanya lihat miliknya sendiri)
        if (auth()->user()->usertype !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        // 🔍 Search by jenis kawat
        if ($request->filled('search')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('jenis_kawat', 'like', '%' . $request->search . '%');
            });
        }

        // 🔍 Filter unit kerja
        if ($request->filled('unit')) {
            $query->where('unit_work', $request->unit);
        }

        // Sorting
        $sortOrder = $request->sortOrder === 'oldest' ? 'asc' : 'desc';
        $query->orderBy('created_at', $sortOrder);

        // Pagination
        $entries = $request->entries ?? 10;
        $kawatlas = $query->paginate($entries)->withQueryString();

        // Data master
        $units = UnitWork::orderBy('name')->get();
        $jenisList = JenisKawatLas::orderBy('kode')->get();

        return view('kawatlas.index', compact('kawatlas', 'units', 'jenisList'));
    }


    /**
     * STORE: Simpan order baru + detail
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_number'               => 'required|string|max:50|unique:kawat_las,order_number',
            'tanggal'                    => 'required|date',
            'unit_work'                  => 'required|string|max:100',
            'seksi'                      => 'nullable|string|max:100', // tambahan
            'detail_kawat.*.jenis_kawat' => 'required|string|max:50',
            'detail_kawat.*.jumlah'      => 'required|integer|min:1',
        ]);

        // 🔍 Validasi seksi harus sesuai unit_work
        if ($request->filled('seksi')) {
            $unit = UnitWork::where('name', $request->unit_work)->first();
            $validSeksiList = $unit ? ($unit->seksi_list ?? []) : [];

            if (!in_array($request->seksi, $validSeksiList, true)) {
                return back()->withErrors([
                    'seksi' => 'Seksi tidak sesuai dengan unit kerja yang dipilih.'
                ])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $kawatLas = KawatLas::create([
                'order_number' => $request->order_number,
                'tanggal'      => $request->tanggal,
                'unit_work'    => $request->unit_work,
                'seksi'        => $request->seksi, // simpan seksi
                'user_id'      => auth()->id(),
            ]);

            $kawatLas->details()->createMany($request->detail_kawat);

            DB::commit();
            return redirect()->route('kawatlas.index')
                             ->with('success', 'Permintaan kawat las berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data. ' . $e->getMessage()])
                             ->withInput();
        }
    }


    /**
     * Ambil data untuk modal Edit (AJAX)
     */
    public function edit($id)
    {
        $kawatlas = KawatLas::with('details')->findOrFail($id);

        if (auth()->user()->usertype !== 'admin' && $kawatlas->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data ini.');
        }

        return response()->json($kawatlas);
    }


    /**
     * UPDATE order + detail
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'order_number'               => 'required|string|max:50|unique:kawat_las,order_number,' . $id,
            'tanggal'                    => 'required|date',
            'unit_work'                  => 'required|string|max:100',
            'seksi'                      => 'nullable|string|max:100', // tambahan
            'detail_kawat.*.jenis_kawat' => 'required|string|max:50',
            'detail_kawat.*.jumlah'      => 'required|integer|min:1',
        ]);

        // 🔍 Validasi seksi harus sesuai unit_work
        if ($request->filled('seksi')) {
            $unit = UnitWork::where('name', $request->unit_work)->first();
            $validSeksiList = $unit ? ($unit->seksi_list ?? []) : [];

            if (!in_array($request->seksi, $validSeksiList, true)) {
                return back()->withErrors([
                    'seksi' => 'Seksi tidak sesuai dengan unit kerja yang dipilih.'
                ])->withInput();
            }
        }

        $kawatlas = KawatLas::with('details')->findOrFail($id);

        if (auth()->user()->usertype !== 'admin' && $kawatlas->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data ini.');
        }

        DB::beginTransaction();
        try {
            $kawatlas->update([
                'order_number' => $request->order_number,
                'tanggal'      => $request->tanggal,
                'unit_work'    => $request->unit_work,
                'seksi'        => $request->seksi, // update seksi
            ]);

            $kawatlas->details()->delete();
            $kawatlas->details()->createMany($request->detail_kawat);

            DB::commit();
            return redirect()->route('kawatlas.index')
                             ->with('success', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data. ' . $e->getMessage()]);
        }
    }


    /**
     * Hapus order beserta detail
     */
    public function destroy($id)
    {
        $kawatlas = KawatLas::findOrFail($id);

        if (auth()->user()->usertype !== 'admin' && $kawatlas->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus data ini.');
        }

        $kawatlas->delete();

        return redirect()->route('kawatlas.index')
                         ->with('success', 'Data berhasil dihapus.');
    }


    /**
     * Update jumlah item pada detail (tidak mempengaruhi stok)
     */
    public function updateJumlah(Request $request, $id)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1',
        ]);

        $detail = KawatLasDetail::findOrFail($id);
        $detail->jumlah = $request->jumlah;
        $detail->save();

        session()->flash('updated_by', auth()->user()->name ?? 'Admin');

        return redirect()
            ->back()
            ->with('success', 'Jumlah permintaan berhasil diperbarui (stok tidak berubah).');
    }


    /**
     * Update status order (Waiting → Good Issue) + Kurangi stok
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'  => 'required|string|in:Waiting List,Good Issue',
            'catatan' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $order = KawatLas::with('details')->findOrFail($id);

            // Kurangi stok jika baru berubah menjadi Good Issue
            if ($request->status === 'Good Issue' && $order->status !== 'Good Issue') {
                foreach ($order->details as $detail) {
                    $jenis = JenisKawatLas::where('kode', $detail->jenis_kawat)->first();
                    if ($jenis) {
                        $jenis->stok = max(0, $jenis->stok - $detail->jumlah);
                        $jenis->save();
                    }
                }
            }

            $order->update([
                'status'  => $request->status,
                'catatan' => $request->catatan,
            ]);

            DB::commit();

            return redirect()
                ->route('notifikasi.index', ['tab' => 'kawatlas'])
                ->with('success', 'Status dan catatan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withErrors(['error' => 'Gagal memperbarui status: ' . $e->getMessage()]);
        }
    }
}
