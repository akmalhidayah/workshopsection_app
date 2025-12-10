<!-- Modal Edit: Edit Permintaan Kawat Las -->
<div id="editForm" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] flex flex-col">

            <!-- Header -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Permintaan Kawat Las</h3>
            </div>

            <!-- Body scrollable -->
            <div class="p-6 overflow-y-auto flex-1">
                <form id="editFormElement" method="POST" class="space-y-4">
                    @csrf @method('PATCH')

                    <!-- Order No -->
                    <div>
                        <label class="block mb-1 text-sm">Nomor Order</label>
                        <input type="text" name="order_number" id="edit_order_number"
                               class="w-full border rounded p-2" required>
                    </div>

                    <!-- Tanggal -->
                    <div>
                        <label class="block mb-1 text-sm">Tanggal</label>
                        <input type="date" name="tanggal" id="edit_tanggal"
                               class="w-full border rounded p-2" required>
                    </div>

                    <!-- Unit Kerja -->
                    <div>
                        <label class="block mb-1 text-sm">Unit Kerja</label>
                        <select name="unit_work" id="edit_unit_work"
                                class="w-full border rounded p-2" required>
                            <option value="">Pilih Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->name }}" data-seksi='@json($unit->seksi_list)'>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Seksi (dependent) -->
                    <div id="wrapSeksiEdit" style="display:none;">
                        <label class="block mb-1 text-sm">Seksi</label>
                        <select name="seksi" id="edit_seksi" class="w-full border rounded p-2">
                            <option value="">Pilih Seksi</option>
                            {{-- options akan di-populate oleh JS dari data-seksi --}}
                        </select>
                    </div>

                    <!-- Detail Kawat (dynamic rows) -->
                    <div class="mt-3 border-t pt-3">
                        <label class="block mb-2 font-semibold">Detail Kawat</label>
                        <div id="edit-detail-container" class="space-y-3">
                            <!-- baris detail akan di-render oleh JS -->
                        </div>

                        <button type="button" onclick="addRowEdit()"
                                class="mt-4 inline-flex items-center px-3 py-1 bg-green-500 text-white rounded">
                            + Tambah Jenis
                        </button>

                        <div id="grand-total-edit" class="mt-3 text-right font-semibold text-indigo-600">
                            Grand Total: Rp 0
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                <button type="submit" form="editFormElement"
                        class="px-4 py-2 bg-indigo-600 text-white rounded">Update</button>
                <button type="button" onclick="closeEditForm()"
                        class="px-4 py-2 bg-gray-400 text-white rounded">Cancel</button>
            </div>
        </div>
    </div>
</div>
