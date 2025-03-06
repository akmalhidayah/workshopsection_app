<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ItemsController extends Controller
{
    // Menampilkan daftar item tanpa model database
    public function index()
    {
        // Data dummy untuk sementara (tanpa model database)
        $items = [
            (object) [
                'nomor_order' => '001',
                'deskripsi_pekerjaan' => 'Pekerjaan Fabrikasi',
                'total_hpp' => 1729483,
                'total_item' => 1294583,
                'total_margin' => 96706,
                'approved' => 'Manager',
            ],
            (object) [
                'nomor_order' => '002',
                'deskripsi_pekerjaan' => 'Pekerjaan Konstruksi',
                'total_hpp' => 874530,
                'total_item' => 1587349,
                'total_margin' => 85014,
                'approved' => 'Senior Manager',
            ],
        ];

        return view('pkm.items.index', compact('items'));
    }

    // Menampilkan form tambah item
    public function create()
    {
        return view('pkm.items.create');
    }
    public function show($nomor_order)
{
    $items = collect([
        (object) [
            'nomor_order' => '001',
            'deskripsi_pekerjaan' => 'Pekerjaan Fabrikasi',
            'total_hpp' => 1729483,
            'total_harga' => 5000000,
            'total_margin' => 500000,
            'materials' => [
                (object) ['nama_material' => 'Besi Hollow', 'harga' => 2000000],
                (object) ['nama_material' => 'Cat Anti Karat', 'harga' => 500000],
            ],
        ],
        (object) [
            'nomor_order' => '002',
            'deskripsi_pekerjaan' => 'Pekerjaan Konstruksi',
            'total_hpp' => 874530,
            'total_harga' => 7000000,
            'total_margin' => 750000,
            'materials' => [
                (object) ['nama_material' => 'Baja WF', 'harga' => 3500000],
                (object) ['nama_material' => 'Las Listrik', 'harga' => 800000],
            ],
        ],
    ]);

    // Cari item berdasarkan nomor_order
    $item = $items->where('nomor_order', $nomor_order)->first();

    if (!$item) {
        abort(404);
    }

    return view('pkm.items.show', compact('item'));
}


public function edit($nomor_order)
{
    $item = collect([
        (object) [
            'nomor_order' => '001',
            'deskripsi_pekerjaan' => 'Pekerjaan Fabrikasi',
            'total_hpp' => 1729483,
            'total_item' => 1294583,
            'total_margin' => 96706,
            'approved' => 'Manager',
        ],
        (object) [
            'nomor_order' => '002',
            'deskripsi_pekerjaan' => 'Pekerjaan Konstruksi',
            'total_hpp' => 874530,
            'total_item' => 1587349,
            'total_margin' => 85014,
            'approved' => 'Senior Manager',
        ],
    ])->where('nomor_order', $nomor_order)->first();

    if (!$item) {
        abort(404);
    }

    return view('pkm.items.edit', compact('item'));
}
public function destroy($nomor_order)
{
    // Simulasi penghapusan dari data dummy
    $items = collect([
        (object) [
            'nomor_order' => '001',
            'deskripsi_pekerjaan' => 'Pekerjaan Fabrikasi',
            'total_hpp' => 1729483,
            'total_item' => 1294583,
            'total_margin' => 96706,
            'approved' => 'Manager',
        ],
        (object) [
            'nomor_order' => '002',
            'deskripsi_pekerjaan' => 'Pekerjaan Konstruksi',
            'total_hpp' => 874530,
            'total_item' => 1587349,
            'total_margin' => 85014,
            'approved' => 'Senior Manager',
        ],
    ]);

    // Cek apakah item ada
    $item = $items->where('nomor_order', $nomor_order)->first();

    if (!$item) {
        return redirect()->route('pkm.items.index')->with('error', 'Item tidak ditemukan.');
    }

    // Simulasi pesan sukses tanpa benar-benar menghapus dari database
    return redirect()->route('pkm.items.index')->with('success', 'Item berhasil dihapus.');
}

}
