<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitWork;

class UnitWorkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mapping Unit → daftar seksi (JSON array)
        $units = [
            'Unit of Clinker Production 2' => [
                'Section of Line 5 RKC Operation',
                'Section of Line 4 RKC Operation',
            ],

            'Unit of Clinker Production 1' => [
                'Section of Line 2/3 RKC Operation',
                'Staff of Performance Monitoring & Eval.',
            ],

            'Unit of Cement Production' => [
                'Section of Line 2/3 FM Operation',
                'Section of Line 4 Finish Mill Operation',
                'Section of Line 5 Finish Mill Operation',
                'Section of Packer Operation',
                'Section of Bulk Cement Operation',
            ],

            'Unit of Quality Assurance' => [
                'Section of Material Quality Assurance',
                'Section of Product Quality Assurance',
                'Section of Cement App & Tech Services',
            ],

            'Unit of Reliability Maintenance' => [
                'Staff of Overhaul Management',
                'Staff of Maintenance Inspection',
                'Staff of Troubleshooting',
                'Staff of PGO',
                'Staff of Planning & Scheduling',
            ],

            'Unit of Machine Maintenance 2' => [
                'Section of Line 4/5 RM Machine Maint',
                'Section of Line 4/5 Kiln & CM Mach Maint',
                'Section of Line 4/5 FM Machine Maint',
            ],

            'Unit of Elins Maintenance 1' => [
                'Section of Packer Elins Maint',
                'Section of Crusher Elins Maintenance',
                'Section of Line 2/3 FM Elins Main',
                'Section of Line 2/3 RKC Elins Maint.',
            ],

            'Unit of Elins Maintenance 2' => [
                'Section of Line 4/5 FM Elins Maint',
                'Section of EP/DC Maintenance',
                'Section of Line 4/5 RKC Elctr. Maint.',
                'Section of Line 4/5 RKC Instr. Maint.',
            ],

            'Unit of Machine Maintenance 1' => [
                'Section of Crusher Machine & Conp Maint.',
                'Section of Line 2/3 FM Machine Maint.',
                'Section of Packer Machine Maintenance',
                'Section of Line 2/3 RKC Machine Maint.',
            ],

            'Unit of Mining' => [
                'Section of Mine Safety Reclamation',
                'Section of Limestone Mining',
                'Section of Clay Mining',
                'Section of Mine Planning & Monitoring',
            ],

            'Unit of Raw Material Management' => [
                'Section of Clay Crusher Operation',
                'Section of Limestone Crusher Operation',
            ],

            'Unit of Power Plant Operation' => [
                'Section of Power Plant Operation I',
                'Section of Power Plant Operation',
                'Section of Water & Coal Quality Control',
                'Section of CUS',
            ],

            'Unit of Power Plant Machine Maint' => [
                'Section of Power Plant Machine Maint',
            ],

            'Unit of Power Distribution' => [
                'Section of Electricity Load Control',
                'Section of Electrical Network Maint',
                'Section of Net & Elec Plant Maint',
                'Staff of Pwr Plant Opr Planning & Ctrlg',
            ],

            'Unit of Power Plant Elins Maintenance' => [
                'Section of Power Plant Elctrical Maint.',
                'Section of Power Plant Instrument Maint.',
            ],

            'Unit of Production Support' => [
                'Section of Heavy Equipment & Coal Transp',
                'Section of Plant Hygiene',
                'Section of Utility',
                'Section of AFR & 3rd Material',
            ],

            'Unit of Quality Control' => [
                'Section of Quality Control (2/3)',
                'Section of Quality Control (4/5)',
                'Section of Quality Development & Eval.',
            ],

            'Unit of Prod. Plan Eval. & Environmental' => [
                'Staff of Coal Mixing Officer',
                'Section of Enviromental Monitoring',
                'Section of PROPER & CDM',
                'Section of Production Planning',
                'Section of RKC Evaluation',
                'Section of Raw Material & Cement Mill Ev',
            ],

            'Unit of OHS' => [
                'Section of Plant Occupational Healt',
                'Section of BKS Occupational Healt',
                'Staff of Permit & OHS Equip Maintenance',
            ],

            'Unit of AFR & Energy' => [
                'Staff of Coal Mixing Officer',
                'Staff of Energy Thermal',
                'Section of AFR & 3rd Material',
                'Staff of AFR',
            ],

            'Unit of Port & Packer' => [
                'Section of BKS Port & Packer Operation',
                'Section of BKS Port & Packer Mainten',
                'Section of Bulk Cemt Tranp & Cemt Silo',
                'Section of CUS',
            ],

            // (Kedua kalinya "Unit of Cement Production" di data kamu sudah digabung ke entri di atas)

            'Unit of Engineering' => [
                'Staff of Mechanical Design Engineer',
                'Staff of Elins Design Engineer',
                'Staff of Civil Design Engineer',
                'Staff of Process Design Engineer',
            ],

            'Unit of Project Management' => [
                'Section of Project Execution (Construction',
                'Staff of OVH Management',
            ],

            'Unit of CAPEX Management' => [
                'Staff of CAPEX',
            ],

            'Unit of Workshop & Design' => [
                'Section of Elins Workshop',
                'Section of Machine Workshop',
                'Staff of Field Supporting',
            ],

            'Unit of Maintenance Planning & Eval.' => [
                'Staff of Maintenance Planning & Eval.',
                'Staff of Spare Part Planning',
            ],
        ];

        // Upsert semua unit + seksi
        foreach ($units as $name => $seksi) {
            UnitWork::updateOrCreate(
                ['name' => $name],
                ['seksi' => array_values(array_unique($seksi))]
            );
        }

        // (Opsional) Hapus unit yang tidak ada di mapping ini:
        // UnitWork::whereNotIn('name', array_keys($units))->delete();
    }
}
