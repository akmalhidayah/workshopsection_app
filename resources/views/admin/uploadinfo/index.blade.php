<x-admin-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="admin-card p-5">
                <div class="admin-header mb-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <i data-lucide="upload" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h1 class="admin-title">Upload Informasi</h1>
                            <p class="admin-subtitle">Kelola dokumen informasi berdasarkan kategori dan role.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================= --}}
            {{-- FORM UPLOAD --}}
            {{-- ========================= --}}
            <div class="admin-card p-5">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">
                    Unggah Dokumen Informasi
                </h3>

                <form action="{{ route('admin.uploadinfo.upload') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf

                    {{-- KATEGORI --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Kategori Dokumen
                        </label>
                        <select id="category"
                                name="category"
                                class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="cara_kerja">Cara Kerja</option>
                            <option value="flowchart_aplikasi">Flowchart Aplikasi</option>
                            <option value="kontrak_pkm">Kontrak PKM</option>
                        </select>
                    </div>

                    {{-- ROLE (HANYA UNTUK CARA KERJA) --}}
                    <div id="role-wrapper">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Role Pengguna
                        </label>
                        <select name="role"
                                class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="pns">PNS</option>
                            <option value="pkm">PKM</option>
                            <option value="approval">Approval</option>
                        </select>
                    </div>

                    {{-- FILE --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            File Dokumen
                        </label>
                        <input type="file"
                               name="files[]"
                               multiple
                               class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">
                            Maksimal 10 MB per file
                        </p>
                    </div>

                    {{-- SUBMIT --}}
                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit"
                                class="admin-btn admin-btn-primary">
                            Upload Dokumen
                        </button>
                    </div>
                </form>
            </div>

            {{-- ========================= --}}
            {{-- LIST DOKUMEN --}}
            {{-- ========================= --}}
            <div class="admin-card p-5 space-y-10">

                {{-- ===== CARA KERJA PER ROLE ===== --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">
                        Cara Kerja Aplikasi
                    </h3>

                    @foreach (['pns' => 'PNS', 'pkm' => 'PKM', 'approval' => 'Approval'] as $key => $label)
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-600 mb-2">
                                {{ $label }}
                            </h4>

                            @forelse ($caraKerja[$key] as $file)
                                <div class="flex justify-between items-center bg-gray-50 border rounded px-4 py-2 mb-2">
                                    <span class="text-sm text-gray-700 flex items-center gap-2">
                                        <i class="fas fa-file-alt text-blue-500"></i>
                                        {{ basename($file) }}
                                    </span>

                                    <div class="flex items-center gap-3">
                                        <a href="{{ asset('storage/' . $file) }}"
                                           target="_blank"
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <form action="{{ route('admin.uploadinfo.delete', basename($file)) }}"
                                              method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="category" value="cara_kerja">
                                            <input type="hidden" name="role" value="{{ $key }}">
                                            <button class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">
                                    Tidak ada dokumen.
                                </p>
                            @endforelse
                        </div>
                    @endforeach
                </div>

                {{-- ===== FLOWCHART ===== --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">
                        Flowchart Aplikasi
                    </h3>

                    @forelse ($flowchartFiles as $file)
                        <div class="flex justify-between items-center bg-gray-50 border rounded px-4 py-2 mb-2">
                            <span class="text-sm text-gray-700">
                                {{ basename($file) }}
                            </span>

                            <div class="flex gap-3">
                                <a href="{{ asset('storage/' . $file) }}" target="_blank" class="text-blue-600">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('admin.uploadinfo.delete', basename($file)) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="category" value="flowchart_aplikasi">
                                    <button class="text-red-600">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Tidak ada dokumen.</p>
                    @endforelse
                </div>

                {{-- ===== KONTRAK PKM ===== --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">
                        Kontrak PKM
                    </h3>

                    @forelse ($kontrakFiles as $file)
                        <div class="flex justify-between items-center bg-gray-50 border rounded px-4 py-2 mb-2">
                            <span class="text-sm text-gray-700">
                                {{ basename($file) }}
                            </span>

                            <div class="flex gap-3">
                                <a href="{{ asset('storage/' . $file) }}" target="_blank" class="text-blue-600">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('admin.uploadinfo.delete', basename($file)) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="category" value="kontrak_pkm">
                                    <button class="text-red-600">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Tidak ada dokumen.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- JS KECIL (ROLE TOGGLE) --}}
    {{-- ========================= --}}
    <script>
        const categorySelect = document.getElementById('category');
        const roleWrapper = document.getElementById('role-wrapper');

        function toggleRole() {
            roleWrapper.style.display =
                categorySelect.value === 'cara_kerja' ? 'block' : 'none';
        }

        categorySelect.addEventListener('change', toggleRole);
        toggleRole();
    </script>
</x-admin-layout>
