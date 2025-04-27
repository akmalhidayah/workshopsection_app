<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Edit Unit Kerja</h2>
    </x-slot>

    <div class="p-6 bg-white shadow rounded-lg max-w-lg mx-auto">
        <form action="{{ route('admin.unit_work.update', $unitWork->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block font-medium text-sm text-gray-700">Nama Unit Kerja</label>
                <input type="text" name="name" class="w-full border border-gray-300 rounded px-3 py-2 mt-1"
                       value="{{ old('name', $unitWork->name) }}">
                @error('name')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-2">
                <a href="{{ route('admin.unit_work.index') }}" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
            </div>
        </form>
    </div>
</x-admin-layout>
