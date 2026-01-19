<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * key   = nama departemen (sesuai di tabel departments)
         * value = email General Manager (boleh null kalau belum ada GM)
         */
        $data = [
            'Infrastructure'                              => 'andi.rachman@sig.id',
            'Clinker & Cement Production'                 => 'ari.mahesthi@sig.id',
            'Maintenance'                                 => 'syafardino@sig.id',
            'Mining & Power Plant'                        => 'hariyono.gunawan@sig.id',
            'Production Planning & Control'               => 'yosi.reapradana@sig.id',
            'Cement Production'                           => null, // belum ada GM
            'Project Management & Maintenance Support'    => 'syafardino@sig.id',
            'PT. Prima Karya Manunggal'                   => null, // belum ada GM
        ];

        foreach ($data as $deptName => $gmEmail) {
            // buat / ambil departemen
            $department = Department::firstOrCreate(
                ['name' => $deptName],
                ['general_manager_id' => null]
            );

            // kalau ada email GM, coba cari user-nya
            if ($gmEmail) {
                $gm = User::where('email', $gmEmail)->first();

                if ($gm && $department->general_manager_id !== $gm->id) {
                    $department->general_manager_id = $gm->id;
                    $department->save();
                }
            }
        }
    }
}
