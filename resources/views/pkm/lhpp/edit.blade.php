<x-pkm-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Form LHPP') }}
        </h2>
    </x-slot>

    <!-- Tombol Kembali -->
    <a href="{{ route('pkm.lhpp.index') }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
        <i class="fas fa-arrow-left mr-2">Kembali</i>
    </a>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form action="{{ route('pkm.lhpp.update', $lhpp->notification_number) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="notifikasi" class="block text-sm font-medium text-gray-700">Order</label>
                            <input type="text" name="notification_number" id="notification_number" value="{{ $lhpp->notification_number }}" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="description_notifikasi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="description_notifikasi" id="description_notifikasi" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ $lhpp->description_notifikasi }}</textarea>
                        </div>
                        <div>
                            <label for="purchase_order_number" class="block text-sm font-medium text-gray-700">Purchasing Order</label>
                            <input type="text" name="purchase_order_number" id="purchase_order_number" value="{{ $lhpp->purchase_order_number }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="unit_kerja" class="block text-sm font-medium text-gray-700">Unit Kerja Peminta</label>
                            <input type="text" name="unit_kerja" id="unit_kerja" value="{{ $lhpp->unit_kerja }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700">Tanggal Selesai Pekerjaan</label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ $lhpp->tanggal_selesai }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" onchange="calculateWorkDuration()">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="waktu_pengerjaan" class="block text-sm font-medium text-gray-700">Waktu Pengerjaan (Hari)</label>
                        <input type="number" name="waktu_pengerjaan" id="waktu_pengerjaan" value="{{ $lhpp->waktu_pengerjaan }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                    </div>

                    <!-- A. Actual Pemakaian Material -->
                    <h3 class="font-semibold text-lg mb-2">A. Actual Pemakaian Material</h3>
                    <div id="material-section">
                        @foreach($lhpp->material_description ?? [] as $key => $description)
                        <div class="grid grid-cols-4 gap-4 mb-4 material-row">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Actual Pemakaian Material</label>
                                <input type="text" name="material_description[]" value="{{ $description }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Volume (Kg)</label>
                                <input type="text" name="material_volume[]" value="{{ $lhpp->material_volume[$key] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Harga Satuan (Rp)</label>
                                <input type="text" name="material_harga_satuan[]" value="{{ $lhpp->material_harga_satuan[$key] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                                <input type="text" name="material_jumlah[]" value="{{ $lhpp->material_jumlah[$key] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <button type="button" id="add-material-row" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-4">Tambah Row Material</button>

                    <!-- Subtotal A -->
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <div class="col-span-3 text-right font-semibold">SUB TOTAL (A)</div>
                        <div>
                            <input type="text" name="material_subtotal" id="material_subtotal" value="{{ $lhpp->material_subtotal }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                        </div>
                    </div>

                    <!-- C. Actual Biaya Upah Kerja -->
                    <h3 class="font-semibold text-lg mb-2">C. Actual Biaya Upah Kerja</h3>
                    <div id="upah-section">
                        @foreach($lhpp->upah_description ?? [] as $key => $description)
                        <div class="grid grid-cols-4 gap-4 mb-4 upah-row">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Actual Biaya Upah Kerja</label>
                                <input type="text" name="upah_description[]" value="{{ $description }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Volume (Jam/Kg)</label>
                                <input type="text" name="upah_volume[]" value="{{ $lhpp->upah_volume[$key] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Harga Satuan (Rp)</label>
                                <input type="text" name="upah_harga_satuan[]" value="{{ $lhpp->upah_harga_satuan[$key] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                                <input type="text" name="upah_jumlah[]" value="{{ $lhpp->upah_jumlah[$key] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <button type="button" id="add-upah-row" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-4">Tambah Row Upah</button>

                    <!-- Subtotal C -->
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <div class="col-span-3 text-right font-semibold">SUB TOTAL (C)</div>
                        <div>
                            <input type="text" name="upah_subtotal" id="upah_subtotal" value="{{ $lhpp->upah_subtotal }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                        </div>
                    </div>

                    <!-- Total Keseluruhan -->
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <div class="col-span-3 text-right font-semibold">TOTAL ACTUAL BIAYA (Material + Upah)</div>
                        <div>
                            <input type="text" name="total_biaya" id="total_biaya" value="{{ $lhpp->total_biaya }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                        </div>
                    </div>

                    <!-- Kontrak PKM -->
                    <div>
                        <h3 class="font-semibold text-lg mb-2">Kontrak PKM</h3>
                        <div class="mb-4">
                            <label for="kontrak_pkm" class="block text-sm font-medium text-gray-700">Pilih Kontrak PKM</label>
                            <select id="kontrak_pkm" name="kontrak_pkm" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="" disabled>Pilih salah satu</option>
                                <option value="Fabrikasi" {{ $lhpp->kontrak_pkm == 'Fabrikasi' ? 'selected' : '' }}>Fabrikasi</option>
                                <option value="Konstruksi" {{ $lhpp->kontrak_pkm == 'Konstruksi' ? 'selected' : '' }}>Konstruksi</option>
                                <option value="Pengerjaan Mesin" {{ $lhpp->kontrak_pkm == 'Pengerjaan Mesin' ? 'selected' : '' }}>Pengerjaan Mesin</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dokumentasi LHPP -->
                    <div class="mt-6">
                        <h3 class="font-semibold text-lg text-gray-800">Dokumentasi LHPP</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-2">
                            @foreach($lhpp->images ?? [] as $key => $image)
                                <div class="border border-gray-300 p-4 rounded-md relative">
                                    <img src="{{ asset('storage/' . $image['path']) }}" alt="Dokumentasi LHPP" class="w-full h-48 object-cover">
                                    <p class="text-sm text-gray-600 mt-2">{{ $image['description'] ?? 'Tanpa Keterangan' }}</p>

                                    <!-- Checkbox untuk hapus gambar -->
                                    <label class="flex items-center space-x-2 text-red-500 text-sm mt-2 cursor-pointer">
                                        <input type="checkbox" name="delete_images[]" value="{{ $image['path'] }}" class="form-checkbox h-4 w-4">
                                        <span>Hapus</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tambahkan gambar baru -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Tambahkan Gambar Baru</label>
                        <input type="file" name="new_images[]" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <!-- Tombol Submit -->
                    <div class="text-right mt-6">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function calculateWorkDuration() {
        const notificationNumber = document.getElementById('notification_number').value;
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

    /* Perhitungan otomatis sama seperti create */
    function setupAutoCalculation(sectionId, prefix, subtotalId) {
        const section = document.getElementById(sectionId);

        function calculateRow(row) {
            const volume = parseFloat(row.querySelector(`[name="${prefix}_volume[]"]`)?.value) || 0;
            const harga = parseFloat(row.querySelector(`[name="${prefix}_harga_satuan[]"]`)?.value) || 0;
            const jumlah = volume * harga;
            const jumlahInput = row.querySelector(`[name="${prefix}_jumlah[]"]`);
            if (jumlahInput) jumlahInput.value = jumlah.toFixed(2);
            calculateSubtotal();
        }

        function calculateSubtotal() {
            let subtotal = 0;
            section.querySelectorAll(`[name="${prefix}_jumlah[]"]`).forEach(input => {
                subtotal += parseFloat(input.value) || 0;
            });
            document.getElementById(subtotalId).value = subtotal.toFixed(2);
            calculateTotal();
        }

        section.addEventListener('input', e => {
            if (e.target.matches(`[name="${prefix}_volume[]"], [name="${prefix}_harga_satuan[]"]`)) {
                const row = e.target.closest('.grid');
                calculateRow(row);
            }
        });
    }

    function calculateTotal() {
        const a = parseFloat(document.getElementById('material_subtotal').value) || 0;
        const c = parseFloat(document.getElementById('upah_subtotal').value) || 0;
        document.getElementById('total_biaya').value = (a + c).toFixed(2);
    }

    setupAutoCalculation('material-section', 'material', 'material_subtotal');
    setupAutoCalculation('upah-section', 'upah', 'upah_subtotal');

    // add-row handlers
    document.getElementById('add-material-row').addEventListener('click', function () {
        const container = document.getElementById('material-section');
        const row = document.createElement('div');
        row.classList.add('grid', 'grid-cols-4', 'gap-4', 'mb-4', 'material-row');
        row.innerHTML = `
            <div><input type="text" name="material_description[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Deskripsi material"></div>
            <div><input type="text" name="material_volume[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Volume"></div>
            <div><input type="text" name="material_harga_satuan[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Harga Satuan"></div>
            <div><input type="text" name="material_jumlah[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Jumlah"></div>
        `;
        container.appendChild(row);
    });

    document.getElementById('add-upah-row').addEventListener('click', function () {
        const container = document.getElementById('upah-section');
        const row = document.createElement('div');
        row.classList.add('grid', 'grid-cols-4', 'gap-4', 'mb-4', 'upah-row');
        row.innerHTML = `
            <div><input type="text" name="upah_description[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Deskripsi upah"></div>
            <div><input type="text" name="upah_volume[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Volume"></div>
            <div><input type="text" name="upah_harga_satuan[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Harga Satuan"></div>
            <div><input type="text" name="upah_jumlah[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Jumlah"></div>
        `;
        container.appendChild(row);
    });
    </script>

    <script src="{{ asset('js/lhpp.js') }}"></script>
</x-pkm-layout>