<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased font-sans">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Bengkel Mesin PT. Semen Tonasa') }}</title>

    <!-- Prefetch theme (hindari kedip saat dark mode) -->
    <script>
        (function () {
            try {
                const theme = localStorage.getItem('theme');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {
                console.warn('Theme init gagal:', e);
            }
        })();
    </script>

    <!-- Fonts / icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Allow pages to push additional styles -->
    @stack('styles')

    <!-- Vite (Tailwind + app JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex flex-col">
        {{-- Top Navigation --}}
        @include('layouts.navigation')

        {{-- Page Heading --}}
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow-sm">
                <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        {{-- Page Content --}}
        <main class="flex-1 pb-8">
            {{ $slot }}
        </main>
    </div>

    {{-- ============================
        Global JS libraries (CDN)
       ============================ --}}
    {{-- jQuery (wajib sebelum Select2) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Select2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>

    {{-- SignaturePad --}}
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js" defer></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4" defer></script>

    {{-- SweetAlert2 (tanpa defer, karena dipakai langsung di script di bawah) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Flatpickr --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>

    {{-- Alpine (hapus ini kalau Alpine sudah di-import di app.js) --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js" defer></script>

    {{-- SweetAlert flash + open PDF jika session punya open_pdf --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function openPdf(url) {
                try {
                    window.open(url, '_blank');
                } catch (e) {
                    console.error('Gagal membuka PDF:', e);
                }
            }

            @if(session()->has('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses',
                    text: {!! json_encode(session('success')) !!},
                    confirmButtonText: 'OK'
                }).then(() => {
                    @if(session()->has('open_pdf'))
                        openPdf({!! json_encode(session('open_pdf')) !!});
                    @endif
                });
            @endif

            @if(session()->has('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: {!! json_encode(session('warning')) !!},
                    confirmButtonText: 'OK'
                }).then(() => {
                    @if(session()->has('open_pdf'))
                        openPdf({!! json_encode(session('open_pdf')) !!});
                    @endif
                });
            @endif

               @if(session()->has('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: {!! json_encode(session('error')) !!},
            confirmButtonText: 'OK'
        });
    @endif

    @if(isset($errors) && $errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Validasi',
            html: {!! json_encode(implode('<br>', $errors->all())) !!},
            confirmButtonText: 'Tutup'
        });
    @endif

        });
    </script>

    {{-- Allow pages/partials to push extra scripts --}}
    @stack('scripts')
</body>
</html>
