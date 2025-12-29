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

    <!-- TOGGLE BUTTON (SELALU TERLIHAT) -->
    <button
        @click="open = !open"
        :class="open ? 'left-60' : 'left-4'"
        class="fixed top-4 z-50 bg-blue-600 text-white p-2 rounded shadow-lg
               transition-all duration-300"
    >
        <i :class="open ? 'fas fa-times' : 'fas fa-bars'" class="text-lg"></i>
    </button>

    <!-- SIDEBAR -->
    <aside
        :class="open ? 'w-56' : 'w-16'"
        class="fixed top-0 left-0 h-full z-40
               bg-blue-900 text-blue-100 text-sm
               transition-all duration-300
               overflow-hidden shadow-lg"
    >

        <!-- HEADER -->
        <div class="flex items-center justify-center h-16 border-b border-blue-800">

            <!-- MODE EXPANDED -->
            <template x-if="open">
                <div class="flex items-center space-x-2 px-2">
                    <x-application-logo class="h-9 w-auto fill-current text-gray-200" />
                    <div class="leading-tight">
                        <span class="block text-lg font-extrabold text-white">Workshop Machine</span>
                        <span class="block text-xs text-blue-200">Dashboard</span>
                    </div>
                </div>
            </template>

            <!-- MODE COLLAPSED -->
            <template x-if="!open">
                <x-application-logo class="h-8 w-auto fill-current text-gray-200" />
            </template>

        </div>

    <!-- NAVIGATION -->
<nav class="mt-3 px-2 space-y-1">

    <!-- DASHBOARD -->
    <a href="{{ route('admin.dashboard') }}"
       class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg
              hover:bg-blue-700
              {{ request()->routeIs('admin.dashboard') ? 'bg-blue-800' : '' }}"
       @click="open = false"
    >
        <i class="fas fa-chart-pie w-4 text-center"></i>
        <span x-show="open" class="font-semibold">Dashboard</span>
    </a>

    <!-- ORDER GROUP -->
    <div x-data="{ orderOpen: {{ in_array(request('tab'), ['notif','kawatlas']) ? 'true' : 'false' }} }">

        <button
            @click="open ? orderOpen = !orderOpen : open = true"
            class="w-full flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg hover:bg-blue-700"
        >
            <i class="fas fa-envelope-open-text w-4 text-center"></i>
            <span x-show="open">Order</span>

            <template x-if="open">
                <i :class="orderOpen ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"
                   class="ml-auto text-[10px]"></i>
            </template>
        </button>

        <!-- SUBMENU ORDER -->
        <div x-show="orderOpen && open" x-collapse class="ml-6 mt-1 space-y-1">

            <a href="{{ route('notifikasi.index', ['tab' => 'notif']) }}"
               class="flex items-center justify-between px-3 py-2 text-xs rounded-lg
                      hover:bg-blue-700 {{ request('tab') === 'notif' ? 'bg-blue-800' : '' }}">
                <span class="font-medium">Order Pekerjaan Jasa</span>
                @if(!empty($jumlahOrderPekerjaan))
                    <span class="bg-red-500 text-[10px] px-2 rounded-full">
                        {{ $jumlahOrderPekerjaan }}
                    </span>
                @endif
            </a>

            <a href="{{ route('notifikasi.index', ['tab' => 'kawatlas']) }}"
               class="flex items-center justify-between px-3 py-2 text-xs rounded-lg
                      hover:bg-blue-700 {{ request('tab') === 'kawatlas' ? 'bg-blue-800' : '' }}">
                <span class="font-medium">Order Kawat Las</span>
                @if(!empty($jumlahOrderKawatLas))
                    <span class="bg-yellow-400 text-black text-[10px] px-2 rounded-full">
                        {{ $jumlahOrderKawatLas }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.orderbengkel.index') }}"
               class="px-3 py-2 text-xs font-medium rounded-lg hover:bg-blue-700
                      {{ request()->routeIs('admin.orderbengkel.*') ? 'bg-blue-800' : '' }}">
                Order Pekerjaan Bengkel
            </a>

        </div>
    </div>

    <!-- MENU UTAMA -->
    @php
        $menus = [
            ['route'=>'admin.inputhpp.index','icon'=>'fa-pencil-alt','label'=>'Create HPP'],
            ['route'=>'admin.verifikasianggaran.index','icon'=>'fa-money-check-alt','label'=>'Verifikasi Anggaran'],
            ['route'=>'admin.purchaseorder','icon'=>'fa-tasks','label'=>'Purchase Order'],
            ['route'=>'admin.lhpp.index','icon'=>'fa-file-alt','label'=>'LHPP'],
            ['route'=>'admin.lpj','icon'=>'fa-folder-open','label'=>'LPJ/PPL'],
            ['route'=>'admin.garansi.index','icon'=>'fa-shield-alt','label'=>'Garansi'],
        ];
    @endphp

    @foreach($menus as $m)
        <a href="{{ route($m['route']) }}"
           class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg hover:bg-blue-700
                  {{ request()->routeIs($m['route']) ? 'bg-blue-800' : '' }}"
           @click="open = false"
        >
            <i class="fas {{ $m['icon'] }} w-4 text-center"></i>
            <span x-show="open">{{ $m['label'] }}</span>
        </a>
    @endforeach

    <!-- LAINNYA -->
    <div x-data="{ openSub: false }">

        <button
            @click="open ? openSub = !openSub : open = true"
            class="w-full flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg hover:bg-blue-700"
        >
            <i class="fas fa-layer-group w-4 text-center"></i>
            <span x-show="open">Lainnya</span>

            <template x-if="open">
                <i :class="openSub ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"
                   class="ml-auto text-[10px]"></i>
            </template>
        </button>

        <!-- SUBMENU LAINNYA -->
        <div x-show="openSub && open" x-collapse class="ml-6 mt-1 space-y-1">

            <a href="{{ route('admin.updateoa') }}"
               class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-blue-700
                      {{ request()->routeIs('admin.updateoa') ? 'bg-blue-800' : '' }}">
                Kuota Anggaran & OA
            </a>

            <a href="{{ route('admin.jenis-kawat-las.index') }}"
               class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-blue-700
                      {{ request()->routeIs('admin.jenis-kawat-las.*') ? 'bg-blue-800' : '' }}">
                Stock Kawat Las
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-blue-700
                      {{ request()->routeIs('admin.users.index') ? 'bg-blue-800' : '' }}">
                User Panel
            </a>

            <a href="{{ route('admin.uploadinfo') }}"
               class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-blue-700
                      {{ request()->routeIs('admin.uploadinfo') ? 'bg-blue-800' : '' }}">
                Upload Informasi
            </a>

            <a href="{{ route('admin.unit_work.index') }}"
               class="block px-3 py-2 text-xs font-medium rounded-lg hover:bg-blue-700
                      {{ request()->routeIs('admin.unit_work.index') ? 'bg-blue-800' : '' }}">
                Unit Kerja
            </a>

        </div>
    </div>

</nav>

    </aside>

<!-- Main content -->
<div :class="open ? 'ml-56' : 'ml-16'"
     class="flex-1 flex flex-col transition-all duration-300 bg-gray-100">


    <!-- Top Navigation (STICKY) -->
    <nav class="sticky top-0 z-40 shadow-lg bg-blue-900">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="relative flex items-center justify-between h-16">

                <!-- LEFT -->
                <div class="flex items-center">
                    <img src="{{ asset('images/logo-sig.png') }}" alt="SIG Logo" class="h-10 w-auto mr-2">
                    <img src="{{ asset('images/logo-st2.png') }}" alt="Semen Tonasa Logo" class="h-10 w-auto mr-2">

                    <div class="hidden sm:flex flex-col text-white">
                        <span class="font-bold text-lg">SECTION OF WORKSHOP</span>
                        <span class="text-sm">Dept. Of Project Management & Main Support</span>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="flex items-center space-x-4">

                    <!-- 🔔 NOTIFICATION -->
                    <div x-data="{ open: false }" class="relative">
                        <button
                            @click="open = !open"
                            @click.outside="open = false"
                            class="relative text-white focus:outline-none"
                        >
                            <i class="fas fa-bell text-xl"></i>

                            @if($adminUnreadCount > 0)
                                <span class="absolute -top-1 -right-1 w-4 h-4 text-[10px]
                                             bg-red-600 text-white rounded-full
                                             flex items-center justify-center">
                                    {{ $adminUnreadCount > 9 ? '9+' : $adminUnreadCount }}
                                </span>
                            @endif
                        </button>

                        <!-- DROPDOWN (DIBATASI + SCROLL) -->
                        <div
                            x-show="open"
                            x-transition
                            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl
                                   border border-gray-200 z-50
                                   max-h-[420px] overflow-y-auto"
                        >
                            @include('admin.partials.notification-dropdown')
                        </div>
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
