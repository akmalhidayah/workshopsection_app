<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('bengkelmesin123');

        $users = [
            // ================== USER APPROVAL (lama) ==================
            ['name' => 'Andi Rachman', 'email' => 'andi.rachman@sig.id'],
            ['name' => 'ARI N.K. TRI MAHESTHI, ST.', 'email' => 'ari.mahesthi@sig.id'],
            ['name' => 'SYAFARDINO, ST.', 'email' => 'syafardino@sig.id'],
            ['name' => 'HARIYONO GUNAWAN, ST., M.S.M.', 'email' => 'hariyono.gunawan@sig.id'],
            ['name' => 'YOSI REAPRADANA, ST.', 'email' => 'yosi.reapradana@sig.id'],
            ['name' => 'MUHAMMAD MURSHAM, ST., M.S.M', 'email' => 'muhammad.mursham@sig.id'],
            ['name' => 'PARLINDUNGAN PARDOSI, ST.,M.S.M', 'email' => 'parlindungan.pardosi@sig.id'],
            ['name' => 'ADI FATKHURROHMAN, ST., MSc.', 'email' => 'adi.fatkhurrohman@sig.id'],
            ['name' => 'DWI KURNIAWAN', 'email' => 'dwi.kurniawan@sig.id'],
            ['name' => 'YULIANTO PRAYOGA, ST.', 'email' => 'yulianto.prayoga@sig.id'],
            ['name' => 'MARYONO, SE.', 'email' => 'maryono@sig.id'],
            ['name' => 'IRSAN. ST., M.S.M', 'email' => 'irsan@sig.id'],
            ['name' => 'ANDI HILMAN, ST.', 'email' => 'andi.hilman@sig.id'],
            ['name' => 'ARDIANSYAH, S.ST', 'email' => 'ardiansyah.5384@sig.id'],
            ['name' => 'IHRAR NUZUL AZIS, ST.', 'email' => 'ihrar.azis@sig.id'],
            ['name' => 'MUHAMMAD RUSDIANTO HN, ST., M', 'email' => 'muhammad.rusdianto@sig.id'],
            ['name' => 'IMRAN, ST., MT.', 'email' => 'imran@sig.id'],
            ['name' => 'ADNAN, ST., MM.', 'email' => 'adnan@sig.id'],
            ['name' => 'ABD.WAHID', 'email' => 'abd.wahid5082@sig.id'],
            ['name' => 'HARDIMAN, SE.', 'email' => 'hardiman@sig.id'],
            ['name' => 'SURYADI PASAMBANGI, ST.', 'email' => 'suryadi.pasambangi@sig.id'],
            ['name' => 'JASMIATI, ST.', 'email' => 'jasmiati@sig.id'],
            ['name' => 'M.ALIANTO M, ST.', 'email' => 'm.alianto@sig.id'],
            ['name' => 'STEVANUS BODRO WIBOWO, S.Si.', 'email' => 'stevanus.bodro@sig.id'],
            ['name' => 'SAHAT WINSTON GULTOM, Ir.', 'email' => 'sahat.gultom@sig.id'],
            ['name' => 'YATMAN SETIAWAN, ST., MM.', 'email' => 'yatman.setiawan@sig.id'],
            ['name' => 'GATOT SUSANTO, ST.', 'email' => 'gatot.susanto@sig.id'],
            ['name' => 'MUH. ASIS ASRI, ST.', 'email' => 'muh.asis@sig.id'],
            ['name' => 'IFNUL MUBARAK, S.Si., M.Si.', 'email' => 'ifnul.mubarak@sig.id'],
            ['name' => 'AMBO MASSE.', 'email' => 'ambo.masse@sig.id'],
            ['name' => 'SIMON SALEA', 'email' => 'simon.salea@sig.id'],
            ['name' => 'Capt. GUNTUR EKO PRASETYO M.Mar', 'email' => 'guntur.prasetyo@sig.id'],
            ['name' => 'HAKMAL CANDRA', 'email' => 'hakmal.candra@sig.id'],
            ['name' => 'ALBAR BUDIMAN, ST.', 'email' => 'albar.budiman@sig.id'],
            ['name' => 'WAHYU A.R., S.Sos.', 'email' => 'wahyu.a@sig.id'],
            ['name' => 'ANTONIUS F.H. SUKMA, AMD', 'email' => 'antonius.sukma@sig.id'],
            ['name' => 'ILYASUSANTO, ST.', 'email' => 'ilyasusanto@sig.id'],
            ['name' => 'MARGIANTONIUS, S.ST', 'email' => 'margiantonius@sig.id'],
            ['name' => 'SUMARDI, ST.', 'email' => 'sumardi@sig.id'],
            ['name' => 'TEMMALEGGA, ST.', 'email' => 'temmalegga@sig.id'],
            ['name' => 'FAHRUL ARIFIANTO, ST.', 'email' => 'fahrul.arifianto@sig.id'],
            ['name' => 'AKHMAD MIFTAKHUL ULUM, AMD.', 'email' => 'akhmad.ulum@sig.id'],
            ['name' => 'IRWAN SAPARUDDIN', 'email' => 'irwan.saparuddin@sig.id'],
            ['name' => 'IMAM SUYUTI, ST.', 'email' => 'imam.suyuti@sig.id'],
            ['name' => 'MUH. DARIS DANIAL, ST.', 'email' => 'muh.danial@sig.id'],
            ['name' => 'MUH.BASRI', 'email' => 'muh.basri4911@sig.id'],
            ['name' => 'PUTRA ADHI SUMARYANTO, S.ST', 'email' => 'putra.sumaryanto@sig.id'],
            ['name' => 'ARIF BUDIMAN, AMD.', 'email' => 'arif.budiman@sig.id'],
            ['name' => 'H. ALIMUDDIN', 'email' => 'alimuddin.5027@sig.id'],
            ['name' => 'MUHAMMAD AGENG ANOM, AMD.', 'email' => 'muhammad.ageng@sig.id'],
            ['name' => 'KAHARUDDIN, AMD.', 'email' => 'kaharuddin.5292@sig.id'],
            ['name' => 'EZRA, ST.', 'email' => 'ezra@sig.id'],
            ['name' => 'H. SYAHRUDDIN, SE.', 'email' => 'syahruddin.5064@sig.id'],
            ['name' => 'GAZALY, ST.', 'email' => 'gazaly@sig.id'],
            ['name' => 'H. FERRY WARDANA', 'email' => 'ferry.wardana@sig.id'],
            ['name' => 'AKBAR GUNAWAN T, ST., S.HI.', 'email' => 'akbar.gunawan@sig.id'],
            ['name' => 'REZKY TRI MULYONO', 'email' => 'muhammad.baso@sig.id'],
            ['name' => 'MUHAMMAD ZUBAIR BASO, ST.', 'email' => 'muhammad.baso@sig.id'],
            ['name' => 'MUH.MUSAFIR, ST.', 'email' => 'muh.musafir@sig.id'],
            ['name' => 'ALWI, SE.', 'email' => 'alwi@sig.id'],
            ['name' => 'RUBEN BONDO, SE.', 'email' => 'ruben.bondo@sig.id'],
            ['name' => 'LAMASI, ST.', 'email' => 'lamasi@sig.id'],
            ['name' => 'MUDASSIR SYAM, ST.', 'email' => 'mudassir.syam@sig.id'],
            ['name' => 'ANDI RAHMAT', 'email' => 'andi.rahmat@sig.id'],
            ['name' => 'ANDI KASMAN SARANSI, ST.', 'email' => 'andi.saransi@sig.id'],
            ['name' => 'RABENKA PALESA, ST. M.S.M', 'email' => 'rabenka.palesa@sig.id'],
            ['name' => 'FAIZAL AMIR RAZAK, SE.', 'email' => 'faizal.razak@sig.id'],
            ['name' => 'ANGGA ADHITYA, ST.', 'email' => 'angga.adhitya@sig.id'],
            ['name' => 'RESTI SETIANINGRUM, ST.', 'email' => 'resti.setianingrum@sig.id'],
            ['name' => 'M.RIZAL M., ST.', 'email' => 'm.rizal@sig.id'],
            ['name' => 'AGUS FIRMANTO', 'email' => 'agus.firmanto@sig.id'],
            ['name' => 'M.YASIN, SE.', 'email' => 'm.yasin@sig.id'],
            ['name' => 'ANDI MAYUNDARI, S.Kom.', 'email' => 'andi.mayundari@sig.id'],
            ['name' => 'LUKAS TANDI, AMD.', 'email' => 'lukas.tandi@sig.id'],
            ['name' => 'ALFIAN JAIS, ST.,M.Si', 'email' => 'alfian.jais@sig.id'],
            ['name' => 'AHMAD ZAKY IMANI, AMD.', 'email' => 'ahmad.imani@sig.id'],
            ['name' => 'SJARIFUDDIN SAID, ST.', 'email' => 'sjarifuddin.said@sig.id'],
            ['name' => 'ABD.KADIR F, SE.', 'email' => 'abd.kadir@sig.id'],
            ['name' => 'SYAMSUPRIADI, S.Si.', 'email' => 'syamsupriadi@sig.id'],
            ['name' => 'SURAHMAN, ST.', 'email' => 'surahman@sig.id'],
            ['name' => 'AHMAD', 'email' => 'ahmad.4924@sig.id'],
            ['name' => 'HERWANTO S.', 'email' => 'herwanto.s@sig.id'],
            ['name' => 'JAMAL, AMD.', 'email' => 'jamal.5358@sig.id'],
            ['name' => 'WELLEM ARIANCE', 'email' => 'wellem.ariance@sig.id'],
            ['name' => 'Nur Asmal Mustafa', 'email' => 'nur.mustafa@sig.id'],


            // ================== USER BARU (bukan approval) ==================
            // PNS
            ['name' => 'PNS 1', 'email' => 'dwi.yanuari@sig.id', 'usertype' => 'user'],
            ['name' => 'PNS 2', 'email' => 'awaluddin.5313@sig.id', 'usertype' => 'pkm'],
            ['name' => 'PNS 3', 'email' => 'ansar.aco@sig.id', 'usertype' => 'user'],
            ['name' => 'PNS 4', 'email' => 'musriadi@sig.id', 'usertype' => 'user'],
            ['name' => 'PNS 5', 'email' => 'dian.himawan@sig.id', 'usertype' => 'user'],

            // Planner / Monitoring (internal)
            ['name' => 'Planner Mesin 1',        'email' => 'plannermesin1@gmail.com',           'usertype' => 'user'],
            ['name' => 'Planner Mesin 2',        'email' => 'plannermesin2@gmail.com',           'usertype' => 'user'],
            ['name' => 'Planner Elins 1',        'email' => 'plannerelins1@gmail.com',           'usertype' => 'user'],
            ['name' => 'Planner Elins 2',        'email' => 'plannerelins2@gmail.com',           'usertype' => 'user'],
            ['name' => 'Planner Operation',      'email' => 'planneroperation@gmail.com',        'usertype' => 'user'],
            ['name' => 'Monitoring User',        'email' => 'monitoringuser@gmail.com',          'usertype' => 'user'],
            ['name' => 'Planner Cus',            'email' => 'plannercus@gmail.com',              'usertype' => 'user'],
            ['name' => 'Planner BTG',            'email' => 'plannerbtg@gmail.com',              'usertype' => 'user'],
            ['name' => 'Planner Pelabuhan BKS',  'email' => 'plannerpelabuhanbks@gmail.com',     'usertype' => 'user'],

            // Monitoring PKM
            ['name' => 'Monitoring Fabrikasi',   'email' => 'monitoringfabrikasi@pkm.com',       'usertype' => 'pkm'],
            ['name' => 'Monitoring Machining',   'email' => 'monitoringmachining@pkm.com',       'usertype' => 'pkm'],
            ['name' => 'Monitoring Konstruksi',  'email' => 'monitoringkonstruksi@pkm.com',      'usertype' => 'pkm'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => trim($u['email'])],
                [
                    'name'     => trim($u['name']),
                    'password' => $password,
                    // default: approval, tapi untuk user baru kita override via array
                    'usertype' => $u['usertype'] ?? 'approval',
                ]
            );
        }
    }
}
