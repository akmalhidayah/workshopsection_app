<x-admin-layout>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4 text-gray-800">Daftar Pengguna</h1>

        @if (session('success'))
            <div class="bg-green-500 text-white p-3 rounded-lg shadow-md mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Swap Button -->
        <div class="flex justify-center mb-4">
            <div class="inline-flex bg-gray-200 rounded-md">
                <a href="{{ route('admin.users.index', ['usertype' => 'user']) }}"
                    class="px-4 py-2 text-sm font-semibold rounded-l-md {{ $usertype === 'user' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-300' }}">
                    User
                </a>
                <a href="{{ route('admin.users.index', ['usertype' => 'approval']) }}"
                    class="px-4 py-2 text-sm font-semibold {{ $usertype === 'approval' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-300' }}">
                    Approval
                </a>
                <a href="{{ route('admin.users.index', ['usertype' => 'pkm']) }}"
                    class="px-4 py-2 text-sm font-semibold rounded-r-md {{ $usertype === 'pkm' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-300' }}">
                    PKM
                </a>
            </div>
        </div>

        <!-- Swap Button untuk Jabatan (Khusus Approval) -->
        @if ($usertype === 'approval')
            <div class="flex justify-center mb-4">
                <div class="inline-flex bg-gray-200 rounded-md">
                    <a href="{{ route('admin.users.index', ['usertype' => 'approval', 'jabatan' => 'Operation Directorate']) }}"
                        class="px-4 py-2 text-sm font-semibold rounded-l-md {{ request('jabatan') === 'Operation Directorate' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-300' }}">
                        Direktur Operasional
                    </a>
                    <a href="{{ route('admin.users.index', ['usertype' => 'approval', 'jabatan' => 'General Manager']) }}"
                        class="px-4 py-2 text-sm font-semibold {{ request('jabatan') === 'General Manager' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-300' }}">
                        General Manager
                    </a>
                    <a href="{{ route('admin.users.index', ['usertype' => 'approval', 'jabatan' => 'Senior Manager']) }}"
                        class="px-4 py-2 text-sm font-semibold {{ request('jabatan') === 'Senior Manager' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-300' }}">
                        Senior Manager
                    </a>
                    <a href="{{ route('admin.users.index', ['usertype' => 'approval', 'jabatan' => 'Manager']) }}"
                        class="px-4 py-2 text-sm font-semibold rounded-r-md {{ request('jabatan') === 'Manager' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-300' }}">
                        Manager
                    </a>
                </div>
            </div>
        @endif

<!-- Tabel Data Pengguna -->
<div class="overflow-x-auto">
    <table class="table-auto w-full bg-white rounded-lg shadow-md text-sm">
        <thead>
            <tr class="bg-blue-600 text-white uppercase text-xs leading-normal">
                <th class="py-2 px-4 text-left rounded-tl-lg">Nama</th>
                <th class="py-2 px-4 text-left">Inisial</th>
                <th class="py-2 px-4 text-left">Email</th>
                <th class="py-2 px-4 text-left">Departemen</th>
                <th class="py-2 px-4 text-left">Unit Kerja</th>
                @if ($usertype === 'approval' && (request('jabatan') === 'General Manager' || request('jabatan') === 'Operation Directorate'))
                    <th class="py-2 px-4 text-left">Unit Kerja</th>
                @endif
                <th class="py-2 px-4 text-left">Jabatan</th>
                <th class="py-2 px-4 text-center rounded-tr-lg">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-gray-700 text-xs font-light">
            @foreach ($users as $user)
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-2 px-4 text-left">{{ $user->name }}</td>
                    <td class="py-2 px-4 text-left">{{ $user->initials }}</td>
                    <td class="py-2 px-4 text-left">{{ $user->email }}</td>
                    <td class="py-2 px-4 text-left">{{ $user->departemen }}</td>
                    <td class="py-2 px-4 text-left">{{ $user->unit_work }}</td>
                    @if ($usertype === 'approval' && (request('jabatan') === 'General Manager' || request('jabatan') === 'Operation Directorate'))
                        <td class="py-2 px-4 text-left">
                            <!-- Decode dan tampilkan JSON related_units -->
                            @php
                                $units = is_array($user->related_units) ? $user->related_units : json_decode($user->related_units, true);
                            @endphp
                            @if (!empty($units))
                                <ul class="list-disc pl-5">
                                    @foreach ($units as $unit)
                                        <li>{{ $unit }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-500 italic">Tidak ada</span>
                            @endif
                        </td>
                    @endif
                    <td class="py-2 px-4 text-left">{{ $user->jabatan }}</td>
                    <td class="py-2 px-4 text-center">
                        <!-- Tombol Edit -->
                        <button onclick="openEditForm('{{ $user->id }}', '{{ $user->name }}', '{{ $user->email }}', '{{ $user->usertype }}', '{{ $user->departemen }}', '{{ $user->unit_work }}', '{{ $user->seksi }}', '{{ $user->jabatan }}', '{{ $user->whatsapp_number }}', '{{ $user->initials }}')"
                            class="bg-blue-500 text-white px-2 py-1 rounded-md hover:bg-blue-700 text-xs">
                            <i class="fas fa-edit"></i>
                        </button>
                        <!-- Tombol Hapus -->
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');"
                            style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-700 text-xs">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>


<!-- Modal Form untuk Edit User -->
<div id="editForm" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
        <div class="fixed inset-0 bg-blue-900 opacity-75" aria-hidden="true"></div> <!-- Background biru dengan opacity -->
        <div class="inline-block bg-blue-800 text-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:align-middle sm:max-w-sm w-full"> <!-- Modal warna biru -->
            <div class="bg-blue-700 px-4 py-3 rounded-t-lg">
                <h3 class="text-base font-bold text-center text-white" id="modal-title">Edit Pengguna</h3>
            </div>
            <div class="px-4 py-3">
                <form id="editUserForm" action="" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')

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
    <label for="editWhatsAppNumber" class="block text-sm font-medium text-gray-300 text-left">WhatsApp Number</label>
    <input type="text" id="editWhatsAppNumber" name="whatsapp_number" placeholder="Nomor WhatsApp"
        class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-blue-900 text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
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
    <input type="text" id="editUnitWork" name="unit_work" placeholder="Unit Kerja"
        class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-blue-900 text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
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

                    <!-- Tombol Aksi -->
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

    <script>
function openEditForm(id, name, email, usertype, departemen, unit_work, seksi, jabatan, whatsapp_number, initials) {
    const form = document.getElementById('editUserForm');
    if (form) {
        form.action = `/admin/users/${id}`;
    }

    document.getElementById('editName').value = name || '';
    document.getElementById('editEmail').value = email || '';
    document.getElementById('editUsertype').value = usertype || '';
    document.getElementById('editDepartemen').value = departemen || '';
    document.getElementById('editUnitWork').value = unit_work || '';
    document.getElementById('editSeksi').value = seksi || '';
    document.getElementById('editJabatan').value = jabatan || '';
    document.getElementById('editWhatsAppNumber').value = whatsapp_number || '';
    document.getElementById('editInitials').value = initials || '';

    document.getElementById('editForm').classList.remove('hidden');
}
function closeEditForm() {
    document.getElementById('editForm').classList.add('hidden');
}

    </script>
</x-admin-layout>
