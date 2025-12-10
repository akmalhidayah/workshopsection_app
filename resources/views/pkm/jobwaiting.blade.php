<x-pkm-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            {{ __('Job Waiting') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            {{-- FILTER --}}
            <div class="bg-white border border-gray-200 rounded-lg p-3 flex flex-col md:flex-row md:items-center gap-3">
                <form method="GET" action="{{ route('pkm.jobwaiting') }}" class="flex flex-1 gap-2 items-center flex-wrap">
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-600">Prioritas</label>
                        <select name="priority" class="px-2 py-1 rounded border border-gray-300 text-sm" onchange="this.form.submit()">
                            <option value="">Semua Prioritas</option>
                            <option value="Urgently" {{ request('priority') == 'Urgently' ? 'selected' : '' }}>Urgently</option>
                            <option value="Hard" {{ request('priority') == 'Hard' ? 'selected' : '' }}>Hard</option>
                            <option value="Medium" {{ request('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                            <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>

                    <div class="flex-1 min-w-[180px]">
                        <label class="text-xs text-gray-600 sr-only">Cari</label>
                        <input type="text" name="search" class="w-full px-3 py-2 rounded border border-gray-300 text-sm"
                            placeholder="Cari Nomor Order atau Job..." value="{{ request('search') }}">
                    </div>

                    <div class="flex gap-2 ml-auto">
                        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-2 rounded-md text-sm">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('pkm.jobwaiting') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-2 rounded-md text-sm">
                            <i class="fas fa-sync-alt mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- PAGINATION TOP --}}
            <div class="flex items-center justify-between">
                <div class="text-xs text-gray-600">Menampilkan {{ $notifications->firstItem() ?? 0 }} - {{ $notifications->lastItem() ?? 0 }} dari {{ $notifications->total() }} pekerjaan</div>
                <div>{{ $notifications->links('pagination::tailwind') }}</div>
            </div>

            {{-- GRID CARDS --}}
            @if($notifications->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($notifications as $notification)
                        <div class="flex flex-col h-full bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                            {{-- Header --}}
                            <div class="px-3 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-[10px] font-semibold opacity-90">Nomor Order</div>
                                        <div class="text-sm font-bold truncate">
                                            <i class="fas fa-bell mr-1"></i>{{ $notification->notification_number }}
                                        </div>
                                    </div>

                                    <div class="text-right min-w-[70px]">
                                        <div class="text-[10px] font-semibold opacity-90">Prioritas</div>
                                        <div class="text-xs font-semibold">
                                            @switch($notification->priority)
                                                @case('Urgently') <span class="text-red-100">Urgent</span>@break
                                                @case('Hard')     <span class="text-yellow-100">High</span>@break
                                                @case('Medium')   <span class="text-blue-100">Medium</span>@break
                                                @case('Low')      <span class="text-green-100">Low</span>@break
                                                @default         <span class="text-gray-100">Not Set</span>
                                            @endswitch
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Body compact --}}
                            <div class="p-3 text-xs text-gray-800 flex-1 flex flex-col">
                                {{-- Job name --}}
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-thumbtack text-red-500 text-sm"></i>
                                    <div class="font-semibold truncate">{{ $notification->job_name ?? 'Nama pekerjaan tidak tersedia' }}</div>
                                </div>


{{-- Badges / dokumen small (sederhana & defensif) --}}
@php
    // pastikan kita selalu punya koleksi/array jenis dokumen
    $dokCollection = collect(optional($notification->dokumenOrders)->pluck('jenis_dokumen') ?? []);
    $docTypes = $dokCollection->map(function($v) {
        return strtolower(trim((string) $v));
    })->values()->all();

    // fungsi bantu: contains case-insensitive (cek substring juga)
    $hasDoc = function(array $possibles) use ($docTypes) {
        foreach ($docTypes as $d) {
            foreach ($possibles as $p) {
                if ($d === $p || strpos($d, $p) !== false) {
                    return true;
                }
            }
        }
        return false;
    };

    $isAbnormal = $hasDoc(['abnormalitas','abnormal','abnormality']);
    $isGambar   = $hasDoc(['gambar_teknik','gambar','gambar-teknik','technical_image']);
    $isScope    = (!empty($notification->scopeOfWork))
                    || $hasDoc(['scope_of_work','scope','scopeofwork','scope-of-work']);
@endphp

<div class="grid grid-cols-2 gap-2 text-[11px] mb-2">
    {{-- Abnormalitas --}}
    <div class="flex items-center gap-2">
        <i class="fas fa-exclamation-circle text-red-500 text-xs"></i>
        @if($isAbnormal)
            <a href="{{ route('dokumen_orders.view', ['notificationNumber' => $notification->notification_number, 'jenis' => 'abnormalitas']) }}"
               class="text-red-600 font-medium truncate" target="_blank">Abnormalitas</a>
        @else
            <span class="text-gray-400 truncate">Abnormalitas -</span>
        @endif
    </div>

{{-- Scope of Work --}}
<div class="flex items-center gap-2">
    <i class="fas fa-tasks text-green-500 text-xs"></i>
    @if($isScope)
        {{-- gunakan route download_pdf bukan view --}}
        <a href="{{ route('dokumen_orders.scope.download_pdf', $notification->notification_number) }}" class="text-green-600 font-medium truncate" target="_blank">Scope of Work</a>
    @else
        <span class="text-gray-400 truncate">Scope -</span>
    @endif
</div>


    {{-- Gambar Teknik --}}
    <div class="flex items-center gap-2">
        <i class="fas fa-image text-blue-500 text-xs"></i>
        @if($isGambar)
            <a href="{{ route('dokumen_orders.view', ['notificationNumber' => $notification->notification_number, 'jenis' => 'gambar_teknik']) }}" class="text-blue-600 font-medium truncate" target="_blank">Gambar Teknik</a>
        @else
            <span class="text-gray-400 truncate">Gambar -</span>
        @endif
    </div>

{{-- HPP  --}}
<div class="flex items-center gap-2">
    <i class="fas fa-file-pdf text-xs" style="color:#d9460d"></i>

    @if(!empty($notification->isHppAvailable) || !empty($notification->has_hpp_fallback))
        @if(!empty($notification->download_route_name) && \Illuminate\Support\Facades\Route::has($notification->download_route_name))
            <a href="{{ route($notification->download_route_name, $notification->notification_number) }}" class="text-orange-600 font-medium truncate" target="_blank" rel="noopener noreferrer">HPP</a>
        @elseif(!empty($notification->has_hpp_fallback) && !empty($notification->hpp_file_path))
            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($notification->hpp_file_path) }}" class="text-orange-600 font-medium truncate" target="_blank" rel="noopener noreferrer">HPP</a>
        @else
            <span class="text-orange-600 font-medium truncate">HPP (tersedia)</span>
        @endif
    @else
        <span class="text-gray-400 truncate">HPP -</span>
    @endif
</div>


    {{-- PO/PR --}}
    <div class="flex items-center gap-2">
        <i class="fas fa-receipt text-xs text-blue-400"></i>
        @if(optional($notification->purchaseOrder)->po_document_path)
            <a href="{{ Storage::url($notification->purchaseOrder->po_document_path) }}" class="text-blue-600 font-medium truncate" target="_blank">PO/PR</a>
        @else
            <span class="text-gray-400 truncate">PO/PR -</span>
        @endif
    </div>

    {{-- Initial Work / SPK --}}
    <div class="flex items-center gap-2">
        <i class="fas fa-file-contract text-xs text-indigo-500"></i>
        @if(!empty($notification->isSpkAvailable))
            <a href="{{ route('spk.show', ['notification_number' => $notification->notification_number]) }}" class="text-indigo-600 font-medium truncate" target="_blank">Initial Work</a>
        @else
            <span class="text-gray-400 truncate">Initial Work -</span>
        @endif
    </div>
</div>


                                
                                {{-- Compact actions --}}
                                <div class="flex gap-2 mt-auto">
                                    <a href="{{ route('pkm.items.index') }}"
                                       id="material-btn-{{ $notification->notification_number }}"
                                       class="px-2 py-1 text-xs rounded bg-gray-600 hover:bg-gray-700 text-white w-1/2 text-center {{ optional($notification->purchaseOrder)->progress_pekerjaan >= 11 ? 'disabled-btn' : '' }}">
                                        Pengadaan
                                    </a>

                                    <button type="button"
                                            id="progress-btn-{{ $notification->notification_number }}-11"
                                            onclick="updateProgress('{{ $notification->notification_number }}', 11, false)"
                                            class="px-2 py-1 text-xs rounded bg-yellow-500 hover:bg-yellow-600 text-white w-1/2 {{ optional($notification->purchaseOrder)->progress_pekerjaan >= 11 ? 'disabled-btn' : '' }}"
                                            {{ optional($notification->purchaseOrder)->progress_pekerjaan >= 11 ? 'disabled' : '' }}>
                                        Start
                                    </button>
                                </div>

                                {{-- Details toggle (collapse) --}}
                                <div class="mt-3">
                                    <button type="button" class="text-xs text-orange-600 font-medium toggle-details" data-target="#details-{{ $notification->notification_number }}">
                                        Show details ▾
                                    </button>

                                    <div id="details-{{ $notification->notification_number }}" class="mt-2 hidden text-[12px]">
                                        {{-- Slider --}}
                                        <div class="flex items-center gap-2 mb-2">
                                            <input type="range" min="11" max="100" step="1"
                                                   value="{{ optional($notification->purchaseOrder)->progress_pekerjaan ?? 0 }}"
                                                   class="w-full"
                                                   id="progress-slider-{{ $notification->notification_number }}"
                                                   oninput="updateSliderValue(this.value, '{{ $notification->notification_number }}')"
                                                   onchange="updateProgress('{{ $notification->notification_number }}', this.value, true)"
                                                   {{ optional($notification->purchaseOrder)->progress_pekerjaan < 11 ? 'disabled' : '' }}>
                                            <div class="w-12 text-right font-semibold" id="slider-value-{{ $notification->notification_number }}">{{ optional($notification->purchaseOrder)->progress_pekerjaan ?? 0 }}%</div>
                                        </div>

                                        {{-- Form update target & catatan --}}
                                        <form method="POST" action="{{ route('pkm.jobwaiting.updateProgress', ['notification_number' => $notification->notification_number]) }}">
                                            @csrf
                                            <div class="grid grid-cols-1 gap-2">
                                                <div>
                                                    <label class="text-[11px] text-gray-600">Target Penyelesaian</label>
                                                    <input type="date" name="target_penyelesaian" class="w-full mt-1 text-sm rounded border border-gray-300 px-2 py-1"
                                                        value="{{ optional($notification->purchaseOrder)->target_penyelesaian ?? '' }}">
                                                </div>

                                                <div>
                                                    <label class="text-[11px] text-gray-600">Status Persetujuan</label>
                                                    <div class="flex items-center gap-2 text-[12px]">
                                                        <i class="fas fa-check-circle {{ optional($notification->purchaseOrder)->approval_target === 'setuju' ? 'text-green-500' : 'text-gray-400' }}"></i>
                                                        <span class="font-medium">
                                                            @if(optional($notification->purchaseOrder)->approval_target === 'setuju')
                                                                Disetujui oleh Admin Bengkel
                                                            @elseif(optional($notification->purchaseOrder)->approval_target === 'tidak_setuju')
                                                                Tidak Disetujui oleh Admin Bengkel
                                                            @else
                                                                Belum Ditentukan
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="text-[11px] text-gray-600">Catatan Anda</label>
                                                    <textarea name="catatan" rows="2" class="w-full mt-1 text-sm rounded border border-gray-300 px-2 py-1" placeholder="Catatan...">{{ optional($notification->purchaseOrder)->catatan ?? '' }}</textarea>
                                                </div>

                                                @if(!empty(optional($notification->purchaseOrder)->catatan_pkm))
                                                    <div class="bg-gray-50 border border-gray-200 rounded p-2 text-[12px]">
                                                        <strong class="text-[11px] text-gray-600">Catatan dari Admin Bengkel:</strong>
                                                        <div class="mt-1 text-[13px] text-gray-800 whitespace-pre-line">{{ $notification->purchaseOrder->catatan_pkm }}</div>
                                                    </div>
                                                @endif

                                                <div class="flex gap-2">
                                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded">Update</button>
                                                    <button type="button" class="bg-gray-200 text-xs px-3 py-1 rounded toggle-details" data-target="#details-{{ $notification->notification_number }}">Close</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div> {{-- end body --}}
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white border border-gray-200 rounded-lg p-6 text-center text-sm text-gray-500">
                    Tidak ada pekerjaan yang menunggu saat ini.
                </div>
            @endif

            {{-- PAGINATION BOTTOM --}}
            <div class="flex items-center justify-between">
                <div class="text-xs text-gray-600">Menampilkan {{ $notifications->firstItem() ?? 0 }} - {{ $notifications->lastItem() ?? 0 }} dari {{ $notifications->total() }} pekerjaan</div>
                <div>{{ $notifications->links('pagination::tailwind') }}</div>
            </div>
        </div>
    </div>

    {{-- Styles untuk card kecil / disabled --}}
    <style>
        .disabled-btn {
            background-color: #d1d5db !important;
            color: #6b7280 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }
        /* pastikan tombol toggle terlihat kecil */
        .toggle-details { cursor: pointer; }
    </style>

    {{-- SweetAlert untuk notifikasi sesi sukses --}}
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Toggle details (show/hide) simple
        document.addEventListener('click', function(e) {
            const toggle = e.target.closest('.toggle-details');
            if (!toggle) return;
            const targetSelector = toggle.dataset.target;
            if (!targetSelector) return;
            const el = document.querySelector(targetSelector);
            if (!el) return;
            el.classList.toggle('hidden');
            // update button text
            if (!el.classList.contains('hidden')) {
                toggle.innerText = 'Hide details ▴';
            } else {
                toggle.innerText = 'Show details ▾';
            }
        });

        // updateSliderValue same as sebelumnya
        function updateSliderValue(value, notificationNumber) {
            let sliderValueElement = document.getElementById(`slider-value-${notificationNumber}`);
            if (sliderValueElement) {
                sliderValueElement.innerText = `${value}%`;
            }
        }

        // updateProgress via fetch. Endpoint: /pkm/jobwaiting/update-progress/{notificationNumber}
        function updateProgress(notificationNumber, progressValue, isSlider = false) {
            const button = document.querySelector(`#progress-btn-${notificationNumber}-11`);
            const materialButton = document.querySelector(`#material-btn-${notificationNumber}`);
            const slider = document.getElementById(`progress-slider-${notificationNumber}`);

            // Real-time UI update
            updateSliderValue(progressValue, notificationNumber);

            if (button && button.disabled && !isSlider) return;

            if (button && !isSlider) {
                button.disabled = true;
                button.classList.add('disabled-btn');
            }
            if (materialButton) {
                materialButton.disabled = true;
                materialButton.classList.add('disabled-btn');
            }

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
                if (slider) {
                    slider.value = data.new_progress;
                    updateSliderValue(data.new_progress, notificationNumber);
                }
                // optionally show small toast
            })
            .catch(err => {
                console.error("Error updateProgress:", err);
            });
        }

        // Sweetalert success
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
