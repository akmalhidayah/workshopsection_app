<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Purchase Request (PR)') }}
        </h2>
    </x-slot>

    <div class="overflow-x-auto h-full p-4">
        <div class="flex justify-between mb-4">
            <input type="text" id="search" placeholder="Search..." class="border border-blue-400 rounded px-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-300 w-full sm:w-1/3">
        </div>
        <table class="min-w-full bg-white text-gray-900 border-separate border-spacing-0 border border-gray-300 text-sm rounded-lg shadow-md">
            <thead class="bg-blue-800 text-white rounded-t-lg">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium uppercase border-b border-gray-200">Nomor Notifikasi</th>
                    <th class="px-4 py-3 text-left text-sm font-medium border-b border-gray-200">Nomor Purchase Request</th>
                    <th class="px-4 py-3 text-left text-sm font-medium border-b border-gray-200">Approve Manager</th>
                    <th class="px-4 py-3 text-left text-sm font-medium border-b border-gray-200">Approve Senior Manager</th>
                    <th class="px-4 py-3 text-left text-sm font-medium border-b border-gray-200">Approve General Manager</th>
                    <th class="px-4 py-3 text-left text-sm font-medium border-b border-gray-200">Approve Direktur Operasional</th>
                    <th class="px-4 py-3 text-left text-sm font-medium border-b border-gray-200">Update Date</th>
                    <th class="px-4 py-3 text-left text-sm font-medium border-b border-gray-200">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-300">
                @foreach($notifications as $notification)
                    @if($notification->isAbnormalAvailable && $notification->isScopeOfWorkAvailable && $notification->isGambarTeknikAvailable && $notification->isHppAvailable)
                        <tr class="hover:bg-gray-50 transition duration-150 ease-in-out">
                            <td class="px-4 py-3 border-b">{{ $notification->notification_number }}</td>
                            <td class="px-4 py-3 border-b">
                                <input type="text" name="purchase_request" placeholder="Masukkan Nomor PR" 
                                    class="w-48 px-4 py-2 rounded-lg bg-blue-100 border-gray-300 text-gray-900 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-500" 
                                    value="{{ old('purchase_request') }}">
                            </td>
                            <td class="px-4 py-3 border-b text-center">
                                <input type="checkbox" {{ $notification->approve_manager ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-green-600 border-green-500 rounded focus:ring-blue-500">
                            </td>
                            <td class="px-4 py-3 border-b text-center">
                                <input type="checkbox" {{ $notification->approve_senior_manager ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-green-600 border-red-500 rounded focus:ring-blue-500">
                            </td>
                            <td class="px-4 py-3 border-b text-center">
                                <input type="checkbox" {{ $notification->approve_general_manager ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-green-600 border-blue-500 rounded focus:ring-blue-500">
                            </td>
                            
                            <!-- Hanya tampilkan jika source_form adalah createhpp1 -->
                            <td class="px-4 py-3 border-b text-center">
                                @if($notification->source_form === 'createhpp1')
                                    <input type="checkbox" {{ $notification->approve_direktur_operasional ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-green-600 border-yellow-500 rounded focus:ring-blue-500">
                                @endif
                            </td>

                            <td class="px-4 py-3 border-b text-center">
                                <input type="text" value="{{ date('Y-m-d') }}" 
                                    class="w-24 px-2 py-1 rounded-lg bg-gray-200 border-gray-300 text-gray-900 text-xs focus:ring-2 focus:ring-blue-300" 
                                    readonly>
                            </td>
                            <td class="px-4 py-3 border-b text-center">
                                <button class="bg-blue-500 text-white px-4 py-2 rounded-lg text-xs hover:bg-blue-600 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-transform duration-200 ease-in-out transform hover:scale-105">
                                    Update
                                </button>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    <script src="{{ asset('js/adminnotifications.js') }}"></script>
</x-admin-layout>
