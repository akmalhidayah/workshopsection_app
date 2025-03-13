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
                            <!-- Iterasi Nomor Notification -->
                            @foreach ($items as $item)
                                <tr class="bg-gray-100">
                                    <td class="px-4 py-3 font-semibold text-sm">
                                        {{ $item->notification_number }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-900">{{ $item->deskripsi_pekerjaan }}</td>
                                    <td class="px-4 py-3 text-gray-900 font-semibold">Rp {{ number_format($item->total_hpp, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-gray-900 font-semibold">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-green-600 font-semibold">Rp {{ number_format($item->total_margin, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-blue-600 font-semibold">
                                        @if($item->total > 0)
                                            {{ number_format(abs(($item->total_margin / $item->total) * 100), 2, ',', '.') }}%
                                        @else
                                            0%
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($item->approved_by === 'Approved')
                                            <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold">Approved</span>
                                        @elseif ($item->approved_by === 'Rejected')
                                            <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold">Rejected</span>
                                            <p class="text-xs text-gray-500 italic mt-1">{{ $item->rejection_reason }}</p>
                                        @else
                                            @if(isset($user) && strtolower(trim($user->jabatan)) === 'operation directorate' && strtolower(trim($user->departemen)) === 'pt. prima karya manunggal')
                                                <div class="flex justify-center space-x-1">
                                                    <button class="approve-btn bg-green-500 text-white px-2 py-1 rounded-md text-xs hover:bg-green-700 transition flex items-center"
                                                            data-id="{{ $item->notification_number }}">
                                                        <i class="fas fa-check mr-1"></i> Approve
                                                    </button>
                                                    <button class="reject-btn bg-red-500 text-white px-2 py-1 rounded-md text-xs hover:bg-red-700 transition flex items-center"
                                                            data-id="{{ $item->notification_number }}">
                                                        <i class="fas fa-times mr-1"></i> Reject
                                                    </button>
                                                </div>
                                            @else
                                                <span class="bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-semibold">Pending</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex space-x-2 justify-center">
                                            <!-- Tombol Lihat -->
                                            <a href="{{ route('pkm.items.show', $item->notification_number) }}" 
                                               class="bg-blue-500 text-white p-2 rounded-md hover:bg-blue-700 transition duration-300 flex items-center">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <!-- Tombol Edit -->
                                            <a href="{{ route('pkm.items.edit', $item->notification_number) }}" 
                                               class="bg-green-500 text-white p-2 rounded-md hover:bg-green-600 transition duration-300 flex items-center">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <!-- Tombol Hapus -->
                                            <form action="{{ route('pkm.items.destroy', $item->notification_number) }}" method="POST" onsubmit="return confirmDelete(this);">
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
        document.querySelectorAll('.approve-btn').forEach(button => {
        button.addEventListener('click', function () {
            let notificationNumber = this.getAttribute('data-id');

            Swal.fire({
                title: 'Konfirmasi Approval',
                text: "Apakah Anda yakin ingin menyetujui item ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Approve!'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateApproval(notificationNumber, 'Approved', null);
                }
            });
        });
    });

    document.querySelectorAll('.reject-btn').forEach(button => {
    button.addEventListener('click', function () {
        let notificationNumber = this.getAttribute('data-id');

        Swal.fire({
            title: 'Konfirmasi Penolakan',
            text: "Apakah Anda yakin ingin menolak item ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Tolak!'
        }).then((result) => {
            if (result.isConfirmed) {
                updateApproval(notificationNumber, 'Rejected');
            }
        });
    });
});


function updateApproval(notificationNumber, status) {
    fetch(`/pkm/items/${notificationNumber}/update-approval`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ approved_by: status })
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire('Sukses!', data.message, 'success').then(() => location.reload());
    })
    .catch(error => Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error'));
}

    </script>
    <script>
    // Menampilkan notifikasi jika ada session 'success'
    @if(session('success'))
        Swal.fire({
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    @endif

    // Konfirmasi sebelum update
    document.querySelector("form").addEventListener("submit", function(event) {
        event.preventDefault();
        let form = this;

        Swal.fire({
            title: 'Konfirmasi Perubahan',
            text: "Apakah Anda yakin ingin memperbarui item ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Konfirmasi sebelum delete
    function confirmDelete(form) {
        Swal.fire({
            title: 'Hapus Item?',
            text: "Item akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

        return false;
    }
</script>
</x-pkm-layout>
