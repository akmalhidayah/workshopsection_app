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
    <!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->
    <link rel="stylesheet" href="{{ asset('build/assets/app-ZZBB8zCG.css') }}">
    <script src="{{ asset('build/assets/app-CH09qwMe.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom Tailwind Styles -->
    <style>
        .form-checkbox:checked {
            background-color: black;
            border-color: black;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen bg-gray-100">
        <!-- Top Navigation -->
        <nav class="bg-blue-900 shadow-lg">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="relative flex items-center justify-between h-16">
                    <!-- Logo Section -->
                    <div class="flex items-center">
                        <!-- Logo -->
                        <img src="{{ asset('images/logo-sig.png') }}" alt="SIG Logo" class="h-10 w-auto mr-2">
                        <img src="{{ asset('images/logo-st2.png') }}" alt="Semen Tonasa Logo" class="h-10 w-auto mr-2">
                        <!-- Text -->
                        <div class="hidden sm:flex flex-col text-white">
                            <span class="font-bold text-base sm:text-xl">SECTION OF WORKSHOP</span>
                            <span class="text-xs sm:text-sm">UNIT OF WORKSHOP DEPT.MAINTENANCE</span>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex sm:hidden">
                        <button id="mobile-menu-button" class="text-white focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="hidden sm:flex items-center">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-500 dark:text-gray-400 bg-white dark:bg-blue-900 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>Welcome {{ Auth::user()->name }}</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
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

                <!-- Mobile Menu -->
                <div id="mobile-menu" class="hidden sm:hidden">
                    <div class="flex flex-col mt-2 space-y-2">
                        <span class="text-white font-bold text-base sm:text-xl">SECTION OF WORKSHOP</span>
                        <span class="text-gray-300 text-xs sm:text-sm">UNIT OF WORKSHOP DEPT.MAINTENANCE</span>
                        <div class="border-t border-gray-600 mt-2"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="text-gray-300 text-sm px-4 py-2 w-full text-left hover:bg-blue-800">Log Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <main class="flex-1 p-4 bg-gray-100">
            {{ $slot }}
        </main>
    </div>

    <script>
        // Toggle mobile menu
        const menuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        menuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
