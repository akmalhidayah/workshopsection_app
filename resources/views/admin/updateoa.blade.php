<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Kuota Anggaran & Outline Agreement') }}
        </h2>
    </x-slot>

    <div class="container mx-auto p-6 bg-white shadow-md rounded-lg">
        <h2 class="text-2xl font-semibold mb-6">Input Kuota Anggaran & OA</h2>
        
        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Tombol Buat OA Baru -->
        <div class="mb-6">
            <button onclick="confirmNewOA()" class="px-4 py-2 bg-green-500 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-75">
                Buat OA Baru
            </button>
        </div>

        <form method="POST" action="{{ route('admin.storeOA') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Unit Kerja -->
                <div>
                    <label for="unit_work" class="block text-sm font-medium text-gray-700">Unit Kerja</label>
                    <input type="text" id="unitKerja" name="unit_work" 
                        value="Unit Of Workshop"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-600 cursor-not-allowed" readonly>
                </div>

                <!-- Outline Agreement (OA) -->
                <div>
                    <label for="outline_agreement" class="block text-sm font-medium text-gray-700">Outline Agreement (OA)</label>
                    <input type="text" id="outline_agreement" name="outline_agreement" 
                        value="{{ request('new') ? '' : old('outline_agreement', $latestData->outline_agreement ?? '') }}" 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                        placeholder="Masukkan nomor OA">
                </div>

                <!-- Jenis Kontrak -->
                <div>
                    <label for="jenisKontrak" class="block text-sm font-medium text-gray-700">Jenis Kontrak</label>
                    <select id="jenisKontrak" name="jenis_kontrak" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" onchange="handleJenisKontrakChange()" required>
                        <option value="">Pilih Jenis Kontrak</option>
                        <option value="Bengkel Mesin" {{ old('jenis_kontrak', $latestData->jenis_kontrak ?? '') == 'Bengkel Mesin' ? 'selected' : '' }}>Bengkel Mesin</option>
                        <option value="Bengkel Listrik" {{ old('jenis_kontrak', $latestData->jenis_kontrak ?? '') == 'Bengkel Listrik' ? 'selected' : '' }}>Bengkel Listrik</option>
                        <option value="Field Supporting" {{ old('jenis_kontrak', $latestData->jenis_kontrak ?? '') == 'Field Supporting' ? 'selected' : '' }}>Field Supporting</option>
                    </select>
                </div>

                <!-- Nama Kontrak -->
                <div id="namaKontrakContainer">
                    <label for="namaKontrak" class="block text-sm font-medium text-gray-700">Nama Kontrak</label>
                    <select id="namaKontrak" name="nama_kontrak" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        <option value="">Pilih Nama Kontrak</option>
                    </select>
                </div>

                <!-- Nilai Kontrak -->
                <div>
                    <label for="nilai_kontrak" class="block text-sm font-medium text-gray-700">Nilai Kontrak</label>
                    <div class="flex items-center">
                        <span class="text-gray-700 mr-2">Rp.</span>
                        <input type="number" id="nilai_kontrak" name="nilai_kontrak" 
                            value="{{ request('new') ? '' : old('nilai_kontrak', $latestData->nilai_kontrak ?? '') }}" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                            oninput="updateTotalKuota()" required>
                    </div>
                </div>

                <!-- Total Kuota Kontrak -->
                <div>
                    <label for="total_kuota_kontrak" class="block text-sm font-medium text-gray-700">Total Kuota Kontrak</label>
                    <div class="flex items-center">
                        <span class="text-gray-700 mr-2">Rp.</span>
                        <input type="number" id="total_kuota_kontrak" name="total_kuota_kontrak" 
                            value="{{ request('new') ? '' : old('total_kuota_kontrak', $latestData->total_kuota_kontrak ?? '') }}" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-600" readonly>
                    </div>
                </div>

                <!-- Periode Kontrak -->
                <div class="col-span-2">
                    <label for="periode_kontrak" class="block text-sm font-medium text-gray-700">Periode Kontrak</label>
                    <div class="flex space-x-4">
                        <input type="date" id="periode_kontrak_start" name="periode_kontrak_start" 
                            value="{{ request('new') ? '' : old('periode_kontrak_start', $latestData->periode_kontrak_start ?? '') }}" 
                            class="mt-1 block w-1/2 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                            onchange="updatePeriodeKontrak()" required>
                        <span class="text-sm font-medium text-gray-700">sampai</span>
                        <input type="date" id="periode_kontrak_end" name="periode_kontrak_end" 
                            value="{{ request('new') ? '' : old('periode_kontrak_end', $latestData->periode_kontrak_end ?? '') }}" 
                            class="mt-1 block w-1/2 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                            onchange="updatePeriodeKontrak()" required>
                    </div>
                </div>
                <!-- Display Periode Kontrak Result -->
                <div class="col-span-2">
                    <p class="text-sm font-medium text-gray-700">Periode Kontrak Sekarang:</p>
                    <p id="periode_kontrak_result" class="text-sm text-gray-700">
                        {{ request('new') ? '-' : old('periode_kontrak_result', ($latestData->periode_kontrak_start ?? '') . ' sampai ' . ($latestData->periode_kontrak_end ?? '')) }}
                    </p>
                </div>

                <!-- Tambahan Kuota Kontrak (Optional) -->
                <div class="col-span-2 bg-yellow-100 p-4 rounded-md">
                    <label for="tambahan_kuota_kontrak" class="block text-sm font-medium text-gray-700">Tambahan Kuota Kontrak (Opsional)</label>
                    <div class="flex items-center">
                        <span class="text-gray-700 mr-2">Rp.</span>
                        <input type="number" id="tambahan_kuota_kontrak" name="tambahan_kuota_kontrak" 
                            value="{{ request('new') ? '' : old('tambahan_kuota_kontrak', $latestData->tambahan_kuota_kontrak ?? '') }}" 
                            class="mt-1 block w-full px-3 py-2 border border-yellow-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm" 
                            oninput="updateTotalKuota()">
                    </div>
                </div>

                <!-- Adendum Periode Kontrak (Optional) -->
                <div class="col-span-2 bg-blue-50 p-4 rounded-md">
                    <label for="adendum_end" class="block text-sm font-medium text-gray-700">Adendum Periode Kontrak (Opsional)</label>
                    <input type="date" id="adendum_end" name="adendum_end" 
                        value="{{ request('new') ? '' : old('adendum_end', $latestData->adendum_end ?? '') }}" 
                        class="mt-1 block w-full px-3 py-2 border border-blue-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                        onchange="updatePeriodeKontrak()">
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75">
                    Simpan
                </button>
            </div>
        </form>
    </div>
    
    <script>
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
                    // Redirect to the route for creating a new OA
                    window.location.href = "{{ route('admin.updateoa', ['new' => true]) }}";
                }
            });
        }
    function handleJenisKontrakChange() {
        const jenisKontrak = document.getElementById('jenisKontrak').value;
        const namaKontrakSelect = document.getElementById('namaKontrak');

        namaKontrakSelect.innerHTML = '';

        if (jenisKontrak === 'Bengkel Mesin') {
            namaKontrakSelect.innerHTML = `<option value="Fabrikasi_Konstruksi_Pengerjaan_Mesin">Fabrikasi, Konstruksi dan Pengerjaan Mesin</option>`;
        } else if (jenisKontrak === 'Bengkel Listrik') {
            namaKontrakSelect.innerHTML = `<option value="Maintenance">Maintenance</option><option value="Perbaikan">Perbaikan</option><option value="Listrik">Listrik</option>`;
        } else if (jenisKontrak === 'Field Supporting') {
            namaKontrakSelect.innerHTML = `<option value="Kontrak Jasa OVH Packer">Kontrak Jasa OVH Packer</option><option value="Kontrak Service">Kontrak Service</option><option value="Kontrak Jasa Area Kiln">Kontrak Jasa Area Kiln</option><option value="Kontrak Jasa Mekanikal">Kontrak Jasa Mekanikal</option>`;
        }
    }

    function updateTotalKuota() {
        const nilaiKontrak = parseFloat(document.getElementById('nilai_kontrak').value) || 0;
        const tambahanKuota = parseFloat(document.getElementById('tambahan_kuota_kontrak').value) || 0;
        document.getElementById('total_kuota_kontrak').value = nilaiKontrak + tambahanKuota;
    }

    function updatePeriodeKontrak() {
        const startDate = new Date(document.getElementById('periode_kontrak_start').value);
        const endDate = new Date(document.getElementById('periode_kontrak_end').value);
        const adendumEndDate = new Date(document.getElementById('adendum_end').value);
        let formattedPeriod = `${startDate.toLocaleDateString('id-ID')} sampai ${endDate.toLocaleDateString('id-ID')}`;

        if (!isNaN(adendumEndDate.getTime())) {
            formattedPeriod += `, diperpanjang sampai ${adendumEndDate.toLocaleDateString('id-ID')}`;
        }

        document.getElementById('periode_kontrak_result').textContent = formattedPeriod;
    }

    // Duplikasi untuk memastikan kode di bawah ini tidak terhapus
    document.addEventListener('DOMContentLoaded', handleJenisKontrakChange);

    function updatePeriodeKontrak() {
        const startDate = new Date(document.getElementById('periode_kontrak_start').value);
        const endDate = new Date(document.getElementById('periode_kontrak_end').value);
        const adendumEndDate = new Date(document.getElementById('adendum_end').value);
        let formattedPeriod = `${startDate.toLocaleDateString('id-ID')} sampai ${endDate.toLocaleDateString('id-ID')}`;

        if (!isNaN(adendumEndDate.getTime())) {
            formattedPeriod += `, diperpanjang sampai ${adendumEndDate.toLocaleDateString('id-ID')}`;
        }

        document.getElementById('periode_kontrak_result').textContent = formattedPeriod;
    }

    document.addEventListener('DOMContentLoaded', () => {
        handleJenisKontrakChange();
        updatePeriodeKontrak(); // Tambahkan ini untuk mengisi data saat pertama kali halaman dimuat
    });
</script>

</x-admin-layout>
