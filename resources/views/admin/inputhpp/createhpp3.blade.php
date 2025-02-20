<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tombol Kembali -->
            <a href="{{ route('admin.inputhpp.index') }}" class="inline-block mb-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="font-semibold text-3xl text-gray-800 leading-tight">Form Input HPP Bengkel</h2>

                <!-- Form Input -->
                <form action="{{ route('admin.inputhpp.store') }}" method="POST">
                    @csrf
                    <!-- Bagian Notifikasi dan Deskripsi -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="notifikasi" class="block text-sm font-medium text-gray-700">Order</label>
                            <select id="notifikasi" name="notification_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="" disabled selected>Pilih Nomor Order</option>
                                @foreach($notifications as $notification)
                                    <option value="{{ $notification->notification_number }}" 
                                        data-unit-work="{{ $notification->unit_work }}"
                                        data-abnormal-title="{{ $notification->abnormal ? $notification->abnormal->abnormal_title : '' }}">
                                        {{ $notification->notification_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="cost_centre" class="block text-sm font-medium text-gray-700">Cost Centre</label>
                            <input type="text" id="cost_centre" name="cost_centre" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <input type="hidden" name="source_form" value="{{ $source_form }}">
                        </div>
                        <div>
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea id="deskripsi" name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                        </div>
                    </div>
                    <!-- Bagian Rencana Pemakaian, Target Penyelesaian, Unit Kerja Peminta, Unit Kerja Pengendali -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <!-- <div>
                            <label for="rencana_pemakaian" class="block text-sm font-medium text-gray-700">Rencana Pemakaian</label>
                            <input type="text" id="rencana_pemakaian" name="usage_plan" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="target_penyelesaian" class="block text-sm font-medium text-gray-700">Target Penyelesaian</label>
                            <input type="text" id="target_penyelesaian" name="completion_target" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div> -->

                        <div>
                            <label for="unit_kerja_peminta" class="block text-sm font-medium text-gray-700">Unit Kerja Peminta</label>
                            <input type="text" id="unit_kerja_peminta" name="requesting_unit" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="unit_kerja_pengendali" class="block text-sm font-medium text-gray-700">Unit Kerja Pengendali</label>
                            <input type="text" id="unit_kerja_pengendali" name="controlling_unit" value="Unit Of Workshop" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" readonly>
                        </div>
                    </div>
                    <!-- Bagian Outline Agreement -->
                    <div class="mt-6">
                        <label for="outline_agreement" class="block text-sm font-medium text-gray-700">Outline Agreement (OA)</label>
                        <input type="text" id="outline_agreement" name="outline_agreement" 
                            value="{{ $currentOA->outline_agreement ?? '' }}" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                            readonly>
                    </div>

                    <!-- Display Periode Kontrak Result -->
                    <div class="col-span-2 mt-6">
                        <p class="text-sm font-medium text-gray-700">Periode Kontrak Sekarang:</p>
                        <p id="periode_kontrak_result" class="text-sm text-gray-700">
                            {{ $currentOA ? \Carbon\Carbon::parse($currentOA->periode_kontrak_start)->format('d/m/Y') . ' sampai ' . \Carbon\Carbon::parse($currentOA->periode_kontrak_end)->format('d/m/Y') : '-' }}
                        </p>
                    </div>
                    <!-- Uraian Pekerjaan Container -->
                    <button type="button" id="tambah-uraian-btn" class="bg-gradient-to-r from-green-400 to-green-600 text-white px-6 py-3 rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition-transform duration-300 ease-in-out mt-4">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Uraian Pekerjaan
                    </button>
                    <button type="button" class="hapus-uraian-btn bg-red-500 text-white px-4 py-2 mt-4 rounded hover:bg-red-700">
                        <i class="fas fa-trash-alt mr-2"></i>Hapus Uraian Pekerjaan
                    </button>
                    <div id="uraian-pekerjaan-container" class="mt-6">
                        <div class="uraian-group">
                            <label class="font-semibold text-xl font-medium text-gray-700">Uraian Pekerjaan</label>
                            <div class="mt-4">
                                <input type="text" placeholder="Jenis Uraian Pekerjaan" name="uraian_pekerjaan[]" class="col-span-4 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Sub Uraian Pekerjaan -->
                            <div class="sub-uraian-container mt-6">
                                <div class="mt-4">
                                    <label class="block text-lg font-medium text-gray-700">Sub Uraian Pekerjaan</label>
                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-2 mt-1">
                                        <input type="text" placeholder="Jenis Sub Uraian Pekerjaan Dll" name="jenis_material[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="number" placeholder="Qty" name="qty[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="text" placeholder="Satuan" name="satuan[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="number" placeholder="Volume Satuan (Kg/Ea/Lot)" name="volume_satuan[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="number" placeholder="Jumlah Volume Satuan" name="jumlah_volume_satuan[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>

                                <!-- Harga Satuan -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700">Harga Satuan</label>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-1">
                                        <input type="number" placeholder="Material" name="harga_material[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="number" placeholder="Consumable" name="harga_consumable[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="number" placeholder="Upah Kerja" name="harga_upah[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>

                                <!-- Jumlah Harga Satuan -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700">Jumlah Harga Satuan</label>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-1">
                                        <input type="number" placeholder="Material" name="jumlah_harga_material[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="number" placeholder="Consumable" name="jumlah_harga_consumable[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="number" placeholder="Upah Kerja" name="jumlah_harga_upah[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>

                                <!-- Harga Total dan Keterangan -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700">Harga Total</label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-1">
                                        <input type="number" placeholder="Harga Total" name="harga_total[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="text" placeholder="Keterangan" name="keterangan[]" class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Field Total Keseluruhan -->
                    <div class="mt-6">
                        <label for="total_keseluruhan" class="block text-sm font-medium text-gray-700">Total Keseluruhan</label>
                        <input type="number" id="total_keseluruhan" name="total_amount" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-500 text-base font-medium text-white hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:w-auto sm:text-sm">Submit</button>
                    </div>
                </form>
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
    function updateJumlahHarga(row) {
        const jumlahVolumeSatuan = parseFloat(row.querySelector('[name="jumlah_volume_satuan[]"]').value) || 0;

        // Harga satuan
        const hargaMaterial = parseFloat(row.querySelector('[name="harga_material[]"]').value) || 0;
        const hargaConsumable = parseFloat(row.querySelector('[name="harga_consumable[]"]').value) || 0;
        const hargaUpah = parseFloat(row.querySelector('[name="harga_upah[]"]').value) || 0;

        // Target input untuk hasil perhitungan
        const jumlahHargaMaterialField = row.querySelector('[name="jumlah_harga_material[]"]');
        const jumlahHargaConsumableField = row.querySelector('[name="jumlah_harga_consumable[]"]');
        const jumlahHargaUpahField = row.querySelector('[name="jumlah_harga_upah[]"]');
        const hargaTotalField = row.querySelector('[name="harga_total[]"]');

        // Hitung hasil perkalian dan isi otomatis
        jumlahHargaMaterialField.value = hargaMaterial * jumlahVolumeSatuan;
        jumlahHargaConsumableField.value = hargaConsumable * jumlahVolumeSatuan;
        jumlahHargaUpahField.value = hargaUpah * jumlahVolumeSatuan;

        // Hitung harga_total sebagai hasil penjumlahan jumlah_harga_material + jumlah_harga_consumable + jumlah_harga_upah
        hargaTotalField.value = (parseFloat(jumlahHargaMaterialField.value) || 0) + 
                                (parseFloat(jumlahHargaConsumableField.value) || 0) + 
                                (parseFloat(jumlahHargaUpahField.value) || 0);

        updateTotalAmount();
    }

    function updateTotalAmount() {
        let totalAmount = 0;
        document.querySelectorAll('[name="harga_total[]"]').forEach(input => {
            totalAmount += parseFloat(input.value) || 0;
        });
        document.getElementById('total_keseluruhan').value = totalAmount;
    }

    function updateJumlahVolumeSatuan(row) {
        const qty = parseFloat(row.querySelector('[name="qty[]"]').value) || 0;
        const volumeSatuan = parseFloat(row.querySelector('[name="volume_satuan[]"]').value) || 0;
        const jumlahVolumeSatuanField = row.querySelector('[name="jumlah_volume_satuan[]"]');

        // Hitung hasil perkalian dan isi otomatis
        jumlahVolumeSatuanField.value = qty * volumeSatuan;

        // Perbarui jumlah harga setelah jumlah volume satuan diperbarui
        updateJumlahHarga(row);
    }

    function attachEventListenersToRow(row) {
        row.querySelectorAll('[name="harga_material[]"], [name="harga_consumable[]"], [name="harga_upah[]"], [name="jumlah_volume_satuan[]"]').forEach(input => {
            input.addEventListener('input', function () {
                updateJumlahHarga(row);
            });
        });

        row.querySelectorAll('[name="qty[]"], [name="volume_satuan[]"]').forEach(input => {
            input.addEventListener('input', function () {
                updateJumlahVolumeSatuan(row);
            });
        });
    }

    function attachEventListeners() {
        document.querySelectorAll('.sub-uraian-container').forEach(row => {
            attachEventListenersToRow(row);
        });
    }

    // Jalankan event listener saat halaman pertama kali dimuat
    attachEventListeners();
});
document.addEventListener("DOMContentLoaded", function () {
    function updateJumlahHarga(row) {
        const jumlahVolumeSatuan = parseFloat(row.querySelector('[name="jumlah_volume_satuan[]"]').value) || 0;

        // Harga satuan
        const hargaMaterial = parseFloat(row.querySelector('[name="harga_material[]"]').value) || 0;
        const hargaConsumable = parseFloat(row.querySelector('[name="harga_consumable[]"]').value) || 0;
        const hargaUpah = parseFloat(row.querySelector('[name="harga_upah[]"]').value) || 0;

        // Target input untuk hasil perhitungan
        const jumlahHargaMaterialField = row.querySelector('[name="jumlah_harga_material[]"]');
        const jumlahHargaConsumableField = row.querySelector('[name="jumlah_harga_consumable[]"]');
        const jumlahHargaUpahField = row.querySelector('[name="jumlah_harga_upah[]"]');
        const hargaTotalField = row.querySelector('[name="harga_total[]"]');

        // Hitung hasil perkalian dan isi otomatis
        jumlahHargaMaterialField.value = hargaMaterial * jumlahVolumeSatuan;
        jumlahHargaConsumableField.value = hargaConsumable * jumlahVolumeSatuan;
        jumlahHargaUpahField.value = hargaUpah * jumlahVolumeSatuan;

        // Hitung harga_total sebagai hasil penjumlahan jumlah_harga_material + jumlah_harga_consumable + jumlah_harga_upah
        hargaTotalField.value = (parseFloat(jumlahHargaMaterialField.value) || 0) + 
                                (parseFloat(jumlahHargaConsumableField.value) || 0) + 
                                (parseFloat(jumlahHargaUpahField.value) || 0);

        updateTotalAmount();
    }

    function updateTotalAmount() {
        let totalAmount = 0;
        document.querySelectorAll('[name="harga_total[]"]').forEach(input => {
            totalAmount += parseFloat(input.value) || 0;
        });
        document.getElementById('total_keseluruhan').value = totalAmount;
    }

    function updateJumlahVolumeSatuan(row) {
        const qty = parseFloat(row.querySelector('[name="qty[]"]').value) || 0;
        const volumeSatuan = parseFloat(row.querySelector('[name="volume_satuan[]"]').value) || 0;
        const jumlahVolumeSatuanField = row.querySelector('[name="jumlah_volume_satuan[]"]');

        // Hitung hasil perkalian dan isi otomatis
        jumlahVolumeSatuanField.value = qty * volumeSatuan;

        // Perbarui jumlah harga setelah jumlah volume satuan diperbarui
        updateJumlahHarga(row);
    }

    function attachEventListenersToRow(row) {
        row.querySelectorAll('[name="harga_material[]"], [name="harga_consumable[]"], [name="harga_upah[]"], [name="jumlah_volume_satuan[]"]').forEach(input => {
            input.addEventListener('input', function () {
                updateJumlahHarga(row);
            });
        });

        row.querySelectorAll('[name="qty[]"], [name="volume_satuan[]"]').forEach(input => {
            input.addEventListener('input', function () {
                updateJumlahVolumeSatuan(row);
            });
        });
    }

    function attachEventListeners() {
        document.querySelectorAll('.sub-uraian-container').forEach(row => {
            attachEventListenersToRow(row);
        });
    }

    // Jalankan event listener saat halaman pertama kali dimuat
    attachEventListeners();

    // Jika ada penambahan row baru, pastikan event listener tetap berfungsi
    document.getElementById('tambah-uraian-btn').addEventListener('click', function () {
        setTimeout(() => {
            attachEventListeners();
            updateTotalAmount();
        }, 100);
    });
});
document.addEventListener("DOMContentLoaded", function () {
    function updateJumlahHarga(row) {
        const jumlahVolumeSatuan = parseFloat(row.querySelector('[name="jumlah_volume_satuan[]"]').value) || 0;

        // Harga satuan
        const hargaMaterial = parseFloat(row.querySelector('[name="harga_material[]"]').value) || 0;
        const hargaConsumable = parseFloat(row.querySelector('[name="harga_consumable[]"]').value) || 0;
        const hargaUpah = parseFloat(row.querySelector('[name="harga_upah[]"]').value) || 0;

        // Target input untuk hasil perhitungan
        const jumlahHargaMaterialField = row.querySelector('[name="jumlah_harga_material[]"]');
        const jumlahHargaConsumableField = row.querySelector('[name="jumlah_harga_consumable[]"]');
        const jumlahHargaUpahField = row.querySelector('[name="jumlah_harga_upah[]"]');
        const hargaTotalField = row.querySelector('[name="harga_total[]"]');

        // Hitung hasil perkalian dan isi otomatis
        jumlahHargaMaterialField.value = hargaMaterial * jumlahVolumeSatuan;
        jumlahHargaConsumableField.value = hargaConsumable * jumlahVolumeSatuan;
        jumlahHargaUpahField.value = hargaUpah * jumlahVolumeSatuan;

        // Hitung harga_total sebagai hasil penjumlahan jumlah_harga_material + jumlah_harga_consumable + jumlah_harga_upah
        hargaTotalField.value = (parseFloat(jumlahHargaMaterialField.value) || 0) + 
                                (parseFloat(jumlahHargaConsumableField.value) || 0) + 
                                (parseFloat(jumlahHargaUpahField.value) || 0);
    }

    function updateJumlahVolumeSatuan(row) {
        const qty = parseFloat(row.querySelector('[name="qty[]"]').value) || 0;
        const volumeSatuan = parseFloat(row.querySelector('[name="volume_satuan[]"]').value) || 0;
        const jumlahVolumeSatuanField = row.querySelector('[name="jumlah_volume_satuan[]"]');

        // Hitung hasil perkalian dan isi otomatis
        jumlahVolumeSatuanField.value = qty * volumeSatuan;

        // Perbarui jumlah harga setelah jumlah volume satuan diperbarui
        updateJumlahHarga(row);
    }

    function attachEventListenersToRow(row) {
        row.querySelectorAll('[name="harga_material[]"], [name="harga_consumable[]"], [name="harga_upah[]"], [name="jumlah_volume_satuan[]"]').forEach(input => {
            input.addEventListener('input', function () {
                updateJumlahHarga(row);
            });
        });

        row.querySelectorAll('[name="qty[]"], [name="volume_satuan[]"]').forEach(input => {
            input.addEventListener('input', function () {
                updateJumlahVolumeSatuan(row);
            });
        });
    }

    function attachEventListeners() {
        document.querySelectorAll('.sub-uraian-container').forEach(row => {
            attachEventListenersToRow(row);
        });
    }

    // Jalankan event listener saat halaman pertama kali dimuat
    attachEventListeners();

    // Jika ada penambahan row baru, pastikan event listener tetap berfungsi
    document.getElementById('tambah-uraian-btn').addEventListener('click', function () {
        setTimeout(() => {
            attachEventListeners();
        }, 100);
    });
});
document.addEventListener("DOMContentLoaded", function () {
    function updateJumlahVolumeSatuan(row) {
        const qty = parseFloat(row.querySelector('[name="qty[]"]').value) || 0;
        const volumeSatuan = parseFloat(row.querySelector('[name="volume_satuan[]"]').value) || 0;
        const jumlahVolumeSatuanField = row.querySelector('[name="jumlah_volume_satuan[]"]');

        // Hitung hasil perkalian dan isi otomatis
        jumlahVolumeSatuanField.value = qty * volumeSatuan;
    }

    function attachEventListenersToRow(row) {
        row.querySelectorAll('[name="qty[]"], [name="volume_satuan[]"]').forEach(input => {
            input.addEventListener('input', function () {
                updateJumlahVolumeSatuan(row);
            });
        });
    }

    function attachEventListeners() {
        document.querySelectorAll('.sub-uraian-container').forEach(row => {
            attachEventListenersToRow(row);
        });
    }

    // Jalankan event listener saat halaman pertama kali dimuat
    attachEventListeners();

    // Jika ada penambahan row baru, pastikan event listener tetap berfungsi
    document.getElementById('tambah-uraian-btn').addEventListener('click', function () {
        setTimeout(() => {
            attachEventListeners();
        }, 100);
    });

    // Event listener untuk dropdown 'notifikasi'
    document.getElementById('notifikasi').addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const unitWork = selectedOption.getAttribute('data-unit-work');
        const abnormalTitle = selectedOption.getAttribute('data-abnormal-title');

        // Isi otomatis field Unit Kerja Peminta dan Deskripsi
        document.getElementById('unit_kerja_peminta').value = unitWork ? unitWork : '';
        document.getElementById('deskripsi').value = abnormalTitle ? abnormalTitle : '';
    });

    // Event listener untuk menambahkan Uraian Pekerjaan baru
    document.getElementById('tambah-uraian-btn').addEventListener('click', function () {
        const uraianPekerjaanContainer = document.getElementById('uraian-pekerjaan-container');
        const newUraianPekerjaan = uraianPekerjaanContainer.querySelector('.uraian-group').cloneNode(true);
        newUraianPekerjaan.querySelectorAll('input').forEach(input => input.value = '');

        // Tambahkan tombol hapus jika belum ada
        const hapusButton = newUraianPekerjaan.querySelector('.hapus-uraian-btn');
        if (!hapusButton) {
            const newHapusButton = document.createElement('button');
            newHapusButton.type = 'button';
            newHapusButton.className = 'hapus-uraian-btn bg-red-500 text-white px-4 py-2 mt-4 rounded hover:bg-red-700';
            newHapusButton.innerHTML = '<i class="fas fa-trash-alt mr-2"></i>Hapus Uraian Pekerjaan';
            newUraianPekerjaan.appendChild(newHapusButton);
            attachRemoveHandler(newHapusButton);
        }

        uraianPekerjaanContainer.appendChild(newUraianPekerjaan);
    });

    // Event listener untuk tombol hapus
    function attachRemoveHandler(button) {
        button.addEventListener('click', function () {
            const uraianGroup = button.closest('.uraian-group');
            if (uraianGroup) {
                uraianGroup.remove();
            }
        });
    }

    // Pastikan semua tombol hapus yang ada bekerja
    document.querySelectorAll('.hapus-uraian-btn').forEach(btn => {
        attachRemoveHandler(btn);
    });
});

            </script>
@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{ session('error') }}',
        });
    </script>
@endif

@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
        });
    </script>
@endif
</x-admin-layout>
