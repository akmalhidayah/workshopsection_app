<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Dokumen Abnormalitas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-900 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ route('abnormal.update', $abnormal->notification_number) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    
                    <!-- Abnormalitas Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-sm text-gray-300" for="abnormal_title">
                                Abnormal Title
                            </label>
                            <input id="abnormal_title" name="abnormal_title" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $abnormal->abnormal_title }}" required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-300" for="notifikasi_no">
                                Notifikasi / Order In Planning (03)
                            </label>
                            <input id="notifikasi_no" name="notification_number" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $notification->notification_number }}" required readonly>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-300" for="unit_kerja">
                                Unit Kerja / Section
                            </label>
                            <input id="unit_kerja" name="unit_kerja" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $notification->unit_work }}" required readonly>
                        </div>

                        <div>
                        <label class="block font-medium text-sm text-gray-300" for="abnormal_date">
                            Abnormal Date
                        </label>
                        <input id="abnormal_date" name="abnormal_date" type="date" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $abnormal->abnormal_date }}" readonly>
                    </div>
                    </div>

                    <!-- Problem Description -->
                    <div class="mt-4">
                        <label class="block font-medium text-sm text-gray-300" for="problem_description">
                            What happened? (Problem Description*)
                        </label>
                        <textarea id="problem_description" name="problem_description" class="form-textarea rounded-md shadow-sm mt-1 block w-full" rows="3" required>{{ $abnormal->problem_description }}</textarea>
                    </div>

                    <!-- Root Cause -->
                    <div class="mt-4">
                        <label class="block font-medium text-sm text-gray-300" for="root_cause">
                            Why did this happen? (Root Cause*)
                        </label>
                        <textarea id="root_cause" name="root_cause" class="form-textarea rounded-md shadow-sm mt-1 block w-full" rows="3" required>{{ $abnormal->root_cause }}</textarea>
                    </div>

                    <!-- Immediate Actions -->
                    <div class="mt-4">
                        <label class="block font-medium text-sm text-gray-300" for="immediate_actions">
                            What was done about it? (Immediate Actions*)
                        </label>
                        <textarea id="immediate_actions" name="immediate_actions" class="form-textarea rounded-md shadow-sm mt-1 block w-full" rows="3" required>{{ $abnormal->immediate_actions }}</textarea>
                    </div>
                    
                    <!-- Summary of Recommendations -->
                    <div class="mt-4">
                        <label class="block font-medium text-sm text-gray-300" for="summary">
                            How We Can Stop it happening again? (Summary Of Recommendations*)
                        </label>
                        <textarea id="summary" name="summary" class="form-textarea rounded-md shadow-sm mt-1 block w-full" rows="3" required>{{ $abnormal->summary }}</textarea>
                    </div>

                    <!-- Actions Required -->
                    <div class="mt-6">
                        <h3 class="font-semibold text-lg text-gray-200">Actions Required</h3>
                        <div id="action-container" class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            @foreach ($abnormal->actions as $key => $action)
                                <div>
                                    <label class="block font-medium text-sm text-gray-200" for="action_{{ $key + 1 }}">
                                        Action {{ $key + 1 }}
                                    </label>
                                    <input id="action_{{ $key + 1 }}" name="actions[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $action['action'] }}" required>
                                </div>

                                <div>
                                    <label class="block font-medium text-sm text-gray-200" for="by_{{ $key + 1 }}">
                                        By
                                    </label>
                                    <input id="by_{{ $key + 1 }}" name="by[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $action['by'] }}" required>
                                </div>

                                <div>
                                    <label class="block font-medium text-sm text-gray-200" for="when_{{ $key + 1 }}">
                                        When
                                    </label>
                                    <input id="when_{{ $key + 1 }}" name="when[]" type="date" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $action['when'] }}" required>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <button type="button" id="add-action" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300">Tambahkan</button>
                        </div>
                    </div>

                    <!-- Equipment Risk -->
                    <div class="mt-6">
                        <h3 class="font-semibold text-lg text-gray-200">Equipment Risk</h3>
                        <div id="risk-container" class="grid grid-cols-1 gap-4 mt-2">
                            @foreach ($abnormal->risks as $key => $risk)
                                <div>
                                    <label class="block font-medium text-sm text-gray-300" for="risk_{{ $key + 1 }}">
                                        Risk {{ $key + 1 }}
                                    </label>
                                    <input id="risk_{{ $key + 1 }}" name="risks[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" value="{{ $risk }}">
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <button type="button" id="add-risk" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300">Tambahkan</button>
                        </div>
                    </div>

                    <!-- Dokumentasi Abnormalitas -->
                    <div class="mt-6">
                        <h3 class="font-semibold text-lg text-gray-200">Dokumentasi Abnormalitas</h3>
                        <div id="dokumentasi-container" class="grid grid-cols-1 gap-4 mt-2">
                            @foreach ($abnormal->files as $key => $file)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block font-medium text-sm text-gray-300" for="foto_{{ $key + 1 }}">
                                            Foto Temuan Abnormalitas {{ $key + 1 }}
                                        </label>
                                        <input id="foto_{{ $key + 1 }}" name="fotos[]" type="file" class="form-input rounded-md shadow-sm mt-1 block w-full">
                                    </div>
                                    <div>
                                        <label class="block font-medium text-sm text-gray-300" for="keterangan_{{ $key + 1 }}">
                                            Keterangan Abnormalitas {{ $key + 1 }}
                                        </label>
                                        <textarea id="keterangan_{{ $key + 1 }}" name="keterangans[]" class="form-textarea rounded-md shadow-sm mt-1 block w-full" rows="3">{{ $file['keterangan'] }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <button type="button" id="add-dokumentasi" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-300">Tambahkan</button>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition duration-300">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('add-action').addEventListener('click', function () {
            const container = document.getElementById('action-container');
            const actionCount = container.childElementCount / 3 + 1;
            const actionGroup = `
                <div>
                    <label class="block font-medium text-sm text-gray-200" for="action_${actionCount}">
                        Action ${actionCount}
                    </label>
                    <input id="action_${actionCount}" name="actions[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-200" for="by_${actionCount}">
                        By
                    </label>
                    <input id="by_${actionCount}" name="by[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-200" for="when_${actionCount}">
                        When
                    </label>
                    <input id="when_${actionCount}" name="when[]" type="date" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', actionGroup);
        });

        document.getElementById('add-risk').addEventListener('click', function () {
            const container = document.getElementById('risk-container');
            const riskCount = container.childElementCount + 1;
            const riskGroup = `
                <div>
                    <label class="block font-medium text-sm text-gray-300" for="risk_${riskCount}">
                        Risk ${riskCount}
                    </label>
                    <input id="risk_${riskCount}" name="risks[]" type="text" class="form-input rounded-md shadow-sm mt-1 block w-full">
                </div>
            `;
            container.insertAdjacentHTML('beforeend', riskGroup);
        });

        document.getElementById('add-dokumentasi').addEventListener('click', function () {
            const container = document.getElementById('dokumentasi-container');
            const dokumentasiCount = container.childElementCount / 2 + 1;
            const dokumentasiGroup = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-sm text-gray-300" for="foto_${dokumentasiCount}">
                            Foto Temuan Abnormalitas ${dokumentasiCount}
                        </label>
                        <input id="foto_${dokumentasiCount}" name="fotos[]" type="file" class="form-input rounded-md shadow-sm mt-1 block w-full">
                    </div>
                    <div>
                        <label class="block font-medium text-sm text-gray-300" for="keterangan_${dokumentasiCount}">
                            Keterangan Abnormalitas ${dokumentasiCount}
                        </label>
                        <textarea id="keterangan_${dokumentasiCount}" name="keterangans[]" class="form-textarea rounded-md shadow-sm mt-1 block w-full" rows="3"></textarea>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', dokumentasiGroup);
        });
    </script>
</x-app-layout>
