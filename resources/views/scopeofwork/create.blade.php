<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Dokumen Scope of Work') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-xl sm:rounded-lg p-6"> 
                
                <!-- ✅ Update action route -->
                <form method="POST" action="{{ route('dokumen_orders.scope.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Scope of Work Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-sm text-gray-800 dark:text-gray-300">
                                Notifikasi / Order In Planning (03)
                            </label>
                            <input id="notification_number" name="notification_number" type="text" 
                                   class="form-input rounded-md shadow-sm mt-1 block w-full" 
                                   value="{{ $notification->notification_number }}" readonly required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-800 dark:text-gray-300">
                                Nama Pekerjaan
                            </label>
                            <input id="nama_pekerjaan" name="nama_pekerjaan" type="text" 
                                   class="form-input rounded-md shadow-sm mt-1 block w-full" 
                                   value="{{ $notification->job_name }}" readonly required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-800 dark:text-gray-300">
                                Nama Penginput
                            </label>
                            <input id="nama_penginput" name="nama_penginput" type="text" 
                                   class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-800 dark:text-gray-300">
                                Unit Kerja
                            </label>
                            <input id="unit_kerja" name="unit_kerja" type="text" 
                                   class="form-input rounded-md shadow-sm mt-1 block w-full" 
                                   value="{{ $notification->unit_work }}" readonly required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-800 dark:text-gray-300">
                                Tanggal Dokumen
                            </label>
                            <input id="tanggal_dokumen" name="tanggal_dokumen" type="date" 
                                   class="form-input rounded-md shadow-sm mt-1 block w-full" 
                                   value="{{ date('Y-m-d') }}" readonly>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-800 dark:text-gray-300">
                                Tanggal Pemakaian
                            </label>
                            <input id="tanggal_pemakaian" name="tanggal_pemakaian" type="date" 
                                   class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                        </div>
                    </div>

                    <!-- Dynamic Scope of Work Fields -->
                    <div class="mt-6">
                        <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-300">Scope of Work</h3>
                        <div id="scope-of-work-container">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-2">
                                <div>
                                    <label class="block text-sm text-gray-800 dark:text-gray-300">Scope Pekerjaan</label>
                                    <input name="scope_pekerjaan[]" type="text" class="form-input w-full" required>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-800 dark:text-gray-300">Qty</label>
                                    <input name="qty[]" type="text" class="form-input w-full" required>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-800 dark:text-gray-300">Satuan</label>
                                    <input name="satuan[]" type="text" class="form-input w-full" required>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-800 dark:text-gray-300">Keterangan</label>
                                    <input name="keterangan[]" type="text" class="form-input w-full">
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="button" id="add-scope-row" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                Tambah Data
                            </button>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="mt-4">
                        <label class="block text-sm text-gray-800 dark:text-gray-300">Catatan</label>
                        <textarea id="catatan" name="catatan" rows="3" 
                                  class="form-textarea rounded-md shadow-sm mt-1 block w-full" required></textarea>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Dynamic Row Script -->
    <script>
        document.getElementById('add-scope-row').addEventListener('click', function() {
            const container = document.getElementById('scope-of-work-container');
            const rowCount = container.querySelectorAll('.grid').length + 1;

            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-1 md:grid-cols-4 gap-4 mt-2';
            newRow.innerHTML = `
                <div>
                    <label class="block text-sm text-gray-800 dark:text-gray-300">Scope Pekerjaan</label>
                    <input name="scope_pekerjaan[]" type="text" class="form-input w-full" required>
                </div>
                <div>
                    <label class="block text-sm text-gray-800 dark:text-gray-300">Qty</label>
                    <input name="qty[]" type="text" class="form-input w-full" required>
                </div>
                <div>
                    <label class="block text-sm text-gray-800 dark:text-gray-300">Satuan</label>
                    <input name="satuan[]" type="text" class="form-input w-full" required>
                </div>
                <div>
                    <label class="block text-sm text-gray-800 dark:text-gray-300">Keterangan</label>
                    <input name="keterangan[]" type="text" class="form-input w-full">
                </div>
            `;
            container.appendChild(newRow);
        });
    </script>
</x-app-layout>
