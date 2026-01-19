<!-- Modal Form untuk Edit User -->
<div id="editForm" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
        <div class="fixed inset-0 bg-blue-900 opacity-75" aria-hidden="true"></div>
        <div class="inline-block bg-blue-800 text-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:align-middle sm:max-w-sm w-full">
            <div class="bg-blue-700 px-4 py-3 rounded-t-lg">
                <h3 class="text-base font-bold text-center text-white" id="modal-title">Edit Pengguna</h3>
            </div>
            <div class="px-4 py-3">
                <form id="editUserForm" action="" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect_usertype" value="{{ $usertype }}">
                    <input type="hidden" name="redirect_jabatan" value="{{ request('jabatan') }}">
                    <input type="hidden" name="redirect_search" value="{{ $search ?? '' }}">

                    <div>
                        <label for="editName" class="block text-sm font-medium text-gray-300 text-left">Nama</label>
                        <input type="text" id="editName" name="name" placeholder="Nama"
                            class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-blue-900 text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            required>
                    </div>
                    <div>
                        <label for="editInitials" class="block text-sm font-medium text-gray-300 text-left">Inisial</label>
                        <input type="text" id="editInitials" name="initials" placeholder="Inisial"
                            class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-blue-900 text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="editEmail" class="block text-sm font-medium text-gray-300 text-left">Email</label>
                        <input type="email" id="editEmail" name="email" placeholder="Email"
                            class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-blue-900 text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            required>
                    </div>
                    <div>
                        <label for="editWhatsAppNumber" class="block text-sm font-medium text-gray-300 text-left">
                            WhatsApp Number
                        </label>

                        <input
                            type="text"
                            id="editWhatsAppNumber"
                            name="whatsapp_number"
                            placeholder="Contoh: 0812-3456-789 (otomatis jadi +62)"
                            class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-blue-900 text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                    </div>

                    <div>
                        <label for="editUsertype" class="block text-sm font-medium text-gray-300 text-left">Usertype</label>
                        <select id="editUsertype" name="usertype"
                            class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-blue-900 text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            required>
                            <option value="admin">Admin</option>
                            <option value="pkm">PKM</option>
                            <option value="approval">Approval</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div>
                        <label for="editDepartemen" class="block text-sm font-medium text-gray-300 text-left">Departemen</label>
                        <input type="text" id="editDepartemen" name="departemen" placeholder="Departemen"
                            class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-blue-900 text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="editUnitWork" class="block text-sm font-medium text-gray-300 text-left">Unit Kerja</label>
                        <select id="editUnitWork" name="unit_work"
                            class="tom-select w-full"
                            placeholder="Pilih Unit Kerja...">
                            @foreach ($units as $unit)
                                <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="editRelatedUnits" class="block text-sm font-medium text-gray-300 text-left">
                            Related Units
                        </label>
                        <select id="editRelatedUnits" multiple class="tom-select w-full">
                            @foreach ($units as $unit)
                                <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" id="hiddenRelatedUnits" name="related_units">
                    </div>

                    <div>
                        <label for="editSeksi" class="block text-sm font-medium text-gray-300 text-left">Seksi</label>
                        <input type="text" id="editSeksi" name="seksi" placeholder="Seksi"
                            class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-blue-900 text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="editJabatan" class="block text-sm font-medium text-gray-300 text-left">Jabatan</label>
                        <select id="editJabatan" name="jabatan"
                            class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-blue-900 text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="Operation Directorate">Operation Directorate</option>
                            <option value="General Manager">General Manager</option>
                            <option value="Senior Manager">Senior Manager</option>
                            <option value="Manager">Manager</option>
                            <option value="Karyawan">Karyawan</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeEditForm()"
                            class="bg-gray-500 hover:bg-gray-400 text-white font-medium py-1 px-3 rounded-md text-xs">
                            Cancel
                        </button>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-400 text-white font-medium py-1 px-3 rounded-md text-xs">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
