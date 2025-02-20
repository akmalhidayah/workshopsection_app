<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 lg:px-6">
            <!-- Card Section -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-3 mb-6">
                <div class="bg-gray-800 p-3 rounded-lg shadow-md flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-300 mb-1">Outstanding Order</h3>
                        <p class="text-lg font-bold text-white">{{ $jumlahNotifikasi }}</p>
                    </div>
                    <div class="text-purple-500">
                        <i class="fas fa-chart-line fa-lg"></i>
                    </div>
                </div>
                <div class="bg-gray-800 p-3 rounded-lg shadow-md flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-300 mb-1">Order in Process</h3>
                        <p class="text-lg font-bold text-white">{{ $jumlahDiproses }}</p>
                    </div>
                    <div class="text-blue-400">
                        <i class="fas fa-tasks fa-lg"></i>
                    </div>
                </div>
                <div class="bg-gray-800 p-3 rounded-lg shadow-md flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-gray-300 mb-1">Completed Order</h3>
                        <p class="text-lg font-bold text-white">{{ $jumlahDiterima }}</p>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-check fa-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-gray-900 p-4 rounded-lg shadow-lg">
                <h3 class="text-md font-semibold text-gray-200 mb-4">Order Details</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-gray-800 border-b border-gray-700 rounded-lg text-xs">
                        <thead class="bg-gray-700 text-white">
                            <tr>
                                <th class="px-2 py-2 text-left font-medium uppercase">Order Number</th>
                                <th class="px-2 py-2 text-left font-medium uppercase">Job Name</th>
                                <th class="px-2 py-2 text-left font-medium uppercase">Unit Work</th>
                                <th class="px-2 py-2 text-left font-medium uppercase">Order Status</th>
                                <th class="px-2 py-2 text-left font-medium uppercase">Budget Status</th>
                                <th class="px-2 py-2 text-left font-medium uppercase">Approval HPP</th>
                                <th class="px-2 py-2 text-left font-medium uppercase">Dokumen PR/PO</th>
                                <th class="px-2 py-2 text-left font-medium uppercase">Progress</th>
                                <th class="px-2 py-2 text-left font-medium uppercase">Dokumen Laporan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($notifications as $index => $notification)
                            <tr class="{{ $index % 2 === 0 ? 'bg-gray-800' : 'bg-gray-900' }}">
                                <td class="px-2 py-2 text-gray-200">{{ $notification->notification_number }}</td>
                                <td class="px-2 py-2 text-gray-200 truncate">{{ $notification->job_name }}</td>
                                <td class="px-2 py-2 text-gray-200">{{ $notification->unit_work }}</td>
                                <td class="px-2 py-2">
                                    <span class="px-2 py-1 rounded text-white {{ $notification->status == 'Pending' ? 'bg-yellow-500' : 'bg-green-500' }}">
                                        {{ $notification->status }}
                                    </span>
                                    <small class="block text-gray-300 mt-1">
                                        {{ $notification->catatan ?? 'Tidak Ada Catatan' }}
                                    </small>
                                </td>
                                <td class="px-2 py-2">
                                    <span class="px-2 py-1 rounded text-white {{ $notification->status_anggaran == 'Tersedia' ? 'bg-green-500' : 'bg-red-500' }}">
                                        {{ $notification->status_anggaran ?? '-' }}
                                    </span>
                                </td>
                                  <td class="px-6 py-4 border-b border-gray-200 text-sm">
                                    @if (!$notification->hpp1)
                                        <span class="text-gray-500">HPP Belum dibuat</span>
                                    @elseif ($notification->hpp1->source_form === 'createhpp1')
                                        @if (is_null($notification->hpp1->manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Manager Bengkel Mesin</span>
                                        @elseif (is_null($notification->hpp1->senior_manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Senior Manager Workshop</span>
                                        @elseif (is_null($notification->hpp1->manager_signature_requesting_unit))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Manager Peminta</span>
                                        @elseif (is_null($notification->hpp1->senior_manager_signature_requesting_unit))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Senior Manager Peminta</span>
                                        @elseif (is_null($notification->hpp1->general_manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari General Manager Pengendali</span>
                                        @elseif (is_null($notification->hpp1->general_manager_signature_requesting_unit))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari General Manager Peminta</span>
                                        @elseif (is_null($notification->hpp1->director_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Director</span>
                                        @else
                                            <span class="text-green-500">Telah Ditandatangani</span>
                                        @endif
                                
                                    @elseif ($notification->hpp1->source_form === 'createhpp2')
                                        @if (is_null($notification->hpp1->manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Manager Bengkel Mesin</span>
                                        @elseif (is_null($notification->hpp1->senior_manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Senior Manager Workshop</span>
                                        @elseif (is_null($notification->hpp1->manager_signature_requesting_unit))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Manager Peminta</span>
                                        @elseif (is_null($notification->hpp1->senior_manager_signature_requesting_unit))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Senior Manager Peminta</span>
                                        @elseif (is_null($notification->hpp1->general_manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari General Manager</span>
                                        @elseif (is_null($notification->hpp1->general_manager_signature_requesting_unit))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari General Manager Peminta</span>
                                        @else
                                            <span class="text-green-500">Telah Ditandatangani</span>
                                        @endif
                                
                                    @elseif ($notification->hpp1->source_form === 'createhpp3')
                                        @if (is_null($notification->hpp1->manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Manager Bengkel Mesin</span>
                                        @elseif (is_null($notification->hpp1->senior_manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Senior Manager Workshop</span>
                                        @elseif (is_null($notification->hpp1->general_manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari General Manager</span>
                                        @else
                                            <span class="text-green-500">Telah Ditandatangani</span>
                                        @endif
                                
                                    @else
                                        <span class="text-gray-500">Source Form Tidak Diketahui</span>
                                    @endif
                                </td>

                                <td class="px-2 py-2 text-gray-200 text-center">
                                    @if($notification->purchaseOrder && $notification->purchaseOrder->po_document_path)
                                        <a href="{{ Storage::url($notification->purchaseOrder->po_document_path) }}" 
                                           target="_blank" 
                                           class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600 transition">
                                           <i class="fas fa-file-alt"></i>
                                          {{ $notification->purchaseOrder->purchase_order_number ?? 'PO Document' }}
                                        </a>
                                    @else
                                        <span class="text-gray-500">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-gray-200 text-center">
                                    <div class="relative w-full bg-gray-700 rounded-full h-2 overflow-hidden">
                                        <div class="absolute top-0 left-0 h-2 bg-green-500 rounded-full" 
                                             style="width: {{ $notification->purchaseOrder->progress_pekerjaan ?? 0 }}%; transition: width 0.3s;">
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-300 mt-1 block">{{ $notification->purchaseOrder->progress_pekerjaan ?? 0 }}%</span>
                                    <small class="block text-gray-300 mt-1">
                                    {{ $notification->purchaseOrder->catatan ?? 'Tidak ada catatan.' }}
                                    </small>
                                    <span class="text-xs text-gray-400 mt-1 block">
                                        Target: 
                                        @if($notification->purchaseOrder && $notification->purchaseOrder->target_penyelesaian)
                                            {{ \Carbon\Carbon::parse($notification->purchaseOrder->target_penyelesaian)->format('d M Y') }}
                                        @else
                                            <span class="text-red-500">Belum Ditentukan</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-gray-200">
                                <div class="flex flex-wrap gap-2">
                                    <!-- LHPP -->
                                    @if($notification->lhpp)
                                    <a href="{{ route('lhpp.show', $notification->notification_number) }}" 
                                        class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600 transition flex items-center space-x-1">
                                        <i class="fas fa-file-alt"></i>
                                        <span>LHPP</span>
                                        </a>
                                    @else
                                        <span class="text-gray-500 italic">LHPP: Tidak ada</span>
                                    @endif

                                    <!-- LPJ -->
                                    @if($notification->lpj && $notification->lpj->lpj_document_path)
                                        <a href="{{ Storage::url($notification->lpj->lpj_document_path) }}" 
                                        target="_blank" 
                                        class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600 transition flex items-center space-x-1">
                                        <i class="fas fa-file-alt"></i>
                                        <span>LPJ: {{ $notification->lpj->lpj_number }}</span>
                                        </a>
                                    @else
                                        <span class="text-gray-500 italic">LPJ: Tidak ada</span>
                                    @endif

                                    <!-- PPL -->
                                    @if($notification->lpj && $notification->lpj->ppl_document_path)
                                        <a href="{{ Storage::url($notification->lpj->ppl_document_path) }}" 
                                        target="_blank" 
                                        class="bg-orange-500 text-white px-3 py-1 rounded text-xs hover:bg-orange-600 transition flex items-center space-x-1">
                                        <i class="fas fa-file-alt"></i>
                                        <span>PPL: {{ $notification->lpj->ppl_number }}</span>
                                        </a>
                                    @else
                                        <span class="text-gray-500 italic">PPL: Tidak ada</span>
                                    @endif
                                </div>
                            </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Section -->
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
