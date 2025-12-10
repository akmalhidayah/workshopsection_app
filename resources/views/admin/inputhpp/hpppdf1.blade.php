<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HPP - {{ $hpp->notification_number }}</title>
    <style>
        @page {
    margin: 5mm; /* Mengurangi margin agar lebih banyak konten muat */
}
        body {
            font-family: Arial, sans-serif;
            font-size: 10px; /* Perkecil ukuran font secara keseluruhan */
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 2px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header img {
    height: 50px; /* Perkecil logo */
}

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        table {
            width: 98%; /* Kurangi sedikit agar pas dalam 1 lembar */
            border-collapse: collapse;
        }

        td, th {
    padding: 3px; /* Kurangi padding agar tabel lebih kecil */
    vertical-align: top;
}

        .no-border td, .no-border th {
        border: none !important;
        padding: 5px;
        }
        .info-table {
            width: 100%;
            border: 1px solid black;
        }
        .info-table td {
            border: 1px solid black;
        }
        .bold {
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .no-border {
            border: none !important;
        }
        /* Perkecil ukuran font hanya untuk tabel HPP */
.table-hpp {
    font-size: 8px; /* Sesuaikan ukuran */
}

.table-hpp th, .table-hpp td {
    padding: 2px; /* Kurangi padding */
    font-size: 8px; /* Perkecil font dalam tabel */
}

.table-hpp th {
    font-weight: bold;
    background-color: #B0C4DE;
}

.table-hpp tr {
    page-break-inside: avoid; /* Hindari pemisahan halaman saat dicetak */
}
/* ==== SIGNATURE (TTD) – Global classes (final, compact + overscale safe) ==== */

/* -------------------------
   1) SLOT (container) umum
   -------------------------
   - width:100% agar mengikuti lebar cell.
   - height: preferensi area; kecil agar tidak meninggalkan ruang kosong.
   - display:flex memudahkan alignment vertikal/horizontal.
   - overflow:visible membolehkan gambar 'menonjol' tanpa menambah height baris.
*/
.sig-slot{
  width:100%;
  height:72px;            /* area preferensi, bisa disesuaikan */
  display:flex;
  align-items:flex-end;   /* nempel ke bawah cell */
  justify-content:center; /* default: tengah */
  overflow:visible;
}
.sig-slot--right{ justify-content:flex-end; } /* helper: rata kanan */

/* -------------------------
   2) Gambar TTD (ukuran normal)
   -------------------------
   - max-height supaya tidak melebihi slot saat tidak overscale.
   - filter untuk meningkatkan kontras saat render PDF.
*/
.sig-img{
  max-height:48px;        /* ukuran tanda tangan normal */
  width:auto;
  object-fit:contain;
  display:block;
  filter:
    brightness(0)
    contrast(750%)
    saturate(160%)
    drop-shadow(.8px .8px .8px rgba(0,0,0,.55));
}

/* Variant: slot sedikit lebih tinggi untuk tanda tangan yang butuh ruang lebih */
.sig-slot--sm {
  height:50px;
  align-items:flex-end;
  justify-content:center;
  padding-bottom:2px;
}

/* Variant: gambar tanda tangan besar (display lebih menonjol namun masih terkendali) */
.sig-img--lg{
  max-height:95px;         /* batas maksimum ketika tidak di-overscale */
  width:auto;
  transform: translateY(0);
  image-rendering: -webkit-optimize-contrast;
  filter:
    brightness(0)
    contrast(820%)
    drop-shadow(.9px .9px .9px rgba(0,0,0,.55));
}

/* -------------------------
   3) COMPACT BOX + OVERSCALE
   -------------------------
   Tujuan: memungkinkan gambar tampil lebih besar (visual kuat)
   tanpa menambah tinggi baris table (menggunakan absolute positioning).
*/

/* compact container yang menjadi bounding box (tetap kecil) */
.sig-box{
  position: relative;
  height: 46px;         /* kecil sehingga baris tabel tidak bertambah tinggi */
  overflow: visible;    /* biarkan gambar menonjol keluar */
  padding-bottom: 0;
}

/* gambar di dalam .sig-box diletakkan absolute bottom-center
   sehingga tidak mempengaruhi flow (tidak mengubah height row) */
.sig-box > img{
  position: absolute;
  left: 50%;
  bottom: -20px;      /* lebih turun agar proporsional */
  transform: translateX(-50%);
  height: 240px;      /* >>> BESARKAN UKURAN TTD <<< */
  max-width: 100%;
  width: auto;
  object-fit: contain;
  display: block;
  z-index: 2;
  filter:
    brightness(0)
    contrast(860%)
    drop-shadow(.8px .8px .8px rgba(0,0,0,.5));
}


/* Fallback teks TTD (dipakai bila image tidak tersedia) */
.sig-fallback{
  font-size:18px;
  font-weight:700;
  position:absolute;
  left:50%;
  bottom:4px;
  transform:translateX(-50%);
  z-index: 2;
}

/* Nama / jabatan di bawah tanda tangan
   - padding-top memberi ruang agar nama tidak tertutup gambar yang menonjol.
*/
.sig-name{
  font-size:10px;
  font-weight:700;
  text-align:center;
  padding-top:34px;   /* sesuaikan angka ini jika gambar lebih/kurang menonjol */
  margin:0;
  line-height:1;
}

/* -------------------------
   4) AUX: tanggal kecil & mini-slot
   -------------------------
*/

/* tanggal kecil yang biasanya diletakkan pojok kanan atas slot */
.sig-date{
  font-size:9px;
  text-align:right;
  color:#333;
  margin: 0px 4px 2px 0;
  line-height:1;
  z-index:3;
}

/* mini-slot untuk panel peminta (lebih kecil) */
.sig-box--mini{
  position:relative;
  height:48px;
  overflow: visible;
}
.sig-mini{
  position:absolute;
  left:50%;
  bottom:-4px;
  transform:translateX(-50%);
  height:84px;
  max-width:92%;
  width:auto;
  object-fit:contain;
  display:block;
  z-index:2;
}
.sig-mini-fallback{
  position:absolute;
  left:50%;
  bottom:6px;
  transform:translateX(-50%);
  font-size:16px;
  font-weight:bolder;
}

/* -------------------------
   5) INLINE small signature (footer) — tidak berubah besar
   -------------------------
*/
.sig-inline{
  height:20px;
  width:auto;
  object-fit:contain;
  vertical-align:middle;
  margin-right:2px;
  filter:
    brightness(0)
    contrast(650%)
    drop-shadow(.6px .6px .8px rgba(0,0,0,.5));
}
.sig-initial{
  font-size:9px;
  margin-right:4px;
  vertical-align:middle;
}

    </style>
</head>
@php
use Illuminate\Support\Facades\Storage;

/** Ambil absolute path file tanda tangan di disk 'signatures' */
$sigPath = function (?string $rel) {
    return ($rel && Storage::disk('signatures')->exists($rel))
        ? Storage::disk('signatures')->path($rel)
        : null;
};

/* Fungsi PENGENDALI (kolom kanan bawah) */
$SIG_DIR = $sigPath($hpp->director_signature ?? null);
$SIG_GM  = $sigPath($hpp->general_manager_signature ?? null);
$SIG_SM  = $sigPath($hpp->senior_manager_signature ?? null);
$SIG_MG = $sigPath($hpp->manager_signature ?? null);

/* Fungsi PEMINTA (panel kanan atas kecil) kalau kamu pakai */
$SIG_REQ_GM = $sigPath($hpp->general_manager_signature_requesting_unit ?? null);
$SIG_REQ_SM = $sigPath($hpp->senior_manager_signature_requesting_unit ?? null);
$SIG_REQ_MG = $sigPath($hpp->manager_signature_requesting_unit ?? null);

// langsung array dari cast
$reqNotes  = $hpp->requesting_notes ?? [];
$ctrlNotes = $hpp->controlling_notes ?? [];

// kumpulkan user_id untuk 1x query
$allIds = collect([$reqNotes, $ctrlNotes])->flatten(1)->pluck('user_id')->filter()->unique()->values();
$users  = \App\Models\User::whereIn('id', $allIds)->get()->keyBy('id');

$fmtTime = function($n) {
    $raw = $n['at'] ?? $n['created_at'] ?? null;
    if (!$raw) return null;
    try { return \Carbon\Carbon::parse($raw)->format('d/m/Y'); } // hanya tanggal
    catch (\Throwable $e) { return (string)$raw; }
};

$dateFmt = function ($v) {
    if (empty($v)) return null;
    try { return \Carbon\Carbon::parse($v)->format('d/m/Y'); } // hanya tanggal
    catch (\Throwable $e) { return (string)$v; }
};

/* siapkan label tanggal untuk tiap role */
$DT_DIR = $dateFmt($hpp->director_signed_at ?? null);
$DT_GM  = $dateFmt($hpp->general_manager_signed_at ?? null);
$DT_SM  = $dateFmt($hpp->senior_manager_signed_at ?? null);
$DT_MG = $dateFmt($hpp->manager_signed_at ?? null);

$DT_REQ_GM = $dateFmt($hpp->general_manager_requesting_signed_at ?? null);
$DT_REQ_SM = $dateFmt($hpp->senior_manager_requesting_signed_at ?? null);
$DT_REQ_MG = $dateFmt($hpp->manager_requesting_signed_at ?? null);

@endphp


<body>
<div class="container">
    <!-- HEADER -->
    <table class="no-border">
    <tr>
        <td style="width: 20%; text-align: left;">
            <img src="{{ public_path('images/logo-sig.png') }}" alt="Logo SIG" style="height: 70px;">
        </td>
        <td style="width: 60%; text-align: center;">
            <p style="font-size: 16px; font-weight: bold; line-height: 1;">HARGA PERKIRAAN PERANCANG (HPP)</p>
            <p style="font-size: 14px; font-weight: normal; line-height: 1;">JASA PEKERJAAN FABRIKASI, KONSTRUKSI & MESIN</p>
        </td>
        <td style="width: 20%; text-align: right;">
            <img src="{{ public_path('images/logo-st.png') }}" alt="Logo Tonasa" style="height: 70px;">
        </td>
    </tr>
</table>
<!-- INFORMASI HPP (rapi & sejajar titik dua) -->
<table style="width: 100%; border: 1px solid black; border-collapse: collapse; font-size: 11px;">
    <tr>
        <!-- BAGIAN KIRI -->
        <td style="width: 60%; vertical-align: top; padding: 6px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 38%; font-weight: bold; padding: 2px 0;">ORDER NO</td>
                    <td style="width: 2%; text-align: center; padding: 2px 0;">:</td>
                    <td style="width: 60%; padding: 2px 0;">{{ $hpp->notification_number }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 2px 0;">DESKRIPSI</td>
                    <td style="text-align: center; padding: 2px 0;">:</td>
                    <td style="padding: 2px 0;">{{ $hpp->description }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 2px 0;">COST CENTRE</td>
                    <td style="text-align: center; padding: 2px 0;">:</td>
                    <td style="padding: 2px 0;">{{ $hpp->cost_centre }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 2px 0;">RENCANA PEMAKAIAN</td>
                    <td style="text-align: center; padding: 2px 0;">:</td>
                    <td style="padding: 2px 0;">{{ $hpp->notification->usage_plan_date ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 2px 0;">UNIT KERJA PEMINTA</td>
                    <td style="text-align: center; padding: 2px 0;">:</td>
                    <td style="padding: 2px 0;">{{ $hpp->notification->seksi ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 2px 0;">UNIT KERJA PENGENDALI</td>
                    <td style="text-align: center; padding: 2px 0;">:</td>
                    <td style="padding: 2px 0;">Section of Workshop Machine</td>
                </tr>
            </table>
        </td>

       <!-- BAGIAN KANAN: FUNGSI PEMINTA (DIUPDATE: pakai sig-box seperti pengendali) -->
<td style="width: 18%; vertical-align: top; padding: 4px; border-left: 1px solid black;">
    <div style="border: 1px solid black; padding: 4px;">
        <div style="text-align: center; font-weight: bold; border-bottom: 1px solid black; padding-bottom: 4px;">
            FUNGSI PEMINTA
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: center;">
            <tr>
                <td style="width: 50%; border-right: 1px solid black; padding: 4px;">
                    <strong>GM Of</strong><br>
                    <span style="font-size: 10px;">{{ $hpp->generalManagerSignatureRequestingUser ? $hpp->generalManagerSignatureRequestingUser->unit_work : 'N/A' }}</span>
                </td>
                <td style="width: 50%; padding: 4px;">
                    <strong>SM Of</strong><br>
                    <span style="font-size: 10px;">{{ $hpp->seniorManagerSignatureRequestingUser ? $hpp->seniorManagerSignatureRequestingUser->unit_work : 'N/A' }}</span>
                </td>
            </tr>

            <!-- TANDA TANGAN (sekarang pakai sig-box seperti panel pengendali) -->
            <tr>
              <!-- GM Peminta -->
<td style="border-right: 1px solid black; padding: 4px; text-align: center; vertical-align: bottom;">
    <div class="sig-box">
        {{-- tanggal kecil di pojok kanan atas --}}
        <div class="sig-date">{{ $DT_REQ_GM ?? '-' }}</div>

        @if($SIG_REQ_GM)
            <img src="{{ $SIG_REQ_GM }}" alt="TTD GM Peminta" class="sig-img--lg">
        @else
            <strong class="sig-fallback">TTD</strong>
        @endif
    </div>
</td>

<!-- SM Peminta -->
<td style="padding:4px; text-align:center; vertical-align:bottom;">
    <div class="sig-box">
        {{-- tanggal kecil di pojok kanan atas --}}
        <div class="sig-date">{{ $DT_REQ_SM ?? '-' }}</div>

        @if($SIG_REQ_SM)
            <img src="{{ $SIG_REQ_SM }}" alt="TTD SM Peminta" class="sig-img--lg">
        @else
            <strong class="sig-fallback">TTD</strong>
        @endif
    </div>
</td>

            </tr>

            <tr>
                <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; font-size: 10px;">
                    <strong>{{ $hpp->generalManagerSignatureRequestingUser ? $hpp->generalManagerSignatureRequestingUser->name : 'N/A' }}</strong>
                </td>
                <td style="border-bottom: 1px solid black; padding: 4px; font-size: 10px;">
                    <strong>{{ $hpp->seniorManagerSignatureRequestingUser ? $hpp->seniorManagerSignatureRequestingUser->name : 'N/A' }}</strong>
                </td>
            </tr>

            <tr>
               <td colspan="3" style="border-top:1px solid #000;border-bottom:1px solid #000;text-align:right;padding:2px 4px;vertical-align:middle;">
                  <strong class="sig-initial">{{ $hpp->managerSignatureRequestingUser->initials ?? 'N/A' }} /</strong>
                  @if($SIG_REQ_MG)
                    <img src="{{ $SIG_REQ_MG }}" alt="Manager Signature" class="sig-inline">
                  @else
                    <strong style="font-size:9px;vertical-align:middle;">TTD</strong>
                  @endif
               </td>
            </tr>
        </table>
    </div>
</td>

                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

<!-- TABEL HPP (group by jenis -> A. Jasa  - item1, - item2 ...) -->
<div class="overflow-x-auto">
@php
    $groupsByJenis = [];   // key = jenis label ('' => 'Lainnya')
    $nama         = $hpp->nama_item ?? [];
    $jumlah       = $hpp->jumlah_item ?? [];
    $jenis        = $hpp->jenis_item ?? [];
    $qty          = $hpp->qty ?? [];
    $satuan       = $hpp->satuan ?? [];
    $harga_satuan = $hpp->harga_satuan ?? [];
    $harga_total  = $hpp->harga_total ?? [];
    $keterangan   = $hpp->keterangan ?? [];

    // kumpulkan items ke dalam bucket berdasarkan jenis (urut kemunculan)
    foreach ($nama as $g => $items) {
        if (!is_array($items)) continue;
        foreach ($items as $i => $nm) {
            $lab = trim($jenis[$g][$i] ?? '');
            $key = ($lab === '') ? 'Lainnya' : $lab;
            $groupsByJenis[$key][] = [
                'g' => $g,
                'i' => $i,
                'nama' => $nm ?? '',
                'jumlah' => $jumlah[$g][$i] ?? null,
                'qty' => $qty[$g][$i] ?? null,
                'satuan' => $satuan[$g][$i] ?? '',
                'harga_satuan' => $harga_satuan[$g][$i] ?? null,
                'harga_total' => $harga_total[$g][$i] ?? null,
                'keterangan' => $keterangan[$g][$i] ?? '',
            ];
        }
    }

    // helper index -> letter (A, B, ..., Z, AA, AB...)
    $indexToLetters = function(int $index) {
        $s = '';
        $n = $index + 1;
        while ($n > 0) {
            $r = ($n - 1) % 26;
            $s = chr(65 + $r) . $s;
            $n = intdiv($n - 1, 26);
        }
        return $s;
    };

    // hitung rowspan OA: total baris yang akan dibuat untuk semua group (header grup + tiap item)
    $totalRows = 0;
    foreach ($groupsByJenis as $label => $rows) {
        $totalRows += 1; // header grup (A. Jasa)
        $totalRows += count($rows); // item - Plate ...
    }
    if ($totalRows === 0) $totalRows = 1;
@endphp

<table class="table-hpp" style="width:100%; border-collapse:collapse; border:1px solid black; font-size:9px;">
    <thead style="background-color:#B0C4DE; color:#333;">
        <tr>
            <th style="border:1px solid black; padding:5px; text-align:center; width:14%;">OUTLINE AGREEMENT (OA)</th>
            <th style="border:1px solid black; padding:5px; text-align:center;">URAIAN PEKERJAAN</th>
            <th style="border:1px solid black; padding:5px; text-align:center; width:6%;">QTY</th>
            <th style="border:1px solid black; padding:5px; text-align:center; width:10%;">SATUAN (EA/LOT/JAM/M2/KG)</th>
            <th style="border:1px solid black; padding:5px; text-align:center; width:12%;">HARGA SATUAN</th>
            <th style="border:1px solid black; padding:5px; text-align:center; width:12%;">JUMLAH</th>
            <th style="border:1px solid black; padding:5px; text-align:center; width:18%;">KETERANGAN</th>
        </tr>
    </thead>
    <tbody>
    @if(empty($groupsByJenis))
        <tr>
            <td style="border:1px solid black; text-align:center;">{{ $hpp->outline_agreement ?? '' }}</td>
            <td colspan="6" style="border:1px solid black; text-align:center; padding:6px;">Tidak ada data</td>
        </tr>
    @else
        @php $printedOA = false; $groupIndex = 0; @endphp

        @foreach ($groupsByJenis as $label => $items)
            {{-- baris header grup: "A. Jasa" --}}
            <tr>
                @if (!$printedOA)
                    <td style="border:1px solid black; text-align:center; vertical-align:top;" rowspan="{{ $totalRows }}">
                        {{ $hpp->outline_agreement ?? '' }}
                    </td>
                    @php $printedOA = true; @endphp
                @endif

                <td style="border:1px solid black; padding:4px 8px; font-weight:bold;">
                    {{ $indexToLetters($groupIndex) }}. {{ $label }}
                </td>
                <td style="border:1px solid black;"></td>
                <td style="border:1px solid black;"></td>
                <td style="border:1px solid black;"></td>
                <td style="border:1px solid black;"></td>
                <td style="border:1px solid black;"></td>
            </tr>

            {{-- baris item di bawah header grup, tampilkan sebagai "- Nama Item" --}}
            @foreach ($items as $it)
                <tr>
                 <td style="border:1px solid black; padding:3px 12px;">
    &nbsp;&nbsp;&nbsp;- {{ $it['nama'] }} = {{ $it['jumlah'] }}</td>

                    <td style="border:1px solid black; text-align:center;">
                        {{ isset($it['qty']) ? rtrim(rtrim(number_format((float)$it['qty'], 3, ',', '.'), '0'), ',') : '' }}
                    </td>
                    <td style="border:1px solid black; text-align:center;">
                        {{ $it['satuan'] ?? '' }}
                    </td>
                    <td style="border:1px solid black; text-align:right; padding-right:6px;">
                        {{ isset($it['harga_satuan']) ? number_format((float)$it['harga_satuan'], 0, ',', '.') : '' }}
                    </td>
                    <td style="border:1px solid black; text-align:right; padding-right:6px;">
                        {{ isset($it['harga_total']) ? number_format((float)$it['harga_total'], 0, ',', '.') : '' }}
                    </td>
                    <td style="border:1px solid black; padding:4px;">
                        {{ $it['keterangan'] ?? '' }}
                    </td>
                </tr>
            @endforeach

            @php $groupIndex++; @endphp
        @endforeach
    @endif

    {{-- TOTAL --}}
    <tr style="font-weight:bold; background-color:#DCDCDC;">
        <td colspan="5" style="border:1px solid black; text-align:center;">TOTAL</td>
        <td style="border:1px solid black; text-align:right; padding-right:6px;">
            {{ ($hpp->total_amount ?? 0) ? number_format((float)$hpp->total_amount, 0, ',', '.') : '' }}
        </td>
        <td style="border:1px solid black;"></td>
    </tr>
    </tbody>
</table>
</div>

<!-- Informasi Catatan dan Tanda Tangan -->
<table style="width: 100%; border: 1px solid black; border-collapse: collapse;">
    <tr>
<td style="width: 28%; border: 1px solid black; vertical-align: top; padding: 8px;">
  <strong>Catatan User Peminta:</strong><br>
  @if(!empty($reqNotes))
    @foreach($reqNotes as $i => $n)
      @php $u = isset($n['user_id']) ? $users->get($n['user_id']) : null; @endphp
      <div style="margin: 4px 0 8px;">
        <div style="margin-bottom:2px;">{{ $i+1 }}. {{ $n['note'] ?? '-' }}</div>
        @if($u)
          <div style="font-size:10px;color:#444;">— {{ $u->name ?? 'N/A' }} ({{ $u->jabatan ?? '-' }})</div>
        @endif
      </div>
    @endforeach
  @else
    <div style="color:#666; font-size:10px;">-</div>
  @endif
</td>

<td style="width: 28%; border: 1px solid black; vertical-align: top; padding: 8px;">
  <strong>Catatan Pengendali:</strong><br>
  @if(!empty($ctrlNotes))
    @foreach($ctrlNotes as $i => $n)
      @php $u = isset($n['user_id']) ? $users->get($n['user_id']) : null; @endphp
      <div style="margin: 4px 0 8px;">
        <div style="margin-bottom:2px;">{{ $i+1 }}. {{ $n['note'] ?? '-' }}</div>
        @if($u)
          <div style="font-size:10px;color:#444;">— {{ $u->name ?? 'N/A' }} ({{ $u->jabatan ?? '-' }})</div>
        @endif
      </div>
    @endforeach
  @else
    <div style="color:#666; font-size:10px;">-</div>
  @endif
</td>

<!-- Kolom Tanda Tangan -->
<td class="px-2 py-2" style="width: 50%; border: 1px solid black;">
    <table class="min-w-full border-collapse text-xs" style="border: 1px solid black;">
        <tr>
            <td class="px-2 py-2 text-center" style="width: 40%; border-right: 1px solid black; border-bottom: 1px solid black; font-weight: bold; font-style: italic;">Menyetujui</td>
            <td colspan="2" class="px-2 py-2 text-center" style="width: 60%; border-bottom: 1px solid black; font-weight: bold; font-style: italic;">FUNGSI PENGENDALI</td>
        </tr>
      <tr>
    <td class="px-2 py-4 text-center" style="width: 33%; border-right: 1px solid black;">
        <strong>Director</strong> of Operation
    </td>
    <td class="px-2 py-4 text-center" style="width: 34%; border-right: 1px solid black;">
        <strong>GM of </strong>{{ optional($hpp->generalManagerSignatureUser)->departemen ?? 'N/A' }}
    </td>
    <td class="px-2 py-4 text-center" style="width: 33%;">
        <strong>SM of </strong>{{ optional($hpp->seniorManagerSignatureUser)->unit_work ?? 'N/A' }}
    </td>
</tr>

{{-- Baris tanggal tanda tangan (kecil & rata kanan) --}}
<tr>
    <td style="width:33%; border-right:1px solid black; padding:6px; font-size:10px; text-align:right; vertical-align:top; color:#333;">
        {{-- pakai $DT_DIR yang sudah kamu siapkan (format d/m/Y H:i) --}}
        {{ $DT_DIR ?? '-' }}
    </td>

    <td style="width:34%; border-right:1px solid black; padding:6px; font-size:10px; text-align:right; vertical-align:top; color:#333;">
        {{ $DT_GM ?? '-' }}
    </td>

    <td style="width:33%; padding:6px; font-size:10px; text-align:right; vertical-align:top; color:#333;">
        {{ $DT_SM ?? '-' }}
    </td>
</tr>

        <tr>
<!-- Tanda Tangan Director of Operation -->
<td class="px-2 py-3 text-center" style="width:33%; border-right:1px solid black; vertical-align:bottom;">
    <div class="sig-box">
        @if($SIG_DIR)
            <img src="{{ $SIG_DIR }}" alt="Director Signature" class="sig-img--lg">
        @else
            <strong style="font-size:22px;font-weight:bolder;position:absolute;left:50%;bottom:6px;transform:translateX(-50%);">
                TTD
            </strong>
        @endif
    </div>
</td>

<!-- Tanda Tangan GM -->
<td class="px-2 py-3 text-center" style="width:34%; border-right:1px solid black; vertical-align:bottom;">
    <div class="sig-box">
        @if($SIG_GM)
            <img src="{{ $SIG_GM }}" alt="GM Signature" class="sig-img--lg">
        @else
            <strong style="font-size:22px;font-weight:bolder;position:absolute;left:50%;bottom:6px;transform:translateX(-50%);">
                TTD
            </strong>
        @endif
    </div>
</td>

<!-- Tanda Tangan SM -->
<td class="px-2 py-3 text-center" style="width:33%; vertical-align:bottom;">
    <div class="sig-box">
        @if($SIG_SM)
            <img src="{{ $SIG_SM }}" alt="SM Signature" class="sig-img--lg">
        @else
            <strong style="font-size:22px;font-weight:bolder;position:absolute;left:50%;bottom:6px;transform:translateX(-50%);">
                TTD
            </strong>
        @endif
    </div>
</td>
        </tr>
        <tr>
            <td class="px-2 py-2 text-center" style="width: 33%; border-right: 1px solid black; border-bottom: 1px solid black;">
                <strong>{{ $hpp->directorSignatureUser ? $hpp->directorSignatureUser->name : 'N/A' }}</strong>
            </td>
            <td class="px-2 py-2 text-center" style="width: 34%; border-right: 1px solid black; border-bottom: 1px solid black;">
                <strong>{{ $hpp->generalManagerSignatureUser ? $hpp->generalManagerSignatureUser->name : 'N/A' }}</strong>
            </td>
            <td class="px-2 py-2 text-center" style="width: 33%; border-bottom: 1px solid black;">
                <strong>{{ $hpp->seniorManagerSignatureUser ? $hpp->seniorManagerSignatureUser->name : 'N/A' }}</strong>
            </td>
        </tr>
        <tr> 
<td colspan="3" style="border-top:1px solid #000;border-bottom:1px solid #000; text-align:right; padding:2px 4px; vertical-align:middle;">
    <div style="position:relative; display:inline-block; width:100%;">
      <strong class="sig-initial">{{ $hpp->managerSignatureUser->initials ?? 'N/A' }} /</strong>
      @if($SIG_MG)
        <img src="{{ $SIG_MG }}" alt="Manager Signature" class="sig-inline">
      @else
        <strong style="font-size:9px;vertical-align:middle;">TTD</strong>
      @endif
    </div>
  </td>
        </tr>
    </table>
</td>
</tr>
</table>
</body>
</html>
