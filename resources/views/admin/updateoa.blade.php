<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Kuota Anggaran & Outline Agreement') }}
        </h2>
    </x-slot>

    <div class="container mx-auto p-6 bg-white shadow-md rounded-lg">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold">Input Kuota Anggaran & OA</h2>
            <div>
                <button onclick="confirmNewOA()" class="px-4 py-2 bg-green-500 text-white rounded-lg shadow hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                    Buat OA Baru
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.storeOA') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Unit Kerja -->
<div>
    <label for="unit_work" class="block text-sm font-medium text-gray-700">Unit Kerja</label>
    <select
        id="unit_work"
        name="unit_work"
        class="mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        required
    >
        <option value="">Pilih Unit Kerja</option>
        @foreach($unitWorks as $unit)
            <option value="{{ $unit->name }}"
                {{ old('unit_work', $latestData->unit_work ?? '') === $unit->name ? 'selected' : '' }}>
                {{ $unit->name }}
            </option>
        @endforeach
    </select>
</div>


                <!-- Outline Agreement (OA) -->
                <div>
                    <label for="outline_agreement" class="block text-sm font-medium text-gray-700">Outline Agreement (OA)</label>
                    <input
                        type="text"
                        id="outline_agreement"
                        name="outline_agreement"
                        value="{{ request('new') ? '' : old('outline_agreement', $latestData->outline_agreement ?? '') }}"
                        class="mt-1 block w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Masukkan nomor OA"
                    >
                </div>

                <!-- Jenis Kontrak -->
                <div>
                    <label for="jenisKontrak" class="block text-sm font-medium text-gray-700">Jenis Kontrak</label>
                    <select
                        id="jenisKontrak"
                        name="jenis_kontrak"
                        class="mt-1 block w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="handleJenisKontrakChange()"
                        required
                    >
                        <option value="">Pilih Jenis Kontrak</option>
                        <option value="Bengkel Mesin"   {{ old('jenis_kontrak', $latestData->jenis_kontrak ?? '') == 'Bengkel Mesin' ? 'selected' : '' }}>Bengkel Mesin</option>
                        <option value="Bengkel Listrik" {{ old('jenis_kontrak', $latestData->jenis_kontrak ?? '') == 'Bengkel Listrik' ? 'selected' : '' }}>Bengkel Listrik</option>
                        <option value="Field Supporting" {{ old('jenis_kontrak', $latestData->jenis_kontrak ?? '') == 'Field Supporting' ? 'selected' : '' }}>Field Supporting</option>
                    </select>
                </div>

                <!-- Nama Kontrak -->
                <div id="namaKontrakContainer">
                    <label for="namaKontrak" class="block text-sm font-medium text-gray-700">Nama Kontrak</label>
                    <select
                        id="namaKontrak"
                        name="nama_kontrak"
                        class="mt-1 block w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                        <option value="">Pilih Nama Kontrak</option>
                    </select>
                </div>

                <!-- Nilai Kontrak -->
                <div>
                    <label for="nilai_kontrak" class="block text-sm font-medium text-gray-700">Nilai Kontrak</label>
                    <div class="flex items-center">
                        <span class="text-gray-700 mr-2">Rp.</span>
                        <input
                            type="number"
                            id="nilai_kontrak"
                            name="nilai_kontrak"
                            value="{{ request('new') ? '' : old('nilai_kontrak', $latestData->nilai_kontrak ?? '') }}"
                            class="mt-1 block w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            oninput="updateTotalKuota()"
                            required
                        >
                    </div>
                </div>

                <!-- Total Kuota Kontrak -->
                <div>
                    <label for="total_kuota_kontrak" class="block text-sm font-medium text-gray-700">Total Kuota Kontrak</label>
                    <div class="flex items-center">
                        <span class="text-gray-700 mr-2">Rp.</span>
                        <input
                            type="number"
                            id="total_kuota_kontrak"
                            name="total_kuota_kontrak"
                            value="{{ request('new') ? '' : old('total_kuota_kontrak', $latestData->total_kuota_kontrak ?? '') }}"
                            class="mt-1 block w-full px-3 py-2 border rounded-md bg-gray-100 text-gray-600"
                            readonly
                        >
                    </div>
                </div>

                <!-- Periode Kontrak -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Periode Kontrak</label>
                    <div class="flex flex-col md:flex-row md:items-center md:space-x-4 mt-1">
                      @php
    // helper singkat: ambil value tanggal yang valid untuk input date (Y-m-d)
    function oldOrModelDate($field, $latestData) {
        // 1) prioritas old() dari request/validation
        $old = old($field);
        if ($old !== null && $old !== '') return $old;

        // 2) jika ada $latestData dan property ada -> format ke Y-m-d
        if (!empty($latestData) && isset($latestData->{$field}) && $latestData->{$field} !== null) {
            try {
                return \Carbon\Carbon::parse($latestData->{$field})->format('Y-m-d');
            } catch (\Throwable $e) {
                return (string) $latestData->{$field};
            }
        }
        return '';
    }
@endphp

<input
    type="date"
    id="periode_kontrak_start"
    name="periode_kontrak_start"
    value="{{ request('new') ? '' : oldOrModelDate('periode_kontrak_start', $latestData) }}"
    class="block w-full md:w-1/2 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
    onchange="updatePeriodeKontrak()"
    required
>
<span class="hidden md:inline text-sm text-gray-700">sampai</span>
<input
    type="date"
    id="periode_kontrak_end"
    name="periode_kontrak_end"
    value="{{ request('new') ? '' : oldOrModelDate('periode_kontrak_end', $latestData) }}"
    class="block w-full md:w-1/2 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mt-2 md:mt-0"
    onchange="updatePeriodeKontrak()"
    required
>

                    </div>
                </div>

                <!-- Display Periode Kontrak Result -->
                <div class="col-span-2">
                    <p class="text-sm font-medium text-gray-700">Periode Kontrak Sekarang:</p>
                    <p id="periode_kontrak_result" class="text-sm text-gray-700 mt-1">
                        {{ request('new') ? '-' : old('periode_kontrak_result', ($latestData->periode_kontrak_start ?? '') . ' sampai ' . ($latestData->periode_kontrak_end ?? '')) }}
                    </p>
                </div>

                <!-- Tambahan Kuota Kontrak (Optional) -->
                <div class="col-span-2 bg-yellow-50 p-4 rounded-md">
                    <label for="tambahan_kuota_kontrak" class="block text-sm font-medium text-gray-700">Tambahan Kuota Kontrak (Opsional)</label>
                    <div class="flex items-center mt-1">
                        <span class="text-gray-700 mr-2">Rp.</span>
                        <input
                            type="number"
                            id="tambahan_kuota_kontrak"
                            name="tambahan_kuota_kontrak"
                            value="{{ request('new') ? '' : old('tambahan_kuota_kontrak', $latestData->tambahan_kuota_kontrak ?? '') }}"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-400"
                            oninput="updateTotalKuota()"
                        >
                    </div>
                </div>

                <!-- Adendum Periode Kontrak (Optional) -->
                <div class="col-span-2 bg-blue-50 p-4 rounded-md">
                    <label for="adendum_end" class="block text-sm font-medium text-gray-700">Adendum Periode Kontrak (Opsional)</label>
                   <input
                        type="date"
                        id="adendum_end"
                        name="adendum_end"
                        value="{{ request('new') ? '' : oldOrModelDate('adendum_end', $latestData) }}"
                        class="mt-1 block w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                        onchange="updatePeriodeKontrak()"
                    >
                </div>
            </div>

            {{-- ================== Target Biaya Jasa Pemeliharaan (dynamic list) ================== --}}
            <div class="mt-6 border border-emerald-200 rounded-lg p-4 bg-emerald-50">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="text-sm font-semibold text-emerald-700">Target Biaya Jasa Pemeliharaan</h3>
                    <div class="text-sm text-gray-700">
                        <span class="font-medium">Ringkasan:</span>
                        <span id="target_ringkas" class="font-semibold">
                            -
                        </span>
                    </div>
                </div>

                <p class="text-xs text-gray-600 mb-3">Tambahkan target pemeliharaan per tahun. Kamu bisa menambah lebih dari satu entry.</p>

                <div id="targetsContainer" class="space-y-3">
                    <!-- template akan diisi lewat JS -->
                </div>

                <div class="mt-4">
                    <button type="button" onclick="addTargetRow()" class="px-3 py-2 bg-emerald-600 text-white rounded shadow hover:bg-emerald-700 focus:outline-none">
                        + Tambah Target per Tahun
                    </button>
                </div>

                <p class="mt-2 text-[11px] text-gray-500">
                    *Opsional. Tidak mempengaruhi Total Kuota Kontrak.
                </p>
            </div>
            {{-- ================== /Target Biaya Jasa Pemeliharaan ================== --}}

            <div class="mt-6">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 focus:outline-none">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    <script>
        // Ambil data awal dari server (PHP -> JS)
        const initialYears = @json(old('tahun', $latestData->tahun ?? []));
        const initialTargets = @json(old('target_biaya_pemeliharaan', $latestData->target_biaya_pemeliharaan ?? []));

        // compute currentYear at runtime so dropdown always based on current date
        const currentYear = new Date().getFullYear();

        // Helper format rupiah simple (tanpa simbol)
        function formatRupiahNumber(v) {
            if (v === null || v === undefined || v === '') return '-';
            const n = Number(v) || 0;
            return 'Rp' + new Intl.NumberFormat('id-ID').format(n);
        }

        function updateTargetSummary() {
            // ringkasan: jumlah total dari semua target (jika ada)
            const inputs = document.querySelectorAll('input[name="target_biaya_pemeliharaan[]"]');
            let sum = 0;
            inputs.forEach(i => {
                const v = parseFloat(i.value) || 0;
                sum += v;
            });
            document.getElementById('target_ringkas').textContent = sum ? formatRupiahNumber(sum) : '-';
        }

        // create select options for year: currentYear, +1, +2
        function makeYearOptions(selectedYear = null) {
            let options = '';
            for (let i = 0; i < 3; i++) {
                const y = currentYear + i;
                const sel = (selectedYear && Number(selectedYear) === y) ? 'selected' : '';
                options += `<option value="${y}" ${sel}>${y}</option>`;
            }
            return options;
        }

        function createTargetRow(year = '', nominal = '') {
            const wrapper = document.createElement('div');
            wrapper.className = 'grid grid-cols-12 gap-2 items-center p-3 bg-white border rounded-md shadow-sm';

            // yearSelectHTML uses makeYearOptions(year) so if year provided (e.g. 2027) it's selected.
            const yearSelectHTML = `<select name="tahun[]" class="mt-1 block w-full px-3 py-2 border rounded-md focus:outline-none">
                                        ${makeYearOptions(year)}
                                    </select>`;

            wrapper.innerHTML = `
                <div class="col-span-12 md:col-span-4">
                    <label class="block text-xs text-gray-600">Tahun</label>
                    ${yearSelectHTML}
                </div>
                <div class="col-span-12 md:col-span-6">
                    <label class="block text-xs text-gray-600">Nominal Target (Rp)</label>
                    <div class="flex items-center mt-1">
                        <span class="text-gray-700 mr-2">Rp.</span>
                        <input type="number" name="target_biaya_pemeliharaan[]" value="${nominal ?? ''}" placeholder="0" class="w-full px-3 py-2 border rounded-md focus:outline-none" oninput="updateTargetSummary()">
                    </div>
                </div>
                <div class="col-span-12 md:col-span-2 flex items-end justify-end">
                    <button type="button" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600" onclick="removeTargetRow(this)">
                        Hapus
                    </button>
                </div>
            `;

            return wrapper;
        }

        function addTargetRow(year = '', nominal = '') {
            const container = document.getElementById('targetsContainer');
            const row = createTargetRow(year, nominal);
            container.appendChild(row);
            updateTargetSummary();
        }

        function removeTargetRow(btn) {
            const row = btn.closest('.grid');
            if (!row) return;
            row.remove();
            updateTargetSummary();
        }

        // initial population on load
        document.addEventListener('DOMContentLoaded', () => {
            // populate nama kontrak sesuai jenis (jika ada)
            handleJenisKontrakChange();

            // safe periode kontrak update
            updatePeriodeKontrak();

            // populate targets: prefer old() values, else latestData arrays
            const maxLen = Math.max(initialYears?.length || 0, initialTargets?.length || 0);
            if (maxLen > 0) {
                for (let i = 0; i < maxLen; i++) {
                    // if initialYears has value use it; else default to currentYear
                    const y = (initialYears[i] !== undefined && initialYears[i] !== null && initialYears[i] !== '') ? initialYears[i] : currentYear;
                    const t = initialTargets[i] ?? '';
                    addTargetRow(y, t);
                }
            } else {
                // jika kosong, buat 1 row default tahun sekarang
                addTargetRow(currentYear, '');
            }

            // initial target summary
            updateTargetSummary();
        });

        // Jenis kontrak -> nama kontrak: restore selected if exists
        function handleJenisKontrakChange() {
            const jenisKontrak = document.getElementById('jenisKontrak').value;
            const namaKontrakSelect = document.getElementById('namaKontrak');
            const previous = "{{ old('nama_kontrak', $latestData->nama_kontrak ?? '') }}";

            namaKontrakSelect.innerHTML = '';

            if (jenisKontrak === 'Bengkel Mesin') {
                namaKontrakSelect.innerHTML = `<option value="Fabrikasi_Konstruksi_Pengerjaan_Mesin">Fabrikasi, Konstruksi dan Pengerjaan Mesin</option>`;
            } else if (jenisKontrak === 'Bengkel Listrik') {
                namaKontrakSelect.innerHTML = `<option value="Maintenance">Maintenance</option><option value="Perbaikan">Perbaikan</option><option value="Listrik">Listrik</option>`;
            } else if (jenisKontrak === 'Field Supporting') {
                namaKontrakSelect.innerHTML = `<option value="Kontrak Jasa OVH Packer">Kontrak Jasa OVH Packer</option><option value="Kontrak Service">Kontrak Service</option><option value="Kontrak Jasa Area Kiln">Kontrak Jasa Area Kiln</option><option value="Kontrak Jasa Mekanikal">Kontrak Jasa Mekanikal</option>`;
            } else {
                namaKontrakSelect.innerHTML = `<option value="">Pilih Nama Kontrak</option>`;
            }

            // restore old value jika ada
            if (previous) {
                try {
                    namaKontrakSelect.value = previous;
                } catch (e) {}
            }
        }

        // update total kuota safely (cek input kosong)
        function updateTotalKuota() {
            const nilaiKontrak = parseFloat(document.getElementById('nilai_kontrak').value) || 0;
            const tambahanKuota = parseFloat(document.getElementById('tambahan_kuota_kontrak').value) || 0;
            document.getElementById('total_kuota_kontrak').value = nilaiKontrak + tambahanKuota;
        }

        // safe periode formatting
        function safeFormatDate(value) {
            if (!value) return null;
            const d = new Date(value);
            return isNaN(d.getTime()) ? null : d.toLocaleDateString('id-ID');
        }

        function updatePeriodeKontrak() {
            const sVal = document.getElementById('periode_kontrak_start').value;
            const eVal = document.getElementById('periode_kontrak_end').value;
            const aVal = document.getElementById('adendum_end').value;

            const s = safeFormatDate(sVal) || '-';
            const e = safeFormatDate(eVal) || '-';
            let formatted = `${s} sampai ${e}`;

            const a = safeFormatDate(aVal);
            if (a) formatted += `, diperpanjang sampai ${a}`;

            document.getElementById('periode_kontrak_result').textContent = formatted;
        }

        function confirmNewOA() {
            Swal.fire({
                title: 'Konfirmasi',
                text: "Anda yakin ingin membuat OA baru?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Buat OA Baru!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('admin.updateoa', ['new' => true]) }}";
                }
            });
        }
    </script>
</x-admin-layout>
