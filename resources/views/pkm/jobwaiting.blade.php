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
                <!-- Pagination Bagian Bawah -->
                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>

                @if(count($notifications) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($notifications as $notification)
                            <div class="bg-gray-50 shadow-md rounded-lg p-4 border border-gray-200 hover:shadow-lg transition duration-200">
                                <h4 class="text-xs font-bold text-gray-800 mb-2 flex items-center space-x-1">
                                    <i class="fas fa-bell text-blue-500"></i>
                                    <span>Nomor Order:</span>
                                </h4>
                                <p class="text-blue-600 text-sm font-semibold">{{ $notification->notification_number }}</p>
                                <div class="mt-2 text-xs flex items-center space-x-2 font-bold">📌 {{ $notification->abnormal->abnormal_title }}</div>
                                <!-- Display Priority Status -->
                                <div class="mt-2 text-xs">
                                    @if($notification->priority == 'Urgently')
                                        <span class="text-red-500 font-bold">Priority: Urgent</span>
                                    @elseif($notification->priority == 'Hard')
                                        <span class="text-orange-500 font-bold">Priority: High</span>
                                    @elseif($notification->priority == 'Medium')
                                        <span class="text-yellow-500 font-bold">Priority: Medium</span>
                                    @elseif($notification->priority == 'Low')
                                        <span class="text-green-500 font-bold">Priority: Low</span>
                                    @else
                                        <span class="text-gray-500">Priority: Not Set</span>
                                    @endif
                                </div>

                                    <!-- Abnormalitas -->
                                    <div class="mt-4 text-xs flex items-center space-x-2">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                    @if($notification->isAbnormalAvailable)
                                        <a href="{{ route('abnormal.download_pdf', ['notificationNumber' => $notification->notification_number]) }}" class="text-red-500 font-semibold" target="_blank">Abnormalitas</a>
                                    @else
                                        <span class="text-gray-500">Abnormalitas: Tidak Tersedia</span>
                                    @endif
                                </div>
                                <!-- Scope of Work -->
                                <div class="mt-2 text-xs flex items-center space-x-2">
                                    <i class="fas fa-tasks text-green-500"></i>
                                    @if($notification->isScopeOfWorkAvailable)
                                        <a href="{{ route('scopeofwork.view', ['notificationNumber' => $notification->notification_number]) }}" class="text-green-500 font-semibold" target="_blank">Scope of Work</a>
                                    @else
                                        <span class="text-gray-500">Scope of Work: Tidak Tersedia</span>
                                    @endif
                                </div>
                                <!-- Gambar Teknik -->
                                <div class="mt-2 text-xs flex items-center space-x-2">
                                    <i class="fas fa-image text-blue-500"></i>
                                    @if($notification->isGambarTeknikAvailable)
                                        <a href="{{ route('view-dokumen', ['notificationNumber' => $notification->notification_number]) }}" 
                                        class="text-blue-500 font-semibold" 
                                        target="_blank">
                                        Gambar Teknik
                                        </a>
                                    @else
                                        <span class="text-gray-500">Gambar Teknik: Tidak Tersedia</span>
                                    @endif
                                </div>
                            <!-- Dokumen HPP -->
                            <div class="mt-2 flex text-xs items-center space-x-2">
                                <i class="fas fa-file-alt text-purple-500"></i>
                                @if($notification->isHppAvailable)
                                    @if($notification->source_form === 'createhpp1')
                                        <a href="{{ route('pkm.inputhpp.download_hpp1', ['notification_number' => $notification->notification_number]) }}" 
                                        class="text-red-500 font-semibold hover:underline" target="_blank">
                                            Dokumen HPP
                                        </a>
                                    @elseif($notification->source_form === 'createhpp2')
                                        <a href="{{ route('pkm.inputhpp.download_hpp2', ['notification_number' => $notification->notification_number]) }}" 
                                        class="text-blue-500 font-semibold hover:underline" target="_blank">
                                            Dokumen HPP
                                        </a>
                                    @elseif($notification->source_form === 'createhpp3')
                                        <a href="{{ route('pkm.inputhpp.download_hpp3', ['notification_number' => $notification->notification_number]) }}" 
                                        class="text-green-500 font-semibold hover:underline" target="_blank">
                                            Dokumen HPP
                                        </a>
                                    @endif
                                @else
                                    <span class="text-gray-500">Dokumen HPP: Tidak Tersedia</span>
                                @endif
                            </div>
                                <!-- Dokumen PO -->
                                <div class="mt-2 text-xs flex items-center space-x-2">
                                    <i class="fas fa-receipt text-blue-400"></i>
                                    @if($notification->purchaseOrder && $notification->purchaseOrder->po_document_path)
                                        <a href="{{ Storage::url($notification->purchaseOrder->po_document_path) }}" target="_blank" class="text-blue-500 font-semibold">Dokumen PO/PR</a>
                                    @else
                                        <span class="text-gray-500">Dokumen PO/PR: Tidak Tersedia</span>
                                    @endif
                                </div>

                                <!-- Dokumen SPK -->
                                <div class="mt-2 text-xs flex items-center space-x-2">
                                    <i class="fas fa-file-contract text-indigo-500"></i>
                                    @if($notification->isSpkAvailable)
                                        <a href="{{ route('spk.show', ['notification_number' => $notification->notification_number]) }}" 
                                        class="text-indigo-500 font-semibold" 
                                        target="_blank">
                                            Lihat Initial Work
                                        </a>
                                    @else
                                        <span class="text-gray-500">Initial Work: Tidak Tersedia</span>
                                    @endif
                                </div>


                                <!-- Form untuk Update Progress -->
                                <form method="POST" action="{{ route('pkm.jobwaiting.updateProgress', $notification->notification_number) }}">
                                    @csrf
                                    <div class="mt-3">
                                        <label for="progress_pekerjaan" class="text-xs font-medium text-gray-700">Progress Pekerjaan</label>
                                        <div class="flex items-center space-x-2">
                                            <input type="range" min="0" max="100" 
                                                value="{{ $notification->purchaseOrder->progress_pekerjaan ?? 0 }}" 
                                                name="progress_pekerjaan" 
                                                class="slider w-full" 
                                                oninput="updateProgressValue(this, '{{ $notification->notification_number }}')">
                                            <span id="progress_value_{{ $notification->notification_number }}" class="text-xs font-medium">
                                                {{ $notification->purchaseOrder->progress_pekerjaan ?? 0 }}%
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Target Penyelesaian -->
                                    <div class="mt-2 text-xs">
                                        <label for="target_penyelesaian" class="font-medium text-gray-700">Target Penyelesaian</label>
                                        <input type="date" name="target_penyelesaian" id="target_penyelesaian" 
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-xs"
                                            value="{{ $notification->purchaseOrder->target_penyelesaian ?? '' }}">
                                    </div>
                                     <!-- Approval Target Penyelesaian  -->
                                    <div class="mt-2 flex items-center space-x-1 text-xs">
                                        <i class="fas fa-check-circle {{ $notification->purchaseOrder->approval_target === 'setuju' ? 'text-green-500' : 'text-red-500' }}"></i>
                                        <span class="font-semibold">
                                            {{ ucfirst($notification->purchaseOrder->approval_target) ?? 'Belum Ditentukan' }}
                                        </span>
                                    </div>
                                    <!-- Catatan -->
                                    <textarea name="catatan" rows="2" class="w-full mt-2 px-2 py-1 border border-gray-300 rounded-lg text-xs" placeholder="Catatan">{{ $notification->purchaseOrder->catatan ?? '' }}</textarea>
                                <!-- Tombol Submit dengan Konfirmasi -->
                                <button type="button" class="mt-3 bg-blue-500 text-white px-3 py-1 text-xs rounded hover:bg-blue-600 transition-colors update-progress-btn" 
                                    data-form-id="progress-form-{{ $notification->notification_number }}">
                                    Update Progress
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
    </div>

    <style>
        .slider {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 8px;
            background: #d3d3d3;
            outline: none;
            opacity: 0.9;
            transition: opacity .2s;
            border-radius: 10px;
        }
        .slider:hover {
            opacity: 1;
        }
        .slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #4caf50;
            cursor: pointer;
        }
        .slider::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #4caf50;
            cursor: pointer;
        }
    </style>

    <script>
        function updateProgressValue(input, notificationNumber) {
            const progressValue = document.getElementById(`progress_value_${notificationNumber}`);
            progressValue.textContent = `${input.value}%`;
        }
    </script>
  <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.querySelectorAll('.update-progress-btn').forEach(button => {
        button.addEventListener('click', function () {
            let form = this.closest('form'); // Ambil form terdekat

            Swal.fire({
                title: 'Konfirmasi Update Progress',
                text: "Apakah Anda yakin ingin memperbarui progress pekerjaan ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Update!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Kirim form jika dikonfirmasi
                }
            });
        });
    });
</script>

@if(session('success'))
    <script>
        Swal.fire({
            title: "Berhasil!",
            text: "{{ session('success') }}",
            icon: "success",
            confirmButtonText: "OK"
        });
    </script>
@endif

</x-pkm-layout>
