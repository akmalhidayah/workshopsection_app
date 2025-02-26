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
                            @if(!empty($hpp->senior_manager_signature_requesting_unit) && file_exists(public_path(str_replace(asset('/'), '', $hpp->senior_manager_signature_requesting_unit))))
                            <img src="{{ asset('storage/signatures/hpp/manager_signature_' . $hpp->notification_number . '.png') }}" 
                                    alt="TTD SM" 
                                    style="width: 150px; height: 70px; object-fit: contain;">
                            @else
                                <strong style="font-size: 20px; font-weight: bold;">TTD</strong>
                            @endif
                        </td>

                        <!-- Tanda Tangan MGR -->
                        <td style="padding: 4px; text-align: center; vertical-align: bottom; height: 70px;">
                            @if(!empty($hpp->manager_signature_requesting_unit) && file_exists(public_path(str_replace(asset('/'), '', $hpp->manager_signature_requesting_unit))))
                            <img src="{{ asset('storage/signatures/hpp/senior_manager_signature_' . $hpp->notification_number . '.png') }}" 
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
                            <strong>{{ $hpp->seniorManagerRequestingUser->name ?? 'N/A' }}</strong>
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


<!-- TABEL HPP -->
<div class="overflow-x-auto">
    <table class="table-hpp" style="width: 100%; border: 1px solid black; border-collapse: collapse;">
        <thead style="background-color: #B0C4DE; color: #333;">
            <tr>
            <th rowspan="3" style="border: 1px solid black; padding: 5px; background-color: #B0C4DE; text-align: center; vertical-align: middle; font-weight: bold;">
                OUTLINE AGREEMENT (OA)
            </th>
            <th rowspan="3" style="border: 1px solid black; padding: 5px; background-color: #B0C4DE; text-align: center; vertical-align: middle; font-weight: bold;">
                URAIAN PEKERJAAN
            </th>
            <th rowspan="3" style="border: 1px solid black; padding: 5px; background-color: #B0C4DE; text-align: center; vertical-align: middle; font-weight: bold;">
                Qty
            </th>
            <th rowspan="3" style="border: 1px solid black; padding: 5px; background-color: #B0C4DE; text-align: center; vertical-align: middle; font-weight: bold;">
                Satuan
            </th>
            <th rowspan="3" style="border: 1px solid black; padding: 5px; background-color: #B0C4DE; text-align: center; vertical-align: middle; font-weight: bold;">
                Volume Satuan
            </th>
            <th rowspan="2" style="border: 1px solid black; padding: 5px; background-color: #B0C4DE; text-align: center; vertical-align: middle; font-weight: bold;">
                Jumlah Volume Satuan
            </th>
            <th colspan="3" style="border: 1px solid black; padding: 5px; background-color: #B0C4DE; text-align: center; vertical-align: middle; font-weight: bold;">
                Harga Satuan
            </th>
            <th colspan="3" style="border: 1px solid black; padding: 5px; background-color: #B0C4DE; text-align: center; vertical-align: middle; font-weight: bold;">
                Jumlah Harga Satuan
            </th>
            <th rowspan="2" style="border: 1px solid black; padding: 5px; background-color: #B0C4DE; text-align: center; vertical-align: middle; font-weight: bold;">
                Harga Total
            </th>
            <th rowspan="3" style="border: 1px solid black; padding: 5px; background-color: #B0C4DE; text-align: center; vertical-align: middle; font-weight: bold;">
                Keterangan
            </th>
            </tr>
            <tr>
                <th style="border: 1px solid black; padding: 5px; background-color: #B0C4DE;">Material</th>
                <th style="border: 1px solid black; padding: 5px; background-color: #B0C4DE;">Consumable</th>
                <th style="border: 1px solid black; padding: 5px; background-color: #B0C4DE;">Upah Kerja</th>
                <th style="border: 1px solid black; padding: 5px; background-color: #B0C4DE;">Material</th>
                <th style="border: 1px solid black; padding: 5px; background-color: #B0C4DE;">Consumable</th>
                <th style="border: 1px solid black; padding: 5px; background-color: #B0C4DE;">Upah Kerja</th>
            </tr>
            <tr>
                <th style="border: 1px solid black; padding: 5px; background-color: #FFD700;">(1)</th>
                <th style="border: 1px solid black; padding: 5px; background-color: #FFD700;">(2)</th>
                <th style="border: 1px solid black; padding: 5px; background-color: #FFD700;">(3)</th>
                <th style="border: 1px solid black; padding: 5px; background-color: #FFD700;">(4)</th>
                <th style="border: 1px solid black; padding: 5px; background-color: #FFD700;">A=(1)*(2)</th>
                <th style="border: 1px solid black; padding: 5px; background-color: #FFD700;">B=(1)*(3)</th>
                <th style="border: 1px solid black; padding: 5px; background-color: #FFD700;">C=(1)*(4)</th>
                <th style="border: 1px solid black; padding: 5px; background-color: #FFD700;">A+B+C</th>
            </tr>
        </thead>
        <tbody>
    @foreach($uraian_pekerjaan as $index => $uraian)
        <tr>
            @if($index === 0)
                <td rowspan="{{ count($uraian_pekerjaan) }}" style="border: 1px solid black; padding: 5px; text-align: center;">
                    {{ ($hpp->outline_agreement !== '-' && $hpp->outline_agreement !== '') ? $hpp->outline_agreement : '' }}
                </td>
            @endif
            <td style="border: 1px solid black; padding: 5px;">
                @if(str_contains(strtolower($uraian), 'bubut kecil'))
                    {{ $uraian !== '-' && $uraian !== '' ? $uraian : '' }} 
                @else
                    <strong>{{ $uraian !== '-' && $uraian !== '' ? $uraian : '' }}</strong><br> 
                    {{ $jenis_material[$index] !== '-' && $jenis_material[$index] !== '' ? $jenis_material[$index] : '' }}
                @endif
            </td>
            <td style="border: 1px solid black; padding: 5px; text-align: center;">
                {{ ($qty[$index] !== '-' && $qty[$index] !== '' && $qty[$index] != 0) ? $qty[$index] : '' }}
            </td>
            <td style="border: 1px solid black; padding: 5px; text-align: center;">
                {{ ($satuan[$index] !== '-' && $satuan[$index] !== '' && $satuan[$index] != 0) ? $satuan[$index] : '' }}
            </td>
            <td style="border: 1px solid black; padding: 5px; text-align: center;">
                {{ ($volume_satuan[$index] !== '-' && $volume_satuan[$index] !== '' && $volume_satuan[$index] != 0) ? $volume_satuan[$index] : '' }}
            </td>
            <td style="border: 1px solid black; padding: 5px; text-align: center;">
                {{ ($jumlah_volume_satuan[$index] !== '-' && $jumlah_volume_satuan[$index] !== '' && $jumlah_volume_satuan[$index] != 0) ? $jumlah_volume_satuan[$index] : '' }}
            </td>
            <td style="border: 1px solid black; padding: 5px; text-align: center;">
                {{ ($harga_material[$index] !== '-' && $harga_material[$index] !== '' && $harga_material[$index] != 0) ? number_format((float)$harga_material[$index], 0, ',', '.') : '' }}
            </td>
            <td style="border: 1px solid black; padding: 5px; text-align: center;">
                {{ ($harga_consumable[$index] !== '-' && $harga_consumable[$index] !== '' && $harga_consumable[$index] != 0) ? number_format((float)$harga_consumable[$index], 0, ',', '.') : '' }}
            </td>
            <td style="border: 1px solid black; padding: 5px; text-align: center;">
                {{ ($harga_upah[$index] !== '-' && $harga_upah[$index] !== '' && $harga_upah[$index] != 0) ? number_format((float)$harga_upah[$index], 0, ',', '.') : '' }}
            </td>
            <td style="border: 1px solid black; padding: 5px; text-align: center;">
                {{ ($jumlah_harga_material[$index] !== '-' && $jumlah_harga_material[$index] !== '' && $jumlah_harga_material[$index] != 0) ? number_format((float)$jumlah_harga_material[$index], 0, ',', '.') : '' }}
            </td>
            <td style="border: 1px solid black; padding: 5px; text-align: center;">
                {{ ($jumlah_harga_consumable[$index] !== '-' && $jumlah_harga_consumable[$index] !== '' && $jumlah_harga_consumable[$index] != 0) ? number_format((float)$jumlah_harga_consumable[$index], 0, ',', '.') : '' }}
            </td>
            <td style="border: 1px solid black; padding: 5px; text-align: center;">
                {{ ($jumlah_harga_upah[$index] !== '-' && $jumlah_harga_upah[$index] !== '' && $jumlah_harga_upah[$index] != 0) ? number_format((float)$jumlah_harga_upah[$index], 0, ',', '.') : '' }}
            </td>
            <td style="border: 1px solid black; padding: 5px; text-align: center;">
                {{ ($harga_total[$index] !== '-' && $harga_total[$index] !== '' && $harga_total[$index] != 0) ? number_format((float)$harga_total[$index], 0, ',', '.') : '' }}
            </td>
            <td style="border: 1px solid black; padding: 5px;">
                {{ ($keterangan[$index] !== '-' && $keterangan[$index] !== '' && $keterangan[$index] != 0) ? $keterangan[$index] : '' }}
            </td>
        </tr>
    @endforeach
    <!-- Baris Total -->
    <tr style="font-weight: bold; background-color: #DCDCDC;">
        <td colspan="12" class="border border-gray-400 px-2 py-1 text-center font-bold bg-gray-200">TOTAL</td>
        <td colspan="1" class="border border-gray-400 px-2 py-1 text-center bg-gray-200">
            <strong>{{ ($hpp->total_amount !== '-' && $hpp->total_amount !== '' && $hpp->total_amount != 0) ? number_format($hpp->total_amount, 0, ',', '.') : '' }}</strong>
        </td>
        <td colspan="1" class="border border-gray-400 px-2 py-1 text-center bg-gray-200"></td>
    </tr>
</tbody>
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
                            @if(!empty($hpp->general_manager_signature) && file_exists(public_path(str_replace(asset('/'), '', $hpp->general_manager_signature))))
                                <div style="display: inline-block; width: 160px; height: 80px;">
                                <img src="{{ asset('storage/signatures/hpp/general_manager_signature_' . $hpp->notification_number . '.png') }}" 
                                        alt="GM Signature" 
                                        style="width: 180px; height: 100px; object-fit: contain; filter: drop-shadow(3px 3px 5px black);">
                                </div>
                            @else
                                <strong style="font-size: 22px; font-weight: bold;">TTD</strong>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-2 py-2 text-center" style="border-bottom: 1px solid black;">
                        <strong>{{ $hpp->generalManagerSignatureUser ? $hpp->generalManagerSignatureUser->name : 'N/A' }}</strong>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
