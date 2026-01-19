<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\UnitWork;

class UnitWorkSeeder extends Seeder
{
    public function run(): void
    {
        $data = [

            // =========================
            // Dept. of Clinker & Cement Production
            // =========================
            'Clinker & Cement Production' => [
                [
                    'name'              => 'Unit of Clinker Production 2',
                    'senior_manager_id' => 9, // PARLINDUNGAN PARDOSI
                    'seksi'             => [
                        'Section of Line 5 RKC Operation',
                        'Section of Line 4 RKC Operation',
                    ],
                ],
                [
                    'name'              => 'Unit of Clinker Production 1',
                    'senior_manager_id' => 10, // ADI FATKHURROHMAN
                    'seksi'             => [
                        'Section of Line 2/3 RKC Operation',
                        'Staff of Performance Monitoring & Eval.',
                    ],
                ],
                [
                    'name'              => 'Unit of Cement Production',
                    'senior_manager_id' => 11, // DWI KURNIAWAN
                    'seksi'             => [
                        'Section of Line 2/3 FM Operation',
                        'Section of Line 4 Finish Mill Operation',
                        'Section of Line 5 Finish Mill Operation',
                        'Section of Packer Operation',
                        'Section of Bulk Cement Operation',
                    ],
                ],
                [
                    'name'              => 'Unit of Quality Assurance',
                    'senior_manager_id' => 12, // YULIANTO PRAYOGA
                    'seksi'             => [
                        'Section of Material Quality Assurance',
                        'Section of Product Quality Assurance',
                        'Section of Cement App & Tech Services',
                    ],
                ],
            ],

            // =========================
            // Dept. of Maintenance
            // =========================
            'Maintenance' => [
                [
                    'name'              => 'Unit of Reliability Maintenance',
                    'senior_manager_id' => 13, // MARYONO
                    'seksi'             => [
                        'Staff of Overhaul Management',
                        'Staff of Maintenance Inspection',
                        'Staff of Troubleshooting',
                        'Staff of PGO',
                        'Staff of Planning & Scheduling',
                    ],
                ],
                [
                    'name'              => 'Unit of Machine Maintenance 2',
                    'senior_manager_id' => 14, // IRSAN
                    'seksi'             => [
                        'Section of Line 4/5 RM Machine Maint',
                        'Section of Line 4/5 Kiln & CM Mach Maint',
                        'Section of Line 4/5 FM Machine Maint',
                    ],
                ],
                [
                    'name'              => 'Unit of Elins Maintenance 1',
                    'senior_manager_id' => 15, // ANDI HILMAN
                    'seksi'             => [
                        'Section of Packer Elins Maint',
                        'Section of Crusher Elins Maintenance',
                        'Section of Line 2/3 FM Elins Main',
                        'Section of Line 2/3 RKC Elins Maint.',
                    ],
                ],
                [
                    'name'              => 'Unit of Elins Maintenance 2',
                    'senior_manager_id' => 16, // ARDIANSYAH
                    'seksi'             => [
                        'Section of Line 4/5 FM Elins Maint',
                        'Section of EP/DC Maintenance',
                        'Section of Line 4/5 RKC Elctr. Maint.',
                        'Section of Line 4/5 RKC Instr. Maint.',
                    ],
                ],
                [
                    'name'              => 'Unit of Machine Maintenance 1',
                    'senior_manager_id' => 17, // IHRAR NUZUL AZIS
                    'seksi'             => [
                        'Section of Crusher Machine & Conp Maint.',
                        'Section of Line 2/3 FM Machine Maint.',
                        'Section of Packer Machine Maintenance',
                        'Section of Line 2/3 RKC Machine Maint.',
                    ],
                ],
            ],

            // =========================
            // Dept. of Mining & Power Plant
            // =========================
            'Mining & Power Plant' => [
                [
                    'name'              => 'Unit of Mining',
                    'senior_manager_id' => null, // HARIYONO GUNAWAN (id belum di-data di list 9-35)
                    'seksi'             => [
                        'Section of Mine Safety Reclamation',
                        'Section of Limestone Mining',
                        'Section of Clay Mining',
                        'Section of Mine Planning & Monitoring',
                    ],
                ],
                [
                    'name'              => 'Unit of Raw Material Management',
                    'senior_manager_id' => 18, // MUHAMMAD RUSDIANTO
                    'seksi'             => [
                        // isi seksi kalau sudah ada
                    ],
                ],
                [
                    'name'              => 'Unit of Power Plant Operation',
                    'senior_manager_id' => null, // belum ada nama di list
                    'seksi'             => [
                        'Section of Power Plant Operation I',
                        'Section of Power Plant Operation',
                        'Section of Water & Coal Quality Control',
                        'Section of CUS',
                    ],
                ],
                [
                    'name'              => 'Bureau of Power Plant II',
                    'senior_manager_id' => null, // belum ada
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Bureau of Power Distribution And Network',
                    'senior_manager_id' => null, // belum ada
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Unit of Power Plant Machine Maintenance',
                    'senior_manager_id' => 19, // IMRAN
                    'seksi'             => [
                        // isi seksi kalau sudah ada
                    ],
                ],
                [
                    'name'              => 'Unit of Power Distribution',
                    'senior_manager_id' => 20, // ADNAN
                    'seksi'             => [
                        'Section of Electricity Load Control',
                        'Section of Electrical Network Maint',
                        'Section of Net & Elec Plant Maint',
                    ],
                ],
                [
                    'name'              => 'Staff of Power Plant Operation Planning & Control',
                    'senior_manager_id' => null, // belum ada nama di list
                    'seksi'             => [
                        'Staff of Pwr Plant Opr Planning & Ctrlg',
                    ],
                ],
                [
                    'name'              => 'Unit of Power Plant Elins Maintenance',
                    'senior_manager_id' => 21, // ABD. WAHID
                    'seksi'             => [
                        'Section of Power Plant Elctrical Maint.',
                        'Section of Power Plant Instrument Maint.',
                    ],
                ],
            ],

            // =========================
            // Dept. of Production Planning & Control
            // =========================
            'Production Planning & Control' => [
                [
                    'name'              => 'Unit of Production Support',
                    'senior_manager_id' => 22, // HARDIMAN
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Unit of Quality Control',
                    'senior_manager_id' => 23, // SURYADI PASAMBANGI
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Unit of Production Plant Evaluation & Environmental',
                    'senior_manager_id' => 24, // JASMIATI
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Unit of OHS',
                    'senior_manager_id' => 25, // M. ALIANTO
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Unit of AFR & Energy',
                    'senior_manager_id' => 26, // STEVANUS
                    'seksi'             => [],
                ],
            ],


            // =========================
            // Dept. of Project Management & Maintenance Support
            // =========================
            'Project Management & Maintenance Support' => [
                [
                    'name'              => 'Unit of Engineering',
                    'senior_manager_id' => 27, // SAHAT
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Unit of Project Management',
                    'senior_manager_id' => 28, // YATMAN
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Staff of TPM',
                    'senior_manager_id' => 29, // GATOT
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Unit of CAPEX Management',
                    'senior_manager_id' => 30, // MUH. ASIS
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Unit of Workshop & Design',
                    'senior_manager_id' => null, // MUHAMMAD MURSHAM (id belum ada di list 9-35)
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Unit of Maintenance Planning & Evaluation',
                    'senior_manager_id' => 31, // IFNUL
                    'seksi'             => [],
                ],
            ],

            // =========================
            // Dept. of Infrastructure
            // =========================
            'Infrastructure' => [
                [
                    'name'              => 'Unit of Packing Plant 2',
                    'senior_manager_id' => 32, // AMBO MASSE
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Unit of Plant & Port Product Discharge Opr',
                    'senior_manager_id' => 33, // SIMON
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Unit of SCM Infra Port Managemnet',
                    'senior_manager_id' => 34, // Capt. GUNTUR
                    'seksi'             => [],
                ],
                [
                    'name'              => 'Unit of of Interplant Logistic',
                    'senior_manager_id' => 35, // HAKMAL
                    'seksi'             => [],
                ],
            ],

        ];

        foreach ($data as $departmentName => $units) {
            $department = Department::where('name', $departmentName)->first();

            if (! $department) {
                continue;
            }

            foreach ($units as $unit) {
                UnitWork::updateOrCreate(
                    [
                        'department_id' => $department->id,
                        'name'          => $unit['name'],
                    ],
                    [
                        'seksi'             => array_values(array_unique($unit['seksi'] ?? [])),
                        'senior_manager_id' => $unit['senior_manager_id'] ?? null,
                    ]
                );
            }
        }
    }
}
