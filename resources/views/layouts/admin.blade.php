<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- TomSelect -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <!-- Font Awesome (optional, kalau masih dipakai di beberapa halaman) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Tailwind + Alpine -->
    <!-- Vite (Tailwind + app JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
             <!-- <link rel="stylesheet" href="{{ asset('build/assets/app-CSwLQ2bl.css') }}">
    <script src="{{ asset('build/assets/app-CH09qwMe.js') }}"></script>  -->
    

    <!-- Lucide Icons (modern) -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>[x-cloak]{ display:none !important; }</style>
</head>

<body class="font-sans antialiased bg-slate-50 text-slate-800">
@php
    $tab = request('tab');

    $isOrder =
        in_array($tab, ['notif','kawatlas'])
        || request()->routeIs('admin.orderbengkel.*');

    $isLainnya =
        request()->routeIs('admin.updateoa')
        || request()->routeIs('admin.jenis-kawat-las.*')
        || request()->routeIs('admin.users.*')
        || request()->routeIs('admin.uploadinfo')
        || request()->routeIs('admin.unit_work.*');

    $user = Auth::user();
    $can = function ($key) use ($user) {
        return $user && $user->hasAdminPermission($key);
    };

    $canOrderJasa = $can('admin.order.jasa');
    $canOrderKawatLas = $can('admin.order.kawatlas');
    $canOrderBengkel = $can('admin.order.bengkel');
    $canAnyOrder = $canOrderJasa || $canOrderKawatLas || $canOrderBengkel;

    $canAccessControl = $user && $user->isSuperAdmin();
    $canAnyLainnya = $canAccessControl
        || $can('admin.updateoa')
        || $can('admin.jenis_kawat_las')
        || $can('admin.users')
        || $can('admin.uploadinfo')
        || $can('admin.unit_work');

    $menus = [
        ['route'=>'admin.inputhpp.index','icon'=>'pencil','label'=>'Create HPP','perm'=>'admin.inputhpp'],
        ['route'=>'admin.verifikasianggaran.index','icon'=>'wallet','label'=>'Verifikasi Anggaran','perm'=>'admin.verifikasianggaran'],
        ['route'=>'admin.purchaseorder','icon'=>'list-checks','label'=>'Purchase Order','perm'=>'admin.purchaseorder'],
        ['route'=>'admin.lhpp.index','icon'=>'file-text','label'=>'LHPP','perm'=>'admin.lhpp'],
        ['route'=>'admin.lpj','icon'=>'folder-open','label'=>'LPJ/PPL','perm'=>'admin.lpj'],
        ['route'=>'admin.garansi.index','icon'=>'shield-check','label'=>'Garansi','perm'=>'admin.garansi'],
    ];
@endphp

<div
    x-data="{
        sidebarOpen: true,   // desktop collapse
        mobileOpen: false,   // mobile drawer
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

<!-- SIDEBAR -->
<aside
    class="fixed inset-y-0 left-0 z-40 bg-blue-900 border-r border-blue-950/30 shadow-sm flex flex-col transition-all duration-300"
    :class="[
        (mobileOpen ? 'translate-x-0' : '-translate-x-full') + ' lg:translate-x-0',
        (sidebarOpen ? 'lg:w-72' : 'lg:w-20'),
        'w-72'
    ]"
>
    <!-- BRAND -->
    <div class="sticky top-0 z-10 bg-blue-900 border-b border-blue-950/30">
        <div class="flex items-center justify-between gap-3 px-4 py-4">
            <div class="flex items-center gap-3 min-w-0">
                <div class="min-w-0" x-show="sidebarOpen" x-transition>
                    <div class="font-extrabold tracking-tight text-white leading-none">Workshop Machine</div>
                    <div class="text-xs text-white/70 truncate">Admin Dashboard</div>
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

            <!-- Dashboard -->
            @if ($can('admin.dashboard'))
                <a href="{{ route('admin.dashboard') }}"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 transition
                          {{ request()->routeIs('admin.dashboard')
                                ? 'bg-white text-blue-900 ring-1 ring-white/30'
                                : 'text-white/90 hover:bg-white/10' }}">
                    <span class="inline-flex w-9 h-9 items-center justify-center rounded-xl transition
                                 {{ request()->routeIs('admin.dashboard')
                                        ? 'bg-blue-100 text-blue-900'
                                        : 'bg-white/10 text-white/90 group-hover:bg-white/15' }}">
                        <i data-lucide="pie-chart" class="w-5 h-5"></i>
                    </span>
                    <span x-show="sidebarOpen" x-transition class="font-medium">Dashboard</span>
                </a>
            @endif

            <!-- Order Dropdown -->
            @if ($canAnyOrder)
            <div x-data="{ open: {{ $isOrder ? 'true' : 'false' }} }" class="rounded-xl">
                <button
                    @click="open = !open"
                    class="w-full group flex items-center gap-3 rounded-xl px-3 py-2.5 transition
                           {{ $isOrder ? 'bg-white text-blue-900 ring-1 ring-white/30' : 'text-white/90 hover:bg-white/10' }}"
                >
                    <span class="inline-flex w-9 h-9 items-center justify-center rounded-xl transition
                                 {{ $isOrder ? 'bg-blue-100 text-blue-900' : 'bg-white/10 text-white/90 group-hover:bg-white/15' }}">
                        <i data-lucide="inbox" class="w-5 h-5"></i>
                    </span>
                    <span x-show="sidebarOpen" x-transition class="flex-1 text-left font-medium">Order</span>
                    <i data-lucide="chevron-down"
                       class="w-4 h-4 transition"
                       :class="open ? 'rotate-180 text-blue-900' : 'text-white/70'"
                       x-show="sidebarOpen" x-transition></i>
                </button>

                <div x-show="open" x-collapse x-cloak class="mt-1 pl-12 space-y-1">
                    @if ($canOrderJasa)
                        <a href="{{ route('notifikasi.index', ['tab' => 'notif']) }}"
                           class="flex items-center justify-between rounded-lg px-3 py-2 transition
                                  {{ $tab === 'notif'
                                        ? 'bg-white text-blue-900'
                                        : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            <span>Order Pekerjaan Jasa</span>
                            @if(!empty($jumlahOrderPekerjaan))
                                <span class="bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full">
                                    {{ $jumlahOrderPekerjaan }}
                                </span>
                            @endif
                        </a>
                    @endif

                    @if ($canOrderKawatLas)
                        <a href="{{ route('notifikasi.index', ['tab' => 'kawatlas']) }}"
                           class="flex items-center justify-between rounded-lg px-3 py-2 transition
                                  {{ $tab === 'kawatlas'
                                        ? 'bg-white text-blue-900'
                                        : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            <span>Order Kawat Las</span>
                            @if(!empty($jumlahOrderKawatLas))
                                <span class="bg-amber-400 text-black text-[10px] px-2 py-0.5 rounded-full">
                                    {{ $jumlahOrderKawatLas }}
                                </span>
                            @endif
                        </a>
                    @endif

                    @if ($canOrderBengkel)
                        <a href="{{ route('admin.orderbengkel.index') }}"
                           class="block rounded-lg px-3 py-2 transition
                                  {{ request()->routeIs('admin.orderbengkel.*')
                                        ? 'bg-white text-blue-900'
                                        : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            Order Pekerjaan Bengkel
                        </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Divider -->
            <div class="pt-2 pb-1" x-show="sidebarOpen" x-transition>
                <div class="text-[11px] uppercase tracking-wider text-white/60 px-3">Menu Utama</div>
            </div>

            <!-- Main Menus -->
            @foreach($menus as $m)
                @if ($can($m['perm']))
                    <a href="{{ route($m['route']) }}"
                       class="group flex items-center gap-3 rounded-xl px-3 py-2.5 transition
                              {{ request()->routeIs($m['route'])
                                    ? 'bg-white text-blue-900 ring-1 ring-white/30'
                                    : 'text-white/90 hover:bg-white/10' }}">
                        <span class="inline-flex w-9 h-9 items-center justify-center rounded-xl transition
                                     {{ request()->routeIs($m['route'])
                                            ? 'bg-blue-100 text-blue-900'
                                            : 'bg-white/10 text-white/90 group-hover:bg-white/15' }}">
                            <i data-lucide="{{ $m['icon'] }}" class="w-5 h-5"></i>
                        </span>
                        <span x-show="sidebarOpen" x-transition class="font-medium">{{ $m['label'] }}</span>
                    </a>
                @endif
            @endforeach

            <!-- Lainnya -->
            @if ($canAnyLainnya)
            <div x-data="{ open: {{ $isLainnya ? 'true' : 'false' }} }" class="rounded-xl">
                <button
                    @click="open = !open"
                    class="w-full group flex items-center gap-3 rounded-xl px-3 py-2.5 transition
                           {{ $isLainnya ? 'bg-white text-blue-900 ring-1 ring-white/30' : 'text-white/90 hover:bg-white/10' }}"
                >
                    <span class="inline-flex w-9 h-9 items-center justify-center rounded-xl transition
                                 {{ $isLainnya ? 'bg-blue-100 text-blue-900' : 'bg-white/10 text-white/90 group-hover:bg-white/15' }}">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </span>
                    <span x-show="sidebarOpen" x-transition class="flex-1 text-left font-medium">Lainnya</span>
                    <i data-lucide="chevron-down"
                       class="w-4 h-4 transition"
                       :class="open ? 'rotate-180 text-blue-900' : 'text-white/70'"
                       x-show="sidebarOpen" x-transition></i>
                </button>

                <div x-show="open" x-collapse x-cloak class="mt-1 pl-12 space-y-1">
                    @if ($canAccessControl)
                        <a href="{{ route('admin.access-control.index') }}"
                           class="block rounded-lg px-3 py-2 transition
                                  {{ request()->routeIs('admin.access-control.*')
                                        ? 'bg-white text-blue-900'
                                        : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            Access Control
                        </a>
                    @endif

                    @if ($can('admin.updateoa'))
                        <a href="{{ route('admin.updateoa') }}"
                           class="block rounded-lg px-3 py-2 transition
                                  {{ request()->routeIs('admin.updateoa')
                                        ? 'bg-white text-blue-900'
                                        : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            Kuota Anggaran & OA
                        </a>
                    @endif

                    @if ($can('admin.jenis_kawat_las'))
                        <a href="{{ route('admin.jenis-kawat-las.index') }}"
                           class="block rounded-lg px-3 py-2 transition
                                  {{ request()->routeIs('admin.jenis-kawat-las.*')
                                        ? 'bg-white text-blue-900'
                                        : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            Stock Kawat Las
                        </a>
                    @endif

                    @if ($can('admin.users'))
                        <a href="{{ route('admin.users.index') }}"
                           class="block rounded-lg px-3 py-2 transition
                                  {{ request()->routeIs('admin.users.*')
                                        ? 'bg-white text-blue-900'
                                        : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            User Panel
                        </a>
                    @endif

                    @if ($can('admin.uploadinfo'))
                        <a href="{{ route('admin.uploadinfo') }}"
                           class="block rounded-lg px-3 py-2 transition
                                  {{ request()->routeIs('admin.uploadinfo')
                                        ? 'bg-white text-blue-900'
                                        : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            Upload Informasi
                        </a>
                    @endif

                    @if ($can('admin.unit_work'))
                        <a href="{{ route('admin.unit_work.index') }}"
                           class="block rounded-lg px-3 py-2 transition
                                  {{ request()->routeIs('admin.unit_work.*')
                                        ? 'bg-white text-blue-900'
                                        : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                            Unit Kerja
                        </a>
                    @endif
                </div>
            </div>
            @endif

        </nav>
    </div>

    <!-- Footer -->
    <div class="border-t border-white/10 p-3">
        <div class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs text-white/70">
            <i data-lucide="sparkles" class="w-4 h-4"></i>
            <span x-show="sidebarOpen" x-transition>Workshop • Admin</span>
        </div>
    </div>
</aside>


    <!-- MAIN WRAPPER -->
    <div class="min-h-screen transition-all duration-300"
         :class="sidebarOpen ? 'lg:pl-72' : 'lg:pl-20'">

        <!-- TOPBAR -->
        <header class="sticky top-0 z-20 bg-blue-900 backdrop-blur border-b border-blue-950/30">
            <div class="px-4 lg:px-6 py-3 flex items-center justify-between">

                <!-- LEFT: Mobile Button + Brand -->
                <div class="flex items-center gap-3">
                    <button
                        @click="toggle()"
                        class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl hover:bg-white/10 text-white transition"
                        aria-label="Open Menu"
                    >
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/logo-sig.png') }}" alt="SIG Logo" class="h-9 w-auto">
                        <img src="{{ asset('images/logo-st2.png') }}" alt="Semen Tonasa Logo" class="h-9 w-auto">
                    </div>

                    <div class="hidden md:flex flex-col text-white leading-tight">
                        <span class="font-extrabold tracking-tight">SECTION OF WORKSHOP</span>
                        <span class="text-xs text-white/80">Dept. Of Project Management & Main Support</span>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="flex items-center gap-3">

                    <!-- NOTIFICATION -->
                    <div x-data="{ open:false }" class="relative">
                        <button
                            @click="open=!open"
                            @click.outside="open=false"
                            class="relative inline-flex items-center justify-center w-10 h-10 rounded-xl hover:bg-white/10 text-white transition"
                            aria-label="Notifications"
                        >
                            <i data-lucide="bell" class="w-5 h-5"></i>

                            @if($adminUnreadCount > 0)
                                <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 text-[10px]
                                             bg-red-600 text-white rounded-full
                                             flex items-center justify-center">
                                    {{ $adminUnreadCount > 9 ? '9+' : $adminUnreadCount }}
                                </span>
                            @endif
                        </button>

                        <div
                            x-show="open"
                            x-transition.origin.top.right
                            x-cloak
                            class="absolute right-0 mt-2 w-[360px] max-w-[90vw] bg-white rounded-2xl shadow-xl
                                   border border-slate-200 z-50 max-h-[420px] overflow-y-auto"
                        >
                            @include('admin.partials.notification-dropdown')
                        </div>
                    </div>

                    <!-- PROFILE (pakai komponen x-dropdown kamu) -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white text-blue-700 hover:bg-blue-50 transition">
                                <span class="inline-flex w-9 h-9 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                                    <i data-lucide="user" class="w-5 h-5"></i>
                                </span>
                                <span class="hidden sm:block text-sm font-semibold">
                                    {{ Auth::user()->name ?? 'Admin' }}
                                </span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-blue-400"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <a href="{{ route('profile.edit') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                Edit Profile
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
