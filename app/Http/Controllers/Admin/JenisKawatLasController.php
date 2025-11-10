<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisKawatLas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class JenisKawatLasController extends Controller
{
    public function index()
    {
        try {
            $data = JenisKawatLas::paginate(10);
            return view('admin.jenis_kawat_las.index', compact('data'));
        } catch (\Exception $e) {
            Log::error("Error load jenis kawat las index: " . $e->getMessage());
            return abort(500, 'Terjadi kesalahan saat memuat data.');
        }
    }

    public function create()
    {
        return view('admin.jenis_kawat_las.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode'         => 'required|string|max:50|unique:jenis_kawat_las,kode',
            'deskripsi'    => 'nullable|string',
            'stok'         => 'required|integer|min:0',
            'harga'        => 'required|numeric|min:0',
            'cost_element' => 'nullable|string|max:50',
            'gambar'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $jenis = new JenisKawatLas();
            $jenis->kode         = $request->kode;
            $jenis->deskripsi    = $request->deskripsi;
            $jenis->stok         = $request->stok;
            $jenis->harga        = $request->harga;
            $jenis->cost_element = $request->cost_element;

            if ($request->hasFile('gambar')) {
                $jenis->gambar = $request->file('gambar')->store('jenis_kawat', 'public');
            }

            $jenis->save();

            return redirect()
                ->route('admin.jenis-kawat-las.index')
                ->with('success', 'Jenis kawat las berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error("Error store jenis kawat las: " . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data.'])
                ->setStatusCode(500);
        }
    }

    public function update(Request $request, JenisKawatLas $jenis_kawat_las)
    {
        $request->validate([
            'kode'         => 'required|string|max:50|unique:jenis_kawat_las,kode,' . $jenis_kawat_las->id,
            'deskripsi'    => 'nullable|string',
            'stok'         => 'required|integer|min:0',
            'harga'        => 'required|numeric|min:0',
            'cost_element' => 'nullable|string|max:50',
            'gambar'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $data = $request->only(['kode', 'deskripsi', 'stok', 'harga', 'cost_element']);

            if ($request->hasFile('gambar')) {
                if ($jenis_kawat_las->gambar && Storage::disk('public')->exists($jenis_kawat_las->gambar)) {
                    Storage::disk('public')->delete($jenis_kawat_las->gambar);
                }
                $data['gambar'] = $request->file('gambar')->store('jenis_kawat', 'public');
            }

            $jenis_kawat_las->update($data);

            return redirect()
                ->route('admin.jenis-kawat-las.index')
                ->with('success', 'Jenis kawat las berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error("Error update jenis kawat las: " . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data.'])
                ->setStatusCode(500);
        }
    }

    public function destroy(JenisKawatLas $jenis_kawat_las)
    {
        try {
            if ($jenis_kawat_las->gambar && Storage::disk('public')->exists($jenis_kawat_las->gambar)) {
                Storage::disk('public')->delete($jenis_kawat_las->gambar);
            }

            $jenis_kawat_las->delete();

            return redirect()
                ->route('admin.jenis-kawat-las.index')
                ->with('success', 'Jenis kawat las berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error("Error destroy jenis kawat las: " . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menghapus data.'])
                ->setStatusCode(500);
        }
    }
    public function editCostElement()
{
    // Ambil salah satu nilai cost_element (atau default)
    $value = JenisKawatLas::value('cost_element') ?? '65810001';
    return view('admin.jenis_kawat_las.cost-element', compact('value'));
}

public function updateCostElement(Request $request)
{
    $request->validate([
        'value' => 'required|string|max:50',
    ]);

    try {
        JenisKawatLas::query()->update(['cost_element' => $request->value]);

        return redirect()
            ->route('admin.cost-element.edit')
            ->with('success', 'Cost element berhasil diperbarui untuk semua jenis kawat.');
    } catch (\Exception $e) {
        return redirect()->back()
            ->withErrors(['error' => 'Gagal memperbarui cost element.'])
            ->setStatusCode(500);
    }
}

}
