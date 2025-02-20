<x-document>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <!-- Header dengan Logo di Sebelah Kanan -->
                <div class="px-4 py-5 sm:px-6 bg-gray-100 flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            FORM
                        </h1>
                        <p class="text-4xl font-bold text-gray-900">Abnormalitas Analysis</p>
                    </div>
                    <!-- Logo -->
                    <img src="{{ asset('images/logo-st2.png') }}" alt="Logo" class="h-16 w-16 sm:h-24 sm:w-24 lg:h-32 lg:w-32 object-contain">
                </div>
                <div class="border-t border-gray-200 overflow-x-auto">
                    <table class="table-auto w-full border-collapse">
                        <tbody>
                            <tr>
                                <th class="text-left px-4 py-2 bg-gray-700 text-white">Abnormal Title</th>
                                <td class="px-4 py-2">{{ $abnormal->abnormal_title }}</td>
                                <th class="text-left px-4 py-2 bg-gray-700 text-white">Order No</th>
                                <td class="px-4 py-2">{{ $abnormal->notification_number }}</td>
                            </tr>
                            <tr>
                                <th class="text-left px-4 py-2 bg-gray-700 text-white">Section / Unit</th>
                                <td class="px-4 py-2">{{ $abnormal->unit_kerja }}</td>
                                <th class="text-left px-4 py-2 bg-gray-700 text-white">Abnormal Date</th>
                                <td class="px-4 py-2">{{ $abnormal->abnormal_date }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Abnormalitas Summary
                    </h3>
                    <table class="table-auto w-full border-collapse">
                        <tbody>
                            <tr>
                                <th class="text-left px-4 py-2 bg-gray-700 text-white w-1/3">What happened?<br><span class="text-sm text-gray-300">Problem Description*</span></th>
                                <td class="px-4 py-2">{{ $abnormal->problem_description }}</td>
                            </tr>
                            <tr>
                                <th class="text-left px-4 py-2 bg-gray-700 text-white w-1/3">Why did this happen?<br><span class="text-sm text-gray-300">Root Cause*</span></th>
                                <td class="px-4 py-2">{{ $abnormal->root_cause }}</td>
                            </tr>
                            <tr>
                                <th class="text-left px-4 py-2 bg-gray-700 text-white w-1/3">What was done about it?<br><span class="text-sm text-gray-300">Immediate Actions*</span></th>
                                <td class="px-4 py-2">{{ $abnormal->immediate_actions }}</td>
                            </tr>
                            <tr>
                                <th class="text-left px-4 py-2 bg-gray-700 text-white w-1/3">How can we stop it happening again?<br><span class="text-sm text-gray-300">Summary of Recommendations*</span></th>
                                <td class="px-4 py-2">{{ $abnormal->summary }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Actions Required
                    </h3>
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-700 text-white">
                                <th class="border px-2 py-2" style="width: 5%;">No</th>
                                <th class="border px-4 py-2">Action</th>
                                <th class="border px-4 py-2">By</th>
                                <th class="border px-4 py-2">When</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($abnormal->actions as $index => $action)
                                <tr>
                                    <td class="border px-2 py-2 text-center" style="width: 5%;">{{ $index + 1 }}</td>
                                    <td class="border px-4 py-2">{{ $action['action'] }}</td>
                                    <td class="border px-4 py-2">{{ $action['by'] }}</td>
                                    <td class="border px-4 py-2">{{ $action['when'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Equipment Risk
                    </h3>
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-700 text-white">
                                <th class="border px-2 py-2" style="width: 5%;">No</th>
                                <th class="border px-4 py-2">Risk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($abnormal->risks as $index => $risk)
                                <tr>
                                    <td class="border px-2 py-2 text-center" style="width: 5%;">{{ $index + 1 }}</td>
                                    <td class="border px-4 py-2">{{ $risk }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

<!-- Tabel untuk Tanda Tangan Persetujuan -->
<div class="border-t border-gray-200 p-6 overflow-x-auto">
    <h3 class="text-lg leading-6 font-medium text-gray-900">
        Approved by User
    </h3>
    <table class="table-auto w-full border-collapse text-sm sm:text-base">
        <tbody>
            <tr>
                <td class="border px-4 py-4 text-center bg-gray-700 text-white whitespace-nowrap">
                    Reviewed by
                </td>
                <td class="border px-4 py-4 whitespace-nowrap">
                    {{ $abnormal->approved_by_manager }}<br>
                    {{ $abnormal->managerUser->name ?? 'N/A' }}
                    <p>Mgr of {{ $abnormal->managerUser->seksi ?? 'N/A' }}</p>
                </td>
                <td class="border px-4 py-4 text-center bg-gray-700 text-white whitespace-nowrap">
                    Approval
                </td>
                <td class="border px-4 py-4 text-center">
                    @if($abnormal->manager_signature)
                        <img src="{{ $abnormal->manager_signature }}" alt="Tanda Tangan Manager" class="w-32 h-auto mx-auto">
                    @else
                        <span class="text-red-500">Belum Ditandatangani</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="border px-4 py-4 text-center bg-gray-700 text-white whitespace-nowrap">
                    Reviewed by
                </td>
                <td class="border px-4 py-4 whitespace-nowrap">
                    {{ $abnormal->approved_by_senior_manager }}<br>
                    {{ $abnormal->seniorManagerUser->name ?? 'N/A' }}
                    <p>SM of {{ $abnormal->seniorManagerUser->unit_work ?? 'N/A' }}</p>
                </td>
                <td class="border px-4 py-4 text-center bg-gray-700 text-white whitespace-nowrap">
                    Approval
                </td>
                <td class="border px-4 py-4 text-center">
                    @if($abnormal->senior_manager_signature)
                        <img src="{{ $abnormal->senior_manager_signature }}" alt="Tanda Tangan Senior Manager" class="w-32 h-auto mx-auto">
                    @else
                        <span class="text-red-500">Belum Ditandatangani</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>

@if($abnormal->files)
    <div class="border-t border-gray-200 p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            Dokumentasi Abnormalitas (Kondisi Actual)
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-sm sm:text-base">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2 text-center" style="width: 40%;">Foto Temuan Abnormalitas</th>
                        <th class="border px-4 py-2" style="width: 60%;">Keterangan Abnormalitas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($abnormal->files as $index => $file)
                        <tr>
                            <td class="border px-4 py-2 text-center">
                                <img src="{{ Storage::url('abnormalitas/' . $file['file_path']) }}" alt="Abnormalitas Image" class="mt-2 mx-auto max-w-xs h-auto">
                            </td>
                            <td class="border px-4 py-2">
                                {{ $file['keterangan'] }}
                            </td>
                        </tr>
                    @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-document>