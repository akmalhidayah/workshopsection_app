<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\UnitWork;
use App\Models\UnitWorkSection;
use Illuminate\Database\Seeder;

class UnitWorkSectionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [

            // =====================================================
            // Dept. of Clinker & Cement Production
            // =====================================================
            'Clinker & Cement Production' => [
                'Unit of Clinker Production 2' => [
                    ['name' => 'Section of Line 5 RKC Operation', 'manager_id' => 36], // ALBAR BUDIMAN
                    ['name' => 'Section of Line 4 RKC Operation', 'manager_id' => 37], // WAHYU A.R.
                ],
                'Unit of Clinker Production 1' => [
                    ['name' => 'Section of Line 2/3 RKC Operation',          'manager_id' => null],
                    ['name' => 'Staff of Performance Monitoring & Eval.',    'manager_id' => null],
                ],
                'Unit of Cement Production' => [
                    ['name' => 'Section of Line 2/3 FM Operation',           'manager_id' => 38], // ANTONIUS
                    ['name' => 'Section of Line 4 Finish Mill Operation',    'manager_id' => null],
                    ['name' => 'Section of Line 5 Finish Mill Operation',    'manager_id' => 39], // ILYASUSANTO
                    ['name' => 'Section of Packer Operation',                'manager_id' => 40], // MARGIANTONIUS
                    ['name' => 'Section of Bulk Cement Operation',           'manager_id' => 41], // SUMARDI
                ],
                'Unit of Quality Assurance' => [
                    ['name' => 'Section of Material Quality Assurance',      'manager_id' => null],
                    ['name' => 'Section of Product Quality Assurance',       'manager_id' => null],
                    ['name' => 'Section of Cement App & Tech Services',      'manager_id' => 42], // TEMMALEGGA
                ],
            ],

            // =====================================================
            // Dept. of Maintenance
            // =====================================================
            'Maintenance' => [
                'Unit of Reliability Maintenance' => [
                    ['name' => 'Staff of Overhaul Management',               'manager_id' => null],
                    ['name' => 'Staff of Maintenance Inspection',            'manager_id' => null],
                    ['name' => 'Staff of Troubleshooting',                   'manager_id' => null],
                    ['name' => 'Staff of PGO',                               'manager_id' => null],
                    ['name' => 'Staff of Planning & Scheduling',             'manager_id' => null],
                ],
                'Unit of Machine Maintenance 2' => [
                    ['name' => 'Section of Line 4/5 RM Machine Maint',       'manager_id' => null],
                    ['name' => 'Section of Line 4/5 Kiln & CM Mach Maint',   'manager_id' => 43], // FAHRUL
                    ['name' => 'Section of Line 4/5 FM Machine Maint',       'manager_id' => 44], // AKHMAD ULUM
                ],
                'Unit of Elins Maintenance 1' => [
                    ['name' => 'Section of Packer Elins Maint',              'manager_id' => 45], // IRWAN
                    ['name' => 'Section of Crusher Elins Maintenance',       'manager_id' => 46], // IMAM
                    ['name' => 'Section of Line 2/3 FM Elins Main',          'manager_id' => 47], // MUH. DARIS
                    ['name' => 'Section of Line 2/3 RKC Elins Maint.',       'manager_id' => 48], // MUH. BASRI
                ],
                'Unit of Elins Maintenance 2' => [
                    ['name' => 'Section of Line 4/5 FM Elins Maint',         'manager_id' => 49], // PUTRA
                    ['name' => 'Section of EP/DC Maintenance',               'manager_id' => 50], // ARIF BUDIMAN
                    ['name' => 'Section of Line 4/5 RKC Elctr. Maint.',      'manager_id' => 51], // H. ALIMUDDIN
                    ['name' => 'Section of Line 4/5 RKC Instr. Maint.',      'manager_id' => 52], // MUHAMMAD AGENG
                ],
                'Unit of Machine Maintenance 1' => [
                    ['name' => 'Section of Crusher Machine & Conp Maint.',   'manager_id' => 53], // KAHARUDDIN
                    ['name' => 'Section of Line 2/3 FM Machine Maint.',      'manager_id' => 54], // EZRA
                    ['name' => 'Section of Packer Machine Maintenance',      'manager_id' => null],
                    ['name' => 'Section of Line 2/3 RKC Machine Maint.',     'manager_id' => 55], // SYAHRUDDIN
                ],
            ],

            // =====================================================
            // Dept. of Mining & Power Plant
            // =====================================================
            'Mining & Power Plant' => [
                'Unit of Mining' => [
                    ['name' => 'Section of Mine Safety Reclamation',         'manager_id' => 56], // GAZALY
                    ['name' => 'Section of Limestone Mining',                'manager_id' => 57], // FERRY
                    ['name' => 'Section of Clay Mining',                     'manager_id' => 58], // AKBAR
                    ['name' => 'Section of Mine Planning & Monitoring',      'manager_id' => 59], // REZKY (email BASO)
                    ['name' => 'Section of Clay Crusher Operation',          'manager_id' => 59], // MUHAMMAD BASO
                    ['name' => 'Section of Limestone Crusher Operation',     'manager_id' => 60], // MUSAFIR
                ],

                'Unit of Raw Material Management' => [
                    ['name' => 'Section of Heavy Equipment & Coal Transp',   'manager_id' => 67], // RABENKA
                    ['name' => 'Section of Plant Hygiene',                   'manager_id' => 68], // FAIZAL
                    ['name' => 'Section of Utility',                         'manager_id' => 69], // ANGGA
                    // versi tanpa manajer
                    ['name' => 'Section of AFR & 3rd Material',              'manager_id' => null],
                ],

                'Unit of Power Plant Operation' => [
                    ['name' => 'Section of Power Plant Operation I',         'manager_id' => null],
                    ['name' => 'Section of Power Plant Operation',           'manager_id' => null],
                    ['name' => 'Section of Water & Coal Quality Control',    'manager_id' => null],
                    ['name' => 'Section of CUS',                             'manager_id' => 61], // ALWI
                ],

                'Unit of Power Plant Machine Maintenance' => [
                    ['name' => 'Section of Power Plant Machine Maint',       'manager_id' => 62], // RUBEN
                ],

                'Unit of Power Distribution' => [
                    ['name' => 'Section of Electricity Load Control',        'manager_id' => 63], // LAMASI
                    ['name' => 'Section of Electrical Network Maint',        'manager_id' => null],
                    ['name' => 'Section of Net & Elec Plant Maint',          'manager_id' => null],
                ],

                'Staff of Power Plant Operation Planning & Control' => [
                    ['name' => 'Staff of Pwr Plant Opr Planning & Ctrlg',    'manager_id' => 64], // MUDASSIR
                ],

                'Unit of Power Plant Elins Maintenance' => [
                    ['name' => 'Section of Power Plant Elctrical Maint.',    'manager_id' => 65], // ANDI RAHMAT
                    ['name' => 'Section of Power Plant Instrument Maint.',   'manager_id' => 66], // ANDI KASMAN
                ],
            ],

            // =====================================================
            // Dept. of Production Planning & Control
            // =====================================================
            'Production Planning & Control' => [
                'Unit of Production Support' => [
                    ['name' => 'Staff of Coal Mixing Officer',               'manager_id' => null],
                    ['name' => 'Section of Production Planning',             'manager_id' => 75], // LUKAS
                    ['name' => 'Section of RKC Evaluation',                  'manager_id' => 76], // ALFIAN
                    ['name' => 'Section of Raw Material & Cement Mil Ev',    'manager_id' => 77], // AHMAD ZAKY
                ],

                'Unit of Quality Control' => [
                    ['name' => 'Section of Quality Control (2/3)',           'manager_id' => 70], // RESTI
                    ['name' => 'Section of Quality Control (4/5)',           'manager_id' => 71], // M. RIZAL
                    ['name' => 'Section of Quality Development & Eval.',     'manager_id' => 72], // AGUS FIRMANTO
                ],

                'Unit of Production Plant Evaluation & Environmental' => [
                    ['name' => 'Section of Enviromental Monitoring',         'manager_id' => 73], // M. YASIN
                    ['name' => 'Section of PROPER & CDM',                    'manager_id' => 74], // ANDI MAYUNDARI
                ],

                'Unit of OHS' => [
                    ['name' => 'Section of Plant Occupational Healt',        'manager_id' => 78], // SJARIFUDDIN
                    ['name' => 'Section of BKS Occupational Healt',          'manager_id' => 79], // ABD. KADIR
                    ['name' => 'Staff of Permit & OHS Equip Maintenance',    'manager_id' => null],
                ],

                'Unit of AFR & Energy' => [
                    ['name' => 'Staff of Coal Mixing Officer',               'manager_id' => null],
                    ['name' => 'Staff of Energy Thermal',                    'manager_id' => null],
                    ['name' => 'Section of AFR & 3rd Material',              'manager_id' => 80], // SYAMSUPRIADI
                    ['name' => 'Staff of AFR',                               'manager_id' => null],
                ],
            ],

            // =====================================================
            // Dept. of Project Management & Maintenance Support
            // =====================================================
            'Project Management & Maintenance Support' => [
                'Unit of Engineering' => [
                    ['name' => 'Staff of Mechanical Design Engineer',        'manager_id' => null],
                    ['name' => 'Staff of Elins Design Engineer',            'manager_id' => null],
                    ['name' => 'Staff of Civil Design Engineer',            'manager_id' => null],
                    ['name' => 'Staff of Process Design Engineer',          'manager_id' => null],
                ],
                'Unit of Project Management' => [
                    ['name' => 'Section of Project Execution (Construction', 'manager_id' => 81], // SURAHMAN
                    ['name' => 'Staff of OVH Management',                    'manager_id' => null],
                ],
                'Staff of TPM' => [
                    // kalau nanti ada seksi khusus TPM bisa ditambah di sini
                ],
                'Unit of CAPEX Management' => [
                    ['name' => 'Staff of CAPEX',                             'manager_id' => 30], // MUH. ASIS (user id dari list sebelumnya)
                ],
                'Unit of Workshop & Design' => [
                    ['name' => 'Section of Elins Workshop',                  'manager_id' => 82], // AHMAD
                    ['name' => 'Section of Machine Workshop',                'manager_id' => 83], // HERWANTO
                    ['name' => 'Staff of Field Supporting',                  'manager_id' => 84], // JAMAL
                ],
                'Unit of Maintenance Planning & Evaluation' => [
                    ['name' => 'Staff of Maintenance Planning & Eval.',      'manager_id' => null],
                    ['name' => 'Staff of Spare Part Planning',               'manager_id' => null],
                ],
            ],

            // =====================================================
            // Dept. of Infrastructure
            // =====================================================
            'Infrastructure' => [
                'Unit of Packing Plant 2' => [
                    ['name' => 'Section of Banjarmasin Packing Plant',       'manager_id' => 85], // WELLEM ARIANCE
                ],
                // Unit of Plant & Port Product Discharge Opr
                // Unit of SCM Infra Port Managemnet
                // Unit of of Interplant Logistic
                // (kalau nanti ada seksi tambahan bisa disusulkan)
            ],

        ];

        foreach ($data as $departmentName => $units) {

            $department = Department::where('name', $departmentName)->first();

            if (! $department) {
                continue;
            }

            foreach ($units as $unitName => $sections) {

                $unitWork = UnitWork::where('department_id', $department->id)
                    ->where('name', $unitName)
                    ->first();

                if (! $unitWork) {
                    continue;
                }

                foreach ($sections as $section) {
                    UnitWorkSection::updateOrCreate(
                        [
                            'unit_work_id' => $unitWork->id,
                            'name'         => $section['name'],
                        ],
                        [
                            'manager_id'   => $section['manager_id'] ?? null,
                        ]
                    );
                }
            }
        }
    }
}
