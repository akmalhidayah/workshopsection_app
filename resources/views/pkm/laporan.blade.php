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
                <input type="text" id="search" placeholder="Cari Nomor Notifikasi..." 
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500 text-sm w-full sm:w-1/3">
                <i class="fas fa-search absolute left-3 top-3 text-gray-500"></i>
            </div>

            <!-- Tampilan Tabel untuk Desktop -->
            <div class="hidden lg:block">
                <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-orange-500 text-white">
                            <th class="px-6 py-3 text-sm font-semibold border-b">Nomor Notifikasi</th>
                            <th class="px-6 py-3 text-sm font-semibold border-b">Dokumen LHPP</th>
                            <th class="px-6 py-3 text-sm font-semibold border-b">Dokumen LPJ</th>
                            <th class="px-6 py-3 text-sm font-semibold border-b">Dokumen PPL</th>
                        </tr>
                    </thead>
                    <tbody id="notificationTable">
                        @foreach($notifications as $notification)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-3 text-sm border-b">{{ $notification->notification_number }}</td>
                                <td class="px-6 py-3 text-sm border-b">
                                    @if($notification->lhpp)
                                        <a href="{{ route('pkm.lhpp.download_pdf', $notification->notification_number) }}" 
                                        target="_blank"
                                           class="bg-green-500 text-white px-3 py-1 rounded-full text-xs flex items-center justify-center space-x-2 hover:bg-green-600 transition">
                                           <i class="fas fa-file-alt"></i>
                                           <span>LHPP</span>
                                        </a>
                                    @else
                                        <span class="text-gray-500 italic">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm border-b">
                                    @if($notification->lpj && $notification->lpj->lpj_document_path)
                                        <a href="{{ Storage::url($notification->lpj->lpj_document_path) }}" 
                                           target="_blank"
                                           class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs flex items-center justify-center space-x-2 hover:bg-blue-600 transition">
                                           <i class="fas fa-file-alt"></i>
                                           <span>LPJ: {{ $notification->lpj->lpj_number }}</span>
                                        </a>
                                    @else
                                        <span class="text-gray-500 italic">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm border-b text-center">
                                    @if($notification->lpj && $notification->lpj->ppl_document_path)
                                        <a href="{{ Storage::url($notification->lpj->ppl_document_path) }}" 
                                           target="_blank" 
                                           class="bg-orange-500 text-white px-4 py-1 rounded-full text-xs flex items-center justify-center space-x-2 hover:bg-orange-600 transition">
                                           <i class="fas fa-file-alt"></i>
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
<!-- Tampilan Grid untuk Mobile -->
<div class="lg:hidden space-y-3">
    @foreach($notifications as $notification)
        <div class="bg-gray-100 shadow-md rounded-lg p-3">
            <h3 class="text-sm font-semibold text-orange-600 mb-2">
                Nomor Notifikasi: {{ $notification->notification_number }}
            </h3>
            
            <div class="space-y-1">
                <!-- LHPP -->
                <div class="flex items-center space-x-2 bg-green-100 p-2 rounded-md">
                    <i class="fas fa-file-alt text-green-600 text-xs"></i>
                    <span class="text-xs font-medium text-green-700">LHPP:</span>
                    @if($notification->lhpp)
                        <a href="{{ route('pkm.lhpp.download_pdf', $notification->notification_number) }}" 
                           target="_blank" class="text-green-600 text-xs underline">Lihat LHPP</a>
                    @else
                        <span class="text-gray-500 text-xs italic">Tidak ada</span>
                    @endif
                </div>

                <!-- LPJ -->
                <div class="flex items-center space-x-2 bg-blue-100 p-2 rounded-md">
                    <i class="fas fa-file-alt text-blue-600 text-xs"></i>
                    <span class="text-xs font-medium text-blue-700">LPJ:</span>
                    @if($notification->lpj && $notification->lpj->lpj_document_path)
                        <a href="{{ Storage::url($notification->lpj->lpj_document_path) }}" 
                           target="_blank" class="text-blue-600 text-xs underline">LPJ: {{ $notification->lpj->lpj_number }}</a>
                    @else
                        <span class="text-gray-500 text-xs italic">Tidak ada</span>
                    @endif
                </div>

                <!-- PPL -->
                <div class="flex items-center space-x-2 bg-orange-100 p-2 rounded-md">
                    <i class="fas fa-file-alt text-orange-600 text-xs"></i>
                    <span class="text-xs font-medium text-orange-700">PPL:</span>
                    @if($notification->lpj && $notification->lpj->ppl_document_path)
                        <a href="{{ Storage::url($notification->lpj->ppl_document_path) }}" 
                           target="_blank" class="text-orange-600 text-xs underline">PPL: {{ $notification->lpj->ppl_number }}</a>
                    @else
                        <span class="text-gray-500 text-xs italic">Tidak ada</span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

        </div>
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
