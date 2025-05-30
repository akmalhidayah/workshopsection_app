<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->
    <link rel="stylesheet" href="{{ asset('build/assets/app-BD6FMr64.css') }}">
    <script src="{{ asset('build/assets/app-CH09qwMe.js') }}"></script> 
    
    </head>
    <body class="font-sans text-gray-900 antialiased bg-cover bg-center" style="background-image: url('{{ asset('images/bg-login.jpg') }}')">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-none">

            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        @if (session('error'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Pendaftaran Ditolak!',
            text: '{{ session('error') }}',
            confirmButtonColor: '#3085d6'
        });
    </script>
@endif

    </body>
</html>
