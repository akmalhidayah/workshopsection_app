{{-- resources/views/scopeofwork/_form.blade.php --}}
@php
    $mode = $mode ?? 'create';
    $scope = $scopeOfWork ?? null;
@endphp

<div class="p-4 max-h-[90vh] overflow-y-auto">
    <h3 class="text-lg font-semibold mb-2">
        {{ $mode === 'create' ? 'Buat Scope of Work' : 'Edit Scope of Work' }}
    </h3>

    <form id="sowForm"
          method="POST"
          action="{{ $mode === 'create'
              ? route('dokumen_orders.scope.store')
              : route('dokumen_orders.scope.update', $scope->notification_number)
          }}">
        @csrf
        @if($mode === 'edit') @method('PATCH') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium">Notifikasi</label>
                <input type="text" name="notification_number"
                       value="{{ $notification->notification_number }}" readonly
                       class="mt-1 block w-full form-input" />
            </div>

            <div>
                <label class="block text-sm font-medium">Nama Pekerjaan</label>
                <input type="text" name="nama_pekerjaan"
                       value="{{ $notification->job_name }}" readonly
                       class="mt-1 block w-full form-input" />
            </div>

            <div>
                <label class="block text-sm font-medium">Nama Penginput</label>
                <input type="text" name="nama_penginput"
                       value="{{ old('nama_penginput', $scope->nama_penginput ?? auth()->user()->name ?? '') }}"
                       class="mt-1 block w-full form-input" />
            </div>

            <div>
                <label class="block text-sm font-medium">Unit Kerja</label>
                <input type="text" name="unit_kerja"
                       value="{{ $notification->unit_work }}" readonly
                       class="mt-1 block w-full form-input" />
            </div>

            <div>
                <label class="block text-sm font-medium">Tanggal Dokumen</label>
                <input type="date" name="tanggal_dokumen"
                       value="{{ old('tanggal_dokumen', $scope->tanggal_dokumen ?? date('Y-m-d')) }}"
                       class="mt-1 block w-full form-input" />
            </div>

            <div>
                <label class="block text-sm font-medium">Tanggal Pemakaian</label>
                <input type="date" name="tanggal_pemakaian"
                       value="{{ old('tanggal_pemakaian', $scope->tanggal_pemakaian ?? $notification->usage_plan_date ?? '') }}"
                       class="mt-1 block w-full form-input" />
            </div>
        </div>

        <div class="mt-4">
            <h4 class="font-semibold">Scope of Work</h4>
            <div id="modal-sow-rows">
                @php
                    $rows = $scope ? max(count($scope->scope_pekerjaan ?? []), 1) : 1;
                @endphp
                @for($i = 0; $i < $rows; $i++)
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-2 sow-row">
                        <div>
                            <label class="text-sm">Scope Pekerjaan</label>
                            <input name="scope_pekerjaan[]" type="text" class="form-input w-full"
                                   value="{{ old('scope_pekerjaan.'.$i, $scope->scope_pekerjaan[$i] ?? '') }}" required />
                        </div>
                        <div>
                            <label class="text-sm">Qty</label>
                            <input name="qty[]" type="text" class="form-input w-full"
                                   value="{{ old('qty.'.$i, $scope->qty[$i] ?? '') }}" required />
                        </div>
                        <div>
                            <label class="text-sm">Satuan</label>
                            <input name="satuan[]" type="text" class="form-input w-full"
                                   value="{{ old('satuan.'.$i, $scope->satuan[$i] ?? '') }}" required />
                        </div>
                        <div>
                            <label class="text-sm">Keterangan</label>
                            <input name="keterangan[]" type="text" class="form-input w-full"
                                   value="{{ old('keterangan.'.$i, $scope->keterangan[$i] ?? '') }}" />
                        </div>
                    </div>
                @endfor
            </div>

            <div class="mt-3">
                <button type="button" id="modal-add-row"
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Tambah Baris
                </button>
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm">Catatan</label>
            <textarea name="catatan" rows="3"
                      class="form-textarea w-full">{{ old('catatan', $scope->catatan ?? '') }}</textarea>
        </div>

        <div class="mt-4 flex justify-end space-x-2">
            <button type="button" onclick="closeSowModal()"
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                Batal
            </button>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                {{ $mode === 'create' ? 'Simpan' : 'Update' }}
            </button>
        </div>
    </form>
</div>

