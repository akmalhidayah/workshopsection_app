<x-admin-layout>
    <div class="py-6">
        <div class="max-w-screen-lg mx-auto sm:px-4 lg:px-6 mb-8">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h2 class="font-bold text-2xl text-gray-900 leading-tight text-center mb-4">Buat Form Harga Perkiraan Perancangan</h2>
                <div class="py-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 justify-center">
                        <!-- Card for Input HPP di bawah 250 Juta -->
                        <div class="bg-blue-500 text-white p-6 rounded-lg shadow-md hover:shadow-xl transition duration-300 ease-in-out transform hover:scale-105">
                            <div class="text-center">
                                <i class="fas fa-file-invoice-dollar fa-2x mb-4"></i>
                                <h3 class="text-lg font-semibold">HPP di bawah 250 Juta</h3>
                                <a href="{{ route('admin.inputhpp.create_hpp2') }}" class="mt-3 inline-block bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-800 text-sm transition duration-300 ease-in-out">Buat Form HPP</a>
                            </div>
                        </div>
                        <!-- Card for Input HPP di atas 250 Juta -->
                        <div class="bg-green-500 text-white p-6 rounded-lg shadow-md hover:shadow-xl transition duration-300 ease-in-out transform hover:scale-105">
                            <div class="text-center">
                                <i class="fas fa-file-invoice-dollar fa-2x mb-4"></i>
                                <h3 class="text-lg font-semibold">HPP di atas 250 Juta</h3>
                                <a href="{{ route('admin.inputhpp.create_hpp1') }}" class="mt-3 inline-block bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800 text-sm transition duration-300 ease-in-out">Buat Form HPP</a>
                            </div>
                        </div>
                        <!-- Card for HPP Permintaan Bengkel Mesin -->
                        <div class="bg-blue-400 !important text-white p-6 rounded-lg shadow-md hover:shadow-xl transition duration-300 ease-in-out transform hover:scale-105">
                            <div class="text-center">
                                <i class="fas fa-tools fa-2x mb-4"></i> <!-- Ikon berbeda untuk Bengkel Mesin -->
                                <h3 class="text-lg font-semibold">HPP Permintaan Bengkel Mesin</h3>
                                <a href="{{ route('admin.inputhpp.create_hpp3') }}" class="mt-3 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm transition duration-300 ease-in-out">Buat Form HPP Bengkel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-full mx-auto sm:px-4 lg:px-6">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h2 class="font-bold text-2xl text-gray-900 leading-tight mb-6">Dokumen HPP</h2>
                <input type="text" id="search" placeholder="Cari dokumen..." class="px-4 py-2 border border-gray-300 rounded-lg w-full mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                
                <!-- Tabel Data HPP -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                    <table class="min-w-full text-gray-800 text-sm">
                        <thead class="bg-gray-200 text-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Nomor Order</th>
                                <th class="px-6 py-3 text-left font-semibold">Rencana Pemakaian</th>
                                <th class="px-6 py-3 text-left font-semibold">Unit Kerja Peminta</th>
                                <th class="px-6 py-3 text-left font-semibold">Posisi Approval</th>
                                <th class="px-6 py-3 text-left font-semibold">Total Hpp</th>
                                <th class="px-6 py-3 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                        @foreach($hpp as $data)
                            <!-- Baris Pertama: Informasi Umum -->
                            <tr class="hover:bg-gray-100 transition duration-150">
                                <td class="px-6 py-4 border-b border-gray-200">{{ $data->notification_number }}</td>
                                <td class="px-6 py-4 border-b border-gray-200"> {{ $data->notification->usage_plan_date ?? '-' }}</td>
                                <td class="px-6 py-4 border-b border-gray-200">{{ $data->requesting_unit }}</td>
                                <td class="px-6 py-4 border-b border-gray-200 text-sm">
                                    @if ($data->source_form === 'createhpp1')
                                        @if (is_null($data->manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Manager Bengkel Mesin</span>
                                        @elseif (is_null($data->senior_manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Senior Manager Workshop</span>
                                        @elseif (is_null($data->manager_signature_requesting_unit))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Manager Peminta</span>
                                        @elseif (is_null($data->senior_manager_signature_requesting_unit))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Senior Manager Peminta</span>
                                        @elseif (is_null($data->general_manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari General Manager Pengendali</span>
                                        @elseif (is_null($data->general_manager_signature_requesting_unit))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari General Manager Peminta</span>
                                        @elseif (is_null($data->director_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Director</span>
                                        @else
                                            <span class="text-green-500">Telah di Tanda Tangani</span>
                                        @endif

                                    @elseif ($data->source_form === 'createhpp2')
                                        @if (is_null($data->manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Manager Bengkel Mesin</span>
                                        @elseif (is_null($data->senior_manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Senior Manager Workshop</span>
                                        @elseif (is_null($data->manager_signature_requesting_unit))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Manager Peminta</span>
                                        @elseif (is_null($data->senior_manager_signature_requesting_unit))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Senior Manager Peminta</span>
                                        @elseif (is_null($data->general_manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari General Manager</span>
                                        @elseif (is_null($data->general_manager_signature_requesting_unit))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari General Manager Peminta</span>
                                        @else
                                            <span class="text-green-500">Telah di Tanda Tangani</span>
                                        @endif

                                    @elseif ($data->source_form === 'createhpp3')
                                        @if (is_null($data->manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Manager Bengkel Mesin</span>
                                        @elseif (is_null($data->senior_manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari Senior Manager Workshop</span>
                                        @elseif (is_null($data->general_manager_signature))
                                            <span class="text-red-500">Menunggu Tanda Tangan dari General Manager</span>
                                        @else
                                            <span class="text-green-500">Telah di Tanda Tangani</span>
                                        @endif

                                    @else
                                        <span class="text-gray-500">Source Form Tidak Diketahui</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 border-b border-gray-200">{{ number_format($data->total_amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 border-0 flex justify-center space-x-2">
                                    <!-- Tombol Lihat, Edit, Hapus tetap muncul -->
                                    @if($data->source_form === 'createhpp1')
                                        <a href="{{ route('admin.inputhpp.view_hpp1', ['notification_number' => $data->notification_number]) }}" 
                                        class="bg-red-500 text-white px-2 py-1 rounded-lg hover:bg-red-700 text-xs flex items-center justify-center" 
                                        target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @elseif($data->source_form === 'createhpp2')
                                        <a href="{{ route('admin.inputhpp.view_hpp2', ['notification_number' => $data->notification_number]) }}" 
                                        class="bg-blue-500 text-white px-2 py-1 rounded-lg hover:bg-blue-700 text-xs flex items-center justify-center" 
                                        target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @elseif($data->source_form === 'createhpp3')
                                        <a href="{{ route('admin.inputhpp.view_hpp3', ['notification_number' => $data->notification_number]) }}" 
                                        class="bg-green-500 text-white px-2 py-1 rounded-lg hover:bg-green-700 text-xs flex items-center justify-center" 
                                        target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.inputhpp.edit', ['notification_number' => $data->notification_number]) }}" 
                                    class="bg-green-500 text-white px-3 py-2 rounded-md hover:bg-green-600 transition duration-300">
                                    <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- Tombol Convert ke PDF -->
                                    @if($data->source_form === 'createhpp1')
                                        <a href="{{ route('admin.inputhpp.download_hpp1', ['notification_number' => $data->notification_number]) }}" 
                                        class="bg-red-500 text-white px-2 py-1 rounded-lg hover:bg-red-700 text-xs flex items-center justify-center" 
                                        target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @elseif($data->source_form === 'createhpp2')
                                        <a href="{{ route('admin.inputhpp.download_hpp2', ['notification_number' => $data->notification_number]) }}" 
                                        class="bg-blue-500 text-white px-2 py-1 rounded-lg hover:bg-blue-700 text-xs flex items-center justify-center" 
                                        target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @elseif($data->source_form === 'createhpp3')
                                        <a href="{{ route('admin.inputhpp.download_hpp3', ['notification_number' => $data->notification_number]) }}" 
                                        class="bg-green-500 text-white px-2 py-1 rounded-lg hover:bg-green-700 text-xs flex items-center justify-center" 
                                        target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.inputhpp.destroy', $data->notification_number) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-600 transition duration-300 delete-button">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Baris Kedua: Pemberitahuan Penolakan jika ada tanda tangan yang ditolak -->
                            @if($data->manager_signature == 'rejected' || $data->senior_manager_signature == 'rejected' || $data->general_manager_signature == 'rejected' || $data->director_signature == 'rejected' || $data->manager_signature_requesting_unit == 'rejected' || $data->senior_manager_signature_requesting_unit == 'rejected' || $data->general_manager_signature_requesting_unit == 'rejected')
                            <tr class="bg-red-100">
                                <td colspan="8" class="px-6 py-4 text-sm text-red-600">
                                    @if($data->manager_signature == 'rejected')
                                        Ditolak oleh Manager
                                    @elseif($data->senior_manager_signature == 'rejected')
                                        Ditolak oleh Senior Manager
                                    @elseif($data->general_manager_signature == 'rejected')
                                        Ditolak oleh General Manager
                                    @elseif($data->director_signature == 'rejected')
                                        Ditolak oleh Director
                                    @elseif($data->manager_signature_requesting_unit == 'rejected')
                                        Ditolak oleh Manager Peminta
                                    @elseif($data->senior_manager_signature_requesting_unit == 'rejected')
                                        Ditolak oleh Senior Manager Peminta
                                    @elseif($data->general_manager_signature_requesting_unit == 'rejected')
                                        Ditolak oleh General Manager Peminta
                                    @endif
                                    - Alasan Penolakan: {{ $data->rejection_reason }}
                                    <p class="text-yellow-600">Harap buat ulang dokumen HPP.</p>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                    </table>
                    </div>
                </div>
                <!-- Tampilkan navigasi pagination -->
                <div class="pagination justify-center mt-6">
                    {{ $hpp->links() }}
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('search').addEventListener('input', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#tableBody tr');
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
<script>
 document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function () {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data ini akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.closest('form').submit(); // Submit form jika user mengkonfirmasi
                }
            });
        });
    });
</script>

</x-admin-layout>
