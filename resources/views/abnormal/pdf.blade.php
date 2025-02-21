<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abnormalitas Analysis</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 5px; }
        .header { text-align: center; font-weight: bold; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        .bg-gray { background-color: #e5e7eb; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .signature { text-align: center; margin-top: 20px; }
        .logo { width: 80px; height: auto; }
    </style>
</head>
<body>
<!-- Header -->
<table style="width: 100%; background-color: #f7f8fa; padding: 15px; border-radius: 8px; border-collapse: collapse; border: none;">
    <tr style="border: none;">
        <td style="vertical-align: middle; border: none;">
            <h2 style="font-size: 16px; font-weight: bold; margin: 0; color: #333;">FORM</h2>
            <h1 style="font-size: 24px; font-weight: bold; margin: 5px 0; color: #111;">Abnormalitas Analysis</h1>
        </td>
        <td style="width: 100px; text-align: right; border: none;">
            <img src="{{ public_path('images/logo-st2.png') }}" alt="Logo" style="height: 70px; width: auto;">
        </td>
    </tr>
</table>
<!-- Informasi Abnormalitas -->
<table style="width: 100%; border-collapse: collapse; border: none; margin-top: 10px;">
    <tr style="border: none;">
        <td style="background-color: #2d3748; color: white; padding: 10px; font-weight: bold; width: 20%;">Abnormal Title</td>
        <td style="padding: 10px; width: 30%;">{{ $abnormal->abnormal_title }}</td>
        <td style="background-color: #2d3748; color: white; padding: 10px; font-weight: bold; width: 20%;">Order No</td>
        <td style="padding: 10px; width: 30%;">{{ $abnormal->notification_number }}</td>
    </tr>
    <tr style="border: none;">
        <td style="background-color: #2d3748; color: white; padding: 10px; font-weight: bold;">Section / Unit</td>
        <td style="padding: 10px;">{{ $abnormal->unit_kerja }}</td>
        <td style="background-color: #2d3748; color: white; padding: 10px; font-weight: bold;">Abnormal Date</td>
        <td style="padding: 10px;">{{ $abnormal->abnormal_date }}</td>
    </tr>
</table>

<!-- Abnormalitas Summary -->
<div style="margin-top: 15px;">
    <h3 style="font-size: 18px; font-weight: bold; color: #111;">Abnormalitas Summary</h3>
    <table style="width: 100%; border-collapse: collapse; border: none;">
        <tr style="border: none;">
            <td style="background-color: #2d3748; color: white; padding: 10px; font-weight: bold; width: 30%;">
                What happened?<br>
                <span style="font-size: 12px; color: #ddd;">Problem Description*</span>
            </td>
            <td style="padding: 10px;">{{ $abnormal->problem_description }}</td>
        </tr>
        <tr style="border: none;">
            <td style="background-color: #2d3748; color: white; padding: 10px; font-weight: bold;">
                Why did this happen?<br>
                <span style="font-size: 12px; color: #ddd;">Root Cause*</span>
            </td>
            <td style="padding: 10px;">{{ $abnormal->root_cause }}</td>
        </tr>
        <tr style="border: none;">
            <td style="background-color: #2d3748; color: white; padding: 10px; font-weight: bold;">
                What was done about it?<br>
                <span style="font-size: 12px; color: #ddd;">Immediate Actions*</span>
            </td>
            <td style="padding: 10px;">{{ $abnormal->immediate_actions }}</td>
        </tr>
        <tr style="border: none;">
            <td style="background-color: #2d3748; color: white; padding: 10px; font-weight: bold;">
                How can we stop it happening again?<br>
                <span style="font-size: 12px; color: #ddd;">Summary of Recommendations*</span>
            </td>
            <td style="padding: 10px;">{{ $abnormal->summary }}</td>
        </tr>
    </table>
</div>
<!-- Actions Required -->
<div style="margin-top: 15px;">
    <h3 style="font-size: 18px; font-weight: bold; color: #111;">Actions Required</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #2d3748; color: white;">
                <th style="padding: 10px; text-align: center; width: 5%;">No</th>
                <th style="padding: 10px;">Action</th>
                <th style="padding: 10px;">By</th>
                <th style="padding: 10px;">When</th>
            </tr>
        </thead>
        <tbody>
            @foreach($abnormal->actions as $index => $action)
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 10px; text-align: center;">{{ $index + 1 }}</td>
                    <td style="padding: 10px;">{{ $action['action'] }}</td>
                    <td style="padding: 10px;">{{ $action['by'] }}</td>
                    <td style="padding: 10px;">{{ $action['when'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Equipment Risk -->
<div style="margin-top: 15px;">
    <h3 style="font-size: 18px; font-weight: bold; color: #111;">Equipment Risk</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #2d3748; color: white;">
                <th style="padding: 10px; text-align: center; width: 5%;">No</th>
                <th style="padding: 10px;">Risk</th>
            </tr>
        </thead>
        <tbody>
            @foreach($abnormal->risks as $index => $risk)
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 10px; text-align: center;">{{ $index + 1 }}</td>
                    <td style="padding: 10px;">{{ $risk }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Tabel untuk Tanda Tangan Persetujuan -->
<div style="margin-top: 20px;">
    <h3 style="font-size: 18px; font-weight: bold; color: #111;">
        Approved by User
    </h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tbody>
            <!-- Tanda Tangan Manager -->
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="background-color: #2d3748; color: white; padding: 10px; text-align: center; width: 20%;">Reviewed by</td>
                <td style="padding: 10px;">
                    <strong>{{ $abnormal->approved_by_manager }}</strong><br>
                    {{ $abnormal->managerUser->name ?? 'N/A' }} <br>
                    <p style="margin: 0;">Mgr of {{ $abnormal->managerUser->seksi ?? 'N/A' }}</p>
                </td>
                <td style="background-color: #2d3748; color: white; padding: 10px; text-align: center; width: 20%;">Approval</td>
                <td style="padding: 10px; text-align: center;">
                    @php
                        $managerSignaturePath = asset('storage/signatures/abnormalitas/manager_signature_' . $abnormal->notification_number . '.png');
                    @endphp
                    @if(!empty($abnormal->manager_signature) && file_exists(public_path('storage/signatures/abnormalitas/manager_signature_' . $abnormal->notification_number . '.png')))
                    <img src="{{ public_path('storage/signatures/abnormalitas/manager_signature_' . $abnormal->notification_number . '.png') }}" alt="Tanda Tangan Manager" style="width: 100px; height: auto;">
                    @else
                        <span style="color: red;">Belum Ditandatangani</span>
                    @endif
                </td>
            </tr>

            <!-- Tanda Tangan Senior Manager -->
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="background-color: #2d3748; color: white; padding: 10px; text-align: center; width: 20%;">Reviewed by</td>
                <td style="padding: 10px;">
                    <strong>{{ $abnormal->approved_by_senior_manager }}</strong><br>
                    {{ $abnormal->seniorManagerUser->name ?? 'N/A' }} <br>
                    <p style="margin: 0;">SM of {{ $abnormal->seniorManagerUser->unit_work ?? 'N/A' }}</p>
                </td>
                <td style="background-color: #2d3748; color: white; padding: 10px; text-align: center; width: 20%;">Approval</td>
                <td style="padding: 10px; text-align: center;">
                    @php
                        $seniorManagerSignaturePath = asset('storage/signatures/abnormalitas/senior_manager_signature_' . $abnormal->notification_number . '.png');
                    @endphp
                    @if(!empty($abnormal->senior_manager_signature) && file_exists(public_path('storage/signatures/abnormalitas/senior_manager_signature_' . $abnormal->notification_number . '.png')))
                    <img src="{{ public_path('storage/signatures/abnormalitas/senior_manager_signature_' . $abnormal->notification_number . '.png') }}" alt="Tanda Tangan Manager" style="width: 100px; height: auto;">
                    @else
                        <span style="color: red;">Belum Ditandatangani</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Dokumentasi Abnormalitas -->
@if($abnormal->files)
    <div style="page-break-before: always; margin-top: 20px;">
        <h3 style="font-size: 18px; font-weight: bold; color: #111;">Dokumentasi Abnormalitas</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #2d3748; color: white;">
                    <th style="padding: 10px; text-align: center; width: 40%;">Foto Temuan Abnormalitas</th>
                    <th style="padding: 10px; width: 60%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($abnormal->files as $file)
                <tr>
                    <td style="text-align: center; padding: 10px;">
                    <img src="{{ public_path('storage/abnormalitas/' . $file['file_path']) }}">
                    </td>
                    <td style="padding: 10px;">{{ $file['keterangan'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
</body>
</html>
