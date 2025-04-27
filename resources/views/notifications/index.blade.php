<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Order/Notification Type 14 Planner Group 001/4701') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="flex justify-between p-6 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white">
                <h3 class="text-lg leading-6 font-medium">
                    List Order User
                </h3>
                <div class="flex space-x-2">
                    <button onclick="confirmCreate()" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-green-600 transition duration-150 ease-in-out">
                        <i class="fas fa-plus mr-2"></i> Input Notifikasi / Order
                    </button>
                </div>
            </div>

            <!-- Search and Sorting -->
            <form method="GET" action="{{ route('notifications.index') }}" class="flex flex-col sm:flex-row justify-between px-6 py-4 bg-white dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 space-y-4 sm:space-y-0">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..." 
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg w-full sm:w-1/3 text-gray-800 dark:text-white bg-white dark:bg-gray-700">

                <div class="flex space-x-2 items-center">
                    <select name="sortOrder" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-white">
                        <option value="latest" {{ request('sortOrder') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="oldest" {{ request('sortOrder') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        <option value="priority-highest" {{ request('sortOrder') == 'priority-highest' ? 'selected' : '' }}>Prioritas Tertinggi</option>
                        <option value="priority-lowest" {{ request('sortOrder') == 'priority-lowest' ? 'selected' : '' }}>Prioritas Terendah</option>
                    </select>

                    <select name="entries" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-white">
                        <option value="10" {{ request('entries') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('entries') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('entries') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('entries') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </form>

                <!-- Table -->
                <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800 border border-gray-300 dark:border-none">
                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nomor Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama Pekerjaan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Unit Kerja</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Prioritas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Tanggal Input</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="divide-y divide-gray-300 dark:divide-gray-700">
                            @foreach($notifications as $index => $notification)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-100 dark:bg-gray-900' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-white">{{ $notification->notification_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-white">{{ $notification->job_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-white">{{ $notification->unit_work }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($notification->priority == 'Urgently') bg-red-500 text-white @endif
                                            @if($notification->priority == 'Hard') bg-orange-500 text-white @endif
                                            @if($notification->priority == 'Medium') bg-yellow-500 text-white @endif
                                            @if($notification->priority == 'Low') bg-green-500 text-white @endif
                                        ">
                                            {{ $notification->priority }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-white">{{ $notification->input_date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <!-- Tombol Edit dengan bentuk persegi dan border 
                                            <button onclick="openEditForm('{{ $notification->notification_number }}')" class="bg-green-500 text-white p-2 border border-green-600 hover:bg-green-600 transition duration-300 ease-in-out">
                                                <i class="fas fa-edit text-sm"></i>
                                            </button>-->

                                            <!-- Tombol Hapus dengan bentuk persegi dan border -->
                                            <form action="{{ route('notifications.destroy', $notification->notification_number) }}" method="POST" onsubmit="return confirmDelete(this)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-500 text-white p-2 border border-red-600 hover:bg-red-600 transition duration-300 ease-in-out">
                                                    <i class="fas fa-trash-alt text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
   <!-- Modal form untuk menambahkan data -->
<div id="dataForm" class="fixed z-50 inset-0 overflow-y-auto @if ($errors->any()) block @else hidden @endif">
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
        </div>
        <div class="inline-block bg-white dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:align-middle sm:max-w-md w-full">
        <div class="bg-gray-100 dark:bg-gray-700 px-6 py-4">
                <div class="text-center sm:text-left">
                    <h3 class="text-lg leading-6 font-medium  text-gray-900 dark:text-white" id="modal-title">Input Notifikasi / Order In Planning (03)</h3>
                    <div class="mt-4">
                        <form id="createForm" action="{{ route('notifications.store') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="notifikasiNo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Notifikasi / Order </label>
                                <input type="text" id="notifikasiNo" name="notification_number" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-500 rounded-md shadow-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                
                                <!-- Menampilkan pesan error jika nomor notifikasi sudah ada -->
                                @if ($errors->has('notification_number'))
                                    <span class="text-red-400 text-sm mt-1">{{ $errors->first('notification_number') }}</span>
                                @endif
                            </div>
                            <div class="mb-4">
                                <label for="namaPekerjaan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Pekerjaan</label>
                                <input type="text" id="namaPekerjaan" name="job_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-500 rounded-md shadow-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                            <div class="mb-4">
                                <label for="unitKerja" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit Kerja</label>
                                <select id="unitKerja" name="unit_work" class="select2 mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-500  rounded-md shadow-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="">Pilih Unit Kerja</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prioritas</label>
                                <select id="priority" name="priority" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-500 rounded-md shadow-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="Urgently">Urgently</option>
                                    <option value="Hard">Hard</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                    <label for="jenisKontrak" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Kontrak</label>
                                    <select id="jenisKontrak" name="jenis_kontrak" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-500 rounded-md shadow-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" onchange="handleJenisKontrakChange()" required>
                                        <option value="">Pilih Jenis Kontrak</option>
                                        <option value="Bengkel Mesin">Bengkel Mesin</option>
                                        <option value="Bengkel Listrik">Bengkel Listrik</option>
                                        <option value="Field Supporting">Field Supporting</option>
                                    </select>
                                </div>

                                <div class="mb-4" id="namaKontrakContainer" style="display:none;">
                                    <label for="namaKontrak" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Kontrak</label>
                                    <select id="namaKontrak" name="nama_kontrak" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-500 rounded-md shadow-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                        <!-- Pilihan Nama Kontrak akan dimasukkan melalui JavaScript -->
                                    </select>
                                </div>
                            <div class="mb-4">
                                <input type="hidden" id="InputDate" name="input_date" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="mb-4">
                                <label for="rencanaPemakaian" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rencana Pemakaian</label>
                                <input type="date" id="rencanaPemakaian" name="usage_plan_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-500 rounded-md shadow-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-end gap-2">
    <button type="submit"
        class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2
               bg-indigo-500 text-base font-medium text-white hover:bg-indigo-600
               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
        Submit
    </button>

    <button type="button"
        class="w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-500 shadow-sm px-4 py-2
               bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300
               hover:bg-gray-100 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:text-sm"
        onclick="closeForm()">
        Cancel
    </button>
</div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal form untuk edit data -->
<div id="editForm" class="fixed z-50 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
        </div>
        <div class="inline-block bg-gray-800 text-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:align-middle sm:max-w-md w-full">
            <div class="bg-gray-700 px-6 py-4">
                <div class="text-center sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Edit Order Permintaan</h3>
                    <div class="mt-4">
                        <form id="editNotificationForm" action="" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="mb-4">
                                <label for="editNotifikasiNo" class="block text-sm font-medium text-gray-300">Nomor Order</label>
                                <input type="text" id="editNotifikasiNo" name="notification_number" class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-gray-900 text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" readonly>
                            </div>
                            <div class="mb-4">
                                <label for="editNamaPekerjaan" class="block text-sm font-medium text-gray-300">Nama Pekerjaan</label>
                                <input type="text" id="editNamaPekerjaan" name="job_name" class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-gray-900 text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                            <div class="mb-4">
                                <label for="editUnitKerja" class="block text-sm font-medium text-gray-300">Unit Kerja</label>
                                <input type="text" id="editUnitKerja" name="unit_work" class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-gray-900 text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                            <div class="mb-4">
                                <label for="priority" class="block text-sm font-medium text-gray-300">Prioritas</label>
                                <select id="priority" name="priority" class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-gray-900 text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="Urgently">Urgently</option>
                                    <option value="Hard">Hard</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="editInputDate" class="block text-sm font-medium text-gray-300">Tanggal Input</label>
                                <input type="date" id="editInputDate" name="input_date" class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-gray-900 text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                            <div class="mb-4">
                                <label for="rencanaPemakaian" class="block text-sm font-medium text-gray-300">Rencana Pemakaian</label>
                                <input type="date" id="rencanaPemakaian" name="usage_plan_date" class="mt-1 block w-full px-3 py-2 border border-gray-500 rounded-md shadow-sm bg-gray-900 text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-500 text-base font-medium text-white hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                                    Update
                                </button>
                                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-500 shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-gray-300 hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm" onclick="closeEditForm()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/custom-notification.js') }}"></script>
<script>
        function confirmDelete(form) {
    event.preventDefault(); // Mencegah form submit langsung

    Swal.fire({
        title: 'Yakin ingin menghapus user ini?',
        text: "Aksi ini tidak dapat dibatalkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit(); // Submit form jika konfirmasi
        }
    });
}
</script>

</x-app-layout>
