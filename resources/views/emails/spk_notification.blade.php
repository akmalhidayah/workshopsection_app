<!DOCTYPE html>
<html>
<head>
    <title>Permintaan Approval SPK untuk Pekerjaan Jasa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            margin: 0 auto;
            padding: 20px;
            max-width: 600px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            margin-top: 20px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.5;
        }
        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Permintaan Approval SPK untuk Pekerjaan Jasa
        </div>
        <div class="content">
            <p>Nomor SPK : <strong>{{ $spk->nomor_spk }}</strong></p>
            <p>Perihal : <strong>{{ $spk->perihal }}</strong></p>
            <p>Tanggal SPK : <strong>{{ $spk->created_at->format('d-m-Y') }}</strong></p>
            <p>Silakan login ke sistem dan tanda tangani dokumen SPK untuk pekerjaan jasa fabrikasi, konstruksi, dan pengerjaan mesin.</p>
            <a href="{{ url('/approval/spk') }}" class="button">Klik di sini untuk melihat & menandatangani dokumen</a>
        </div>
    </div>
</body>
</html>
