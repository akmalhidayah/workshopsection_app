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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->
    <link rel="stylesheet" href="{{ asset('build/assets/app-BD6FMr64.css') }}">
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
    <button @click="open = !open" class="fixed top-4 left-4 z-20 bg-orange-600 text-white p-2 rounded">
        <i x-show="open" class="fas fa-times text-xl"></i>
        <i x-show="!open" class="fas fa-bars text-xl"></i>
    </button>

    <!-- Sidebar Section -->
    <aside class="fixed top-0 left-0 h-full z-10 w-56 px-2 py-4 shadow-lg text-orange-100 bg-orange-500 text-sm"
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
                <span class="text-2xl font-extrabold text-gray-200 block">PKM</span>
                <span class="text-xl font-extrabold text-white block">Dashboard</span>
            </div>
        </div>
        <nav class="mt-5">
                <!-- Navigation Links -->
                <a href="{{ route('pkm.dashboard') }}" 
                class="block px-4 py-2 mt-2 text-sm font-semibold rounded-lg flex items-center 
                {{ request()->routeIs('pkm.dashboard') ? 'bg-white text-orange-600' : 'text-white hover:bg-orange-600' }}">
                    <i class="fas fa-tachometer-alt icon mr-2"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="{{ route('pkm.jobwaiting') }}" 
                    class="block px-4 py-2 mt-2 text-sm font-semibold rounded-lg flex items-center 
                    {{ request()->routeIs('pkm.jobwaiting') ? 'bg-white text-orange-600' : 'text-white hover:bg-orange-600' }}">
                        <i class="fa fa-bell icon mr-2"></i>
                        <span class="nav-text">List Pekerjaan</span>
                    </a>
                    <a href="{{ route('pkm.items.index') }}" 
                    class="block px-4 py-2 mt-2 text-sm font-semibold rounded-lg flex items-center 
                    {{ request()->routeIs('pkm.items.index') ? 'bg-white text-orange-600' : 'text-white hover:bg-orange-600' }}">
                    <i class="fa fa-boxes icon mr-2"></i>
                    <span class="nav-text">Item Kebutuhan Kerjaan</span>
                </a>
                    <a href="{{ route('pkm.lhpp.index') }}" 
                    class="block px-4 py-2 mt-2 text-sm font-semibold rounded-lg flex items-center 
                    {{ request()->routeIs('pkm.lhpp.index') ? 'bg-white text-orange-600' : 'text-white hover:bg-orange-600' }}">
                        <i class="fas fa-file-alt icon mr-2"></i>
                        <span class="nav-text">Buat LHPP</span>
                    </a>

                    <a href="{{ route('pkm.laporan') }}" 
                    class="block px-4 py-2 mt-2 text-sm font-semibold rounded-lg flex items-center 
                    {{ request()->routeIs('pkm.laporan') ? 'bg-white text-orange-600' : 'text-white hover:bg-orange-600' }}">
                        <i class="fas fa-tasks icon mr-2"></i>
                        <span class="nav-text">Dokumen</span>
                    </a>
                    </nav>
</aside>
<!-- Main content -->
<div :class="open ? 'ml-56' : 'ml-0'" class="flex-1 flex flex-col transition-all duration-300 bg-gray-100 overflow-y-auto">
  <!-- Top Navigation -->
  <nav class="bg-orange-500 shadow-lg">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="relative flex items-center justify-between h-16">
                        <div class="flex items-center">
                            <!-- Logo di sebelah kiri -->
                            <img src="{{ asset('images/pkm.png') }}" alt="PKM Logo" class="h-10 w-auto mr-2">
                            <!-- Teks di sebelah kiri -->
                            <div class="flex flex-col text-white">
                                <span class="font-bold text-sm">PT. Prima Karya Manuggal</span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                        <!-- Dropdown Profil -->
                        <x-dropdown align="right" width="48">
                                        <x-slot name="trigger">
                                            <button class="flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md bg-white text-gray-600 hover:text-gray-800 focus:outline-none transition duration-150">
                                                <i class="fas fa-user-circle text-sm mr-1"></i>
                                                <span>Welcome {{ Auth::user()->name }}</span>
                                                <svg class="fill-current h-3 w-3 ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </x-slot>
                                <x-slot name="content">
                                <x-slot name="content">
    <x-dropdown-link :href="route('profile.edit')">
        {{ __('Profile') }}
    </x-dropdown-link>

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
