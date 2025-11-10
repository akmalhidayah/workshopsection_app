<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisKawatLas;

class JenisKawatLasSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        $data = [
            ['kode' => 'SI00013378', 'deskripsi' => 'E AWS 7018 DIA 3,2 MM'],
            ['kode' => 'SI00012315', 'deskripsi' => 'E AWS 7018 DIA 4 MM'],
            ['kode' => 'SI00013380', 'deskripsi' => 'E AWS 310 DIAMETER 3,2 MM'],
            ['kode' => 'SI00013366', 'deskripsi' => 'E AWS 310 DIAMETER 4 MM'],
            ['kode' => 'SI00020354', 'deskripsi' => 'E HARDFACING DIAMETER 3,2 MM'],
            ['kode' => 'SI00013379', 'deskripsi' => 'E AWS 6013 DIAMETER 2,5 MM'],
            ['kode' => 'SI00013384', 'deskripsi' => 'E AWS 6013 DIAMETER 3,2 MM'],
            ['kode' => 'SI00013383', 'deskripsi' => 'E GOUGING (CANFER) DIAMETER 3,2 MM'],
            ['kode' => 'SI00013386', 'deskripsi' => 'E GOUGING (CANFER) DIAMETER 4 MM'],
            ['kode' => 'SI00013382', 'deskripsi' => 'E AWS 312 DIAMETER 3,2 MM'],
            ['kode' => 'SI00013388', 'deskripsi' => 'E AWS 312 DIAMETER 4 MM'],
            ['kode' => 'SI00012330', 'deskripsi' => 'E AWS 312 DIAMETER 2,5 MM'],
            ['kode' => 'SI00020317', 'deskripsi' => 'E AWS 308 DIAMETER 4 MM'],
            ['kode' => 'SI00013376', 'deskripsi' => 'E CAST IRON DIAMETER 4 MM'],
            ['kode' => 'SI00013368', 'deskripsi' => 'E CAST IRON DIAMETER 3,2 MM'],
            ['kode' => 'SI00013378', 'deskripsi' => 'E HARDFACING DIA 4 MM'],
            ['kode' => 'SI00013381', 'deskripsi' => 'CARBON GOUGING DIA 6 MM'],
            ['kode' => 'SI00013387', 'deskripsi' => 'CARBON GOUGING 8 MM'],
            ['kode' => 'SI00013379', 'deskripsi' => 'E AWS 309 DIA 3,2 MM'],
        ];

        foreach ($data as $item) {
            JenisKawatLas::firstOrCreate(
                ['kode' => $item['kode']],
                [
                    'deskripsi' => $item['deskripsi'],
                    'stok' => rand(10, 50),      // stok acak
                    'harga' => rand(20000, 80000), // harga acak
                    'cost_element' => null,
                    'gambar' => null,
                ]
            );
        }
    }
}
