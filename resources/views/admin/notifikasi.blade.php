<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-700 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <!-- Search Bar -->
    <div class="flex justify-between mb-4">
        <input type="text" id="search" placeholder="Cari Nomor Notifikasi..." 
            class="border border-gray-300 rounded px-4 py-2 text-xs w-full sm:w-1/3 focus:outline-none focus:ring-2 focus:ring-gray-300">
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>

    <!-- Tabel Data (Desktop) -->
    <div class="hidden lg:block bg-white rounded-lg shadow-md overflow-hidden">
        <table id="notificationTable" class="min-w-full text-gray-800 text-sm">
            <thead class="bg-gray-200 text-gray-600">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase">Nomor Order</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase">Detail Pekerjaan</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase">Catatan User</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($notifications as $index => $notification)
                    @if($notification->isAbnormalAvailable && $notification->isScopeOfWorkAvailable && $notification->isGambarTeknikAvailable)
                        <tr class="{{ $index % 2 === 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition duration-150">
                            <!-- Nomor Notifikasi -->
                            <td class="px-3 py-2 text-xs font-medium text-gray-600 notification-number">
                                {{ $notification->notification_number }}
                            </td>

                            <!-- Detail Pekerjaan -->
                            <td class="px-3 py-2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div class="col-span-2 font-semibold text-gray-700">📌 {{ $notification->job_name }}</div>
                                    <div>🏢 <span class="font-medium">Unit:</span> {{ $notification->unit_work }}</div>
                                    <div>📅 <span class="font-medium">Tanggal:</span> {{ $notification->input_date }}</div>
                                    
                                    <!-- Priority Selection -->
                                    <div class="col-span-2 mt-1">
                                        <form action="{{ route('notifications.updatePriority', $notification->notification_number) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <div class="flex items-center gap-2">
                                                <select name="priority" class="px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs w-28">
                                                    <option value="Urgently" {{ $notification->priority == 'Urgently' ? 'selected' : '' }}>Urgently</option>
                                                    <option value="Hard" {{ $notification->priority == 'Hard' ? 'selected' : '' }}>Hard</option>
                                                    <option value="Medium" {{ $notification->priority == 'Medium' ? 'selected' : '' }}>Medium</option>
                                                    <option value="Low" {{ $notification->priority == 'Low' ? 'selected' : '' }}>Low</option>
                                                </select>
                                                <button type="submit" class="bg-gray-500 text-white px-3 py-1 rounded text-xs hover:bg-gray-600 transition">Update</button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Dokumen -->
                                    <div class="col-span-2 flex flex-wrap gap-2 mt-2">
                                        <a href="{{ route('admin.abnormal.download_pdf', $notification->notification_number) }}" 
                                           class="bg-red-500 text-white px-3 py-1 rounded-lg text-xs hover:bg-red-600 transition">📄 Abnormalitas</a>
                                        <a href="{{ route('scopeofwork.view', $notification->notification_number) }}" 
                                           class="bg-green-400 text-white px-3 py-1 rounded-lg text-xs hover:bg-green-500 transition">📄 Scope</a>
                                        <a href="{{ route('view-dokumen', $notification->notification_number) }}" 
                                           class="bg-blue-400 text-white px-3 py-1 rounded-lg text-xs hover:bg-blue-500 transition">📄 Gambar</a>
                                    </div>
                                </div>
                            </td>

                            <!-- Catatan -->
                            <td class="px-3 py-2 text-right">
                                <form action="{{ route('notifications.update', $notification->notification_number) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <div class="flex flex-col gap-1">
                                        <select name="status" class="px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs w-full">
                                            <option value="Pending" {{ $notification->status == 'Pending' ? 'selected' : '' }}>⏳ Pending</option>
                                            <option value="Approved" {{ $notification->status == 'Approved' ? 'selected' : '' }}>✅ Approved</option>
                                        </select>
                                        <textarea name="catatan" placeholder="Catatan" rows="1" class="w-full px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs resize-none">{{ $notification->catatan ?? 'Tidak Ada Catatan' }}</textarea>
                                        <button type="submit" class="bg-gray-500 text-white px-3 py-1 rounded-full hover:bg-gray-600 transition">Save</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile Version -->
    <div class="lg:hidden space-y-4">
        @foreach($notifications as $notification)
            <div class="bg-white shadow-md rounded-lg p-4">
                <h3 class="text-md font-semibold text-orange-600">📌 {{ $notification->job_name }}</h3>
                <p class="text-sm text-gray-600">🏢 <strong>Unit:</strong> {{ $notification->unit_work }}</p>
                <p class="text-sm text-gray-600">📅 <strong>Tanggal:</strong> {{ $notification->input_date }}</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <a href="{{ route('admin.abnormal.download_pdf', $notification->notification_number) }}" 
                       class="bg-red-500 text-white px-2 py-1 rounded-lg text-xs hover:bg-red-600 transition">📄 Abnormalitas</a>
                    <a href="{{ route('scopeofwork.view', $notification->notification_number) }}" 
                       class="bg-green-400 text-white px-2 py-1 rounded-lg text-xs hover:bg-green-500 transition">📄 Scope</a>
                    <a href="{{ route('view-dokumen', $notification->notification_number) }}" 
                       class="bg-blue-400 text-white px-2 py-1 rounded-lg text-xs hover:bg-blue-500 transition">📄 Gambar</a>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.getElementById('search').addEventListener('keyup', function () {
            let query = this.value.toLowerCase();
            let rows = document.querySelectorAll('#notificationTable tbody tr');

            rows.forEach(row => {
                let notificationNumber = row.querySelector('.notification-number').textContent.toLowerCase();
                row.style.display = notificationNumber.includes(query) ? '' : 'none';
            });
        });
    </script>
</x-admin-layout>
