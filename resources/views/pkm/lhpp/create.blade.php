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
                                  <option value="{{ $notification->notification_number }}"
        data-unit-work="{{ $notification->unit_work }}">
    {{ $notification->notification_number }}
</option>

                                @endforeach
                            </select>
                        </div>

                        <div>
                            <input type="hidden" name="nomor_order" id="nomor_order" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
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
                            <input type="text" name="unit_kerja" id="unit_kerja" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
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
                        <div class="grid grid-cols-4 gap-4 mb-4 material-row">
                            <div>
                                <label for="material_description_1" class="block text-sm font-medium text-gray-700">Actual Pemakaian Material</label>
                                <input type="text" name="material_description[]" id="material_description_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="material_volume_1" class="block text-sm font-medium text-gray-700">Jumlah (Kg/Jam/M2/Cm3)</label>
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

                    <!-- C. Actual Biaya Upah Kerja -->
                    <h3 class="font-semibold text-lg mb-2">B. Actual Biaya Jasa</h3>
                    <div id="upah-section">
                        <div class="grid grid-cols-4 gap-4 mb-4 upah-row">
                            <div>
                                <label for="upah_description_1" class="block text-sm font-medium text-gray-700">Actual Biaya Upah Kerja</label>
                                <input type="text" name="upah_description[]" id="upah_description_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label for="upah_volume_1" class="block text-sm font-medium text-gray-700">Jumlah (Kg/Jam/M2/Cm3)</label>
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
                        <div class="col-span-3 text-right font-semibold">TOTAL ACTUAL BIAYA (Jasa)</div>
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
                            <button type="button" id="add-dokumentasi" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300">Tambahkan Dokumentasi</button>
                        </div>
                    </div>

                    <!-- Kontrak PKM -->
                    <div>
                        <h3 class="font-semibold text-lg mb-2">Kontrak PKM</h3>
                        <div class="mb-4">
                            <label for="kontrak_pkm" class="block text-sm font-medium text-gray-700">Pilih Kontrak PKM</label>
                            <select id="kontrak_pkm" name="kontrak_pkm" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="" disabled selected>Pilih salah satu</option>
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

                @if ($errors->any())
                    <script>
                        Swal.fire({
                            title: 'Gagal!',
                            html: `{!! implode('<br>', $errors->all()) !!}`,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    </script>
                @endif

            </div>
        </div>
    </div>
<script>
/* ===============================
   🔹 Utility helpers & safety
================================*/
function qs(selector, root = document) { return root.querySelector(selector); }
function qsa(selector, root = document) { return Array.from(root.querySelectorAll(selector)); }
function safeAddEvent(el, event, fn) { if (el) el.addEventListener(event, fn); }

/* ===============================
   🔹 Tambah Dokumentasi
================================*/
let dokumentasiIndex = 2;
safeAddEvent(qs('#add-dokumentasi'), 'click', function () {
    const container = qs('#dokumentasi-container');
    if (!container) return;

    const newRow = document.createElement('div');
    newRow.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4', 'mt-2');
    newRow.innerHTML = `
        <div>
            <label class="block font-medium text-sm text-gray-700" for="foto_${dokumentasiIndex}">
                Foto Dokumentasi LHPP ${dokumentasiIndex}
            </label>
            <input id="foto_${dokumentasiIndex}" name="images[]" type="file" accept="image/*" class="form-input rounded-md shadow-sm mt-1 block w-full">
        </div>
    `;
    container.appendChild(newRow);
    dokumentasiIndex++;
});

/* ===============================
   🔹 Fetch Otomatis data LHPP saat memilih notifikasi
================================*/
safeAddEvent(qs('#notifikasi'), 'change', function () {
    const selectedNotification = this.value;
    if (!selectedNotification) return;

    const selectedOption = this.options[this.selectedIndex];
    const unitWork = selectedOption?.getAttribute('data-unit-work') ?? '';
    if (qs('#unit_kerja')) qs('#unit_kerja').value = unitWork;

    // job name
    fetch(`/pkm/lhpp/get-jobname/${selectedNotification}`)
        .then(r => r.json())
        .then(data => { if (qs('#description_notifikasi')) qs('#description_notifikasi').value = data.job_name || '-'; })
        .catch(e => console.error('Error fetching job name:', e));

    // purchase order
    fetch(`/pkm/lhpp/get-purchase-order/${selectedNotification}`)
        .then(r => r.json())
        .then(data => { if (qs('#purchase_order_number')) qs('#purchase_order_number').value = data.purchase_order_number || '-'; })
        .catch(e => console.error('Error fetching purchase order:', e));

    // nomor order
    fetch(`/pkm/lhpp/get-nomor-order/${selectedNotification}`)
        .then(r => r.json())
        .then(data => { if (qs('#nomor_order')) qs('#nomor_order').value = data.nomor_order || '-'; })
        .catch(e => console.error('Error fetching nomor order:', e));

    // recalc waktu if possible
    if (typeof calculateWorkDuration === 'function') calculateWorkDuration();
});

/* ===============================
   🔹 Hitung Durasi Pekerjaan (via endpoint)
================================*/
function calculateWorkDuration() {
    const notificationNumber = qs('#notifikasi')?.value;
    const tanggalSelesai = qs('#tanggal_selesai')?.value;
    if (!notificationNumber || !tanggalSelesai) return;

    fetch(`/pkm/calculate-work-duration/${notificationNumber}/${tanggalSelesai}`)
        .then(r => r.json())
        .then(data => { if (qs('#waktu_pengerjaan')) qs('#waktu_pengerjaan').value = data.waktu_pengerjaan || 0; })
        .catch(e => console.error('Error fetching work duration:', e));
}
safeAddEvent(qs('#tanggal_selesai'), 'change', calculateWorkDuration);

/* ===============================
   🔹 Perhitungan Otomatis (row, subtotal, total)
================================*/
function calculateTotal() {
    const a = parseFloat(qs('#material_subtotal')?.value || 0) || 0;
    const c = parseFloat(qs('#upah_subtotal')?.value || 0) || 0;
    if (qs('#total_biaya')) qs('#total_biaya').value = (a + c).toFixed(2);
}

function setupAutoCalculation(sectionId, prefix, subtotalId) {
    const section = qs(`#${sectionId}`);
    if (!section) return;

    function calculateRow(row) {
        const volInput = row.querySelector(`[name="${prefix}_volume[]"]`);
        const hargaInput = row.querySelector(`[name="${prefix}_harga_satuan[]"]`);
        const jumlahInput = row.querySelector(`[name="${prefix}_jumlah[]"]`);

        const volume = parseFloat((volInput?.value || '').toString().replace(/,/g, '')) || 0;
        const harga = parseFloat((hargaInput?.value || '').toString().replace(/,/g, '')) || 0;
        const jumlah = volume * harga;

        if (jumlahInput) jumlahInput.value = jumlah.toFixed(2);
        calculateSubtotal();
    }

    function calculateSubtotal() {
        let subtotal = 0;
        qsa(`[name="${prefix}_jumlah[]"]`, section).forEach(input => {
            subtotal += parseFloat((input.value || '0').toString().replace(/,/g, '')) || 0;
        });
        if (qs(`#${subtotalId}`)) qs(`#${subtotalId}`).value = subtotal.toFixed(2);
        calculateTotal();
    }

    // listen input changes inside section
    section.addEventListener('input', function (e) {
        const isVolOrHarga = e.target.matches(`[name="${prefix}_volume[]"], [name="${prefix}_harga_satuan[]"]`);
        if (!isVolOrHarga) return;

        // find parent row: prefer explicit row classes (material-row/upah-row), fallback to closest grid
        let row = e.target.closest(`.${prefix}-row`);
        if (!row) row = e.target.closest('.grid');
        if (!row) return;
        calculateRow(row);
    });
}

/* ===============================
   🔹 Recalculate existing server-filled rows on page load
   (fix: edit page where values already present)
================================*/
function recalcSectionRows(sectionId, prefix, subtotalId) {
    const section = qs(`#${sectionId}`);
    if (!section) return;

    // Prefer rows with explicit classes; fallback to any direct .grid children
    let rows = qsa(`.${prefix}-row`, section);
    if (!rows.length) {
        rows = qsa('#' + sectionId + ' > .grid, #' + sectionId + ' .grid', section);
    }

    rows.forEach(row => {
        const volInput = row.querySelector(`[name="${prefix}_volume[]"]`);
        const hargaInput = row.querySelector(`[name="${prefix}_harga_satuan[]"]`);
        const jumlahInput = row.querySelector(`[name="${prefix}_jumlah[]"]`);

        const volume = parseFloat((volInput?.value || '').toString().replace(/,/g, '')) || 0;
        const harga = parseFloat((hargaInput?.value || '').toString().replace(/,/g, '')) || 0;
        const jumlah = volume * harga;

        if (jumlahInput) jumlahInput.value = jumlah.toFixed(2);
    });

    // compute subtotal
    let subtotal = 0;
    qsa(`[name="${prefix}_jumlah[]"]`, section).forEach(input => {
        subtotal += parseFloat((input.value || '0').toString().replace(/,/g, '')) || 0;
    });
    if (qs(`#${subtotalId}`)) qs(`#${subtotalId}`).value = subtotal.toFixed(2);
}

/* ===============================
   🔹 Dynamic add-row handlers
   (material & upah)
================================*/
safeAddEvent(qs('#add-material-row'), 'click', function () {
    const container = qs('#material-section');
    if (!container) return;

    const row = document.createElement('div');
    row.classList.add('grid', 'grid-cols-4', 'gap-4', 'mb-4', 'material-row');
    row.innerHTML = `
        <div><input type="text" name="material_description[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Deskripsi material"></div>
        <div><input type="text" name="material_volume[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Volume"></div>
        <div><input type="text" name="material_harga_satuan[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Harga Satuan"></div>
        <div><input type="text" name="material_jumlah[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Jumlah" readonly></div>
    `;
    container.appendChild(row);
});

safeAddEvent(qs('#add-upah-row'), 'click', function () {
    const container = qs('#upah-section');
    if (!container) return;

    const row = document.createElement('div');
    row.classList.add('grid', 'grid-cols-4', 'gap-4', 'mb-4', 'upah-row');
    row.innerHTML = `
        <div><input type="text" name="upah_description[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Deskripsi upah"></div>
        <div><input type="text" name="upah_volume[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Volume"></div>
        <div><input type="text" name="upah_harga_satuan[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Harga Satuan"></div>
        <div><input type="text" name="upah_jumlah[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Jumlah" readonly></div>
    `;
    container.appendChild(row);
});

/* ===============================
   🔹 Init: attach calculators & recalc on DOM ready
================================*/
document.addEventListener('DOMContentLoaded', function () {
    // Attach auto-calculation handlers
    setupAutoCalculation('material-section', 'material', 'material_subtotal');
    setupAutoCalculation('upah-section', 'upah', 'upah_subtotal');

    // Recompute existing rows (fix edit page)
    recalcSectionRows('material-section', 'material', 'material_subtotal');
    recalcSectionRows('upah-section', 'upah', 'upah_subtotal');

    // Compute grand total from subtotals
    calculateTotal();
});
</script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    @if(session('success'))
    Swal.fire({
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        icon: 'success',
        confirmButtonText: 'OK'
    });
    @endif

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
