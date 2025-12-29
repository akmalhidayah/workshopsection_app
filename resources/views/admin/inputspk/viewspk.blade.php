<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK - {{ $spk->nomor_spk }}</title>

    <style>
        @page { margin: 10mm; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            padding: 4px;
            vertical-align: top;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }

        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
        }

        /* ================= SIGNATURE ================= */
        .sig-cell {
            position: relative;
            height: 150px;
            padding-top: 18px;
        }

        .sig-date-top {
            position: absolute;
            top: 4px;
            right: 6px;
            font-size: 9px;
            font-weight: bold;
        }

        .sig-box {
            height: 100px;
            position: relative;
        }

        .sig-box img {
            max-height: 95px;
            max-width: 220px;
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
        }

        .sig-name {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            margin-top: 6px;
        }

        .sig-role {
            font-size: 10px;
            text-align: center;
            font-weight: bold;
        }

        .border {
            border: 1px solid #000;
        }
    </style>
</head>

@php
    use Illuminate\Support\Facades\Storage;

    $sig = fn ($path) =>
        $path && Storage::disk('public')->exists($path)
            ? storage_path('app/public/'.$path)
            : null;
@endphp

<body>

{{-- ================= HEADER ================= --}}
<table>
    <tr>
        <td style="width:20%">
            <img src="{{ public_path('images/logo-st.png') }}" style="height:60px">
        </td>
        <td style="width:60%" class="text-center">
            <div class="title">SURAT PERINTAH KERJA (SPK)</div>
            <div class="subtitle">JASA FABRIKASI, KONSTRUKSI & MESIN</div>
        </td>
        <td style="width:20%"></td>
    </tr>
</table>

<br>

{{-- ================= INFO SPK ================= --}}
<table>
    <tr><td class="bold" width="25%">KEPADA YTH</td><td>: {{ $spk->kepada_yth ?? 'PT. PRIMA KARYA MANUNGGAL' }}</td></tr>
    <tr><td class="bold">PERIHAL</td><td>: {{ $spk->perihal }}</td></tr>
    <tr><td class="bold">NOMOR SPK</td><td>: {{ $spk->nomor_spk }}</td></tr>
    <tr><td class="bold">NOMOR ORDER</td><td>: {{ $spk->notification_number }}</td></tr>
    <tr><td class="bold">UNIT KERJA PEMINTA</td><td>: {{ $spk->unit_work }}</td></tr>
</table>

<br>

{{-- ================= TABEL PEKERJAAN ================= --}}
<table class="border">
    <thead>
        <tr class="bold text-center">
            <th class="border">No</th>
            <th class="border">Functional Location</th>
            <th class="border">Scope Pekerjaan</th>
            <th class="border">Qty</th>
            <th class="border">Stn</th>
            <th class="border">Keterangan</th>
        </tr>
    </thead>
    <tbody>
    @foreach($spk->functional_location ?? [] as $i => $loc)
        <tr>
            <td class="border text-center">{{ $i+1 }}</td>
            <td class="border">{{ $loc }}</td>
            <td class="border">{{ $spk->scope_pekerjaan[$i] ?? '-' }}</td>
            <td class="border text-center">{{ $spk->qty[$i] ?? '-' }}</td>
            <td class="border text-center">{{ $spk->stn[$i] ?? '-' }}</td>
            <td class="border">{{ $spk->keterangan[$i] ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<br>

<strong>Keterangan Pekerjaan:</strong><br>
{{ $spk->keterangan_pekerjaan ?? '-' }}

<br><br>

{{-- ================= TANDA TANGAN ================= --}}
<table class="border" style="width:70%">
    <tr>
        <td colspan="2" class="text-center bold border">
            PT. SEMEN TONASA – UNIT WORKSHOP
        </td>
    </tr>
    <tr>

        {{-- SENIOR MANAGER --}}
        <td class="border sig-cell">
            <div class="sig-date-top">
                {{ $spk->senior_manager_signed_at?->format('d/m/Y') ?? '-' }}
            </div>
            <div class="sig-box">
                @if($p = $sig($spk->senior_manager_signature))
                    <img src="{{ $p }}">
                @endif
            </div>
            <div class="sig-name">
                {{ $spk->seniorManagerSignatureUser?->name ?? '-' }}
            </div>
            <div class="sig-role">Senior Manager Workshop</div>
        </td>

        {{-- MANAGER --}}
        <td class="border sig-cell">
            <div class="sig-date-top">
                {{ $spk->manager_signed_at?->format('d/m/Y') ?? '-' }}
            </div>
            <div class="sig-box">
                @if($p = $sig($spk->manager_signature))
                    <img src="{{ $p }}">
                @endif
            </div>
            <div class="sig-name">
                {{ $spk->managerSignatureUser?->name ?? '-' }}
            </div>
            <div class="sig-role">Manager Workshop</div>
        </td>

    </tr>
</table>

</body>
</html>
