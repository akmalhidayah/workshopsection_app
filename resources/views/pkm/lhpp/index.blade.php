<x-pkm-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('LHPP List') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-4 lg:px-4">
            <!-- Bagian Tombol Buat Form LHPP dan Search -->
            <div class="flex flex-wrap justify-between mb-4 items-center">
                <form action="" method="GET" class="flex items-center w-full sm:w-auto mb-2 sm:mb-0">
                    <input type="text" name="search" placeholder="Cari dokumen..." class="border border-gray-300 rounded-lg px-4 py-2 w-full sm:w-auto" />
                    <button type="submit" class="bg-orange-400 text-white px-4 py-2 ml-2 rounded-lg hover:bg-gray-600 flex items-center">
                        <i class="fas fa-search mr-2"></i> Cari
                    </button>
                </form>
                <a href="{{ route('pkm.lhpp.create') }}" class="bg-orange-400 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center w-full sm:w-auto justify-center">
                    <i class="fas fa-plus mr-2"></i> Buat Form LHPP
                </a>
            </div>

            <!-- Bagian Tabel -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white text-sm rounded-lg shadow-lg">
                        <thead class="bg-orange-400 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Nomor Order</th>
                                <th class="px-6 py-3 text-left font-semibold">Nomor PO</th>
                                <th class="px-6 py-3 text-left font-semibold">Unit Kerja Peminta</th>
                                <th class="px-6 py-3 text-left font-semibold">Tanggal Selesai</th>
                                <th class="px-6 py-3 text-left font-semibold">Waktu Pengerjaan (Hari)</th>
                                <th class="px-6 py-3 text-left font-semibold">Total Biaya</th>
                                <th class="px-6 py-3 text-left font-semibold">Status Approval</th>
                                <th class="px-6 py-3 text-left font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($lhpps as $lhpp)
                            <tr class="hover:bg-gray-100 transition duration-150">
                                <!-- Baris Pertama: Informasi Umum -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $lhpp->notification_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $lhpp->purchase_order_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $lhpp->unit_kerja }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $lhpp->tanggal_selesai }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $lhpp->waktu_pengerjaan }} Hari</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($lhpp->total_biaya, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-normal text-sm text-gray-900 break-words">
                                    <div class="text-red-500">
                                        @if (is_null($lhpp->manager_signature))
                                            <span>Menunggu Tanda Tangan dari Manager Bengkel Mesin</span>
                                        @elseif (is_null($lhpp->manager_signature_requesting))
                                            <span>Menunggu Tanda Tangan dari Manager User</span>
                                        @elseif (is_null($lhpp->manager_pkm_signature))
                                            <span>Menunggu Tanda Tangan dari Manager PKM</span>
                                        @else
                                            <span class="text-green-500">Telah Ditandatangani</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium flex justify-center space-x-2">
                        <!-- Tombol 
                        <a href="{{ route('pkm.lhpp.show', $lhpp->notification_number) }}" 
                        class="bg-blue-500 text-white px-2 py-1 rounded-lg hover:bg-blue-700 text-xs flex items-center justify-center" 
                        target="_blank">
                            <i class="fas fa-eye"></i> 
                        </a>Lihat -->
                        <!-- Tombol Edit -->
                        <a href="{{ route('pkm.lhpp.edit', $lhpp->notification_number) }}" class="bg-green-500 text-white px-3 py-2 rounded-md hover:bg-green-600">
                        <i class="fas fa-edit"></i>
                    </a>
                        <!-- Tombol Hapus -->
                        <form action="{{ route('pkm.lhpp.destroy', $lhpp->notification_number) }}" 
                            method="POST" 
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" 
                            style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-600 transition duration-300">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form> 
                    <!-- Tombol Download PDF -->
                    <a href="{{ route('pkm.lhpp.download_pdf', $lhpp->notification_number) }}" 
                    class="bg-gray-700 text-white px-3 py-2 rounded-md hover:bg-gray-900 transition duration-300 flex items-center justify-center">
                        <i class="fas fa-file-pdf mr-1"></i>
                    </a>
                    </td>
                    </tr>
                    <!-- Baris Kedua: Pemberitahuan Penolakan -->
                    @if($lhpp->status_approve === 'Rejected' || $lhpp->manager_signature === 'rejected' || $lhpp->manager_signature_requesting === 'rejected' || $lhpp->manager_pkm_signature === 'rejected')
                    <tr class="bg-red-100">
                        <td colspan="7" class="px-6 py-4 text-sm text-red-600">
                            <strong>Dokumen ditolak</strong> - 

                            @if($lhpp->status_approve === 'Rejected')
                                Ditolak oleh Admin
                            @elseif($lhpp->manager_signature === 'rejected')
                                Ditolak oleh Manager
                            @elseif($lhpp->manager_signature_requesting === 'rejected')
                                Ditolak oleh Manager Peminta
                            @elseif($lhpp->manager_pkm_signature === 'rejected')
                                Ditolak oleh Manager PKM
                            @endif

                            - Alasan: {{ $lhpp->rejection_reason }}

                            @if($lhpp->status_approve !== 'Rejected') 
                                <p class="text-yellow-600">Harap buat ulang dokumen LHPP.</p>
                            @endif
                        </td>
                    </tr>
                    @endif
                        @endforeach
                    </tbody>

                    </table>
                </div>
            </div>
            <!-- Bagian Pagination -->
            <div class="mt-4">
                {{ $lhpps->links() }} <!-- Menambahkan pagination -->
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
    <script>
        Swal.fire({
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            title: 'Gagal!',
            text: "{{ session('error') }}",
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>
@endif
    </x-pkm-layout>
