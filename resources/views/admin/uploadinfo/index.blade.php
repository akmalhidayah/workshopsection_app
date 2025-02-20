<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Informasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-xl font-semibold mb-6 text-gray-700">Unggah Dokumen Informasi</h3>

                <!-- Form Upload -->
                <form action="{{ route('admin.uploadinfo.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Pilih Kategori Dokumen:</label>
                        <select id="category" name="category" class="mt-1 block w-full p-2 border border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="cara_kerja">Cara Kerja</option>
                            <option value="flowchart_aplikasi">Flowchart Aplikasi</option>
                            <option value="kontrak_pkm">Kontrak PKM</option>
                        </select>
                    </div>
                    <div>
                        <label for="files" class="block text-sm font-medium text-gray-700">Unggah File:</label>
                        <input type="file" id="files" name="files[]" multiple class="mt-1 block w-full p-2 border border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        Upload
                    </button>
                </form>

                <!-- Dokumen yang Diunggah -->
                <h3 class="text-lg font-semibold mt-8 text-gray-700">Dokumen yang Diunggah</h3>

                <!-- Section: Cara Kerja -->
                <div class="mt-6">
                    <h4 class="text-md font-bold text-gray-600">Cara Kerja</h4>
                    <div class="space-y-2">
                        @forelse ($caraKerjaFiles as $file)
                            <div class="flex items-center justify-between p-3 bg-gray-100 rounded shadow-sm">
                                <span class="flex items-center">
                                    <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                                    {{ basename($file) }}
                                </span>
                                <div class="flex items-center space-x-4">
                                    <a href="{{ asset('storage/' . $file) }}" class="text-blue-600 hover:underline" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.uploadinfo.delete', ['filename' => basename($file), 'category' => 'cara_kerja']) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">Tidak ada dokumen untuk kategori ini.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Section: Flowchart Aplikasi -->
                <div class="mt-6">
                    <h4 class="text-md font-bold text-gray-600">Flowchart Aplikasi</h4>
                    <div class="space-y-2">
                        @forelse ($flowchartFiles as $file)
                            <div class="flex items-center justify-between p-3 bg-gray-100 rounded shadow-sm">
                                <span class="flex items-center">
                                    <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                                    {{ basename($file) }}
                                </span>
                                <div class="flex items-center space-x-4">
                                    <a href="{{ asset('storage/' . $file) }}" class="text-blue-600 hover:underline" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.uploadinfo.delete', ['filename' => basename($file), 'category' => 'flowchart_aplikasi']) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">Tidak ada dokumen untuk kategori ini.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Section: Kontrak PKM -->
                <div class="mt-6">
                    <h4 class="text-md font-bold text-gray-600">Kontrak PKM</h4>
                    <div class="space-y-2">
                        @forelse ($kontrakFiles as $file)
                            <div class="flex items-center justify-between p-3 bg-gray-100 rounded shadow-sm">
                                <span class="flex items-center">
                                    <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                                    {{ basename($file) }}
                                </span>
                                <div class="flex items-center space-x-4">
                                    <a href="{{ asset('storage/' . $file) }}" class="text-blue-600 hover:underline" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.uploadinfo.delete', ['filename' => basename($file), 'category' => 'kontrak_pkm']) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">Tidak ada dokumen untuk kategori ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
