<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Font Awesome (biar icon lama masih aman) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Tailwind + Alpine (cukup dari Vite, jangan dobel CDN Alpine) -->
    <!-- Vite (Tailwind + app JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
             <!-- <link rel="stylesheet" href="{{ asset('build/assets/app-CSwLQ2bl.css') }}">
    <script src="{{ asset('build/assets/app-CH09qwMe.js') }}"></script>  -->

    <!-- Lucide (modern icon) -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak]{ display:none !important; }
        /* hide scrollbar but keep scroll */
        .no-scrollbar{ -ms-overflow-style:none; scrollbar-width:none; }
        .no-scrollbar::-webkit-scrollbar{ display:none; }
    </style>
</head>

<body class="font-sans antialiased bg-slate-50 text-slate-800">
@php
    // PKM routes (buat active state)
    $pkmMenus = [
        ['route'=>'pkm.dashboard',   'icon'=>'layout-dashboard', 'label'=>'Dashboard'],
        ['route'=>'pkm.jobwaiting',  'icon'=>'bell',             'label'=>'List Pekerjaan'],
        ['route'=>'pkm.items.index', 'icon'=>'boxes',            'label'=>'Item Kebutuhan'],
        ['route'=>'pkm.lhpp.index',  'icon'=>'file-text',        'label'=>'Buat LHPP'],
        ['route'=>'pkm.laporan',     'icon'=>'folder-open',      'label'=>'Dokumen'],
    ];
@endphp

<div
    x-data="{
        sidebarOpen: true,  // desktop collapse
        mobileOpen: false,  // mobile drawer
        toggle() {
            if (window.innerWidth >= 1024) this.sidebarOpen = !this.sidebarOpen;
            else this.mobileOpen = !this.mobileOpen;
        },
        closeMobile(){ this.mobileOpen = false; }
    }"
    x-init="$watch('mobileOpen', v => document.body.classList.toggle('overflow-hidden', v))"
    class="min-h-screen"
>

    <!-- MOBILE OVERLAY -->
    <div
        x-show="mobileOpen"
        x-transition.opacity
        class="fixed inset-0 bg-black/40 z-30 lg:hidden"
        @click="closeMobile()"
        x-cloak
    ></div>

    <!-- SIDEBAR (ORANGE THEME) -->
    <aside
        class="fixed inset-y-0 left-0 z-40 bg-orange-600 border-r border-orange-800/30 shadow-sm flex flex-col transition-all duration-300"
        :class="[
            (mobileOpen ? 'translate-x-0' : '-translate-x-full') + ' lg:translate-x-0',
            (sidebarOpen ? 'lg:w-72' : 'lg:w-20'),
            'w-72'
        ]"
    >
        <!-- BRAND -->
        <div class="sticky top-0 z-10 bg-orange-600 border-b border-orange-800/30">
            <div class="flex items-center justify-between gap-3 px-4 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <img src="{{ asset('images/logo-st2.png') }}"
                         alt="Semen Tonasa Logo"
                         class="h-9 w-auto drop-shadow-sm">

                    <div class="min-w-0" x-show="sidebarOpen" x-transition>
                        <div class="font-extrabold tracking-tight text-white leading-none">Vendor BMS</div>
                        <div class="text-xs text-white/70 truncate">Dashboard</div>
                    </div>
                </div>

                <!-- collapse button -->
                <button
                    @click="toggle()"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl hover:bg-white/10 text-white active:scale-[0.98] transition"
                    aria-label="Toggle Sidebar"
                >
                    <i data-lucide="panel-left" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Search -->
            <div class="px-4 pb-4" x-show="sidebarOpen" x-transition>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-white/60"></i>
                    <input
                        type="text"
                        placeholder="Cari menu..."
                        class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-white/15 bg-white/10 text-white placeholder:text-white/50
                               focus:outline-none focus:ring-2 focus:ring-white/25 focus:border-white/25"
                    >
                </div>
            </div>
        </div>

        <!-- NAV -->
        <div class="flex-1 overflow-y-auto no-scrollbar px-3 py-4">
            <nav class="space-y-1 text-sm">

                @foreach($pkmMenus as $m)
                    <a href="{{ route($m['route']) }}"
                       class="group flex items-center gap-3 rounded-xl px-3 py-2.5 transition
                              {{ request()->routeIs($m['route'])
                                    ? 'bg-white text-orange-700 ring-1 ring-white/30'
                                    : 'text-white/90 hover:bg-white/10' }}">
                        <span class="inline-flex w-9 h-9 items-center justify-center rounded-xl transition
                                     {{ request()->routeIs($m['route'])
                                            ? 'bg-orange-100 text-orange-700'
                                            : 'bg-white/10 text-white/90 group-hover:bg-white/15' }}">
                            <i data-lucide="{{ $m['icon'] }}" class="w-5 h-5"></i>
                        </span>
                        <span x-show="sidebarOpen" x-transition class="font-medium">{{ $m['label'] }}</span>
                    </a>
                @endforeach

            </nav>
        </div>

        <!-- Footer -->
        <div class="border-t border-white/10 p-3">
            <div class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs text-white/70">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                <span x-show="sidebarOpen" x-transition>Vendor • PKM</span>
            </div>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="min-h-screen transition-all duration-300"
         :class="sidebarOpen ? 'lg:pl-72' : 'lg:pl-20'">

        <!-- TOPBAR (ORANGE THEME) -->
        <header class="sticky top-0 z-20 bg-orange-600 backdrop-blur border-b border-orange-800/30">
            <div class="px-4 lg:px-6 py-3 flex items-center justify-between">

                <!-- LEFT -->
                <div class="flex items-center gap-3">
                    <button
                        @click="toggle()"
                        class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl hover:bg-white/10 text-white transition"
                        aria-label="Open Menu"
                    >
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/logo-st2.png') }}" alt="Semen Tonasa Logo" class="h-9 w-auto">
                    </div>

                    <div class="hidden md:flex flex-col text-white leading-tight">
                        <span class="font-extrabold tracking-tight">Vendor Workshop Section</span>
                        <span class="text-xs text-white/80">Halaman Dashboard Vendor</span>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="flex items-center gap-3">

                    <!-- PROFILE -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white text-orange-700 hover:bg-orange-50 transition">
                                <span class="inline-flex w-9 h-9 items-center justify-center rounded-xl bg-orange-50 text-orange-700">
                                    <i data-lucide="user" class="w-5 h-5"></i>
                                </span>
                                <span class="hidden sm:block text-sm font-semibold">
                                    {{ Auth::user()->name ?? 'Vendor' }}
                                </span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-orange-300"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <a href="{{ route('profile.edit') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                Profile
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                                 onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>

                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <main class="p-4 lg:p-6">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 lg:p-6">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>
