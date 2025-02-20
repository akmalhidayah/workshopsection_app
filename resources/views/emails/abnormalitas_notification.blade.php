<!DOCTYPE html>
<html>
<head>
    <title>Permintaan Approval Pekerjaan Jasa Fabrikasi Konstruksi dan Pengerjaan Mesin</title>
</head>
<body>
    <h1>Permintaan Approval Pekerjaan Jasa Fabrikasi Konstruksi dan Pengerjaan Mesin</h1>
    <p>Nomor Notifikasi :<strong>{{ $abnormal->notification_number }}</strong></p>
    <p>Nama Pekerjaan : <strong>{{ $abnormal->abnormal_title }}</strong> telah dibuat.</p>
    <p>Silakan login ke sistem dan tanda tangani dokumen abnormalitas:</p>
    <a href="{{ url('/approval') }}">Klik di sini untuk melihat & menandatangani dokumen</a>
</body>
</html>
