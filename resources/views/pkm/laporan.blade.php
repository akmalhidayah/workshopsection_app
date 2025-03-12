<x-pkm-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            {{ __('Laporan PKM') }}
        </h2>
    </x-slot>

    <div class="p-4">
        <div class="max-w-7xl mx-auto">
            <!-- Input Pencarian -->
            <div class="mb-4 relative">
                <input type="text" id="search" placeholder="Cari Nomor Order..." 
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-orange-300 focus:border-orange-500 text-sm w-full sm:w-1/3">
                <i class="fas fa-search absolute left-3 top-3 text-gray-500"></i>
            </div>

            <!-- Tampilan Tabel untuk Desktop -->
            <div class="hidden lg:block">
                <table class="w-full bg-white border border-gray-300 rounded-lg shadow-lg overflow-hidden">
                    <thead>
                        <tr class="bg-orange-600 text-white">
                            <th class="px-4 py-2 text-sm font-semibold border-b">Nomor Order</th>
                            <th class="px-4 py-2 text-sm font-semibold border-b">Deskripsi Pekerjaan</th>
                            <th class="px-4 py-2 text-sm font-semibold border-b">Dokumen HPP</th>
                            <th class="px-4 py-2 text-sm font-semibold border-b">Dokumen PO</th>
                            <th class="px-4 py-2 text-sm font-semibold border-b">Dokumen LHPP</th>
                            <th class="px-4 py-2 text-sm font-semibold border-b">Dokumen LPJ</th>
                            <th class="px-4 py-2 text-sm font-semibold border-b">Dokumen PPL</th>
                        </tr>
                    </thead>
                    <tbody id="notificationTable">
                        @foreach($notifications as $notification)
                            <tr class="hover:bg-gray-100 transition duration-200">
                                <td class="px-4 py-2 text-sm border-b">{{ $notification->notification_number }}</td>
                                <td class="px-4 py-2 text-sm border-b">
                                    {{ $notification->abnormal ? $notification->abnormal->abnormal_title : 'Tidak ada' }}
                                </td>

                                <!-- ✅ Dokumen HPP -->
                                <td class="px-4 py-2 border-b border-gray-200 text-center">
                                    @if($notification->hpp1)
                                        @php
                                            $hppRoutes = [
                                                'createhpp1' => ['route' => 'pkm.inputhpp.download_hpp1', 'color' => 'bg-blue-500 hover:bg-red-700', 'label' => 'HPP'],
                                                'createhpp2' => ['route' => 'pkm.inputhpp.download_hpp2', 'color' => 'bg-blue-500 hover:bg-blue-700', 'label' => 'HPP'],
                                                'createhpp3' => ['route' => 'pkm.inputhpp.download_hpp3', 'color' => 'bg-blue-500 hover:bg-green-700', 'label' => 'HPP'],
                                            ];
                                        @endphp

                                        @if(array_key_exists($notification->hpp1->source_form, $hppRoutes))
                                            <a href="{{ route($hppRoutes[$notification->hpp1->source_form]['route'], ['notification_number' => $notification->notification_number]) }}" 
                                               class="{{ $hppRoutes[$notification->hpp1->source_form]['color'] }} text-white px-3 py-1 rounded text-xs shadow-sm flex items-center justify-center space-x-2 transition duration-300 ease-in-out" 
                                               target="_blank">
                                                <i class="fas fa-file-pdf text-sm"></i>
                                                <span>{{ $hppRoutes[$notification->hpp1->source_form]['label'] }}</span>
                                            </a>
                                        @else
                                            <span class="text-gray-500">Tidak ada</span>
                                        @endif
                                    @else
                                        <span class="text-gray-500">Tidak ada dokumen HPP</span>
                                    @endif
                                </td>
                                 <!-- ✅ PO Document -->
                                <td class="px-4 py-2 text-sm border-b text-center">
                                    @if($notification->purchaseOrder && $notification->purchaseOrder->po_document_path)
                                        <a href="{{ Storage::url($notification->purchaseOrder->po_document_path) }}" 
                                        target="_blank"
                                        class="bg-purple-500 text-white px-3 py-1 rounded text-xs flex items-center justify-center space-x-2 hover:bg-purple-600 transition">
                                        <i class="fas fa-file-alt text-sm"></i>
                                        <span>PO</span>
                                        </a>
                                    @else
                                        <span class="text-gray-500 italic">Tidak ada</span>
                                    @endif
                                </td>
                                <!-- ✅ Dokumen LHPP -->
                                <td class="px-4 py-2 text-sm border-b text-center">
                                    @if($notification->lhpp)
                                        <a href="{{ route('pkm.lhpp.download_pdf', $notification->notification_number) }}" 
                                           target="_blank"
                                           class="bg-green-500 text-white px-3 py-1 rounded text-xs flex items-center justify-center space-x-2 hover:bg-green-600 transition">
                                           <i class="fas fa-file-alt text-sm"></i>
                                           <span>LHPP</span>
                                        </a>
                                    @else
                                        <span class="text-gray-500 italic">Tidak ada</span>
                                    @endif
                                </td>

                                <!-- ✅ Dokumen LPJ -->
                                <td class="px-4 py-2 text-sm border-b text-center">
                                    @if($notification->lpj && $notification->lpj->lpj_document_path)
                                        <a href="{{ Storage::url($notification->lpj->lpj_document_path) }}" 
                                           target="_blank"
                                           class="bg-blue-500 text-white px-3 py-1 rounded text-xs flex items-center justify-center space-x-2 hover:bg-blue-600 transition">
                                           <i class="fas fa-file-alt text-sm"></i>
                                           <span>LPJ: {{ $notification->lpj->lpj_number }}</span>
                                        </a>
                                    @else
                                        <span class="text-gray-500 italic">Tidak ada</span>
                                    @endif
                                </td>

                                <!-- ✅ Dokumen PPL -->
                                <td class="px-4 py-2 text-sm border-b text-center">
                                    @if($notification->lpj && $notification->lpj->ppl_document_path)
                                        <a href="{{ Storage::url($notification->lpj->ppl_document_path) }}" 
                                           target="_blank" 
                                           class="bg-orange-500 text-white px-3 py-1 rounded text-xs flex items-center justify-center space-x-2 hover:bg-orange-600 transition">
                                           <i class="fas fa-file-alt text-sm"></i>
                                           <span>PPL: {{ $notification->lpj->ppl_number }}</span>
                                        </a>
                                    @else
                                        <span class="text-gray-500 italic">Tidak ada</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

          <!-- Grid untuk Mobile -->
    <div class="lg:hidden grid grid-cols-1 gap-2 w-full">
    @foreach($notifications as $notification)
        <div class="bg-white rounded-lg shadow p-3">
            <h3 class="text-sm font-semibold text-orange-600 mb-2">
                Nomor Order: {{ $notification->notification_number }}
            </h3>

            <div class="space-y-2">
                <div class="text-gray-700 text-xs">
                    Deskripsi: {{ $notification->abnormal ? $notification->abnormal->abnormal_title : 'Tidak ada' }}
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <!-- ✅ Dokumen HPP -->
                    @if($notification->hpp1)
                        <a href="{{ route('pkm.inputhpp.download_hpp1', ['notification_number' => $notification->notification_number]) }}" 
                           class="bg-red-500 text-white px-3 py-1 rounded text-xs flex items-center justify-center">
                            <i class="fas fa-file-pdf text-sm"></i> HPP
                        </a>
                    @endif

                    <!-- ✅ Dokumen LHPP -->
                    @if($notification->lhpp)
                        <a href="{{ route('pkm.lhpp.download_pdf', $notification->notification_number) }}" 
                           class="bg-green-500 text-white px-3 py-1 rounded text-xs flex items-center justify-center">
                            <i class="fas fa-file-alt text-sm"></i> LHPP
                        </a>
                    @endif

                    <!-- ✅ Dokumen LPJ -->
                    @if($notification->lpj && $notification->lpj->lpj_document_path)
                        <a href="{{ Storage::url($notification->lpj->lpj_document_path) }}" 
                           class="bg-blue-500 text-white px-3 py-1 rounded text-xs flex items-center justify-center">
                            <i class="fas fa-file-alt text-sm"></i> LPJ
                        </a>
                    @endif

                    <!-- ✅ Dokumen PPL -->
                    @if($notification->lpj && $notification->lpj->ppl_document_path)
                        <a href="{{ Storage::url($notification->lpj->ppl_document_path) }}" 
                           class="bg-orange-500 text-white px-3 py-1 rounded text-xs flex items-center justify-center">
                            <i class="fas fa-file-alt text-sm"></i> PPL
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

    <!-- Script untuk Pencarian Real-Time -->
    <script>
        document.getElementById('search').addEventListener('input', function () {
            let searchText = this.value.toLowerCase();
            let rows = document.querySelectorAll('#notificationTable tr');

            rows.forEach(row => {
                let notificationNumber = row.children[0].textContent.toLowerCase();
                if (notificationNumber.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</x-pkm-layout>
