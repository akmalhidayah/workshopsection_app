<!-- resources/views/notifications/_create_modal.blade.php -->
<!-- Create Modal -->
<div id="modalCreate" class="fixed inset-0 z-50 hidden flex items-center justify-center overflow-y-auto">
    <!-- overlay -->
    <div class="fixed inset-0 bg-black/60"></div>

    <!-- modal box -->
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-lg mx-4 z-10 p-5">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white">Order</h4>
            <button type="button" class="text-gray-600 dark:text-gray-200" onclick="closeCreate()">×</button>
        </div>

        <form id="createForm" action="{{ route('notifications.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="text-sm text-gray-700 dark:text-gray-300">Nomor Order</label>
                <input name="notification_number" id="notifikasiNo" required
                       class="w-full px-3 py-2 rounded border bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                @error('notification_number')<div class="text-red-500 text-sm mt-1">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="text-sm text-gray-700 dark:text-gray-300">Nama Pekerjaan</label>
                <input name="job_name" id="namaPekerjaan" required class="w-full px-3 py-2 rounded border bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
            </div>

     <div>
  <label class="text-sm">Unit Kerja</label>
  <select name="unit_work" id="unitKerjaCreate" data-role="unit" class="w-full px-3 py-2 rounded border" required>
      <option value="">Pilih Unit Kerja</option>
      @foreach($units as $unit)
          <option value="{{ $unit->name }}" data-seksi='@json($unit->seksi_list)'>
              {{ $unit->name }}
          </option>
          {{-- Debug sementara --}}
@if($units->isEmpty())
  <div class="text-red-600 text-xs">Unit kerja belum ada di DB.</div>
@endif

      @endforeach
  </select>
</div>

<div id="wrapSeksiCreate" data-role="wrap-seksi" style="display:none;">
  <label class="text-sm">Seksi</label>
  <select name="seksi" id="seksiCreate" data-role="seksi" class="w-full px-3 py-2 rounded border">
      <option value="">Pilih Seksi</option>
  </select>
</div>


            <div>
                <label class="text-sm text-gray-700 dark:text-gray-300">Prioritas</label>
                <select id="priority_create" name="priority" class="w-full px-3 py-2 rounded border bg-white dark:bg-gray-900 text-gray-900 dark:text-white" required>
                    <option value="Urgently">Emergency</option>
                    <option value="Hard">High</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="Low">Low</option>
                </select>
            </div>

            <input type="hidden" id="InputDate" name="input_date" value="{{ date('Y-m-d') }}">

            <div>
                <label class="text-sm text-gray-700 dark:text-gray-300">Rencana Pemakaian</label>
                <input type="date" id="rencanaPemakaian" name="usage_plan_date" required class="w-full px-3 py-2 rounded border bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
            </div>

            <div class="flex justify-end gap-2 mt-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Submit</button>
                <button type="button" class="px-4 py-2 bg-gray-300 rounded" onclick="closeCreate()">Cancel</button>
            </div>
        </form>
    </div>
</div>
