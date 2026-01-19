{{-- resources/views/errors/404.blade.php --}}
<x-guest-layout>
    <div class="flex flex-col items-center text-center py-6">

        {{-- ICON / ANIMASI KUCING TIDUR --}}
        <div class="relative mb-4">
            {{-- bantal --}}
            <div class="w-40 h-16 bg-blue-100 rounded-3xl mx-auto shadow-md"></div>

            {{-- kucing --}}
            <div class="absolute inset-0 -top-10 flex justify-center">
                <div class="w-24 h-24 bg-orange-200 rounded-full shadow-lg flex items-center justify-center animate-bounce">
                    <span class="text-5xl">🐱</span>
                </div>
            </div>

            {{-- zzz animasi --}}
            <div class="absolute -right-6 -top-6 flex flex-col items-center gap-1 text-blue-400">
                <span class="text-xs animate-pulse">z</span>
                <span class="text-sm animate-pulse delay-150">z</span>
                <span class="text-base animate-pulse delay-300">z</span>
            </div>
        </div>

        {{-- JUDUL BESAR 404 --}}
        <h1 class="text-4xl font-extrabold tracking-wide text-red-600 mb-1">
            404
        </h1>
        <p class="text-sm uppercase tracking-[0.2em] text-gray-500 mb-4">
            Halaman tidak ditemukan
        </p>

        {{-- TOMBOL AKSI --}}
        <div class="flex flex-col sm:flex-row gap-3 w-full justify-center">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center justify-center px-4 py-2 rounded-md
                      bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow-sm
                      transition">
                <i class="fas fa-home mr-2 text-xs"></i>
                Kembali ke Dashboard
            </a>

            <button type="button"
                onclick="window.history.back()"
                class="inline-flex items-center justify-center px-4 py-2 rounded-md
                       border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-semibold
                       bg-white transition">
                <i class="fas fa-arrow-left mr-2 text-xs"></i>
                Halaman Sebelumnya
            </button>
        </div>

        {{-- CATATAN KECIL --}}
        <p class="mt-6 text-[11px] text-gray-400">
            Jika masalah terus terjadi, hubungi admin Section of Workshop.
        </p>
                <p class="mt-6 text-[11px] text-gray-400">
            Jika masalah terus terjadi, hubungi admin Section of Workshop.
        </p>
    </div>
</x-guest-layout>
