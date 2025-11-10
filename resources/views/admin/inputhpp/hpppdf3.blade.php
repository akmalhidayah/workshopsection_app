<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HPP - {{ $hpp->notification_number }}</title>
    <style>

        body {
            font-family: Arial, sans-serif;
            font-size: 11px; /* Perkecil ukuran font secara keseluruhan */
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
    font-size: 10px; /* Sesuaikan ukuran */
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

    </style>
</head>
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

<!-- INFORMASI HPP -->
<table style="width: 100%; border: 1px solid black; border-collapse: collapse;">
    <tr>
        <!-- Kolom Informasi HPP -->
        <td style="width: 60%; vertical-align: top; padding: 6px;">
            <table style="width: 100%; border: none;" class="no-border">
                <tr>
                    <td style="font-weight: bold;">ORDER NO</td>
                    <td>: {{ $hpp->notification_number }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">DESKRIPSI</td>
                    <td>: {{ $hpp->description }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">COST CENTRE</td>
                    <td>: {{ $hpp->cost_centre }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">RENCANA PEMAKAIAN</td>
                    <td>: {{ $hpp->notification->usage_plan_date ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">UNIT KERJA PEMINTA</td>
                    <td>: {{ $hpp->requesting_unit }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">UNIT KERJA PENGENDALI</td>
                    <td>: {{ $hpp->controlling_unit }}</td>
                </tr>
            </table>
        </td>

        <!-- Kolom FUNGSI PEMINTA -->
        <td style="width: 40%; vertical-align: top; padding: 4px; border-left: 1px solid black;">
            <div style="border: 1px solid black; padding: 4px;">
                <div style="text-align: center; font-weight: bold; border-bottom: 1px solid black; padding-bottom: 4px;">
                    FUNGSI PEMINTA
                </div>
                <table style="width: 100%; border-collapse: collapse; text-align: center;">
                    <tr>
                        <td style="width: 50%; border-right: 1px solid black; padding: 4px;">
                            <strong>SM Of Unit Of Workshop</strong>
                        </td>
                        <td style="width: 50%; padding: 4px;">
                            <strong>Mgr Of Workshop Machine</strong>
                        </td>
                    </tr>
                    <tr>
                       <!-- Tanda Tangan SM -->
                        <td style="border-right: 1px solid black; padding: 4px; text-align: center; vertical-align: bottom; height: 70px;">
                            @if(file_exists(storage_path("app/public/signatures/hpp/senior_manager_signature_{$hpp->notification_number}.png")))
                                <img src="{{ storage_path("app/public/signatures/hpp/senior_manager_signature_{$hpp->notification_number}.png") }}" 
                                    alt="TTD SM" 
                                    style="width: 150px; height: 70px; object-fit: contain;">
                            @else
                                <strong style="font-size: 20px; font-weight: bold;">TTD</strong>
                            @endif
                        </td>

                        <!-- Tanda Tangan MGR -->
                        <td style="padding: 4px; text-align: center; vertical-align: bottom; height: 70px;">
                            @if(file_exists(storage_path("app/public/signatures/hpp/manager_signature_{$hpp->notification_number}.png")))
                                <img src="{{ storage_path("app/public/signatures/hpp/manager_signature_{$hpp->notification_number}.png") }}" 
                                    alt="TTD MGR" 
                                    style="width: 150px; height: 70px; object-fit: contain;">
                            @else
                                <strong style="font-size: 20px; font-weight: bold;">TTD</strong>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <!-- Nama SM -->
                        <td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 4px; font-size: 10px;">
                            <strong>{{ $hpp->seniorManagerSignatureUser ? $hpp->seniorManagerSignatureUser->name : 'N/A' }}</strong>
                        </td>

                        <!-- Nama MGR -->
                        <td style="border-bottom: 1px solid black; padding: 4px; font-size: 10px;">
                            <strong>{{ $hpp->managerSignatureUser ? $hpp->managerSignatureUser->name : 'N/A' }}</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

<!-- TABEL HPP (final + nomor jenis + indent grup) -->
<div class="overflow-x-auto">
@php
$groups        = $hpp->uraian_pekerjaan ?? [];
$jenis         = $hpp->jenis_item ?? [];
$nama          = $hpp->nama_item ?? [];
$qty           = $hpp->qty ?? [];
$satuan        = $hpp->satuan ?? [];
$harga_satuan  = $hpp->harga_satuan ?? [];
$harga_total   = $hpp->harga_total ?? [];
$keterangan    = $hpp->keterangan ?? [];

/*
 * Rowspan OA = (judul grup) + (jumlah label jenis unik) + (jumlah item) untuk
 * setiap grup, lalu dijumlahkan untuk semua grup.
 */
$rowspanOA = 0;
foreach ($groups as $gIdx => $gTitle) {
    $rowCount = is_array($nama[$gIdx] ?? null) ? count($nama[$gIdx]) : 0;

    // hitung label unik sesuai data jenis_item; kosong -> "Lainnya"
    $labels = [];
    for ($i = 0; $i < $rowCount; $i++) {
        $lab = trim($jenis[$gIdx][$i] ?? '');
        $lab = ($lab === '') ? 'Lainnya' : $lab;
        if (!in_array($lab, $labels, true)) $labels[] = $lab;
    }

    $rowspanOA += 1 /*judul grup*/ + count($labels) + $rowCount;
}
if ($rowspanOA === 0) $rowspanOA = 1;
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
@php $printedOA = false; @endphp

@forelse ($groups as $g => $groupTitle)
    {{-- BARIS JUDUL GRUP --}}
    <tr>
        @if (!$printedOA)
            <td style="border:1px solid black; text-align:center; vertical-align:top;" rowspan="{{ $rowspanOA }}">
                {{ $hpp->outline_agreement ?? '' }}
            </td>
            @php $printedOA = true; @endphp
        @endif
        <td style="border:1px solid black; padding:3px; font-size:8.5px; font-weight:bold;">
            {{ chr(65 + $g) }}. {{ $groupTitle }}
        </td>
        <td style="border:1px solid black;"></td>
        <td style="border:1px solid black;"></td>
        <td style="border:1px solid black;"></td>
        <td style="border:1px solid black;"></td>
        <td style="border:1px solid black;"></td>
    </tr>

    @php
        $rowCount = is_array($nama[$g] ?? null) ? count($nama[$g]) : 0;

        // bucket dinamis: label -> daftar index item
        $buckets = [];
        for ($i = 0; $i < $rowCount; $i++) {
            $lab = trim($jenis[$g][$i] ?? '');
            $key = ($lab === '') ? 'Lainnya' : $lab;
            $buckets[$key] = $buckets[$key] ?? [];
            $buckets[$key][] = $i;
        }
        // urutan label mengikuti urutan kemunculan
        $order = array_keys($buckets);
        $noJenis = 1;
    @endphp

    @foreach ($order as $label)
        {{-- LABEL JENIS (opsional, bebas) --}}
        <tr>
            <td style="border:1px solid black; padding:4px 4px 4px 12px; font-weight:bold;">
                {{ $noJenis }}. {{ $label }}
            </td>
            <td style="border:1px solid black;"></td>
            <td style="border:1px solid black;"></td>
            <td style="border:1px solid black;"></td>
            <td style="border:1px solid black;"></td>
            <td style="border:1px solid black;"></td>
        </tr>
        @php $noJenis++; @endphp

        {{-- ITEM DI BAWAH LABEL TERKAIT --}}
        @foreach ($buckets[$label] as $i)
            <tr>
                <td style="border:1px solid black; padding:4px 4px 4px 16px;">
                    {{ $nama[$g][$i] ?? '' }}
                </td>
                <td style="border:1px solid black; text-align:center;">
                    {{ isset($qty[$g][$i]) ? rtrim(rtrim(number_format((float)$qty[$g][$i], 3, ',', '.'), '0'), ',') : '' }}
                </td>
                <td style="border:1px solid black; text-align:center;">
                    {{ $satuan[$g][$i] ?? '' }}
                </td>
                <td style="border:1px solid black; text-align:right; padding-right:6px;">
                    {{ isset($harga_satuan[$g][$i]) ? number_format((float)$harga_satuan[$g][$i], 0, ',', '.') : '' }}
                </td>
                <td style="border:1px solid black; text-align:right; padding-right:6px;">
                    {{ isset($harga_total[$g][$i]) ? number_format((float)$harga_total[$g][$i], 0, ',', '.') : '' }}
                </td>
                <td style="border:1px solid black; padding:4px;">
                    {{ $keterangan[$g][$i] ?? '' }}
                </td>
            </tr>
        @endforeach
    @endforeach

@empty
    <tr>
        <td style="border:1px solid black; text-align:center;">{{ $hpp->outline_agreement ?? '' }}</td>
        <td colspan="6" style="border:1px solid black; text-align:center; padding:6px;">Tidak ada data</td>
    </tr>
@endforelse

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
        <!-- Kolom Catatan User Peminta -->
        <td style="width: 30%; border: 1px solid black; vertical-align: top; padding: 10px;">
            <strong>Catatan User Peminta (jika ada):</strong>
            <br>
            @if(!empty($hpp->requesting_notes))
                @foreach(json_decode($hpp->requesting_notes, true) as $index => $noteData)
                    <div style="margin-bottom: 5px;">
                        <strong>{{ $index + 1 }}.</strong> {{ $noteData['note'] ?? 'Tidak ada catatan' }}
                        <br>
                        <small>
                            @php
                                $user = \App\Models\User::find($noteData['user_id']);
                            @endphp
                            @if($user)
                                <em>Ditambahkan oleh: {{ $user->jabatan }}</em>
                            @else
                                <em>Nama User Tidak Tersedia</em>
                            @endif
                        </small>
                    </div>
                @endforeach
            @else
                <br><br><br><br> <!-- Jika tidak ada catatan, tambahkan spasi -->
            @endif
        </td>

        <!-- Kolom Catatan Pengendali -->
        <td style="width: 30%; border: 1px solid black; vertical-align: top; padding: 10px;">
            <strong>Catatan Pengendali (jika ada):</strong>
            <br>
            @if(!empty($hpp->controlling_notes))
                @foreach(json_decode($hpp->controlling_notes, true) as $index => $noteData)
                    <div style="margin-bottom: 5px;">
                        <strong>{{ $index + 1 }}.</strong> {{ $noteData['note'] ?? 'Tidak ada catatan' }}
                        <br>
                        <small>
                            @php
                                $user = \App\Models\User::find($noteData['user_id']);
                            @endphp
                            @if($user)
                                <em>Ditambahkan oleh: {{ $user->jabatan }}</em>
                            @else
                                <em>Nama User Tidak Tersedia</em>
                            @endif
                        </small>
                    </div>
                @endforeach
            @else
                <br><br><br><br> <!-- Jika tidak ada catatan, tambahkan spasi -->
            @endif
        </td>

        <!-- Kolom Tanda Tangan GM Saja -->
        <td class="px-2 py-2" style="width: 30%; border: 1px solid black;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td class="px-2 py-2 text-center" style="border-bottom: 1px solid black; font-weight: bold; font-style: italic;">
                        FUNGSI PENGENDALI
                    </td>
                </tr>
                <tr>
                    <td class="px-2 py-4 text-center" style="border-bottom: 1px solid black;">
                        <strong>GM OF</strong> {{ $hpp->generalManagerSignatureUser ? $hpp->generalManagerSignatureUser->departemen : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <!-- Tanda tangan GM -->
                    <td class="px-2 py-3 text-center" style="vertical-align: bottom;">
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 90px; min-height: 90px;">
                        @if(file_exists(storage_path("app/public/signatures/hpp/general_manager_signature_{$hpp->notification_number}.png")))
                            <img src="{{ storage_path("app/public/signatures/hpp/general_manager_signature_{$hpp->notification_number}.png") }}" 
                                style="width: 180px; height: 100px; object-fit: contain;">
                        @else
                            <strong style="font-size: 20px; font-weight: bold;">TTD</strong>
                        @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-2 py-2 text-center" style="border-bottom: 1px solid black;">
                        <strong>{{ $hpp->generalManagerSignatureUser ? $hpp->generalManagerSignatureUser->name : 'N/A / TTD' }}</strong>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
