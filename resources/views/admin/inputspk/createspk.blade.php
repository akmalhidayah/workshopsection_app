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

        {{-- Validasi / Error --}}
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-sm text-red-700 rounded">
                <strong>Terdapat beberapa kesalahan:</strong>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form SPK -->
        <form action="{{ route('spk.store') }}" method="POST" id="spkForm">
            @csrf

            <!-- Input Field -->
            <div class="mb-4">
                <label for="kepada_yth" class="block text-sm font-medium text-gray-700">Kepada YTH</label>
                <input type="text" id="kepada_yth" name="kepada_yth" value="{{ old('kepada_yth') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="PT. Prima Karya Manunggal">
            </div>

            <div class="mb-4">
                <label for="perihal" class="block text-sm font-medium text-gray-700">Perihal</label>
                <input type="text" id="perihal" name="perihal" value="{{ old('perihal') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            {{-- Nomor SPK: tampil read-only. Controller akan generate ulang saat store untuk keamanan --}}
            <div class="mb-4">
                <label for="nomor_spk" class="block text-sm font-medium text-gray-700">Nomor SPK</label>
                <input type="text" id="nomor_spk" name="nomor_spk" 
                       value="{{ $nomorSpkOtomatis ?? old('nomor_spk') }}"
                       readonly 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed">
                <p class="text-xs text-gray-500 mt-1">Nomor SPK di-generate otomatis. Sistem tetap akan generate ulang di server saat menyimpan.</p>
            </div>

            <div class="mb-4">
                <label for="tanggal_spk" class="block text-sm font-medium text-gray-700">Tanggal SPK</label>
                <input type="date" id="tanggal_spk" name="tanggal_spk" value="{{ old('tanggal_spk', now()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div class="mb-4">
                <label for="nomor_notifikasi" class="block text-sm font-medium text-gray-700">Nomor Order</label>
                <input type="text" id="nomor_notifikasi" name="notification_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly value="{{ $notification->notification_number ?? old('notification_number', '') }}">
            </div>

            <div class="mb-4">
                <label for="unit_kerja_peminta" class="block text-sm font-medium text-gray-700">Unit Kerja Peminta</label>
                <input type="text" id="unit_kerja_peminta" name="unit_work" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly value="{{ $notification->unit_work ?? old('unit_work', '') }}">
            </div>
            <div class="mb-4">
                <label for="seksi_peminta" class="block text-sm font-medium text-gray-700">Seksi Peminta</label>
                <input type="text" id="seksi_peminta" name="seksi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly value="{{ $notification->seksi ?? old('seksi', '') }}">
            </div>

            <!-- Tombol untuk menambah dan menghapus Functional Location -->
            <div class="flex justify-end mb-4">
                <button type="button" id="addFunctionalLocationBtn" class="bg-green-500 text-white px-3 py-1 rounded-lg hover:bg-green-600 mr-2">Tambah Tabel</button>
                <button type="button" id="removeLastTableBtn" class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600">Hapus Tabel</button>
            </div>

            <!-- Tabel untuk Scope Pekerjaan -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-gray-900 border-gray-300 text-xs rounded-lg">
                    <thead class="bg-gray-600 text-white">
                        <tr>
                            <th class="px-2 py-2">Functional Location</th>
                            <th class="px-2 py-2">Scope Pekerjaan</th>
                            <th class="px-2 py-2">Qty</th>
                            <th class="px-2 py-2">Stn</th>
                            <th class="px-2 py-2">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="functionalLocationTable">
                        {{-- Render nilai lama jika ada (preserve old input arrays) --}}
                        @php
                            $oldFL = old('functional_location', []);
                            $oldScope = old('scope_pekerjaan', []);
                            $oldQty = old('qty', []);
                            $oldStn = old('stn', []);
                            $oldKet = old('keterangan', []);
                            $rows = max(1, max(count($oldFL), count($oldScope), count($oldQty), count($oldStn), count($oldKet)));
                        @endphp

                        @for ($i = 0; $i < $rows; $i++)
                            <tr id="functional_location_{{ $i + 1 }}">
                                <td>
                                    <input type="text" name="functional_location[]" 
                                           value="{{ $oldFL[$i] ?? '' }}" 
                                           class="w-full rounded-md border-gray-300 shadow-sm" required>
                                </td>
                                <td>
                                    <input type="text" name="scope_pekerjaan[]" 
                                           value="{{ $oldScope[$i] ?? '' }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm" required>
                                </td>
                                <td>
                                    <input type="number" name="qty[]" 
                                           value="{{ $oldQty[$i] ?? '' }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm" min="1" required>
                                </td>
                                <td>
                                    <input type="text" name="stn[]" 
                                           value="{{ $oldStn[$i] ?? '' }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm" required>
                                </td>
                                <td>
                                    <input type="text" name="keterangan[]" 
                                           value="{{ $oldKet[$i] ?? '' }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm">
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>

                <div class="mb-4 mt-4">
                    <label for="keterangan_pengerjaan" class="block text-sm font-medium text-gray-700">Keterangan Pengerjaan Urgensi</label>
                    <textarea id="keterangan_pengerjaan" name="keterangan_pekerjaan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('keterangan_pekerjaan') }}</textarea>
                </div>
            </div>

            <!-- Tombol Simpan -->
            <div class="flex justify-end mt-4">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Script untuk menambah dan menghapus tabel & handling submit -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // hitung awal berdasarkan row yang sudah ada
            let tableBody = document.getElementById('functionalLocationTable');
            let rowCount = tableBody.querySelectorAll('tr').length || 1;

            // Tambah Functional Location baru
            document.getElementById('addFunctionalLocationBtn').addEventListener('click', function () {
                rowCount++;
                let newFunctionalLocation = `
                    <tr id="functional_location_${rowCount}">
                        <td><input type="text" name="functional_location[]" class="w-full rounded-md border-gray-300 shadow-sm" required></td>
                        <td><input type="text" name="scope_pekerjaan[]" class="w-full rounded-md border-gray-300 shadow-sm" required></td>
                        <td><input type="number" name="qty[]" class="w-full rounded-md border-gray-300 shadow-sm" min="1" required></td>
                        <td><input type="text" name="stn[]" class="w-full rounded-md border-gray-300 shadow-sm" required></td>
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

            // Mengisi input kosong dengan "-" sebelum form di-submit,
            // tapi skip readonly fields and hidden inputs.
            document.getElementById('spkForm').addEventListener('submit', function(e) {
                let inputs = document.querySelectorAll('#spkForm input, #spkForm textarea');

                inputs.forEach(input => {
                    // skip hidden or readonly fields
                    if (input.type === 'hidden' || input.readOnly) return;
                    if (input.type === 'number') return;

                    // skip inputs that explicitly should be left empty (you can add classes to skip)
                    if (input.classList.contains('skip-fill')) return;

                    // only fill if empty and not checkbox/radio/file
                    if ((input.type !== 'checkbox' && input.type !== 'radio' && input.type !== 'file') && input.value.trim() === '') {
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

    @if(session('error_spk'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal menyimpan SPK',
                text: '{{ session('error_spk') }}',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
</x-admin-layout>
