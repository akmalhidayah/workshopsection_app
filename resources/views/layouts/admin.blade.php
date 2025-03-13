<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Title -->
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->
    <link rel="stylesheet" href="{{ asset('build/assets/app-CmMamunY.css') }}">
    <script src="{{ asset('build/assets/app-CH09qwMe.js') }}"></script> 

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>

    <!-- Import Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="font-sans antialiased bg-gray-100">

<!-- Sidebar -->
<div x-data="{ open: true }" class="relative min-h-screen flex bg-gray-200">
    <!-- Tombol Toggle Sidebar -->
    <button @click="open = !open" class="fixed top-4 left-4 z-20 bg-blue-600 text-white p-2 rounded">
        <i x-show="open" class="fas fa-times text-xl"></i>
        <i x-show="!open" class="fas fa-bars text-xl"></i>
    </button>

    <!-- Sidebar Section -->
    <aside 
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform -translate-x-full"
    x-transition:enter-end="opacity-100 transform translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-x-0"
    x-transition:leave-end="opacity-0 transform -translate-x-full"
    class="fixed top-0 left-0 h-full z-10 w-56 px-2 py-4 shadow-lg text-blue-100 bg-blue-900 text-sm">

        
        <!-- Sidebar Header -->
        <div class="flex items-center space-x-2 px-2">
            <a href="#">
                <x-application-logo class="block h-9 w-auto fill-current text-gray-200" />
            </a>
            <div>
                <span class="text-2xl font-extrabold text-gray-200 block">BMS</span>
                <span class="text-xl font-extrabold text-white block">Dashboard</span>
            </div>
        </div>
        <nav class="mt-5">
    <!-- Dashboard Icon -->
    <a href="{{ route('admin.dashboard') }}"
       class="block px-4 py-2 mt-2 text-xs font-semibold text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.dashboard') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-chart-pie mr-2 text-lg group-hover:text-blue-300 transition duration-200"></i>
        <span>Dashboard</span>
    </a>

    <!-- Notifikasi Icon -->
    <a href="{{ route('notifikasi.index') }}"
       class="block px-4 py-2 mt-2 text-xs font-semibold text-white rounded-lg flex items-center group
              {{ request()->routeIs('notifikasi.index') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-envelope-open-text mr-2 text-lg group-hover:text-blue-300 transition duration-200"></i>
        <span>Notifikasi</span>
    </a>

    <!-- Input HPP Icon -->
    <a href="{{ route('admin.inputhpp.index') }}"
       class="block px-4 py-2 mt-2 text-xs font-semibold text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.inputhpp.index') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-pencil-alt mr-2 text-lg group-hover:text-blue-300 transition duration-200"></i>
        <span>Create HPP</span>
    </a>

    <!-- Verifikasi Anggaran Icon -->
    <a href="{{ route('admin.verifikasianggaran.index') }}"
       class="block px-4 py-2 mt-2 text-xs font-semibold text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.verifikasianggaran.index') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-money-check-alt mr-2 text-lg group-hover:text-blue-300 transition duration-200"></i>
        <span>Verifikasi Anggaran</span>
    </a>

    <!-- PR / PO Icon -->
    <a href="{{ route('admin.purchaseorder') }}"
       class="block px-4 py-2 mt-2 text-xs font-semibold text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.purchaseorder') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-tasks mr-2 text-lg group-hover:text-blue-300 transition duration-200"></i>
        <span>PR / PO</span>
    </a>

    <!-- LHPP Icon -->
    <a href="{{ route('admin.lhpp.index') }}"
   class="block px-4 py-2 mt-2 text-xs font-semibold text-white rounded-lg flex items-center group
          {{ request()->routeIs('admin.lhpp.index') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
   @click="open = false">
    <i class="fas fa-file-alt mr-2 text-lg group-hover:text-blue-300 transition duration-200"></i>
    <span>LHPP</span>
</a>



    <!-- LPJ Icon -->
    <a href="{{ route('admin.lpj') }}"
       class="block px-4 py-2 mt-2 text-xs font-semibold text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.lpj') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-folder-open mr-2 text-lg group-hover:text-blue-300 transition duration-200"></i>
        <span>LPJ/PPL</span>
    </a>

    <!-- Kuota Anggaran & OA Icon -->
    <a href="{{ route('admin.updateoa') }}"
       class="block px-4 py-2 mt-2 text-xs font-semibold text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.updateoa') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-clipboard-list mr-2 text-lg group-hover:text-blue-300 transition duration-200"></i>
        <span>Kuota Anggaran & OA</span>
    </a>

    <!-- User Panel Icon -->
    <a href="{{ route('admin.users.index') }}"
       class="block px-4 py-2 mt-2 text-xs font-semibold text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.users.index') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-user-circle mr-2 text-lg group-hover:text-blue-300 transition duration-200"></i>
        <span>User Panel</span>
    </a>
    <!-- Upload Informasi Icon -->
    <a href="{{ route('admin.uploadinfo') }}"
    class="block px-4 py-2 mt-2 text-xs font-semibold text-white rounded-lg flex items-center group
            {{ request()->routeIs('admin.uploadinfo') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
    @click="open = false">
        <i class="fas fa-upload mr-2 text-lg group-hover:text-blue-300 transition duration-200"></i>
        <span>Upload Informasi</span>
    </a>
</nav>
</aside>
<!-- Main content -->
<div :class="open ? 'ml-56' : 'ml-0'" class="flex-1 flex flex-col transition-all duration-300 bg-gray-100 overflow-y-auto">
    <!-- Top Navigation -->
    <nav class="shadow-lg bg-blue-900">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="relative flex items-center justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo SIG di sebelah kiri -->
                    <img src="{{ asset('images/logo-sig.png') }}" alt="SIG Logo" class="h-10 w-auto mr-2">
                    <!-- Logo Semen Tonasa di sebelah kiri -->
                    <img src="{{ asset('images/logo-st2.png') }}" alt="Semen Tonasa Logo" class="h-10 w-auto mr-2">
                    <!-- Text Section (Hidden di Mobile) -->
                    <div class="hidden sm:flex flex-col text-white">
                        <span class="font-bold text-lg">SECTION OF WORKSHOP</span>
                        <span class="text-sm">Dept. Of Project Management & Main Support</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Icon Lonceng Notifikasi -->
                    <div class="relative">
                        <button class="text-white focus:outline-none relative">
                            <i class="fas fa-bell text-xl"></i>
                            <!-- Badge Notifikasi Dinamis -->
                            <span x-show="true" 
                                  class="absolute top-0 right-0 inline-flex items-center justify-center w-4 h-4 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                                  3 <!-- Ganti angka sesuai jumlah notifikasi -->
                            </span>
                        </button>
                    </div>

                    <!-- Dropdown Profil di Sebelah Kanan -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-500 bg-white hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                <!-- Icon Profile -->
                                <i class="fas fa-user-circle text-xl mr-2 text-blue-500"></i>
                                <div class="hidden sm:block">Welcome {{ Auth::user()->name }}</div>
                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                                 onclick="event.preventDefault();
                                                 this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 p-5 bg-gray-100 overflow-y-auto">
        {{ $slot }}
    </main>
</div>
</body>
</html>
