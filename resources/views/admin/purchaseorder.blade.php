<x-admin-layout> 
    <x-slot name="header">
        <h2 class="font-semibold text-[11px] text-gray-800 leading-tight flex items-center gap-2">
            <i class="fas fa-file-invoice text-blue-600"></i>
            <span>Purchase Order (PO) Dashboard</span>
        </h2>
    </x-slot>

    @php
        /* ==== MINI PRESET (konsisten dgn Verifikasi Anggaran) ==== */
        $baseSel = 'min-h-[26px] text-[10px] leading-[1.3] px-2 pr-9 rounded-[6px] appearance-none focus:ring-1 truncate';
        $baseInp = 'min-h-[26px] text-[10px] leading-[1.3] px-2 rounded-[6px] focus:ring-1';
        $baseBtn = 'min-h-[26px] text-[10px] leading-[1.3] px-3 rounded-[6px]';

        $selIndigo = $baseSel.' bg-indigo-100 text-indigo-800 border border-indigo-600 focus:ring-indigo-500 focus:border-indigo-600';
        $selGreen  = $baseSel.' bg-emerald-100 text-emerald-800 border border-emerald-600 focus:ring-emerald-500 focus:border-emerald-600';
        $selSlate  = $baseSel.' bg-slate-100 text-slate-800 border border-slate-600 focus:ring-slate-500 focus:border-slate-600';

        $btnPrimary = $baseBtn.' bg-indigo-600 text-white hover:bg-indigo-700';
        $btnGhost   = $baseBtn.' border border-slate-600 text-slate-700 hover:bg-slate-50';
    @endphp

    <div class="p-4 space-y-4">
        <!-- 🔹 Filter & Pencarian (mini & konsisten) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3">
            <div class="mb-2">
                <h3 class="font-semibold text-[11px] text-slate-900 leading-tight">Purchase Order</h3>
                <p class="text-[9px] text-slate-500 leading-tight">Cari berdasarkan nomor order/nama pekerjaan dan status persetujuan.</p>
            </div>

            <form method="GET" action="{{ route('admin.purchaseorder') }}"
                  class="flex items-center gap-2 overflow-x-auto whitespace-nowrap">

                {{-- Search (Indigo) --}}
                <div class="relative">
                    <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3 h-3 text-indigo-500" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari Nomor Order atau Nama Pekerjaan..."
                           class="{{ $selIndigo }} pl-6 w-72" />
                    <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-indigo-600 text-[10px]">⌕</span>
                </div>

                {{-- Status (Green) --}}
                <div class="relative">
                    <select name="status" class="{{ $selGreen }} w-44">
                        <option value="">Status Approval</option>
                        <option value="setuju"       {{ request('status')==='setuju'?'selected':'' }}>✅ Disetujui</option>
                        <option value="tidak_setuju" {{ request('status')==='tidak_setuju'?'selected':'' }}>❌ Tidak Disetujui</option>
                    </select>
                    <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-emerald-700 text-[10px]">▾</span>
                </div>

                {{-- (Opsional) Entries – aktifkan kalau controllermu support --}}
                {{-- 
                <div class="relative">
                    <select name="entries" class="{{ $selSlate }} w-20">
                        <option value="10" {{ request('entries',10)==10?'selected':'' }}>10</option>
                        <option value="25" {{ request('entries')==25?'selected':'' }}>25</option>
                        <option value="50" {{ request('entries')==50?'selected':'' }}>50</option>
                    </select>
                    <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-slate-700 text-[10px]">▾</span>
                </div>
                --}}

                <button class="{{ $btnPrimary }} ml-auto inline-flex items-center">
                    <i class="fas fa-filter mr-1 text-[10px]"></i> Terapkan
                </button>
                <a href="{{ route('admin.purchaseorder') }}" class="{{ $btnGhost }} inline-flex items-center">
                    <i class="fas fa-undo mr-1 text-[10px]"></i> Reset
                </a>
            </form>
        </div>

        <!-- 🔹 Tabel Notifikasi (TIDAK DIUBAH) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-md overflow-x-auto">
            <table class="min-w-full text-[11px] text-gray-700">
              <thead class="bg-gradient-to-r from-blue-700 to-blue-500 text-white uppercase">
                <tr>
                    <th class="px-3 py-3 text-center font-semibold">
                        <i class="fas fa-hashtag"></i> Order
                    </th>
                    <th class="px-3 py-3 text-center font-semibold">
                        <i class="fas fa-file-signature"></i> Nomor PO
                    </th>
                    <th class="px-3 py-3 text-center font-semibold relative">
                        <i class="fas fa-clipboard-check"></i> Target &amp; Approval
                        <div class="text-[10px] font-normal text-blue-100 italic mt-1">
                            Tanggal diisi oleh Vendor
                        </div>
                    </th>
                    <th class="px-3 py-3 text-center font-semibold">
                        <i class="fas fa-chart-line"></i> Progress
                        <div class="text-[10px] font-normal text-blue-100 italic mt-1">
                            Progress diperbarui Vendor
                        </div>
                    </th>
                    <th class="px-3 py-3 text-center font-semibold">
                        <i class="fas fa-file-upload"></i> Dokumen PO
                    </th>
                    <th class="px-3 py-3 text-center font-semibold">
                        <i class="fas fa-comment-dots"></i> Catatan
                    </th>
                    <th class="px-3 py-3 text-center font-semibold">
                        <i class="fas fa-cog"></i> Aksi
                    </th>
                </tr>
              </thead>

              <tbody>
                @forelse($notifications as $notification)
                    <form action="{{ route('admin.purchaseorder.update', $notification->notification_number) }}" 
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        <tr class="border-b hover:bg-gray-50 align-top transition-all duration-150 ease-in-out">
                            <!-- Nomor Order -->
                            <td class="px-3 py-3 font-semibold text-gray-900 text-center">
                                <span class="flex flex-col items-center">
                                    <i class="fas fa-hashtag text-blue-600 mb-1"></i>
                                    {{ $notification->notification_number }}
                                </span>
                            </td>

                            <!-- Nomor PR/PO -->
                            <td class="px-3 py-3 text-center">
                                <input type="text" name="purchase_order_number"
                                    value="{{ old('purchase_order_number') ?? $notification->purchaseOrder->purchase_order_number ?? '' }}" 
                                    class="w-24 px-2 py-1 text-xs text-center bg-gray-50 border border-gray-300 rounded focus:ring focus:ring-blue-200">

                                @if($notification->purchaseOrder && $notification->purchaseOrder->approval_note)
                                    <p class="text-[10px] text-gray-500 italic mt-1">
                                        <i class="fas fa-sticky-note"></i> {{ $notification->purchaseOrder->approval_note }}
                                    </p>
                                @endif
                            </td>

                            <!-- Target & Approval -->
                            <td class="px-3 py-3">
                                <!-- Target -->
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                    <input type="date" name="target_penyelesaian"
                                        value="{{ old('target_penyelesaian') ?? ($notification->purchaseOrder->target_penyelesaian ?? '') }}" 
                                        class="w-full px-2 py-1 text-xs text-center bg-gray-50 border border-gray-300 rounded focus:ring focus:ring-blue-200">
                                </div>

                                <!-- Approval Target -->
                                <select name="approval_target" 
                                    class="w-full mb-2 px-2 py-1 text-xs border rounded text-center focus:ring focus:ring-blue-200 transition"
                                    onchange="updateSelectColor(this)" 
                                    style="background-color: {{ optional($notification->purchaseOrder)->approval_target === 'setuju' ? '#d4edda' : (optional($notification->purchaseOrder)->approval_target === 'tidak_setuju' ? '#f8d7da' : '#ffffff') }};">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="setuju" {{ old('approval_target', optional($notification->purchaseOrder)->approval_target) === 'setuju' ? 'selected' : '' }}>
                                        ✅ Disetujui
                                    </option>
                                    <option value="tidak_setuju" {{ old('approval_target', optional($notification->purchaseOrder)->approval_target) === 'tidak_setuju' ? 'selected' : '' }}>
                                        ❌ Tidak Disetujui
                                    </option>
                                </select>

                                <!-- Approval Checklist -->
                                <div class="flex flex-col space-y-1 text-xs">
                                    <label class="flex items-center space-x-1">
                                        <input type="checkbox" name="approve_manager" value="1" 
                                            {{ optional($notification->purchaseOrder)->approve_manager ? 'checked' : '' }}
                                            class="form-checkbox h-3 w-3 text-green-500"> <span>Manager</span>
                                    </label>
                                    <label class="flex items-center space-x-1">
                                        <input type="checkbox" name="approve_senior_manager" value="1" 
                                            {{ optional($notification->purchaseOrder)->approve_senior_manager ? 'checked' : '' }}
                                            class="form-checkbox h-3 w-3 text-green-500"> <span>Senior Manager</span>
                                    </label>
                                    <label class="flex items-center space-x-1">
                                        <input type="checkbox" name="approve_general_manager" value="1" 
                                            {{ optional($notification->purchaseOrder)->approve_general_manager ? 'checked' : '' }}
                                            class="form-checkbox h-3 w-3 text-green-500"> <span>General Manager</span>
                                    </label>
                                    @if($notification->source_form === 'createhpp1')
                                        <label class="flex items-center space-x-1">
                                            <input type="checkbox" name="approve_direktur_operasional" value="1" 
                                                {{ optional($notification->purchaseOrder)->approve_direktur_operasional ? 'checked' : '' }}
                                                class="form-checkbox h-3 w-3 text-green-500"> <span>Direktur Operation</span>
                                        </label>
                                    @endif
                                </div>
                            </td>

                            <!-- Progress -->
                            <td class="px-3 py-3 text-center font-medium">
                                <div class="flex flex-col items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mb-1 overflow-hidden">
                                        <div class="bg-green-500 h-2 rounded-full" 
                                             style="width: {{ $notification->purchaseOrder->progress_pekerjaan ?? 0 }}%;"></div>
                                    </div>
                                    <span class="text-[11px] font-semibold text-gray-600">
                                        {{ $notification->purchaseOrder->progress_pekerjaan ?? 0 }}%
                                    </span>
                                </div>
                            </td>

                            <!-- Dokumen -->
                            <td class="px-3 py-3 text-center">
                                <label for="po_document_{{ $notification->notification_number }}" 
                                    class="cursor-pointer bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded shadow-sm text-xs inline-flex items-center space-x-1">
                                    <i class="fas fa-upload"></i><span>Upload</span>
                                </label>
                                <input id="po_document_{{ $notification->notification_number }}" type="file" name="po_document" class="hidden">
                                
                                @if($notification->purchaseOrder && $notification->purchaseOrder->po_document_path)
                                    <a href="{{ asset('storage/' . $notification->purchaseOrder->po_document_path) }}" 
                                        target="_blank" class="block text-blue-600 hover:underline text-xs mt-1 truncate w-28 mx-auto">
                                        <i class="fas fa-file-pdf"></i> {{ basename($notification->purchaseOrder->po_document_path) }}
                                    </a>
                                @endif
                            </td>

                            <!-- Catatan -->
                            <td class="px-3 py-3 align-top text-xs">
                                @if(!empty($notification->purchaseOrder->catatan))
                                    <div class="mb-2 p-2 bg-orange-50 border border-orange-200 rounded">
                                        <p class="font-semibold text-orange-700 mb-1">
                                            🧾 Catatan dari Vendor:
                                        </p>
                                        <p class="text-gray-800 whitespace-pre-line">
                                            {{ $notification->purchaseOrder->catatan }}
                                        </p>
                                    </div>
                                @endif

                                <label class="block text-gray-700 font-semibold mb-1">
                                    ✏️ Catatan Admin:
                                </label>
                                <textarea name="catatan_pkm" 
                                    placeholder="Tambahkan catatan untuk Vendor..."
                                    class="w-full h-16 text-xs px-2 py-1 bg-gray-50 border border-gray-300 rounded focus:ring focus:ring-blue-200 resize-none">{{ $notification->purchaseOrder->catatan_pkm ?? '' }}</textarea>
                            </td>

                            <!-- Tombol Update -->
                            <td class="px-3 py-3 text-center">
                                <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-semibold shadow flex items-center justify-center space-x-1">
                                    <i class="fas fa-save"></i>
                                    <span>Update</span>
                                </button>
                            </td>
                        </tr>
                    </form>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-5 text-center text-gray-500">
                            <i class="fas fa-info-circle text-blue-400 mr-1"></i> Tidak ada data ditemukan.
                        </td>
                    </tr>
                @endforelse
              </tbody>
            </table>
        </div>

        <!-- 🔹 Pagination -->
        <div class="mt-3">
            {{ $notifications->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('success'))
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: @json(session('success')),
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif

    <!-- Warna dropdown dinamis -->
    <script>
        function updateSelectColor(selectElement) {
            if (selectElement.value === 'setuju') {
                selectElement.style.backgroundColor = '#b2f0b2';
            } else if (selectElement.value === 'tidak_setuju') {
                selectElement.style.backgroundColor = '#ffb3b3';
            } else {
                selectElement.style.backgroundColor = '#ffffff';
            }
        }
        document.querySelectorAll('select[name="approval_target"]').forEach(updateSelectColor);
    </script>
</x-admin-layout>
