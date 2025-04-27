<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Daftar Unit Kerja</h2>
    </x-slot>

    <div class="p-6 bg-white shadow rounded-lg">
        @if(session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '{{ session('success') }}',
                    timer: 2000,
                    showConfirmButton: false
                });
            </script>
        @endif

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Data Unit Kerja</h3>
            <a href="{{ route('admin.unit_work.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-plus mr-1"></i> Tambah
            </a>
        </div>

        <table class="min-w-full bg-white border border-gray-200 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-4 py-2">Nama Unit Kerja</th>
                    <th class="text-center px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($units as $unit)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $unit->name }}</td>
                    <td class="px-4 py-2 text-center space-x-2">
                        <a href="{{ route('admin.unit_work.edit', $unit->id) }}" class="text-blue-600 hover:text-blue-800" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.unit_work.destroy', $unit->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Yakin ingin menghapus unit kerja ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center text-gray-500 py-4">Belum ada data unit kerja.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $units->links() }}
        </div>
    </div>
</x-admin-layout>
