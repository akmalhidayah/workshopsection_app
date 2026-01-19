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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Vite (Tailwind + app JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
             <!-- <link rel="stylesheet" href="{{ asset('build/assets/app-CSwLQ2bl.css') }}">
    <script src="{{ asset('build/assets/app-CH09qwMe.js') }}"></script>  -->
    
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="bg-white shadow-lg sm:rounded-lg max-w-7xl w-full p-6">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
