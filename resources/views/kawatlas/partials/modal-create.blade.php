<div id="dataForm" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] flex flex-col">
            
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Input Permintaan Kawat Las</h3>
            </div>

            <!-- Body (scrollable) -->
            <div class="p-6 overflow-y-auto flex-1">
                <form action="{{ route('kawatlas.store') }}" method="POST" id="createForm">
                    @csrf

                    <!-- Order No -->
                    <div class="mb-4">
                        <label class="block mb-1 text-sm">Nomor Order</label>
                        <input type="text" name="order_number" class="w-full border rounded p-2" required value="{{ old('order_number') }}">
                    </div>

                    <!-- Tanggal -->
                    <input type="hidden" name="tanggal" value="{{ date('Y-m-d') }}">

                    <!-- Unit Kerja -->
                    <div class="mb-4">
                        <label class="block mb-1 text-sm">Unit Kerja</label>
                        <select name="unit_work" id="unitKerjaCreate" class="w-full border rounded p-2" required>
                            <option value="">Pilih Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->name }}" data-seksi='@json($unit->seksi_list)'>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_work') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <!-- Seksi (dynamic, muncul ketika unit memiliki seksi) -->
                    <div id="wrapSeksiCreate" class="mb-4" style="display: none;">
                        <label class="block mb-1 text-sm">Seksi</label>
                        <select name="seksi" id="seksiCreate" class="w-full border rounded p-2">
                            <option value="">Pilih Seksi</option>
                            <!-- opsi akan diisi oleh JS -->
                        </select>
                        @error('seksi') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <!-- Detail -->
                    <div id="detail-container">
                        <div class="detail-row flex flex-col gap-2 mb-4">
                            <div class="flex gap-2">
                                <select name="detail_kawat[0][jenis_kawat]"
                                        class="border rounded p-2 w-1/2 jenis-kawat-select"
                                        onchange="updateInfo(this)" required>
                                    <option value="">Pilih Jenis</option>
                                    @foreach ($jenisList as $jenis)
                                        <option value="{{ $jenis->kode }}"
                                                data-stok="{{ $jenis->stok }}"
                                                data-deskripsi="{{ $jenis->deskripsi }}"
                                                data-harga="{{ $jenis->harga }}"
                                                data-cost="{{ $jenis->cost_element }}"
                                                data-gambar="{{ $jenis->gambar ? asset('storage/'.$jenis->gambar) : '' }}">
                                            {{ $jenis->kode }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="number" name="detail_kawat[0][jumlah]" class="border rounded p-2 w-1/3"
                                       min="1" required placeholder="Jumlah"
                                       oninput="updateInfo(this.closest('.detail-row').querySelector('select'))">
                                <button type="button" onclick="removeRow(this)" class="px-2 bg-red-500 text-white rounded">-</button>
                            </div>
                            <!-- Box info -->
                            <div class="flex items-center gap-2 info-box hidden">
                                <img src="" alt="preview" class="w-12 h-12 object-cover rounded border">
                                <small class="text-xs text-gray-500 info-text"></small>
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="addRow()" class="mb-4 px-3 py-1 bg-green-500 text-white rounded">
                        + Tambah Jenis
                    </button>

                    <!-- Grand Total -->
                    <div class="text-right font-semibold text-gray-800 dark:text-white mt-2">
                        <span id="grand-total">Grand Total: Rp 0</span>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                <button type="submit" form="createForm" class="px-4 py-2 bg-indigo-600 text-white rounded">Submit</button>
                <button type="button" onclick="closeForm()" class="px-4 py-2 bg-gray-400 text-white rounded">Cancel</button>
            </div>
        </div>
    </div>
</div>
