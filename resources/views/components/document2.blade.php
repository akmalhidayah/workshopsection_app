<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
    @page {
        size: A4 landscape; /* Gunakan orientasi lanskap */
        margin: 0; /* Hilangkan margin default */
    }

    body {
        
        margin: 0;
        padding: 0;
        background: white !important;
        font-size: 2px;
    }

    .container {
        width: 100%;
        max-width: 100%;
    }

    .max-w-full {
        max-width: 100% !important;
    }

    .shadow-lg {
        box-shadow: none !important; /* Hilangkan shadow jika mengganggu layout */
    }

    .p-6 {
        padding: 0 !important; /* Kurangi padding agar tidak ada spasi berlebih */
    }
    
}

    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen bg-white flex items-center justify-center">
        <div class="bg-white max-w-7xl w-full p-6">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
