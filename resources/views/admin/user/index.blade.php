<x-admin-layout>
    <div class="py-6">
        <div class="w-full max-w-[98%] mx-auto">
            <div class="admin-card p-5 mb-4">
                <div class="admin-header">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h1 class="admin-title">Daftar Pengguna</h1>
                            <p class="admin-subtitle">Kelola akun pembuat order, approval, dan vendor.</p>
                        </div>
                    </div>
                </div>

                @if (session('success'))
                    <div class="mt-4 admin-badge admin-badge-soft">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mt-4 flex flex-col gap-3">
                    <div class="flex justify-center">
                        <div class="inline-flex gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1">
                            <a href="{{ route('admin.users.index', ['usertype' => 'user']) }}"
                                class="px-4 py-2 text-sm font-semibold rounded-lg {{ $usertype === 'user' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-white' }}">
                                Pembuat Order
                            </a>
                            <a href="{{ route('admin.users.index', ['usertype' => 'approval']) }}"
                                class="px-4 py-2 text-sm font-semibold rounded-lg {{ $usertype === 'approval' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-white' }}">
                                Approval
                            </a>
                            <a href="{{ route('admin.users.index', ['usertype' => 'pkm']) }}"
                                class="px-4 py-2 text-sm font-semibold rounded-lg {{ $usertype === 'pkm' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-white' }}">
                                Vendor
                            </a>
                            <a href="{{ route('admin.users.index', ['usertype' => 'admin']) }}"
                                class="px-4 py-2 text-sm font-semibold rounded-lg {{ $usertype === 'admin' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-white' }}">
                                Admin
                            </a>
                        </div>
                    </div>

                    @if ($usertype === 'approval')
                        <div class="flex justify-center">
                            <div class="inline-flex gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1">
                                <a href="{{ route('admin.users.index', ['usertype' => 'approval', 'jabatan' => 'Operation Directorate']) }}"
                                    class="px-4 py-2 text-sm font-semibold rounded-lg {{ request('jabatan') === 'Operation Directorate' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-white' }}">
                                    Direktur Operasional
                                </a>
                                <a href="{{ route('admin.users.index', ['usertype' => 'approval', 'jabatan' => 'General Manager']) }}"
                                    class="px-4 py-2 text-sm font-semibold rounded-lg {{ request('jabatan') === 'General Manager' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-white' }}">
                                    General Manager
                                </a>
                                <a href="{{ route('admin.users.index', ['usertype' => 'approval', 'jabatan' => 'Senior Manager']) }}"
                                    class="px-4 py-2 text-sm font-semibold rounded-lg {{ request('jabatan') === 'Senior Manager' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-white' }}">
                                    Senior Manager
                                </a>
                                <a href="{{ route('admin.users.index', ['usertype' => 'approval', 'jabatan' => 'Manager']) }}"
                                    class="px-4 py-2 text-sm font-semibold rounded-lg {{ request('jabatan') === 'Manager' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-white' }}">
                                    Manager
                                </a>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('admin.users.index') }}" method="GET" class="admin-filter">
                        <input type="hidden" name="usertype" value="{{ $usertype }}">
                        @if (request('jabatan'))
                            <input type="hidden" name="jabatan" value="{{ request('jabatan') }}">
                        @endif
                        <input type="text" name="search" id="searchUsersLive" value="{{ $search ?? '' }}"
                            placeholder="Cari nama / email / unit / jabatan..."
                            class="admin-input w-72">
                        <button type="submit" class="admin-btn admin-btn-primary">
                            <i data-lucide="search" class="w-4 h-4"></i> Cari
                        </button>
                        <a href="{{ route('admin.users.index', ['usertype' => $usertype, 'jabatan' => request('jabatan')]) }}"
                            class="admin-btn admin-btn-ghost">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                        </a>
                    </form>
                </div>
            </div>

<!-- Tabel Data Pengguna -->
<div class="admin-card overflow-x-auto">
    <table class="table-auto w-full text-sm">
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
        <tbody class="text-gray-700 text-xs font-light" id="userTableBody">
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
                      <!-- Tombol Edit -->
<button onclick='openEditForm(
        "{{ $user->id }}",
        "{{ $user->name }}",
        "{{ $user->email }}",
        "{{ $user->usertype }}",
        "{{ $user->departemen }}",
        "{{ $user->unit_work }}",
        "{{ $user->seksi }}",
        "{{ $user->jabatan }}",
        "{{ $user->whatsapp_number }}",
        "{{ $user->initials }}",
        {!! json_encode($user->related_units ?? []) !!}
    )'
    class="bg-blue-500 text-white px-2 py-1 rounded-md hover:bg-blue-700 text-xs">
    <i class="fas fa-edit"></i>
</button>

                        <!-- Tombol Hapus -->
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');"
                            style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="redirect_usertype" value="{{ $usertype }}">
                            <input type="hidden" name="redirect_jabatan" value="{{ request('jabatan') }}">
                            <input type="hidden" name="redirect_search" value="{{ $search ?? '' }}">
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


@include('admin.user.partials._edit_modal', [
    'usertype' => $usertype,
    'units' => $units,
    'search' => $search ?? '',
])
@include('admin.user.partials._create_modal')
<style>
/* ===== Styling TomSelect biar nyatu dengan modal biru ===== */
.ts-wrapper {
    background-color: #1e3a8a !important; /* bg-blue-900 */
    border: 1px solid #3b82f6 !important; /* border biru */
    border-radius: 0.375rem; /* rounded-md */
    color: #fff !important;
    text-align: left !important;
    font-size: 0.875rem; /* sm */
}

.ts-control {
    background-color: #1e3a8a !important; 
    border: none !important;
    color: #fff !important;
    text-align: left !important;
    padding: 0.5rem 0.75rem; /* px-3 py-2 */
}

.ts-control input {
    color: #fff !important;
}

.ts-dropdown {
    background-color: #1e40af; /* bg-blue-800 */
    color: #fff;
    border-radius: 0.375rem;
    text-align: left !important;
}

.ts-dropdown .option {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
}

.ts-dropdown .option:hover {
    background-color: #2563eb; /* hover:bg-blue-600 */
    color: #fff;
}

/* Tag/Chip untuk multi-select (Related Units) */
.ts-wrapper.multi .ts-control > div {
    background-color: #2563eb !important; /* bg-blue-600 */
    border-radius: 0.375rem;
    padding: 0.2rem 0.5rem;
    color: #fff !important;
    font-size: 0.75rem;
}
</style>

<script>
// Inisialisasi TomSelect
document.addEventListener("DOMContentLoaded", function () {
    // Unit Kerja (single)
    new TomSelect("#editUnitWork", {
        plugins: ['dropdown_input'],
        create: false,
        sortField: { field: "text", direction: "asc" }
    });

    // Related Units (multi) - sementara dimatikan jika element tidak ada
    let relatedUnitsSelect = null;
    const relatedUnitsEl = document.querySelector("#editRelatedUnits");
    if (relatedUnitsEl) {
        relatedUnitsSelect = new TomSelect(relatedUnitsEl, {
            plugins: ['remove_button'],
            persist: false,
            create: false,
            hideSelected: true,
            sortField: { field: "text", direction: "asc" }
        });
    }

    // Simpan pilihan ke hidden input sebelum submit (jika aktif)
    const form = document.getElementById('editUserForm');
    if (form) {
        form.addEventListener("submit", function () {
            if (!relatedUnitsSelect) return;
            const values = relatedUnitsSelect.getValue();
            const hidden = document.getElementById('hiddenRelatedUnits');
            if (hidden) hidden.value = JSON.stringify(values);
        });
    }

    // Override openEditForm agar isi TomSelect juga
    window.openEditForm = function (
        id, name, email, usertype, departemen, unit_work, seksi, jabatan,
        whatsapp_number, initials, related_units = []
    ) {
        if (form) form.action = `/admin/users/${id}`;

        document.getElementById('editName').value = name || '';
        document.getElementById('editEmail').value = email || '';
        document.getElementById('editUsertype').value = usertype || '';
        document.getElementById('editDepartemen').value = departemen || '';
        document.getElementById('editUnitWork').tomselect.setValue(unit_work || '');
        document.getElementById('editSeksi').value = seksi || '';
        document.getElementById('editJabatan').value = jabatan || '';
        document.getElementById('editWhatsAppNumber').value = whatsapp_number || '';
        document.getElementById('editInitials').value = initials || '';

        // reset related units (jika aktif)
        if (relatedUnitsSelect) {
            relatedUnitsSelect.clear();
            if (Array.isArray(related_units)) {
                relatedUnitsSelect.setValue(related_units);
            }
        }

        document.getElementById('editForm').classList.remove('hidden');
    }

    window.closeEditForm = function () {
        document.getElementById('editForm').classList.add('hidden');
    }

    // Live search (client-side) untuk tabel user
    const searchInput = document.getElementById('searchUsersLive');
    const tbody = document.getElementById('userTableBody');
    if (searchInput && tbody) {
        searchInput.addEventListener('keyup', function () {
            const q = (this.value || '').toLowerCase();
            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }
});
</script>


</x-admin-layout>
