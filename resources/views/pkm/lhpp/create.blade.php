<x-pkm-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Form LHPP') }}
        </h2>
    </x-slot>
    <!-- Tombol Kembali -->
    <a href="{{ route('pkm.lhpp.index') }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
    <i class="fas fa-arrow-left mr-2">Kembali</i>
            </a>

                    <div class="py-12">
                        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                              <!-- ✅ Tambahkan enctype agar bisa unggah file -->
                            <form action="{{ route('pkm.lhpp.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="notifikasi" class="block text-sm font-medium text-gray-700">Order</label>
                        <select id="notifikasi" name="notification_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="" disabled selected>Pilih Nomor Order</option>
                            @foreach($notifications as $notification)
                                <option value="{{ $notification->notification_number }}" data-unit-work="{{ $notification->unit_work }}">
                                    {{ $notification->notification_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                        <div>
                            <label for="nomor_order" class="block text-sm font-medium text-gray-700">Nomor Order</label>
                            <input type="text" name="nomor_order" id="nomor_order" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="description_notifikasi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="description_notifikasi" id="description_notifikasi" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                        </div>
                        <div>
                            <label for="purchase_order_number" class="block text-sm font-medium text-gray-700">Purchasing Order</label>
                            <input type="text" name="purchase_order_number" id="purchase_order_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" >
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="unit_kerja" class="block text-sm font-medium text-gray-700">Unit Kerja Peminta</label>
                        <input type="text" name="unit_kerja" id="unit_kerja" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>
                        <div>
                        <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700">Tanggal Selesai Pekerjaan</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" onchange="calculateWorkDuration()">
                        </div>
                    </div>

                    <div class="mb-4">
                    <label for="waktu_pengerjaan" class="block text-sm font-medium text-gray-700">Waktu Pengerjaan (Hari)</label>
                    <input type="number" name="waktu_pengerjaan" id="waktu_pengerjaan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                     <!-- A. Actual Pemakaian Material -->
                     <h3 class="font-semibold text-lg mb-2">A. Actual Pemakaian Material</h3>
                    <div id="material-section">
                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <div>
                                <label for="material_description_1" class="block text-sm font-medium text-gray-700">Actual Pemakaian Material</label>
                                <input type="text" name="material_description[]" id="material_description_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="material_volume_1" class="block text-sm font-medium text-gray-700">Volume (Kg)</label>
                                <input type="text" name="material_volume[]" id="material_volume_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="material_harga_satuan_1" class="block text-sm font-medium text-gray-700">Harga Satuan (Rp)</label>
                                <input type="text" name="material_harga_satuan[]" id="material_harga_satuan_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="material_jumlah_1" class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                                <input type="text" name="material_jumlah[]" id="material_jumlah_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-material-row" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-4">Tambah Row Material</button>

                    <!-- Subtotal A -->
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <div class="col-span-3 text-right font-semibold">SUB TOTAL (A)</div>
                        <div>
                            <input type="text" name="material_subtotal" id="material_subtotal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                        </div>
                    </div>
                    <!-- B. Actual Pemakaian Consumable -->
                    <h3 class="font-semibold text-lg mb-2">B. Actual Pemakaian Consumable</h3>
                    <div id="consumable-section">
                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <div>
                                <label for="consumable_description_1" class="block text-sm font-medium text-gray-700">Actual Pemakaian Consumable</label>
                                <input type="text" name="consumable_description[]" id="consumable_description_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="consumable_volume_1" class="block text-sm font-medium text-gray-700">Volume (Jam/Kg)</label>
                                <input type="text" name="consumable_volume[]" id="consumable_volume_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="consumable_harga_satuan_1" class="block text-sm font-medium text-gray-700">Harga Satuan (Rp)</label>
                                <input type="text" name="consumable_harga_satuan[]" id="consumable_harga_satuan_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="consumable_jumlah_1" class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                                <input type="text" name="consumable_jumlah[]" id="consumable_jumlah_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-consumable-row" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-4">Tambah Row Consumable</button>

                    <!-- Subtotal B -->
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <div class="col-span-3 text-right font-semibold">SUB TOTAL (B)</div>
                        <div>
                            <input type="text" name="consumable_subtotal" id="consumable_subtotal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                        </div>
                    </div>


                     <!-- C. Actual Biaya Upah Kerja -->
                    <h3 class="font-semibold text-lg mb-2">C. Actual Biaya Upah Kerja</h3>
                    <div id="upah-section">
                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <div>
                                <label for="upah_description_1" class="block text-sm font-medium text-gray-700">Actual Biaya Upah Kerja</label>
                                <input type="text" name="upah_description[]" id="upah_description_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="upah_volume_1" class="block text-sm font-medium text-gray-700">Volume (Jam/Kg)</label>
                                <input type="text" name="upah_volume[]" id="upah_volume_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="upah_harga_satuan_1" class="block text-sm font-medium text-gray-700">Harga Satuan (Rp)</label>
                                <input type="text" name="upah_harga_satuan[]" id="upah_harga_satuan_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="upah_jumlah_1" class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                                <input type="text" name="upah_jumlah[]" id="upah_jumlah_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-upah-row" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-4">Tambah Row Upah</button>

                    <!-- Subtotal C -->
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <div class="col-span-3 text-right font-semibold">SUB TOTAL (C)</div>
                        <div>
                            <input type="text" name="upah_subtotal" id="upah_subtotal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                        </div>
                    </div>
                    <!-- Total Keseluruhan -->
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <div class="col-span-3 text-right font-semibold">TOTAL ACTUAL BIAYA (A + B + C)</div>
                        <div>
                            <input type="text" name="total_biaya" id="total_biaya" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                        </div>
                    </div>
                       <!-- Form untuk Dokumentasi LHPP -->
                       <div class="mt-6">
                        <h3 class="font-semibold text-lg text-gray-800">Dokumentasi LHPP</h3>
                        <div id="dokumentasi-container" class="grid grid-cols-1 gap-4 mt-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700" for="foto_1">
                                        Foto Dokumentasi LHPP 1
                                    </label>
                                    <input id="foto_1" name="images[]" type="file" accept="image/*" class="form-input rounded-md shadow-sm mt-1 block w-full">
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="button" id="add-dokumentasi"
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300">
                                Tambahkan Dokumentasi
                            </button>
                        </div>
                    </div>
                        <!-- Kontrak PKM -->
                        <div>
                            <h3 class="font-semibold text-lg mb-2">Kontrak PKM</h3>
                            <div class="mb-4">
                                <label for="kontrak_pkm" class="block text-sm font-medium text-gray-700">Pilih Kontrak PKM</label>
                                <select id="kontrak_pkm" name="kontrak_pkm" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="" disabled selected>Pilih salah satu</option> <!-- Default placeholder -->
                                    <option value="Fabrikasi">Fabrikasi</option>
                                    <option value="Konstruksi">Konstruksi</option>
                                    <option value="Pengerjaan Mesin">Pengerjaan Mesin</option>
                                </select>
                            </div>
                        </div>

                    <!-- Tombol Submit -->
                    <div class="text-right">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
      // Script untuk tambah baris dokumentasi baru
      let dokumentasiIndex = 2;
document.getElementById('add-dokumentasi').addEventListener('click', function () {
    const container = document.getElementById('dokumentasi-container');

    const newRow = document.createElement('div');
    newRow.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4', 'mt-2');
    newRow.innerHTML = `
        <div>
            <label class="block font-medium text-sm text-gray-700" for="foto_${dokumentasiIndex}">
                Foto Dokumentasi LHPP ${dokumentasiIndex}
            </label>
            <input id="foto_${dokumentasiIndex}" name="images[]" type="file" accept="image/*"
                class="form-input rounded-md shadow-sm mt-1 block w-full">
        </div>
    `;

    container.appendChild(newRow);
    dokumentasiIndex++;
});
document.getElementById('notifikasi').addEventListener('change', function () {
    const selectedNotification = this.value; // Ambil notification_number yang dipilih

    if (selectedNotification) {
        // 🔹 Ambil Purchase Order berdasarkan notification_number
        fetch(`/pkm/lhpp/get-purchase-order/${selectedNotification}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('purchase_order_number').value = data.purchase_order_number || '-';
            });

        // 🔹 Ambil Nomor Order berdasarkan notification_number
        fetch(`/pkm/lhpp/get-nomor-order/${selectedNotification}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('nomor_order').value = data.nomor_order || '-';
            });

        // 🔹 Isi otomatis Unit Kerja berdasarkan data `data-unit-work` di `<option>`
        const selectedOption = this.options[this.selectedIndex];
        const unitWork = selectedOption.getAttribute('data-unit-work');
        document.getElementById('unit_kerja').value = unitWork ? unitWork : '';
    }
});

document.getElementById('notifikasi').addEventListener('change', function () {
    const selectedNotification = this.value; // Ambil notification_number yang dipilih

    if (selectedNotification) {
        // 🔹 Ambil Purchase Order berdasarkan notification_number
        fetch(`/pkm/lhpp/get-purchase-order/${selectedNotification}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('purchase_order_number').value = data.purchase_order_number || '-';
            });

        // 🔹 Ambil Nomor Order berdasarkan notification_number
        fetch(`/pkm/lhpp/get-nomor-order/${selectedNotification}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('nomor_order').value = data.nomor_order || '-';
            });

        // 🔹 Isi otomatis Unit Kerja berdasarkan data `data-unit-work` di `<option>`
        const selectedOption = this.options[this.selectedIndex];
        const unitWork = selectedOption.getAttribute('data-unit-work');
        document.getElementById('unit_kerja').value = unitWork ? unitWork : '';

        // 🔹 Coba hitung durasi pekerjaan setelah order dipilih (jika tanggal sudah dipilih)
        calculateWorkDuration();
    }
});

document.getElementById('notifikasi').addEventListener('change', function () {
    const selectedNotification = this.value; // Ambil notification_number yang dipilih

    if (selectedNotification) {
        // 🔹 Ambil Deskripsi Notifikasi (Abnormal Title)
        fetch(`/pkm/lhpp/get-abnormal-description/${selectedNotification}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('description_notifikasi').value = data.description_notifikasi || '-';
            })
            .catch(error => console.error('Error fetching abnormal description:', error));

        // 🔹 Ambil Purchase Order berdasarkan notification_number
        fetch(`/pkm/lhpp/get-purchase-order/${selectedNotification}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('purchase_order_number').value = data.purchase_order_number || '-';
            })
            .catch(error => console.error('Error fetching purchase order:', error));

        // 🔹 Ambil Nomor Order berdasarkan notification_number
        fetch(`/pkm/lhpp/get-nomor-order/${selectedNotification}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('nomor_order').value = data.nomor_order || '-';
            })
            .catch(error => console.error('Error fetching nomor order:', error));

        // 🔹 Isi otomatis Unit Kerja berdasarkan data `data-unit-work` di `<option>`
        const selectedOption = this.options[this.selectedIndex];
        const unitWork = selectedOption.getAttribute('data-unit-work');
        document.getElementById('unit_kerja').value = unitWork ? unitWork : '';

        // 🔹 Coba hitung durasi pekerjaan setelah order dipilih (jika tanggal sudah dipilih)
        calculateWorkDuration();
    }
});

function calculateWorkDuration() {
    const notificationNumber = document.getElementById('notifikasi').value;
    const tanggalSelesai = document.getElementById('tanggal_selesai').value;

    if (notificationNumber && tanggalSelesai) {
        fetch(`/pkm/calculate-work-duration/${notificationNumber}/${tanggalSelesai}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('waktu_pengerjaan').value = data.waktu_pengerjaan || 0;
            })
            .catch(error => console.error('Error fetching work duration:', error));
    }
}

// 🔹 Pastikan hitungan berjalan setiap kali tanggal selesai berubah
document.getElementById('tanggal_selesai').addEventListener('change', calculateWorkDuration);

</script>
    <script src="{{ asset('js/lhpp.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // SweetAlert untuk pesan sukses
    @if(session('success'))
    Swal.fire({
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        icon: 'success',
        confirmButtonText: 'OK'
    });
    @endif

    // SweetAlert untuk pesan error
    @if(session('error'))
    Swal.fire({
        title: 'Gagal!',
        text: "{{ session('error') }}",
        icon: 'error',
        confirmButtonText: 'OK'
    });
    @endif
</script>
</x-pkm-layout>
