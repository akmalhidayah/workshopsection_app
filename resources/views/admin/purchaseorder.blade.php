<x-admin-layout> 
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            {{ __('Purchase Order (PO) Dashboard') }}
        </h2>
    </x-slot>

    <div class="p-4 space-y-4">
        <!-- Pencarian -->
        <div class="flex justify-between items-center mb-4">
            <input type="text" id="search" placeholder="Cari Notifikasi..." 
                class="border border-gray-300 rounded px-3 py-1 text-sm shadow-sm focus:ring-1 focus:ring-gray-300 focus:outline-none w-full sm:w-1/3">
        </div>

        <!-- Tabel Notifikasi -->
        <div class="bg-white border border-gray-300 rounded-lg shadow-md overflow-x-auto">
            <table class="min-w-full text-xs text-left text-gray-500">
                <thead class="bg-gray-200 text-gray-700 uppercase font-medium">
                    <tr>
                        <th class="px-4 py-2">Nomor Order</th>
                        <th class="px-4 py-2">Nomor PR / PO</th>
                        <th class="px-4 py-2">Target Penyelesaian</th>
                        <th class="px-4 py-2">Progress Pekerjaan</th>
                        <th class="px-4 py-2">Upload Dokumen</th>
                        <th class="px-4 py-2">Approval Target</th>
                        <th class="px-4 py-2">Approval</th>
                        <th class="px-4 py-2">Catatan dari PKM</th>
                        <th class="px-4 py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifications as $notification)
                        <form action="{{ route('admin.purchaseorder.update', $notification->notification_number) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-900">
                                    <a href="#" class="text-blue-500 hover:underline">{{ $notification->notification_number }}</a>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="text" name="purchase_order_number" 
                                        value="{{ old('purchase_order_number') ?? $notification->purchaseOrder->purchase_order_number ?? '' }}" 
                                        class="w-full px-2 py-1 bg-gray-50 border border-gray-300 rounded shadow-sm focus:ring-1 focus:ring-blue-200 focus:outline-none">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="date" name="target_penyelesaian" 
                                        value="{{ old('target_penyelesaian') ?? ($notification->purchaseOrder->target_penyelesaian ?? '') }}" 
                                        class="w-full px-2 py-1 bg-gray-50 border border-gray-300 rounded shadow-sm focus:ring-1 focus:ring-blue-200 focus:outline-none">
                                </td>
                                <td class="px-4 py-2 text-center">
                                    {{ $notification->purchaseOrder->progress_pekerjaan ?? 0 }}%
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex flex-col items-start space-y-1">
                                        <label for="po_document_{{ $notification->notification_number }}" 
                                            class="cursor-pointer bg-green-500 text-white px-3 py-1 rounded shadow-sm text-xs flex items-center hover:bg-green-600 transition duration-200">
                                            <i class="fas fa-upload"></i>
                                            <span>Upload</span>
                                        </label>
                                        <input id="po_document_{{ $notification->notification_number }}" type="file" name="po_document" class="hidden">
                                        
                                        @if($notification->purchaseOrder && $notification->purchaseOrder->po_document_path)
                                            <a href="{{ asset('storage/' . $notification->purchaseOrder->po_document_path) }}" 
                                            target="_blank" class="text-blue-500 hover:underline text-xs mt-1">
                                                {{ basename($notification->purchaseOrder->po_document_path) }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <select name="approval_target" 
                                        class="w-full px-2 py-1 border rounded shadow-sm focus:ring-1 focus:ring-blue-200 focus:outline-none text-sm"
                                        onchange="updateSelectColor(this)" 
                                        style="background-color: {{ optional($notification->purchaseOrder)->approval_target === 'setuju' ? '#d4edda' : (optional($notification->purchaseOrder)->approval_target === 'tidak_setuju' ? '#f8d7da' : '#ffffff') }}">
                                        <option value="setuju" {{ old('approval_target', optional($notification->purchaseOrder)->approval_target) === 'setuju' ? 'selected' : '' }}>Setuju</option>
                                        <option value="tidak_setuju" {{ old('approval_target', optional($notification->purchaseOrder)->approval_target) === 'tidak_setuju' ? 'selected' : '' }}>Tidak Setuju</option>
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex flex-col space-y-1">
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="approve_manager" value="1" 
                                                {{ optional($notification->purchaseOrder)->approve_manager ? 'checked' : '' }}
                                                class="form-checkbox h-4 w-4 text-green-500">
                                            <span class="text-xs">Manager</span>
                                        </label>
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="approve_senior_manager" value="1" 
                                                {{ optional($notification->purchaseOrder)->approve_senior_manager ? 'checked' : '' }}
                                                class="form-checkbox h-4 w-4 text-green-500">
                                            <span class="text-xs">Senior Manager</span>
                                        </label>
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="approve_general_manager" value="1" 
                                                {{ optional($notification->purchaseOrder)->approve_general_manager ? 'checked' : '' }}
                                                class="form-checkbox h-4 w-4 text-green-500">
                                            <span class="text-xs">General Manager</span>
                                        </label>
                                        @if($notification->source_form === 'createhpp1')
                                            <label class="flex items-center space-x-2">
                                                <input type="checkbox" name="approve_direktur_operasional" value="1" 
                                                    {{ optional($notification->purchaseOrder)->approve_direktur_operasional ? 'checked' : '' }}
                                                    class="form-checkbox h-4 w-4 text-green-500">
                                                <span class="text-xs">Direktur Operasional</span>
                                            </label>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <p class="px-2 py-1 bg-gray-50 border border-gray-300 rounded">
                                        {{ $notification->purchaseOrder->catatan ?? 'Tidak ada catatan.' }}
                                    </p>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button type="submit" 
                                        class="bg-blue-500 text-white px-3 py-1 rounded shadow-sm hover:bg-blue-600 focus:ring-1 focus:ring-blue-200 transition duration-150">
                                        Update
                                    </button>
                                </td>
                            </tr>
                        </form>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('success'))
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ session("success") }}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
    <script>
// Fungsi untuk mengubah warna background box select
function updateSelectColor(selectElement) {
    if (selectElement.value === 'setuju') {
        selectElement.style.backgroundColor = '#8aff8a'; // Hijau mencolok (Setuju)
    } else if (selectElement.value === 'tidak_setuju') {
        selectElement.style.backgroundColor = '#ff8a8a'; // Merah mencolok (Tidak Setuju)
    } else {
        selectElement.style.backgroundColor = '#ffffff'; // Putih (Default)
    }
}



    // Inisialisasi warna saat halaman pertama kali dimuat
    document.querySelectorAll('select[name="approval_target"]').forEach(select => {
        updateSelectColor(select);
    });
</script>
</x-admin-layout>
