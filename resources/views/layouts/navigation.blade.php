@php
    $usertype = Auth::check() ? Auth::user()->usertype : null;

    // Menandai apakah submenu ORDER sedang aktif (untuk styling)
    $orderActive = request()->routeIs('notifications.index') || request()->routeIs('dokumen_orders.*');
@endphp

<nav
    x-data="{
        open: false, // mobile menu
        darkMode: localStorage.getItem('theme') === 'dark',
        openOrderDesktop: {{ $orderActive ? 'true' : 'false' }},
        toggleTheme() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', this.darkMode);
        }
    }"
    class="sticky top-0 z-50 border-b border-red-800/70 dark:border-gray-800
           bg-gradient-to-r from-red-800 via-red-700 to-red-700
           dark:from-gray-900 dark:via-gray-900 dark:to-gray-900
           backdrop-blur shadow-md"
>
    <!-- PRIMARY NAV BAR (DESKTOP) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- LEFT : Logos + Title + Desktop Menu -->
            <div class="flex items-center gap-4">
                {{-- LOGO AREA --}}
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo-sig.png') }}" alt="SIG Logo" class="h-9 w-auto drop-shadow-sm">
                    <img src="{{ asset('images/logo-st2.png') }}" alt="Semen Tonasa Logo" class="h-9 w-auto drop-shadow-sm">
                </div>

                {{-- TITLE --}}
                <div class="hidden sm:flex flex-col leading-tight text-white">
                    <span class="font-extrabold text-sm sm:text-base tracking-wide">
                        SECTION OF WORKSHOP
                    </span>
                    <span class="text-[11px] sm:text-xs font-semibold text-red-100/90 dark:text-gray-300">
                        Dept. Of Project Management &amp; Main Support
                    </span>
                </div>

                {{-- DESKTOP MENU (kecuali approval & pkm) --}}
                @if ($usertype !== 'approval' && $usertype !== 'pkm')
                    <div class="hidden sm:flex sm:ml-10 lg:ml-12 items-stretch space-x-3 lg:space-x-5">
                        {{-- DASHBOARD --}}
                        <x-nav-link
                            :href="route('dashboard')"
                            :active="request()->routeIs('dashboard')"
                            class="flex items-center gap-2 text-xs lg:text-sm font-semibold uppercase tracking-wide
                                   text-red-50/90 hover:text-white dark:text-gray-200 dark:hover:text-white
                                   px-3 py-2 rounded-md hover:bg-red-600/80 dark:hover:bg-gray-800/80 transition"
                        >
                            <i class="fas fa-tachometer-alt text-[11px] lg:text-xs"></i>
                            <span class="font-bold">
                                {{ __('Dashboard') }}
                            </span>
                        </x-nav-link>

                        {{-- ORDER (DROPDOWN DESKTOP) --}}
                        <div class="relative flex items-stretch">
                            <button
                                type="button"
                                @click="openOrderDesktop = !openOrderDesktop"
                                class="flex items-center gap-2 text-xs lg:text-sm font-semibold uppercase tracking-wide
                                       px-3 py-2 rounded-md
                                       {{ $orderActive
                                           ? 'bg-red-900/85 text-white shadow-sm'
                                           : 'text-red-50/90 hover:text-white hover:bg-red-600/80' }}
                                       dark:text-gray-200 dark:hover:bg-gray-800/80 transition"
                            >
                                <i class="fas fa-clipboard-list text-[11px] lg:text-xs"></i>
                                <span class="font-bold">Order</span>
                                <i class="fas" :class="openOrderDesktop ? 'fa-chevron-up text-[10px]' : 'fa-chevron-down text-[10px]'"></i>
                            </button>

                            <!-- DROPDOWN MENU -->
                            <div
                                x-show="openOrderDesktop"
                                x-transition
                                class="absolute left-0 mt-2 w-56 rounded-xl bg-white/95 dark:bg-gray-900 border border-red-100/80 dark:border-gray-700 shadow-lg overflow-hidden z-50"
                                style="display: none;"
                            >
                                <div class="py-1">
                                    <a
                                        href="{{ route('notifications.index') }}"
                                        class="flex items-center gap-2 px-4 py-2 text-xs lg:text-sm font-semibold
                                               {{ request()->routeIs('notifications.index')
                                                  ? 'bg-red-100 text-red-800 dark:bg-gray-800 dark:text-white'
                                                  : 'text-gray-800 hover:bg-red-100 hover:text-red-800 dark:text-gray-100 dark:hover:bg-gray-800 dark:hover:text-white' }}"
                                    >
                                        <i class="fas fa-tasks text-[11px]"></i>
                                        <span>Order Pekerjaan</span>
                                    </a>

                                    <a
                                        href="{{ route('dokumen_orders.index') }}"
                                        class="flex items-center gap-2 px-4 py-2 text-xs lg:text-sm font-semibold
                                               {{ request()->routeIs('dokumen_orders.*')
                                                  ? 'bg-red-100 text-red-800 dark:bg-gray-800 dark:text-white'
                                                  : 'text-gray-800 hover:bg-red-100 hover:text-red-800 dark:text-gray-100 dark:hover:bg-gray-800 dark:hover:text-white' }}"
                                    >
                                        <i class="fas fa-file-alt text-[11px]"></i>
                                        <span>Dokumen Order Pekerjaan</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- ORDER PERMINTAAN KAWAT LAS --}}
                        <x-nav-link
                            :href="route('kawatlas.index')"
                            :active="request()->routeIs('kawatlas.index')"
                            class="flex items-center gap-2 text-xs lg:text-sm font-semibold uppercase tracking-wide
                                   text-red-50/90 hover:text-white dark:text-gray-200 dark:hover:text-white
                                   px-3 py-2 rounded-md hover:bg-red-600/80 dark:hover:bg-gray-800/80 transition"
                        >
                            <i class="fas fa-industry text-[11px] lg:text-xs"></i>
                            <span class="font-bold">
                                {{ __('Order Permintaan Kawat Las') }}
                            </span>
                        </x-nav-link>
                    </div>
                @endif
            </div>

          <!-- RIGHT : Theme Toggle + User Dropdown -->
<div class="hidden sm:flex sm:items-center sm:space-x-5">
    {{-- THEME TOGGLE --}}
    <div class="flex items-center gap-2">
        <span x-show="!darkMode" class="inline-flex items-center justify-center">
            <i class="fas fa-sun text-yellow-300 text-sm"></i>
        </span>
        <span x-show="darkMode" class="inline-flex items-center justify-center">
            <i class="fas fa-moon text-gray-200 text-sm"></i>
        </span>

        <label class="relative inline-flex items-center cursor-pointer">
            <input
                type="checkbox"
                class="sr-only peer"
                x-model="darkMode"
                @change="toggleTheme"
            >
            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer transition duration-300 ease-in-out peer-checked:bg-orange-500"></div>
            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transform transition duration-300 ease-in-out peer-checked:translate-x-5"></div>
        </label>
    </div>

    {{-- USER DROPDOWN / LOGIN --}}
    @auth
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button
                    class="inline-flex items-center px-3 py-2 border border-transparent text-xs lg:text-sm leading-4 font-semibold rounded-md
                           text-gray-800 dark:text-gray-200 bg-white/90 dark:bg-gray-800 hover:bg-white dark:hover:bg-gray-700
                           focus:outline-none transition ease-in-out duration-150"
                >
                    <div class="truncate max-w-[140px] lg:max-w-[200px] text-left">
                        {{ Auth::user()->name }}
                    </div>

                    <div class="ml-2">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-dropdown-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-dropdown-link
                        :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                    >
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    @else
        {{-- Kalau guest, tampilkan tombol Login saja --}}
        <a
            href="{{ route('login') }}"
            class="inline-flex items-center px-3 py-2 text-xs lg:text-sm font-semibold rounded-md
                   text-white border border-white/60 hover:bg-white/10 transition"
        >
            <i class="fas fa-sign-in-alt mr-2 text-xs"></i> Login
        </a>
    @endauth
</div>


            <!-- HAMBURGER (MOBILE) -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button
                    @click="open = !open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-red-50 hover:text-white hover:bg-red-700/80
                           focus:outline-none focus:bg-red-800 focus:text-white transition duration-150 ease-in-out"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path
                            :class="{'hidden': open, 'inline-flex': !open }"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                        <path
                            :class="{'hidden': !open, 'inline-flex': open }"
                            class="hidden"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- MOBILE MENU -->
    <div
        :class="{ 'block': open, 'hidden': !open }"
        class="hidden sm:hidden bg-red-800 dark:bg-gray-900 border-t border-red-900/70 dark:border-gray-800"
    >
        @if ($usertype !== 'approval' && $usertype !== 'pkm')
            <div class="pt-2 pb-3 space-y-1" x-data="{ openOrder: {{ $orderActive ? 'true' : 'false' }} }">
                {{-- DASHBOARD --}}
                <x-responsive-nav-link
                    :href="route('dashboard')"
                    :active="request()->routeIs('dashboard')"
                    class="font-semibold"
                >
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-tachometer-alt text-xs"></i>
                        <span>Dashboard</span>
                    </span>
                </x-responsive-nav-link>

                {{-- GROUP ORDER --}}
                <button
                    type="button"
                    @click="openOrder = !openOrder"
                    class="w-full flex items-center justify-between px-4 py-2 text-left text-sm font-semibold
                           {{ $orderActive ? 'bg-red-900/80 text-white' : 'text-red-50 hover:bg-red-700/70 hover:text-white' }}"
                >
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-clipboard-list text-xs"></i>
                        <span>Order</span>
                    </span>
                    <span>
                        <i class="fas" :class="openOrder ? 'fa-chevron-up text-xs' : 'fa-chevron-down text-xs'"></i>
                    </span>
                </button>

                {{-- SUBMENU ORDER --}}
                <div x-show="openOrder" x-transition class="space-y-1 pl-4 pr-2 pb-1">
                    <x-responsive-nav-link
                        :href="route('notifications.index')"
                        :active="request()->routeIs('notifications.index')"
                        class="font-semibold"
                    >
                        <span class="inline-flex items-center gap-2">
                            <i class="fas fa-tasks text-xs"></i>
                            <span>Order Pekerjaan</span>
                        </span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link
                        :href="route('dokumen_orders.index')"
                        :active="request()->routeIs('dokumen_orders.*')"
                        class="font-semibold"
                    >
                        <span class="inline-flex items-center gap-2">
                            <i class="fas fa-file-alt text-xs"></i>
                            <span>Dokumen Order Pekerjaan</span>
                        </span>
                    </x-responsive-nav-link>
                </div>

                {{-- KAWAT LAS --}}
                <x-responsive-nav-link
                    :href="route('kawatlas.index')"
                    :active="request()->routeIs('kawatlas.index')"
                    class="font-semibold"
                >
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-industry text-xs"></i>
                        <span>Order Permintaan Kawat Las</span>
                    </span>
                </x-responsive-nav-link>
            </div>
        @endif

        <!-- MOBILE USER AREA -->
<div class="pt-4 pb-4 border-t border-red-900/70 dark:border-gray-800">
    @auth
        <div class="px-4">
            <div class="font-semibold text-base text-white dark:text-gray-100">
                {{ Auth::user()->name }}
            </div>
            <div class="font-medium text-xs text-red-100/90 dark:text-gray-300">
                {{ Auth::user()->email }}
            </div>
        </div>

        <div class="mt-3 space-y-1">
            <x-responsive-nav-link :href="route('profile.edit')" class="font-semibold">
                <span class="inline-flex items-center gap-2">
                    <i class="fas fa-user-cog text-xs"></i>
                    <span>Profile</span>
                </span>
            </x-responsive-nav-link>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-responsive-nav-link
                    :href="route('logout')"
                    onclick="event.preventDefault(); this.closest('form').submit();"
                    class="font-semibold"
                >
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-sign-out-alt text-xs"></i>
                        <span>Log Out</span>
                    </span>
                </x-responsive-nav-link>
            </form>
        </div>
    @else
        <div class="px-4">
            <a
                href="{{ route('login') }}"
                class="inline-flex items-center px-4 py-2 rounded-md bg-white/10 text-white text-sm font-semibold"
            >
                <i class="fas fa-sign-in-alt mr-2 text-xs"></i>
                <span>Login</span>
            </a>
        </div>
    @endauth
</div>

    </div>
</nav>
