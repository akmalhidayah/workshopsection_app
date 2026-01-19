<x-admin-layout>
    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="admin-card p-5">
                <div class="admin-header mb-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-slate-50 text-slate-600">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h1 class="admin-title">Stock Kawat Las</h1>
                            <p class="admin-subtitle">Daftar jenis kawat las dan cost element.</p>
                        </div>
                    </div>
                    <div class="admin-actions">
                        <a href="{{ route('admin.cost-element.edit') }}" class="admin-btn admin-btn-ghost">
                            <i data-lucide="settings" class="w-4 h-4"></i> Atur Cost Element
                        </a>
                        <button onclick="openCreateModal()" class="admin-btn admin-btn-primary">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Jenis
                        </button>
                    </div>
                </div>

<!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                            <tr>
                                <th class="px-3 py-2 text-left">Kode</th>
                                <th class="px-3 py-2 text-left">Deskripsi</th>
                                <th class="px-3 py-2 text-left">Stok (kg/ea)</th>
                                <th class="px-3 py-2 text-left">Harga</th>
                                <th class="px-3 py-2 text-left">Cost Element</th>
                                <th class="px-3 py-2 text-left">Gambar</th>
                                <th class="px-3 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($data as $item)
                                <tr>
                                    <td class="px-3 py-2">{{ $item->kode }}</td>
                                    <td class="px-3 py-2">{{ $item->deskripsi ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $item->stok ?? 0 }}</td>
                                    <td class="px-3 py-2">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2">{{ $item->cost_element ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        @if($item->gambar)
                                            <img src="{{ asset('storage/'.$item->gambar) }}" 
                                                 alt="gambar" class="h-10 w-10 object-cover rounded">
                                        @else
                                            <span class="text-gray-500 text-xs">Tidak ada</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right space-x-1">
                                        <button 
                                            onclick="openEditModal({{ $item->id }}, '{{ $item->kode }}', '{{ $item->deskripsi }}', '{{ $item->stok }}', '{{ $item->harga }}', '{{ $item->cost_element }}')" 
                                            class="px-2 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.jenis-kawat-las.destroy', $item->id) }}" 
                                              method="POST" class="inline"
                                              onsubmit="return confirm('Yakin hapus data ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" 
                                                    class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-4 text-center text-gray-500">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div id="createModal" 
         class="fixed inset-0 hidden z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 w-[350px] max-w-[90%]">
            <h2 class="text-base font-semibold mb-3">Tambah Jenis Kawat</h2>
            <form action="{{ route('admin.jenis-kawat-las.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-2">
                    <label class="text-sm">Kode</label>
                    <input type="text" name="kode" class="w-full border rounded p-2 text-sm" required>
                </div>
                <div class="mb-2">
                    <label class="text-sm">Deskripsi</label>
                    <textarea name="deskripsi" class="w-full border rounded p-2 text-sm"></textarea>
                </div>
                <div class="mb-2">
                    <label class="text-sm">Stok</label>
                    <input type="number" name="stok" value="0" min="0" class="w-full border rounded p-2 text-sm">
                </div>
                <div class="mb-2">
                    <label class="text-sm">Harga</label>
                    <input type="number" step="0.01" name="harga" value="0" min="0" class="w-full border rounded p-2 text-sm">
                </div>
                <div class="mb-2">
                    <label class="text-sm">Gambar</label>
                    <input type="file" name="gambar" class="w-full border rounded p-2 text-sm">
                </div>
                <div class="flex justify-end gap-2 mt-3">
                    <button type="button" onclick="closeCreateModal()" 
                            class="px-3 py-1 bg-gray-400 text-white text-sm rounded">Batal</button>
                    <button type="submit" 
                            class="px-3 py-1 bg-indigo-600 text-white text-sm rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="editModal" 
         class="fixed inset-0 hidden z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 w-[350px] max-w-[90%]">
            <h2 class="text-base font-semibold mb-3">Edit Jenis Kawat</h2>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="mb-2">
                    <label class="text-sm">Kode</label>
                    <input type="text" name="kode" id="editKode" class="w-full border rounded p-2 text-sm" required>
                </div>
                <div class="mb-2">
                    <label class="text-sm">Deskripsi</label>
                    <textarea name="deskripsi" id="editDeskripsi" class="w-full border rounded p-2 text-sm"></textarea>
                </div>
                <div class="mb-2">
                    <label class="text-sm">Stok</label>
                    <input type="number" name="stok" id="editStok" min="0" class="w-full border rounded p-2 text-sm">
                </div>
                <div class="mb-2">
                    <label class="text-sm">Harga</label>
                    <input type="number" step="0.01" name="harga" id="editHarga" min="0" class="w-full border rounded p-2 text-sm">
                </div>
                <div class="mb-2">
                    <label class="text-sm">Gambar (opsional)</label>
                    <input type="file" name="gambar" class="w-full border rounded p-2 text-sm">
                </div>
                <div class="flex justify-end gap-2 mt-3">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-3 py-1 bg-gray-400 text-white text-sm rounded">Batal</button>
                    <button type="submit" 
                            class="px-3 py-1 bg-indigo-600 text-white text-sm rounded">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }
        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function openEditModal(id, kode, deskripsi, stok, harga, costElement) {
            let form = document.getElementById('editForm');
            form.action = `/admin/jenis-kawat-las/${id}`;
            document.getElementById('editKode').value = kode;
            document.getElementById('editDeskripsi').value = deskripsi;
            document.getElementById('editStok').value = stok;
            document.getElementById('editHarga').value = harga;
            document.getElementById('editModal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</x-admin-layout>
