<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Formulir Pembuatan SPK') }}
        </h2>
    </x-slot>
    
    <div class="p-6">
        <!-- Tombol Kembali -->
        <a href="{{ route('notifikasi.index') }}" class="inline-block mb-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>

        <!-- Form SPK -->
        <form action="{{ route('spk.store') }}" method="POST" id="spkForm">
            @csrf

            <!-- Input Field -->
            <div class="mb-4">
                <label for="kepada_yth" class="block text-sm font-medium text-gray-700">Kepada YTH</label>
                <input type="text" id="kepada_yth" name="kepada_yth" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="PT. Prima Karya Manunggal">
            </div>
            <div class="mb-4">
                <label for="perihal" class="block text-sm font-medium text-gray-700">Perihal</label>
                <input type="text" id="perihal" name="perihal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="mb-4">
                <label for="nomor_spk" class="block text-sm font-medium text-gray-700">Nomor SPK</label>
                <input type="text" id="nomor_spk" name="nomor_spk" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="mb-4">
                <label for="tanggal_spk" class="block text-sm font-medium text-gray-700">Tanggal SPK</label>
                <input type="date" id="tanggal_spk" name="tanggal_spk" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="mb-4">
                <label for="nomor_notifikasi" class="block text-sm font-medium text-gray-700">Nomor Order</label>
                <input type="text" id="nomor_notifikasi" name="notification_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly value="{{ $notification->notification_number ?? '' }}">
            </div>
            <div class="mb-4">
                <label for="unit_kerja_peminta" class="block text-sm font-medium text-gray-700">Unit Kerja Peminta</label>
                <input type="text" id="unit_kerja_peminta" name="unit_work" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly value="{{ $notification->unit_work ?? '' }}">
            </div>

            <!-- Tombol untuk menambah dan menghapus Functional Location -->
            <div class="flex justify-end mb-4">
                <button type="button" id="addFunctionalLocationBtn" class="bg-green-500 text-white px-3 py-1 rounded-lg hover:bg-green-600 mr-2">Tambah Tabel</button>
                <button type="button" id="removeLastTableBtn" class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600">Hapus Tabel</button>
            </div>

            <!-- Tabel untuk Scope Pekerjaan -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-gray-900 border border-gray-300 text-xs rounded-lg shadow-md">
                    <thead class="bg-blue-800 text-white">
                        <tr>
                            <th class="px-2 py-2">Functional Location</th>
                            <th class="px-2 py-2">Scope Pekerjaan</th>
                            <th class="px-2 py-2">Qty</th>
                            <th class="px-2 py-2">Stn</th>
                            <th class="px-2 py-2">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="functionalLocationTable">
                        <tr id="functional_location_1">
                            <td><input type="text" name="functional_location[]" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                            <td><input type="text" name="scope_pekerjaan[]" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                            <td><input type="number" name="qty[]" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                            <td><input type="text" name="stn[]" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                            <td><input type="text" name="keterangan[]" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                        </tr>
                    </tbody>
                </table>

                <div class="mb-4">
                    <label for="keterangan_pengerjaan" class="block text-sm font-medium text-gray-700">Keterangan Pengerjaan Urgensi</label>
                    <textarea id="keterangan_pengerjaan" name="keterangan_pekerjaan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
            </div>

            <!-- Tombol Simpan -->
            <div class="flex justify-end mt-4">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Script untuk menambah dan menghapus tabel -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let rowCount = 1;

            // Tambah Functional Location baru
            document.getElementById('addFunctionalLocationBtn').addEventListener('click', function () {
                rowCount++;
                let newFunctionalLocation = `
                    <tr id="functional_location_${rowCount}">
                        <td><input type="text" name="functional_location[]" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                        <td><input type="text" name="scope_pekerjaan[]" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                        <td><input type="number" name="qty[]" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                        <td><input type="text" name="stn[]" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                        <td><input type="text" name="keterangan[]" class="w-full rounded-md border-gray-300 shadow-sm"></td>
                    </tr>
                `;
                document.getElementById('functionalLocationTable').insertAdjacentHTML('beforeend', newFunctionalLocation);
            });

            // Hapus tabel terakhir yang ditambahkan
            document.getElementById('removeLastTableBtn').addEventListener('click', function () {
                let tableRows = document.querySelectorAll('#functionalLocationTable tr');
                if (tableRows.length > 1) {
                    tableRows[tableRows.length - 1].remove();
                    rowCount--;
                }
            });

            // Mengisi input kosong dengan "-" sebelum form di-submit
            document.querySelector('form').addEventListener('submit', function() {
                let inputs = document.querySelectorAll('input, textarea');
                
                inputs.forEach(input => {
                    if (input.value.trim() === '') {
                        input.value = '-';
                    }
                });
            });
        });
    </script>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @if(session('success_spk'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success_spk') }}',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
</x-admin-layout>
