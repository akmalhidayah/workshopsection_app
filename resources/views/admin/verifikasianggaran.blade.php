<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            {{ __('Verifikasi Anggaran') }}
        </h2>
    </x-slot>

    <!-- 🔹 Pencarian & Kontrol Pagination dalam SATU BARIS -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4 p-3 bg-gray-100 rounded-lg shadow">
        <form method="GET" action="{{ route('admin.verifikasianggaran.index') }}" class="flex w-full sm:w-auto gap-3">
            <div class="relative flex-grow">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Cari Nomor Order..." 
                    value="{{ request('search') }}"
                    class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full focus:ring-2 focus:ring-blue-300 shadow-sm"
                >
                <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-blue-600 text-white rounded-md p-2 hover:bg-blue-700 transition">
                    <i class="fas fa-search text-sm"></i>
                </button>
            </div>

            <div class="flex items-center gap-2">
                <label for="entries" class="text-sm text-gray-600 hidden sm:block">Show:</label>
                <select 
                    name="entries" 
                    id="entries"
                    onchange="this.form.submit()" 
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 shadow-sm"
                >
                    <option value="5" {{ request('entries') == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ request('entries') == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('entries') == 15 ? 'selected' : '' }}>15</option>
                </select>
            </div>
        </form>
    </div>

    <!-- 🔹 Daftar Notifikasi (Menjadi Grid Rapi) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-3">
        @forelse($notifications as $notification)
            @if($notification->isAbnormalAvailable && $notification->isScopeOfWorkAvailable && $notification->isGambarTeknikAvailable && $notification->isHppAvailable)
                <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
                    
                    <!-- 🔹 Nomor Notifikasi & Deskripsi Abnormal -->
                    <div class="mb-2">
                        <span class="text-gray-800 font-semibold text-sm flex items-center gap-1">
                            📌 {{ $notification->notification_number }}
                        </span>
                        <p 
                            class="text-xs text-gray-500 italic"
                            data-abnormal-title="{{ $notification->abnormal ? $notification->abnormal->abnormal_title : '' }}">
                            {{ $notification->abnormal ? $notification->abnormal->abnormal_title : 'Tidak ada deskripsi' }}
                        </p>
                        <span class="text-gray-500 text-xs">📅 {{ $notification->update_date ? $notification->update_date->format('Y-m-d') : '-' }}</span>
                    </div>

                    <!-- 🔹 Dokumen yang tersedia (Flexbox agar RAPIH) -->
                    <div class="flex flex-wrap gap-2 mb-3 text-xs">
                        @if($notification->isAbnormalAvailable)
                            <a href="{{ route('admin.abnormal.download_pdf', $notification->notification_number) }}" 
                               class="bg-gray-400 text-black px-2 py-1 rounded hover:bg-blue-400 transition">
                                📄 Abnormalitas
                            </a>
                        @endif
                        @if($notification->isScopeOfWorkAvailable)
                            <a href="{{ route('scopeofwork.view', $notification->notification_number) }}" 
                               class="bg-gray-400 text-black px-2 py-1 rounded hover:bg-blue-400 transition">
                                📄 Scope of Work
                            </a>
                        @endif
                        @if($notification->isGambarTeknikAvailable)
                            <a href="{{ route('view-dokumen', $notification->notification_number) }}" 
                               class="bg-gray-400 text-black px-2 py-1 rounded hover:bg-blue-400 transition">
                                📄 Gambar Teknik
                            </a>
                        @endif
                        
                        @php
                        $hppRoutes = [
                            'createhpp1' => route('admin.inputhpp.download_hpp1', ['notification_number' => $notification->notification_number]),
                            'createhpp2' => route('admin.inputhpp.download_hpp2', ['notification_number' => $notification->notification_number]),
                            'createhpp3' => route('admin.inputhpp.download_hpp3', ['notification_number' => $notification->notification_number]),
                        ];

                        $hppColors = [
                            'createhpp1' => 'bg-gray-400 hover:bg-blue-400',
                            'createhpp2' => 'bg-gray-400 hover:bg-blue-400',
                            'createhpp3' => 'bg-gray-400 hover:bg-blue-400',
                        ];

                        $hppRoute = $hppRoutes[$notification->source_form] ?? null;
                        $hppColor = $hppColors[$notification->source_form] ?? 'bg-gray-500 hover:bg-gray-700';
                        @endphp

                        @if($hppRoute)
                            <a href="{{ $hppRoute }}" class="{{ $hppColor }} text-black px-2 py-1 rounded-lg text-xs">
                                📄 HPP
                            </a>
                        @endif
                    </div>

                    <!-- 🔹 Total Anggaran & Status -->
                    <div class="flex justify-between items-center text-sm">
                        <div class="text-gray-700 font-semibold">
                            💰 Rp{{ number_format($notification->total_amount, 0, ',', '.') }}
                        </div>
                        <form 
                            action="{{ route('notifications.updateStatusAnggaran', $notification->notification_number) }}" 
                            method="POST"
                        >
                        @csrf
                        @method('PATCH')
                        <select 
                            name="status_anggaran" 
                            class="px-2 py-1 rounded text-white text-xs font-medium focus:ring-2 focus:ring-blue-300
                            {{ $notification->status_anggaran === 'Tersedia' ? 'bg-green-500' : ($notification->status_anggaran === 'Tidak Tersedia' ? 'bg-red-500' : 'bg-yellow-500') }}"
                            onchange="this.form.submit()"
                        >
                            <option value="Verifikasi Anggaran" {{ !$notification->status_anggaran ? 'selected' : '' }}>
                                🔍 Verifikasi Anggaran
                            </option>
                            <option value="Tersedia" {{ $notification->status_anggaran === 'Tersedia' ? 'selected' : '' }}>
                                ✅ Tersedia
                            </option>
                            <option value="Tidak Tersedia" {{ $notification->status_anggaran === 'Tidak Tersedia' ? 'selected' : '' }}>
                                ❌ Tidak Tersedia
                            </option>
                        </select>
                        </form>
                    </div>
                </div>
            @else
            @endif
        @empty
        @endforelse
    </div>

    <!-- 🔹 Pagination -->
    <div class="mt-3 px-3">
        {{ $notifications->appends(request()->query())->links() }}
    </div>
    
    <script>
        document.querySelectorAll('select[name="status_anggaran"]').forEach(function(selectElement) {
            selectElement.addEventListener('change', function() {
                this.classList.toggle('bg-green-500', this.value === 'Tersedia');
                this.classList.toggle('bg-red-500', this.value === 'Tidak Tersedia');
            });
        });
    </script>
</x-admin-layout>
