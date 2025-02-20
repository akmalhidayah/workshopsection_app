<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Dokumen Scope of Work') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-900 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Update action route to scopeofwork.update -->
                <form method="POST" action="{{ route('scopeofwork.update', ['notificationNumber' => $scopeOfWork->notification_number]) }}">
                    @csrf
                    @method('PATCH')

                    <!-- Scope of Work Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-sm text-gray-300" for="notification_number">
                                Notifikasi / Order In Planning (03)
                            </label>
                            <input id="notification_number" name="notification_number" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $scopeOfWork->notification_number }}" required readonly>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-300" for="nama_pekerjaan">
                                Nama Pekerjaan
                            </label>
                            <input id="nama_pekerjaan" name="nama_pekerjaan" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $scopeOfWork->nama_pekerjaan }}" required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-300" for="nama_penginput">
                                Nama Penginput
                            </label>
                            <input id="nama_penginput" name="nama_penginput" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $scopeOfWork->nama_penginput }}" required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-300" for="unit_kerja">
                                Unit Kerja
                            </label>
                            <input id="unit_kerja" name="unit_kerja" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $scopeOfWork->unit_kerja }}" required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-300" for="tanggal_dokumen">
                                Tanggal Dokumen
                            </label>
                            <input id="tanggal_dokumen" name="tanggal_dokumen" type="date" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $scopeOfWork->tanggal_dokumen }}" required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-300" for="tanggal_pemakaian">
                                Tanggal Pemakaian
                            </label>
                            <input id="tanggal_pemakaian" name="tanggal_pemakaian" type="date" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $scopeOfWork->tanggal_pemakaian }}" required>
                        </div>
                    </div>

                    <!-- Dynamic Scope of Work Fields -->
                    <div class="mt-6">
                        <h3 class="font-semibold text-lg text-gray-200">Scope of Work</h3>
                        <div id="scope-of-work-container">
                            @foreach($scopeOfWork->scope_pekerjaan as $index => $scope)
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-2">
                                <div>
                                    <label class="block font-medium text-sm text-gray-300" for="scope_pekerjaan_{{ $index + 1 }}">
                                        Scope Pekerjaan
                                    </label>
                                    <input id="scope_pekerjaan_{{ $index + 1 }}" name="scope_pekerjaan[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $scope }}" required>
                                </div>

                                <div>
                                    <label class="block font-medium text-sm text-gray-300" for="qty_{{ $index + 1 }}">
                                        Qty
                                    </label>
                                    <input id="qty_{{ $index + 1 }}" name="qty[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $scopeOfWork->qty[$index] }}" required>
                                </div>

                                <div>
                                    <label class="block font-medium text-sm text-gray-300" for="satuan_{{ $index + 1 }}">
                                        Satuan
                                    </label>
                                    <input id="satuan_{{ $index + 1 }}" name="satuan[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $scopeOfWork->satuan[$index] }}" required>
                                </div>

                                <div>
                                    <label class="block font-medium text-sm text-gray-300" for="keterangan_{{ $index + 1 }}">
                                        Keterangan
                                    </label>
                                    <input id="keterangan_{{ $index + 1 }}" name="keterangan[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $scopeOfWork->keterangan[$index] ?? '' }}" required>
                                </div>

                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <button type="button" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300" id="add-scope-row">Tambah Data</button>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="mt-4">
                        <label class="block font-medium text-sm text-gray-300" for="catatan">
                            Catatan
                        </label>
                        <textarea id="catatan" name="catatan" class="form-textarea rounded-md shadow-sm mt-1 block w-full" rows="3" required>{{ $scopeOfWork->catatan }}</textarea>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('add-scope-row').addEventListener('click', function() {
            const container = document.getElementById('scope-of-work-container');
            const rowCount = container.querySelectorAll('.grid-cols-1').length + 1;

            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-1 md:grid-cols-4 gap-4 mt-2';
            newRow.innerHTML = `
                <div>
                    <label class="block font-medium text-sm text-gray-300" for="scope_pekerjaan_${rowCount}">
                        Scope Pekerjaan
                    </label>
                    <input id="scope_pekerjaan_${rowCount}" name="scope_pekerjaan[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-300" for="qty_${rowCount}">
                        Qty
                    </label>
                    <input id="qty_${rowCount}" name="qty[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-300" for="satuan_${rowCount}">
                        Satuan
                    </label>
                    <input id="satuan_${rowCount}" name="satuan[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-300" for="keterangan_${rowCount}">
                        Keterangan
                    </label>
                    <input id="keterangan_${rowCount}" name="keterangan[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                </div>
            `;

            container.appendChild(newRow);
        });
    </script>
</x-app-layout>
