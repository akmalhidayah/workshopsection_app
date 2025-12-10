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
    
    <!-- Flatpickr CSS tetap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- TomSelect CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<!-- TomSelect JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
  <!-- <link rel="stylesheet" href="{{ asset('build/assets/app-DJspGMRf.css') }}">
    <script src="{{ asset('build/assets/app-CH09qwMe.js') }}"></script>  -->
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
    class="fixed top-0 left-0 h-full z-10 w-56 px-2 py-4 shadow-lg text-blue-100 bg-blue-900 text-sm 
           overflow-y-auto scroll-smooth scrollbar-thin scrollbar-thumb-blue-600 scrollbar-track-blue-300"
>        
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
        <!-- Sidebar Navigation -->
<!-- Sidebar Navigation -->
<nav class="mt-5">
    <!-- Dashboard Icon -->
    <a href="{{ route('admin.dashboard') }}"
       class="block px-3 py-2 mt-1 text-[11px] font-medium text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.dashboard') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-chart-pie mr-1 text-sm group-hover:text-blue-300 transition duration-200"></i>
        <span>Dashboard</span>
    </a>

<!-- ORDER (dengan Submenu & Badge jumlah) -->
<div 
    x-data="{ orderOpen: {{ in_array(request('tab'), ['notif','kawatlas']) ? 'true' : 'false' }} }" 
    class="mt-1"
>
    <!-- Tombol Utama Order -->
    <button
        @click="orderOpen = !orderOpen; open = true"
        class="w-full flex items-center justify-between px-3 py-2 text-[11px] font-medium text-white rounded-lg group
               {{ in_array(request('tab'), ['notif','kawatlas']) ? 'bg-blue-800' : 'hover:bg-blue-700' }}">
        <div class="flex items-center">
            <i class="fas fa-envelope-open-text mr-2 text-sm group-hover:text-blue-300 transition duration-200"></i>
            <span>Order</span>

            <!-- 🔴 Badge total pending notifikasi -->
            @php
                $totalNotif = ($jumlahOrderPekerjaan ?? 0) + ($jumlahOrderKawatLas ?? 0);
            @endphp
            @if($totalNotif > 0)
                <span class="ml-2 bg-red-600 text-white text-[10px] font-bold px-2 py-[2px] rounded-full">
                    {{ $totalNotif }}
                </span>
            @endif
        </div>
        <i :class="orderOpen ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
    </button>

    <!-- Submenu Order -->
    <div x-show="orderOpen" x-collapse class="mt-1 ml-3 space-y-1">

       <!-- 1️⃣ Order Pekerjaan -->
<a href="{{ route('notifikasi.index', ['tab' => 'notif']) }}"
   @click="open = false"
   class="flex items-center justify-between px-3 py-2 text-xs text-white rounded-lg 
          hover:bg-blue-700 {{ request('tab') === 'notif' ? 'bg-blue-800' : '' }}">
    <div class="flex items-center">
        <i class="fas fa-bell mr-2 text-[12px]"></i> 
        <span>Order Pekerjaan Jasa</span>
    </div>
    @if(!empty($jumlahOrderPekerjaan))
        <span class="bg-red-500 text-[10px] font-semibold px-2 py-[1px] rounded-full">
            {{ $jumlahOrderPekerjaan }}
        </span>
    @endif
</a>

<!-- 2️⃣ Order Kawat Las -->
<a href="{{ route('notifikasi.index', ['tab' => 'kawatlas']) }}"
   @click="open = false"
   class="flex items-center justify-between px-3 py-2 text-xs text-white rounded-lg 
          hover:bg-blue-700 {{ request('tab') === 'kawatlas' ? 'bg-blue-800' : '' }}">
    <div class="flex items-center">
        <i class="fas fa-wrench mr-2 text-[12px]"></i> 
        <span>Order Kawat Las</span>
    </div>
    @if(!empty($jumlahOrderKawatLas))
        <span class="bg-yellow-400 text-[10px] font-semibold text-black px-2 py-[1px] rounded-full">
            {{ $jumlahOrderKawatLas }}
        </span>
    @endif
</a>

      <!-- Order Pekerjaan Bengkel -->
<a href="{{ route('admin.orderbengkel.index') }}"
   class="flex items-center justify-between px-3 py-2 text-xs text-white rounded-lg hover:bg-blue-700 {{ request()->routeIs('admin.orderbengkel.*') ? 'bg-blue-800' : '' }}">
    <div class="flex items-center">
        <i class="fas fa-list-alt mr-2 text-[12px]"></i>
        <span>Order Pekerjaan Bengkel</span>
    </div>
    @if(!empty($jumlahOrderPekerjaan))
        <span class="bg-red-500 text-[10px] font-semibold px-2 py-[1px] rounded-full">{{ $jumlahOrderPekerjaan }}</span>
    @endif
</a>


    </div>
</div>


    <!-- Create HPP Icon -->
    <a href="{{ route('admin.inputhpp.index') }}"
       class="block px-3 py-2 mt-1 text-[11px] font-medium text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.inputhpp.index') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-pencil-alt mr-1 text-sm group-hover:text-blue-300 transition duration-200"></i>
        <span>Create HPP</span>
    </a>

    <!-- Verifikasi Anggaran Icon -->
    <a href="{{ route('admin.verifikasianggaran.index') }}"
       class="block px-3 py-2 mt-1 text-[11px] font-medium text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.verifikasianggaran.index') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-money-check-alt mr-1 text-sm group-hover:text-blue-300 transition duration-200"></i>
        <span>Verifikasi Anggaran</span>
    </a>

    <!-- PR / PO Icon -->
    <a href="{{ route('admin.purchaseorder') }}"
       class="block px-3 py-2 mt-1 text-[11px] font-medium text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.purchaseorder') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-tasks mr-1 text-sm group-hover:text-blue-300 transition duration-200"></i>
        <span>Purchase Order</span>
    </a>

    <!-- LHPP Icon -->
    <a href="{{ route('admin.lhpp.index') }}"
       class="block px-3 py-2 mt-1 text-[11px] font-medium text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.lhpp.index') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-file-alt mr-1 text-sm group-hover:text-blue-300 transition duration-200"></i>
        <span>LHPP</span>
    </a>

    <!-- LPJ Icon -->
    <a href="{{ route('admin.lpj') }}"
       class="block px-3 py-2 mt-1 text-[11px] font-medium text-white rounded-lg flex items-center group
              {{ request()->routeIs('admin.lpj') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
       @click="open = false">
        <i class="fas fa-folder-open mr-1 text-sm group-hover:text-blue-300 transition duration-200"></i>
        <span>LPJ/PPL</span>
    </a>

<!-- Garansi Icon -->
<a href="{{ route('admin.garansi.index') }}"
   class="block px-3 py-2 mt-1 text-[11px] font-medium text-white rounded-lg flex items-center group
          {{ request()->routeIs('admin.garansi.index') ? 'bg-blue-800' : 'hover:bg-blue-700' }}"
   @click="open = false">
    <i class="fas fa-shield-alt mr-1 text-sm group-hover:text-blue-300 transition duration-200"></i>
    <span>Garansi</span>
</a>

    <!-- Dropdown Lainnya -->
    <div x-data="{ openSub: false }">
        <button @click="openSub = !openSub" class="w-full flex items-center justify-between px-4 py-2 mt-2 text-xs font-semibold text-white bg-blue-700 hover:bg-blue-800 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-layer-group mr-2 text-lg"></i>
                <span>Lainnya</span>
            </div>
            <i :class="openSub ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
        </button>
        <div x-show="openSub" x-transition class="ml-4 mt-1 space-y-1">
            <a href="{{ route('admin.updateoa') }}"
               class="block px-4 py-2 text-xs text-white rounded-lg hover:bg-blue-700 {{ request()->routeIs('admin.updateoa') ? 'bg-blue-800' : '' }}">
                <i class="fas fa-clipboard-list mr-2"></i> Kuota Anggaran & OA
            </a>
           <a href="{{ route('admin.jenis-kawat-las.index') }}"
   class="block px-4 py-2 text-xs text-white rounded-lg hover:bg-blue-700 {{ request()->routeIs('admin.jenis-kawat-las.*') ? 'bg-blue-800' : '' }}">
    <i class="fas fa-boxes mr-2"></i> Stock Kawat Las
</a>

            <a href="{{ route('admin.users.index') }}"
               class="block px-4 py-2 text-xs text-white rounded-lg hover:bg-blue-700 {{ request()->routeIs('admin.users.index') ? 'bg-blue-800' : '' }}">
                <i class="fas fa-user-circle mr-2"></i> User Panel
            </a>
            <a href="{{ route('admin.uploadinfo') }}"
               class="block px-4 py-2 text-xs text-white rounded-lg hover:bg-blue-700 {{ request()->routeIs('admin.uploadinfo') ? 'bg-blue-800' : '' }}">
                <i class="fas fa-upload mr-2"></i> Upload Informasi
            </a>
            <a href="{{ route('admin.unit_work.index') }}"
               class="block px-4 py-2 text-xs text-white rounded-lg hover:bg-blue-700 {{ request()->routeIs('admin.unit_work.index') ? 'bg-blue-800' : '' }}">
                <i class="fas fa-building mr-2"></i> Unit Kerja
            </a>
        </div>
    </div>
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
