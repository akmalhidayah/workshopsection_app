<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ $title ?? 'Approval Center — '.config('app.name') }}</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>
    .chip{ @apply inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium; }
    .chip-blue{ @apply bg-blue-50 text-blue-700 ring-1 ring-blue-200; }
    .chip-green{ @apply bg-green-50 text-green-700 ring-1 ring-green-200; }
    .chip-purple{ @apply bg-purple-50 text-purple-700 ring-1 ring-purple-200; }
    .chip-amber{ @apply bg-amber-50 text-amber-800 ring-1 ring-amber-200; }
    .chip-slate{ @apply bg-slate-100 text-slate-700 ring-1 ring-slate-300; }
  </style>
</head>
<body class="h-full bg-slate-50 text-slate-800">
  <div class="min-h-full">
    <!-- Top Nav -->
    <nav class="bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/60 sticky top-0 z-40 border-b border-slate-200">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo-sig.png') }}" class="h-8 w-auto" alt="SIG" />
            <img src="{{ asset('images/logo-st2.png') }}" class="h-8 w-auto" alt="ST" />
            <div class="hidden sm:block leading-tight">
              <div class="font-semibold">SECTION OF WORKSHOP</div>
              <div class="text-xs text-slate-500">UNIT OF WORKSHOP · DEPT. MAINTENANCE</div>
            </div>
          </div>

          <!-- Search slot (optional) -->
          @isset($search)
          <div class="flex-1 max-w-xl mx-4 hidden md:block">{{ $search }}</div>
          @endisset

          <div class="flex items-center gap-3">
            <span class="hidden sm:block text-sm leading-tight text-slate-600">
    {{ Auth::user()->name }}<br>
    <span class="text-xs text-slate-400">{{ Auth::user()->display_title }}</span>
</span>

            <x-dropdown align="right" width="48">
              <x-slot name="trigger">
                <button class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm hover:bg-slate-50">
                  <i class="fa-regular fa-user"></i>
                  <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
                </button>
              </x-slot>
              <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">@csrf
                  <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-dropdown-link>
                </form>
              </x-slot>
            </x-dropdown>
            <button id="mobile-menu-button" class="md:hidden inline-flex items-center justify-center rounded-md p-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-bars"></i></button>
          </div>
        </div>
      </div>
      <!-- Mobile quick actions -->
      <div id="mobile-menu" class="md:hidden hidden border-t border-slate-200">
        <div class="px-4 py-3 space-y-2">
          @isset($search)
          <div>{{ $search }}</div>
          @endisset
          <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="w-full text-left rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Log Out</button>
          </form>
        </div>
      </div>
    </nav>

    <!-- Page header slot -->
    @isset($header)
    <header class="bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        {{ $header }}
      </div>
    </header>
    @endisset

    <!-- Main -->
    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
      {{ $slot }}
    </main>
  </div>

  <script>
    const btn = document.getElementById('mobile-menu-button');
    const mob = document.getElementById('mobile-menu');
    if (btn) btn.addEventListener('click',()=> mob.classList.toggle('hidden'));
  </script>
</body>
</html>