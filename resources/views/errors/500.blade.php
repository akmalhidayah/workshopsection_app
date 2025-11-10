{{-- resources/views/errors/500.blade.php --}}
<x-admin-layout>
    <div x-data="{ show: true }">
        <!-- Overlay (semi-transparent, klik tidak menutup agar user paksa memilih tombol) -->
        <div
            x-show="show"
            x-transition.opacity
            class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-start justify-center pt-24"
            aria-hidden="true"
        >
            <!-- Modal kecil -->
            <div
                x-show="show"
                x-transition.scale.duration.200ms
                class="w-full max-w-lg mx-4 bg-white rounded-xl shadow-xl overflow-hidden"
                role="dialog" aria-modal="true"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 bg-red-600 text-white">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-triangle text-2xl"></i>
                        <div>
                            <div class="font-semibold">Error 500 - Server Error</div>
                            <div class="text-xs opacity-90">Terjadi kesalahan internal</div>
                        </div>
                    </div>

                    <!-- Tombol close (jika mau hanya hide tanpa pindah halaman) -->
                    <button type="button" @click="show = false" class="text-white hover:opacity-90 p-1 rounded">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="px-6 py-5">
                    <p class="text-sm text-gray-700 mb-2">
                        {{ $message ?? 'Terjadi kesalahan saat memproses permintaan.' }}
                    </p>
                    <p class="text-xs text-gray-500 mb-4">
                        Mohon coba lagi nanti atau hubungi admin jika masalah berlanjut.
                    </p>

                    <!-- Opsional: tampilkan detail error (developer only) -->
                    @if(config('app.debug'))
                        <div class="bg-gray-50 border border-gray-100 rounded p-3 text-xs text-red-600 mb-3">
                            {{-- Jika controller mengirimkan $exception atau $debug, tampilkan --}}
                            @isset($exception)
                                {{ \Illuminate\Support\Str::limit($exception->getMessage(), 400) }}
                            @endisset
                            @isset($debug)
                                {{ \Illuminate\Support\Str::limit($debug, 400) }}
                            @endisset
                        </div>
                    @endif

                    <div class="flex justify-end gap-3">
                        <!-- TOMBOL: kembali ke Purchase Order (sesuai permintaan) -->
                       <a 
                        href="{{ url()->previous() }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-md"
                    >
                        ← Kembali
                    </a>
                    
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
