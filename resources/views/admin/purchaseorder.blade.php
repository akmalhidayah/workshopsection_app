<x-admin-layout>
    <div class="space-y-4">
        <div class="mb-4">
            <div class="admin-header">
                <div class="flex items-center gap-3">
                    <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                        <i data-lucide="file-check" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h1 class="admin-title">Purchase Order</h1>
                        <p class="admin-subtitle">Pantau PO, target, approval, dan dokumen vendor.</p>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.purchaseorder') }}"
                  class="admin-filter mt-4 grid grid-cols-1 md:grid-cols-12 gap-3">

                <div class="md:col-span-4">
                    <label class="text-[11px] font-semibold text-slate-600 mb-1 block">Cari Order</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Nomor order atau nama pekerjaan"
                               class="admin-input pl-9 w-full" />
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="text-[11px] font-semibold text-slate-600 mb-1 block">Status Target</label>
                    <select name="status" class="admin-select w-full">
                        <option value="">Semua Status</option>
                        <option value="setuju" {{ request('status')==='setuju'?'selected':'' }}>Disetujui</option>
                        <option value="tidak_setuju" {{ request('status')==='tidak_setuju'?'selected':'' }}>Ditolak</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="text-[11px] font-semibold text-slate-600 mb-1 block">Unit Kerja</label>
                    <select name="unit" class="admin-select w-full">
                        <option value="">Semua Unit</option>
                        @foreach($units as $u)
                            <option value="{{ $u }}" {{ request('unit')===$u?'selected':'' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-1">
                    <label class="text-[11px] font-semibold text-slate-600 mb-1 block">Tanggal Dari</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="admin-input w-full">
                </div>
                <div class="md:col-span-1">
                    <label class="text-[11px] font-semibold text-slate-600 mb-1 block">Tanggal Sampai</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="admin-input w-full">
                </div>

                <div class="md:col-span-1">
                    <label class="text-[11px] font-semibold text-slate-600 mb-1 block">Per Halaman</label>
                    <select name="entries" class="admin-select w-full">
                        @foreach([10,25,50,100] as $n)
                            <option value="{{ $n }}" {{ (int) request('entries', $entries ?? 10) === $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-12 flex items-center gap-2">
                    <button class="admin-btn admin-btn-primary" type="submit">
                        <i data-lucide="filter" class="w-4 h-4"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.purchaseorder') }}" class="admin-btn admin-btn-ghost">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel PO -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-[11px] text-gray-700">
              <thead class="bg-blue-700 text-white uppercase">
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
                            Tanggal diisi oleh vendor
                        </div>
                    </th>
                    <th class="px-3 py-3 text-center font-semibold">
                        <i class="fas fa-chart-line"></i> Progress
                        <div class="text-[10px] font-normal text-blue-100 italic mt-1">
                            Progress diperbarui vendor
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
                                    <option value="">Status Ajuan Penyelesaian PKM</option>
                                    <option value="setuju" {{ old('approval_target', optional($notification->purchaseOrder)->approval_target) === 'setuju' ? 'selected' : '' }}>
                                        Setujui Tanggal
                                    </option>
                                    <option value="tidak_setuju" {{ old('approval_target', optional($notification->purchaseOrder)->approval_target) === 'tidak_setuju' ? 'selected' : '' }}>
                                        Tolak Tanggal
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
                                    @if(in_array($notification->source_form, ['createhpp1','createhpp3','createhpp5'], true))
                                        <label class="flex items-center space-x-1">
                                            <input type="checkbox" name="approve_direktur_operasional" value="1" 
                                                {{ optional($notification->purchaseOrder)->approve_direktur_operasional ? 'checked' : '' }}
                                                class="form-checkbox h-3 w-3 text-green-500"> <span>Direktur Operasional</span>
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
                                <input id="po_document_{{ $notification->notification_number }}" type="file" name="po_document" class="hidden" data-file-label="po_file_label_{{ $notification->notification_number }}">
                                <div id="po_file_label_{{ $notification->notification_number }}" class="text-[10px] text-slate-600 mt-1 truncate w-28 mx-auto">
                                    Belum ada file dipilih
                                </div>
                                
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
                                            Catatan dari Vendor:
                                        </p>
                                        <p class="text-gray-800 whitespace-pre-line">
                                            {{ $notification->purchaseOrder->catatan }}
                                        </p>
                                    </div>
                                @endif

                                <label class="block text-gray-700 font-semibold mb-1">
                                    Catatan Admin:
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

                <!-- Pagination -->
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

        document.querySelectorAll('input[type="file"][data-file-label]').forEach((input) => {
            const labelId = input.getAttribute('data-file-label');
            const labelEl = document.getElementById(labelId);
            if (!labelEl) return;

            input.addEventListener('change', () => {
                const fileName = input.files && input.files.length > 0 ? input.files[0].name : 'Belum ada file dipilih';
                labelEl.textContent = fileName;
            });
        });
    </script>
</x-admin-layout>




