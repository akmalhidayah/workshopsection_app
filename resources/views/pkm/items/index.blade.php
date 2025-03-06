<x-pkm-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Item Kebutuhan Kerjaan') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Bagian Tombol Tambah dan Pencarian -->
            <div class="flex flex-wrap justify-between items-center mb-4">
                <form action="" method="GET" class="flex items-center space-x-2 w-full sm:w-auto">
                    <input type="text" name="search" placeholder="Cari item..." 
                           class="border border-gray-300 rounded-lg px-4 py-2 w-full sm:w-64 focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all" />
                    <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-all flex items-center">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <!-- Tombol Tambah Item -->
                <a href="{{ route('pkm.items.create') }}" 
                   class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-all flex items-center w-full sm:w-auto justify-center">
                    <i class="fas fa-plus mr-2"></i> Tambah Item
                </a>
            </div>

            <!-- Bagian Tabel -->
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white text-sm rounded-lg shadow-lg">
                        <thead class="bg-orange-500 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Nomor Order</th>
                                <th class="px-4 py-3 text-left font-semibold">Deskripsi Pekerjaan</th>
                                <th class="px-4 py-3 text-left font-semibold">Total HPP</th>
                                <th class="px-4 py-3 text-left font-semibold">Total Kebutuhan Kerjaan</th>
                                <th class="px-4 py-3 text-left font-semibold">Total Margin</th>
                                <th class="px-4 py-3 text-left font-semibold">Persentase Margin</th>
                                <th class="px-4 py-3 text-left font-semibold">Approved</th>
                                <th class="px-4 py-3 text-center font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Iterasi Nomor Order -->
                            @foreach ($items as $item)
                                <tr class="bg-gray-100">
                                    <td class="px-4 py-3 font-semibold text-sm">
                                        {{ $item->nomor_order }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-900">{{ $item->deskripsi_pekerjaan }}</td>
                                    <td class="px-4 py-3 text-gray-900 font-semibold">Rp {{ number_format($item->total_hpp, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-gray-900 font-semibold">Rp {{ number_format($item->total_item, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-green-600 font-semibold">Rp {{ number_format($item->total_margin, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-blue-600 font-semibold">
                                        {{ number_format(($item->total_margin / $item->total_item) * 100, 2) }}%
                                    </td>
                                    <td class="px-4 py-3 text-gray-900">by Manager</td>
                                    <td class="px-4 py-3">
                                        <div class="flex space-x-2 justify-center">
                                            <!-- Tombol Lihat -->
                                            <a href="{{ route('pkm.items.show', $item->nomor_order) }}" 
                                               class="bg-blue-500 text-white p-2 rounded-md hover:bg-blue-700 transition duration-300 flex items-center">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <!-- Tombol Edit -->
                                            <a href="{{ route('pkm.items.edit', $item->nomor_order) }}" 
                                               class="bg-green-500 text-white p-2 rounded-md hover:bg-green-600 transition duration-300 flex items-center">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <!-- Tombol Hapus -->
                                            <form action="{{ route('pkm.items.destroy', $item->nomor_order) }}" method="POST" onsubmit="return confirmDelete(this);">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-500 text-white p-2 rounded-md hover:bg-red-600 transition duration-300 flex items-center">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form> 
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- SweetAlert untuk Notifikasi -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(form) {
            Swal.fire({
                title: 'Hapus Item?',
                text: "Item akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                preConfirm: () => {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            resolve(true);
                            form.submit(); // Eksekusi form hapus
                        }, 1500);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Terhapus!', 'Item telah dihapus.', 'success');
                }
            });

            return false;
        }
    </script>
</x-pkm-layout>
