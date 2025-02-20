<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Dokumen Permintaan') }}
        </h2>
    </x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-gray-900 shadow-lg overflow-hidden sm:rounded-lg">
            <!-- Header dan Filter -->
            <div class="p-6 bg-gray-700 text-white flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
                <h3 class="text-lg font-semibold">List Dokumen & Permintaan User</h3>
                <form id="filterForm" action="{{ route('abnormalitas.index') }}" method="GET" class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0 w-full sm:w-auto">
                   <!-- Search Bar -->
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        placeholder="Search..." 
                        value="{{ request('search') }}" 
                        class="px-3 py-2 border border-gray-600 bg-gray-800 text-white rounded-md text-sm placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 w-full sm:w-auto"
                    />

                    <!-- Sort Dropdown -->
                    <select 
                        id="sortOrder" 
                        name="sortOrder" 
                        class="px-3 py-2 border border-gray-600 bg-gray-800 text-white rounded-md text-sm focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 w-full sm:w-auto"
                    >
                        <option value="latest" {{ request('sortOrder') == 'latest' ? 'selected' : '' }}>Urutkan Berdasarkan Terbaru</option>
                        <option value="oldest" {{ request('sortOrder') == 'oldest' ? 'selected' : '' }}>Urutkan Berdasarkan Terlama</option>
                    </select>

                    <!-- Entries Dropdown -->
                    <select 
                        id="entries" 
                        name="entries" 
                        class="px-3 py-2 border border-gray-600 bg-gray-800 text-white rounded-md text-sm focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 w-full sm:w-auto"
                    >
                        <option value="10" {{ request('entries') == 10 ? 'selected' : '' }}>Show 10 entries</option>
                        <option value="25" {{ request('entries') == 25 ? 'selected' : '' }}>Show 25 entries</option>
                        <option value="50" {{ request('entries') == 50 ? 'selected' : '' }}>Show 50 entries</option>
                        <option value="100" {{ request('entries') == 100 ? 'selected' : '' }}>Show 100 entries</option>
                    </select>

                    <!-- Search Button -->
                    <button type="submit" class="px-3 py-2 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        Search
                    </button>

                </form>
            </div>
        </div>

                <!-- Konten List Dokumen -->
                <div class="p-6 bg-gray-900 space-y-4">
                    @foreach($abnormalitas as $index => $abnormality)
                        @if($abnormality['user_id'] == auth()->id())
                        <div class="p-4 bg-gray-800 rounded-lg shadow-md" data-notification-id="{{ $abnormality['notification_number'] }}">
                                <!-- Informasi Dasar -->
                                <div class="mb-4">
                                    <p class="text-white font-semibold">Nomor Order: {{ $abnormality['notification_number'] }}</p>
                                    <p class="text-white">Nama Pekerjaan: {{ $abnormality['job_name'] }}</p>
                                    <p class="text-white">Input Date: {{ $abnormality['input_date'] }}</p>
                                </div>
                                <!-- Action Sections -->
                                <div class="space-y-2">
                                    <!-- Abnormalitas Section -->
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2 border-t border-gray-700 pt-2">
                                        <span class="text-sm text-gray-400">Abnormalitas:</span>
                                        @if(!$abnormality->abnormal)
                                            <a href="{{ route('abnormal.create', ['notificationNumber' => $abnormality['notification_number']]) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-xs w-full sm:w-auto text-center">
                                                <i class="fas fa-plus-circle mr-1"></i> Create Abnormalitas
                                            </a>
                                        @else
                                            <a href="{{ route('abnormal.edit', ['notificationNumber' => $abnormality['notification_number']]) }}" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-xs w-full sm:w-auto text-center">
                                                <i class="fas fa-edit mr-1"></i> Edit Abnormalitas
                                            </a>
                                            <a href="{{ route('abnormal.view', ['notificationNumber' => $abnormality['notification_number']]) }}" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs w-full sm:w-auto text-center" target="_blank">
                                                <i class="fas fa-file-pdf mr-1"></i> Lihat Abnormalitas
                                            </a>
                                            <a href="{{ route('abnormal.download_pdf', ['notificationNumber' => $abnormality['notification_number']]) }}" 
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs w-full sm:w-auto text-center" target="_blank">
                                                <i class="fas fa-file-pdf mr-1"></i> Download PDF
                                            </a>
                                        @endif
                                    </div>

                                    <!-- Scope of Work Section -->
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2 border-t border-gray-700 pt-2">
                                        <span class="text-sm text-gray-400">Scope of Work:</span>
                                        @if(!$abnormality->scopeOfWork)
                                            <a href="{{ route('scopeofwork.create', ['notificationNumber' => $abnormality['notification_number']]) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-xs w-full sm:w-auto text-center">
                                                <i class="fas fa-plus-circle mr-1"></i> Create Scope Of Work
                                            </a>
                                        @else
                                    <!-- Tanda Tangani Button -->
                                    <button onclick="openSignPad('{{ $abnormality['notification_number'] }}')" class="bg-gray-500 text-white px-3 py-1 rounded-md text-xs w-full sm:w-auto text-center hover:bg-gray-600">
                                                <i class="fas fa-signature mr-1"></i> Tanda Tangani
                                            </button>
                                            <a href="{{ route('scopeofwork.edit', ['notificationNumber' => $abnormality['notification_number']]) }}" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-xs w-full sm:w-auto text-center">
                                                <i class="fas fa-edit mr-1"></i> Edit Scope Of Work
                                            </a>
                                            <a href="{{ route('scopeofwork.view', ['notificationNumber' => $abnormality['notification_number']]) }}" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs w-full sm:w-auto text-center" target="_blank">
                                                <i class="fas fa-file-pdf mr-1"></i> Lihat Scope Of Work
                                            </a>
                                        @endif
                                    </div>

                                    <!-- Gambar Teknik Section -->
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2 border-t border-gray-700 pt-2">
                                        <span class="text-sm text-gray-400">Gambar Teknik:</span>
                                        <form method="POST" action="{{ route('upload-dokumen') }}" enctype="multipart/form-data" id="uploadForm_{{ $abnormality['notification_number'] }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="notification_number" value="{{ $abnormality['notification_number'] }}">
                                            <input type="file" name="dokumen" class="hidden" id="upload_dokumen_{{ $abnormality['notification_number'] }}" onchange="uploadDokumen(this, '{{ $abnormality['notification_number'] }}');">
                                            <a href="#" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded-md text-xs w-full sm:w-auto text-center" onclick="document.getElementById('upload_dokumen_{{ $abnormality['notification_number'] }}').click(); return false;">
                                                <i class="fas fa-upload mr-1"></i> Upload Gambar
                                            </a>
                                        </form>
                                        <a href="{{ route('view-dokumen', ['notificationNumber' => $abnormality['notification_number']]) }}" 
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-xs w-full sm:w-auto text-center lihat-dokumen-link" 
                                        data-id="{{ $abnormality['notification_number'] }}" 
                                        target="_blank">
                                        <i class="fas fa-file-pdf mr-1"></i> Lihat Gambar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
      <!-- Modal for E-Sign -->
      <div id="signPadModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                            </div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tanda Tangani Dokumen Scope Of Work</h3>
                                            <div class="mt-2">
                                                <canvas id="signaturePad" class="border rounded w-full" style="height: 300px;"></canvas>
                                                <input type="hidden" id="scopeOfWorkId" name="scopeOfWorkId" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-500 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm" onclick="saveSignature()">Save</button>
                                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeSignPad()">Cancel</button>
                                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-red-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="clearSignature()">Clear</button>
                                </div>
                            </div>
                        </div>
                    </div>


    <script src="{{ asset('js/abnormalitas.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('success'))
        <script>
            Swal.fire({
                title: 'Sukses!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
</x-app-layout>
