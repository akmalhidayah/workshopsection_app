<div id="modalEdit" class="fixed inset-0 z-50 hidden flex items-center justify-center overflow-y-auto">
  <div class="fixed inset-0 bg-black/60"></div>

  <div class="relative w-full max-w-lg mx-4 z-10 p-5 rounded-lg shadow-lg bg-white dark:bg-gray-800 max-h-[90vh] overflow-auto">
    <div class="flex items-center justify-between mb-3">
      <h4 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Order Permintaan</h4>
      <button type="button" class="text-gray-600 dark:text-gray-200" onclick="closeEdit()">×</button>
    </div>

    <form id="editForm" method="POST" class="space-y-3">
      @csrf
      @method('PATCH')

      <div>
        <label class="text-sm">Nomor Order</label>
        <input id="editNotifikasiNo" name="notification_number" readonly
               class="w-full px-3 py-2 rounded border bg-white dark:bg-gray-900">
      </div>

      <div>
        <label class="text-sm">Nama Pekerjaan</label>
        <input id="editNamaPekerjaan" name="job_name" required
               class="w-full px-3 py-2 rounded border bg-white dark:bg-gray-900">
      </div>

      <div>
        <label class="text-sm">Unit Kerja</label>
        <select id="unitKerjaEdit" name="unit_work" class="w-full px-3 py-2 rounded border" required>
          <option value="">Pilih Unit Kerja</option>
          @foreach($units as $unit)
            <option value="{{ $unit->name }}" data-seksi='@json($unit->seksi_list)'>
              {{ $unit->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div id="wrapSeksiEdit" style="display:none;">
        <label class="text-sm">Seksi</label>
        <select id="seksiEdit" name="seksi" class="w-full px-3 py-2 rounded border">
          <option value="">Pilih Seksi</option>
        </select>
      </div>

      <div>
        <label class="text-sm">Prioritas</label>
        <select id="priority_edit" name="priority" required class="w-full px-3 py-2 rounded border">
          <option value="Urgently">Urgently</option>
          <option value="Hard">Hard</option>
          <option value="Medium">Medium</option>
          <option value="Low">Low</option>
        </select>
      </div>

      <div>
        <label class="text-sm">Tanggal Input</label>
        <input type="date" id="editInputDate" name="input_date" required
               class="w-full px-3 py-2 rounded border bg-white dark:bg-gray-900">
      </div>

      <div>
        <label class="text-sm">Rencana Pemakaian</label>
        <input type="date" id="editRencanaPemakaian" name="usage_plan_date" required
               class="w-full px-3 py-2 rounded border bg-white dark:bg-gray-900">
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">Update</button>
        <button type="button" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-100 rounded" onclick="closeEdit()">Cancel</button>
      </div>
    </form>
  </div>
</div>
