<x-pkm-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            {{ __('Job Waiting') }}
        </h2>
    </x-slot>

    <!-- Filter Prioritas dan Pencarian -->
    <div class="mb-4">
        <form method="GET" action="{{ route('pkm.jobwaiting') }}" class="flex flex-wrap gap-2">
            <!-- Filter Prioritas -->
            <select name="priority" class="px-2 py-1 rounded border-gray-300 text-sm" onchange="this.form.submit()">
                <option value="">Semua Prioritas</option>
                <option value="Urgently" {{ request('priority') == 'Urgently' ? 'selected' : '' }}>Urgently</option>
                <option value="Hard" {{ request('priority') == 'Hard' ? 'selected' : '' }}>Hard</option>
                <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
            </select>

            <!-- Pencarian Berdasarkan Nomor Notifikasi -->
            <input type="text" name="search" class="px-2 py-1 rounded border-gray-300 text-sm" placeholder="Cari Nomor Order" value="{{ request('search') }}" oninput="this.form.submit()">
        </form>
    </div>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-6">
            <div class="bg-white overflow-hidden sm:rounded-lg p-3">
                <h3 class="text-sm font-semibold mb-3">Pekerjaan yang Menunggu</h3>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>

             @if(count($notifications) > 0)
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($notifications as $notification)
        <div class="bg-white border border-gray-300 rounded-lg shadow-sm hover:shadow-md transition duration-200 overflow-hidden">
            
            <!-- 🔹 HEADER CARD: Order, Job, dan Priority dalam satu bar horizontal -->
            <div class="flex justify-between items-center px-3 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white">
                <div class="flex flex-col leading-tight">
                    <span class="text-[10px] font-semibold opacity-80">Nomor Order</span>
                    <span class="text-sm font-bold tracking-wide">
                        <i class="fas fa-bell mr-1"></i>{{ $notification->notification_number }}
                    </span>
                </div>

                <div class="flex flex-col text-right">
                    <span class="text-[10px] font-semibold opacity-80">Prioritas</span>
                    @switch($notification->priority)
                        @case('Urgently')
                            <span class="text-xs font-bold text-red-200">Urgent 🔥</span>
                            @break
                        @case('Hard')
                            <span class="text-xs font-bold text-yellow-200">High ⚡</span>
                            @break
                        @case('Medium')
                            <span class="text-xs font-bold text-blue-200">Medium ⚙️</span>
                            @break
                        @case('Low')
                            <span class="text-xs font-bold text-green-200">Low 🌿</span>
                            @break
                        @default
                            <span class="text-xs text-gray-200">Not Set</span>
                    @endswitch
                </div>
            </div>

            <!-- 🔸 BODY CARD -->
            <div class="p-3 text-xs text-gray-800 space-y-2">
                <!-- Nama pekerjaan -->
                <div class="flex items-center font-semibold text-gray-700">
                    <i class="fas fa-thumbtack text-red-500 mr-1"></i>
                    {{ $notification->job_name ?? 'Nama pekerjaan tidak tersedia' }}
                </div>
<!-- Grid dua kolom untuk file dokumen (rapat & ergonomis) -->
<div class="grid grid-cols-2 gap-x-3 gap-y-1 mt-2 text-[11px] leading-snug">
    <!-- Abnormalitas -->
    <div class="flex items-center space-x-1">
        <i class="fas fa-exclamation-circle text-red-500 text-[12px]"></i>
        @if($notification->isAbnormalAvailable)
            <a href="{{ route('dokumen_orders.view', [
                'notificationNumber' => $notification->notification_number,
                'jenis' => 'abnormalitas'
            ]) }}"
               class="text-red-500 font-semibold hover:underline truncate"
               target="_blank">Abnormalitas</a>
        @else
            <span class="text-gray-500 truncate">Abnormalitas: -</span>
        @endif
    </div>

    <!-- Scope of Work -->
    <div class="flex items-center space-x-1">
        <i class="fas fa-tasks text-green-500 text-[12px]"></i>
        @if($notification->isScopeOfWorkAvailable)
            <a href="{{ route('dokumen_orders.scope.view', $notification->notification_number) }}"
               class="text-green-500 font-semibold hover:underline truncate"
               target="_blank">Scope of Work</a>
        @else
            <span class="text-gray-500 truncate">Scope: -</span>
        @endif
    </div>

    <!-- Gambar Teknik -->
    <div class="flex items-center space-x-1">
        <i class="fas fa-image text-blue-500 text-[12px]"></i>
        @if($notification->isGambarTeknikAvailable)
            <a href="{{ route('dokumen_orders.view', [
                'notificationNumber' => $notification->notification_number,
                'jenis' => 'gambar_teknik'
            ]) }}"
               class="text-blue-500 font-semibold hover:underline truncate"
               target="_blank">Gambar Teknik</a>
        @else
            <span class="text-gray-500 truncate">Gambar: -</span>
        @endif
    </div>

    <!-- Dokumen HPP -->
    <div class="flex items-center space-x-1">
        @if($notification->isHppAvailable)
            @php
                $hppColor = match($notification->source_form) {
                    'createhpp1' => 'text-red-500',
                    'createhpp2' => 'text-blue-500',
                    'createhpp3' => 'text-green-500',
                    default => 'text-gray-500',
                };
            @endphp
            <a href="{{ route('pkm.download_hpp', ['notification_number' => $notification->notification_number]) }}"
               class="{{ $hppColor }} font-semibold hover:underline flex items-center space-x-1 truncate"
               target="_blank" title="Unduh Dokumen HPP">
                <i class="fas fa-file-pdf text-[12px]"></i>
                <span>Dokumen HPP</span>
            </a>
        @else
            <span class="text-gray-500 truncate">HPP: -</span>
        @endif
    </div>

    <!-- Dokumen PO -->
    <div class="flex items-center space-x-1">
        <i class="fas fa-receipt text-blue-400 text-[12px]"></i>
        @if($notification->purchaseOrder && $notification->purchaseOrder->po_document_path)
            <a href="{{ Storage::url($notification->purchaseOrder->po_document_path) }}"
               target="_blank"
               class="text-blue-500 font-semibold hover:underline truncate">Dokumen PO/PR</a>
        @else
            <span class="text-gray-500 truncate">PO/PR: -</span>
        @endif
    </div>

    <!-- Dokumen SPK -->
    <div class="flex items-center space-x-1">
        <i class="fas fa-file-contract text-indigo-500 text-[12px]"></i>
        @if($notification->isSpkAvailable)
            <a href="{{ route('spk.show', ['notification_number' => $notification->notification_number]) }}"
               class="text-indigo-500 font-semibold hover:underline truncate"
               target="_blank">Initial Work</a>
        @else
            <span class="text-gray-500 truncate">Initial Work: -</span>
        @endif
    </div>
</div>

                                <!-- Wrapper untuk Tombol -->
                                <div class="flex space-x-2">
                                    <!-- Tombol Pengadaan Material -->
                                    <a href="{{ route('pkm.items.index') }}"
                                        id="material-btn-{{ $notification->notification_number }}"
                                        class="progress-button px-3 py-1 text-xs rounded bg-gray-500 hover:bg-gray-700 text-white w-full
                                        {{ $notification->purchaseOrder->progress_pekerjaan >= 11 ? 'disabled-btn' : '' }}">
                                        Pengadaan Kebutuhan Item
                                    </a>

                                    <!-- Tombol Start Pekerjaan -->
                                    <button id="progress-btn-{{ $notification->notification_number }}-11"
                                        type="button"
                                        onclick="updateProgress('{{ $notification->notification_number }}', 11, false)"
                                        class="progress-button px-3 py-1 text-xs rounded bg-yellow-500 hover:bg-yellow-700 text-white w-full
                                        {{ $notification->purchaseOrder->progress_pekerjaan >= 11 ? 'disabled-btn' : '' }}"
                                        data-progress="{{ $notification->purchaseOrder->progress_pekerjaan }}"
                                        {{ $notification->purchaseOrder->progress_pekerjaan >= 11 ? 'disabled' : '' }}>
                                        Start Pekerjaan
                                    </button>
                                </div>
                           <!-- Wrapper untuk Slider & Persentase -->
                            <div class="mt-2 flex items-center w-full space-x-2">
                                <!-- Slider Progress -->
                                <input type="range" min="11" max="100" step="1" 
                                value="{{ $notification->purchaseOrder->progress_pekerjaan }}" 
                                class="w-full cursor-pointer"
                                id="progress-slider-{{ $notification->notification_number }}"
                                oninput="updateSliderValue(this.value, '{{ $notification->notification_number }}')" 
                                onchange="updateProgress('{{ $notification->notification_number }}', this.value, true)"
                                {{ $notification->purchaseOrder->progress_pekerjaan < 11 ? 'disabled' : '' }}>

                                <!-- Menampilkan Nilai Slider di Samping -->
                                <span id="slider-value-{{ $notification->notification_number }}" 
                                    class="text-xs font-semibold text-gray-700 w-10 text-right">
                                    {{ $notification->purchaseOrder->progress_pekerjaan }}%
                                </span>
                            </div>
                                <!-- Form Update Progress -->
                                <form method="POST" action="{{ route('pkm.jobwaiting.updateProgress', ['notification_number' => $notification->notification_number]) }}">
                                    @csrf
                                    <div class="mt-2 text-xs">
                                        <label for="target_penyelesaian-{{ $notification->notification_number }}" class="font-medium text-gray-700">Target Penyelesaian</label>
                                        <input type="date" name="target_penyelesaian" 
                                            id="target_penyelesaian-{{ $notification->notification_number }}"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-xs"
                                            value="{{ $notification->purchaseOrder->target_penyelesaian ?? '' }}">
                                    </div>
                                    <div class="mt-2 flex items-center space-x-1 text-xs">
                                        <i class="fas fa-check-circle {{ $notification->purchaseOrder->approval_target === 'setuju' ? 'text-green-500' : 'text-red-500' }}"></i>
                                        <span class="font-semibold">
                                            @if(optional($notification->purchaseOrder)->approval_target === 'setuju')
                                                Disetujui oleh Admin Bengkel
                                            @elseif(optional($notification->purchaseOrder)->approval_target === 'tidak_setuju')
                                                Tidak Disetujui oleh Admin Bengkel
                                            @else
                                                Belum Ditentukan
                                            @endif
                                        </span>
                                    </div>
                                    <!-- Catatan PKM (yang bisa diisi PKM sendiri) -->
                                    <textarea name="catatan" rows="2"
                                        id="catatan-{{ $notification->notification_number }}"
                                        class="w-full mt-2 px-2 py-1 border border-gray-300 rounded-lg text-xs focus:ring-orange-400 focus:border-orange-400"
                                        placeholder="Catatan Anda untuk pekerjaan ini...">{{ $notification->purchaseOrder->catatan ?? '' }}</textarea>

                                    <!-- Catatan dari Admin Bengkel -->
                                    @if(!empty($notification->purchaseOrder->catatan_pkm))
                                        <div class="mt-2 bg-gray-100 border border-gray-300 rounded-lg p-2">
                                            <span class="block text-[11px] text-gray-600 font-semibold mb-1">
                                                💬 Catatan dari Admin Bengkel:
                                            </span>
                                            <p class="text-[12px] text-gray-800 leading-snug whitespace-pre-line">
                                                {{ $notification->purchaseOrder->catatan_pkm }}
                                            </p>
                                        </div>
                                    @endif
                                    <!-- Tombol Submit -->
                                    <button type="submit" class="mt-3 bg-blue-500 text-white px-3 py-1 text-xs rounded hover:bg-blue-600 transition-colors">
                                        Update
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 mt-3">Tidak ada pekerjaan yang menunggu saat ini.</p>
                @endif
        </div>
    </div>
    <style>
    .disabled-btn {
        background-color: #d1d5db !important; /* Warna abu-abu */
        color: #6b7280 !important; /* Warna teks lebih gelap */
        cursor: not-allowed !important;
        pointer-events: none !important;
    }
</style>

    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
// Fungsi untuk menampilkan nilai slider secara real-time saat digeser
function updateSliderValue(value, notificationNumber) {
    let sliderValueElement = document.getElementById(`slider-value-${notificationNumber}`);
    if (sliderValueElement) {
        sliderValueElement.innerText = `${value}%`; // Menampilkan angka persen saat slider bergerak
    }
}

// Fungsi untuk menangani perubahan slider dan mengupdate backend setelah lepas slider
function updateProgress(notificationNumber, progressValue, isSlider = false) {
    let button = document.querySelector(`#progress-btn-${notificationNumber}-11`);
    let materialButton = document.querySelector(`#material-btn-${notificationNumber}`);
    let slider = document.getElementById(`progress-slider-${notificationNumber}`);
    let sliderValueElement = document.getElementById(`slider-value-${notificationNumber}`);

    // Update angka persen di UI saat slider digeser (tanpa backend)
    updateSliderValue(progressValue, notificationNumber);

    // Cegah update ganda jika tombol sudah disabled
    if (button && button.disabled && !isSlider) return;

    // Nonaktifkan tombol "Start Pekerjaan" setelah diklik
    if (button && !isSlider) {
        button.disabled = true;
        button.classList.add('disabled-btn'); // Tambah class abu-abu
        button.classList.remove('hover:bg-gray-700', 'hover:bg-yellow-700');
    }

    // Nonaktifkan tombol "Pengadaan Kebutuhan" setelah progress berjalan
    if (materialButton) {
        materialButton.disabled = true;
        materialButton.classList.add('disabled-btn'); // Tambah class abu-abu
        materialButton.classList.remove('hover:bg-gray-700');
    }

    // Kirim update progress ke backend setelah slider dilepas
    fetch(`/pkm/jobwaiting/update-progress/${notificationNumber}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ progress_pekerjaan: progressValue })
    })
    .then(response => {
        if (!response.ok) throw new Error("Gagal memperbarui progress");
        return response.json();
    })
    .then(data => {
        // Update nilai slider dan teks persen setelah sukses update ke backend
        if (slider) {
            slider.value = data.new_progress;
            updateSliderValue(data.new_progress, notificationNumber);
        }
    })
    .catch(error => {
        console.error("Error saat update progress:", error);
    });
}

// Alert hanya untuk Update Target & Catatan
@if(session('success'))
    Swal.fire({
        title: "Berhasil!",
        text: "{{ session('success') }}",
        icon: "success",
        confirmButtonText: "OK"
    });
@endif
</script>
</x-pkm-layout>