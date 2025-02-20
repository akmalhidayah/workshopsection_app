<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('LPJ Management') }}
        </h2>
    </x-slot>

    <div class="p-4 space-y-4">
        <!-- Pencarian -->
        <div class="flex justify-between items-center">
            <input type="text" id="search" placeholder="Cari Notifikasi..."
                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-300 w-full sm:w-1/3 shadow-sm">
        </div>

        <!-- Tabel LPJ Management -->
        <div class="bg-white overflow-x-auto shadow-lg rounded-lg">
            <table class="min-w-full bg-white text-sm border border-gray-300">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-xs uppercase">
                        <th class="py-3 px-4 text-left">Nomor Order</th>
                        <th class="py-3 px-4 text-left">Tanggal Update</th>
                        <th class="py-3 px-4 text-left">Nomor LPJ</th>
                        <th class="py-3 px-4 text-left">Dokumen LPJ</th>
                        <th class="py-3 px-4 text-left">Nomor PPL</th>
                        <th class="py-3 px-4 text-left">Dokumen PPL</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($notifications as $notification)
                        @php
                            $lpj = App\Models\Lpj::where('notification_number', $notification->notification_number)->first();
                            $has_lhpp = App\Models\LHPP::where('notification_number', $notification->notification_number)->exists();
                        @endphp
                        @if($lpj || $has_lhpp)
                            <tr class="hover:bg-gray-100 transition">
                                <td class="px-4 py-3">{{ $notification->notification_number }}</td>
                                <td class="px-4 py-3">{{ $lpj->update_date ?? now()->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">
                                    <input type="text" name="lpj_number" form="form-{{ $notification->notification_number }}" 
                                        value="{{ $lpj->lpj_number ?? old('lpj_number') }}"
                                        class="w-full px-3 py-1 border border-gray-300 rounded text-xs focus:ring-2 focus:ring-blue-100">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <!-- Tombol Upload -->
                                        <label for="lpj_document_{{ $notification->notification_number }}"
                                            class="cursor-pointer bg-green-500 text-white px-3 py-1 rounded text-xs flex items-center hover:bg-green-600 transition">
                                            <i class="fas fa-upload"></i>
                                        </label>
                                        @if($lpj && $lpj->lpj_document_path)
                                            <a href="{{ Storage::url($lpj->lpj_document_path) }}" target="_blank">
                                                <span class="text-blue-500 underline">Lihat LPJ</span>
                                            </a>
                                        @else
                                            <span class="text-gray-500">Belum ada dokumen LPJ</span>
                                        @endif
                                        <!-- Input File Hidden -->
                                        <input id="lpj_document_{{ $notification->notification_number }}" 
                                            type="file" name="lpj_document"
                                            form="form-{{ $notification->notification_number }}" 
                                            class="hidden"
                                            onchange="showFileName(this, 'lpj_filename_{{ $notification->notification_number }}')">

                                        <!-- Menampilkan Nama File yang Sementara Diupload -->
                                        <span id="lpj_filename_{{ $notification->notification_number }}" class="text-xs text-gray-500">
                                            @if($lpj && $lpj->lpj_document_path)
                                                {{ basename($lpj->lpj_document_path) }}
                                            @else
                                                Tidak ada file
                                            @endif
                                        </span>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <input type="text" name="ppl_number" form="form-{{ $notification->notification_number }}"
                                        value="{{ $lpj->ppl_number ?? old('ppl_number') }}"
                                        class="w-full px-3 py-1 border border-gray-300 rounded text-xs focus:ring-2 focus:ring-blue-100">
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <!-- Tombol Upload -->
                                        <label for="ppl_document_{{ $notification->notification_number }}"
                                            class="cursor-pointer bg-green-500 text-white px-3 py-1 rounded text-xs flex items-center hover:bg-green-600 transition">
                                            <i class="fas fa-upload"></i> 
                                        </label>
                                        @if($lpj && $lpj->lpj_document_path)
                                            <a href="{{ Storage::url($lpj->ppl_document_path) }}" target="_blank">
                                                <span class="text-blue-500 underline">Lihat LPJ</span>
                                            </a>
                                        @else
                                            <span class="text-gray-500">Belum ada dokumen LPJ</span>
                                        @endif
                                        <!-- Input File Hidden -->
                                        <input id="ppl_document_{{ $notification->notification_number }}" 
                                            type="file" name="ppl_document"
                                            form="form-{{ $notification->notification_number }}" 
                                            class="hidden"
                                            onchange="showFileName(this, 'ppl_filename_{{ $notification->notification_number }}')">

                                        <!-- Menampilkan Nama File yang Sementara Diupload -->
                                        <span id="ppl_filename_{{ $notification->notification_number }}" class="text-xs text-gray-500">
                                            @if($lpj && $lpj->ppl_document_path)
                                                {{ basename($lpj->ppl_document_path) }}
                                            @else
                                                Tidak ada file
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <form id="form-{{ $notification->notification_number }}" action="{{ route('lpj.update', $notification->notification_number) }}"
                                        method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <button type="submit"
                                            class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600 transition">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
<!-- Script untuk Menampilkan Nama File yang Dipilih -->
<script>
    function showFileName(input, spanId) {
        let fileName = input.files.length > 0 ? input.files[0].name : "Tidak ada file";
        document.getElementById(spanId).textContent = fileName;
    }
</script>
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('success'))
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ session("success") }}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
</x-admin-layout>
