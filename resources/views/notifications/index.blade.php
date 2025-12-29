<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Order ') }}
        </h2>
    </x-slot>
    {{-- FLASH PLACEHOLDERS --}}
@if(session('success') || session('success_priority') || session('status'))
  <div id="flash-success"
       data-message="{{ session('success') ?? session('success_priority') ?? session('status') }}"></div>
@endif

@if(session('error'))
  <div id="flash-error" data-message="{{ session('error') }}"></div>
@endif

@if ($errors->any())
  <div id="flash-error" data-message="{{ implode(' • ', $errors->all()) }}"></div>
@endif


    <div class="py-6">
        <div class="w-full max-w-[98%] mx-auto">
            <!-- HEADER + FILTER -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 border border-gray-200 dark:border-gray-700 mb-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <!-- Judul -->
                    <div>
                        <h3 class="font-bold text-lg text-gray-800 dark:text-gray-100">
                            📋 List Order User
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Buat dan kelola order</p>
                    </div>

                    <!-- Tombol tambah -->
                    <div class="relative inline-block text-left">
                        <button id="openCreateBtn"
                                class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-semibold shadow focus:outline-none">
                            <i class="fas fa-plus mr-2"></i> Input Order
                        </button>
                    </div>
                </div>

                <!-- FILTER -->
                <form method="GET" action="{{ route('notifications.index') }}"
                      class="flex flex-wrap items-end justify-between gap-3 mt-5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md p-3">

                    <div class="flex flex-wrap gap-3 items-end">
                        <!-- Pencarian -->
                        <div>
                            <label class="text-[10px] font-semibold text-gray-600 dark:text-gray-300 block mb-1">Pencarian</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Cari nomor / nama pekerjaan..."
                                   class="w-64 sm:w-72 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-[13px] focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100">
                        </div>

                        <!-- Sorting -->
                        <div>
                            <label class="text-[10px] font-semibold text-gray-600 dark:text-gray-300 block mb-1">Sort</label>
                            <select name="sortOrder" onchange="this.form.submit()"
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-[13px] bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100">
                                <option value="latest" {{ request('sortOrder') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="oldest" {{ request('sortOrder') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                                <option value="priority-highest" {{ request('sortOrder') == 'priority-highest' ? 'selected' : '' }}>Prioritas Tertinggi</option>
                                <option value="priority-lowest" {{ request('sortOrder') == 'priority-lowest' ? 'selected' : '' }}>Prioritas Terendah</option>
                            </select>
                        </div>

                        <!-- Entries -->
                        <div>
                            <label class="text-[10px] font-semibold text-gray-600 dark:text-gray-300 block mb-1">Per halaman</label>
                            <select name="entries" onchange="this.form.submit()"
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-[13px] bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100">
                                @foreach([10,25,50,100] as $n)
                                    <option value="{{ $n }}" {{ (int) request('entries', 10) === $n ? 'selected' : '' }}>{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Tombol reset/filter (kanan) -->
                    <div class="flex gap-2">
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-[13px] shadow flex items-center">
                            <i class="fas fa-filter mr-2 text-[12px]"></i> Filter
                        </button>
                        <a href="{{ route('notifications.index') }}"
                           class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1.5 rounded-md text-[13px] shadow flex items-center">
                            <i class="fas fa-undo mr-2 text-[12px]"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

<!-- TABEL (Diperbarui: tambah kolom Informasi Order & Verifikasi Anggaran + tombol Lengkapi Dokumen) -->
<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white">
            <tr>
                <th class="px-4 py-3 text-left font-medium">Nomor Order</th>
                <th class="px-4 py-3 text-left font-medium">Nama Pekerjaan</th>
                <th class="px-4 py-3 text-left font-medium">Unit Kerja</th>
                <th class="px-4 py-3 text-left font-medium">Prioritas</th>
                <th class="px-4 py-3 text-left font-medium">Tanggal Input</th>

                <!-- NEW: Informasi Order (status + catatan) -->
                <th class="px-4 py-3 text-left font-medium">Informasi Order</th>

                <!-- NEW: Verifikasi Anggaran -->
                <th class="px-4 py-3 text-left font-medium">Verifikasi Anggaran</th>

                <th class="px-4 py-3 text-left font-medium w-40">Aksi</th>
            </tr>
            </thead>

            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($notifications as $notification)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $notification->notification_number }}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ Str::limit($notification->job_name, 80) }}</td>
                   <td class="px-4 py-3">
    <div class="text-[11px] text-gray-700 dark:text-gray-200">
        {{ $notification->unit_work }}
    </div>

    @if(!empty($notification->seksi))
        <div class="mt-1">
            <span
                class="text-[9px] font-semibold
                       bg-yellow-100 text-indigo-800 ring-1 ring-indigo-200
                       dark:bg-indigo-900/40 dark:text-indigo-300 dark:ring-indigo-700">
                <i class="fas fa-sitemap text-[9px] opacity-80"></i>
                {{ $notification->seksi }}
            </span>
        </div>
    @endif
</td>

                    <td class="px-4 py-3">
                      <span class="px-2 py-0.5 rounded text-xs {{ $notification->priority_badge['class'] }}">
  {{ $notification->priority_badge['label'] }}
</span>

                    </td>

                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $notification->input_date }}</td>
<!-- Informasi Order: status + catatan + progress (FINAL & CLEAN) -->
<td class="px-4 py-3 align-top">
    @php
        $orderBengkel = $notification->orderBengkel;
    @endphp

    <div class="flex items-start gap-3">

        {{-- STATUS UTAMA --}}
        <div class="min-w-[84px]">
            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $notification->status_badge['class'] }}">
                {{ $notification->status_badge['label'] }}
            </span>
        </div>

        {{-- DETAIL --}}
        <div class="flex-1 min-w-0">
            <div class="text-[12px] leading-tight text-gray-700 dark:text-gray-200">

                {{-- CATATAN NOTIFIKASI --}}
                <div class="mb-1 line-clamp-2">
                    <strong class="text-[11px]">Catatan:</strong>
                    <span class="text-[11px] text-gray-600 dark:text-gray-300">
                        {{ $notification->catatan ?? 'Tidak Ada Catatan' }}
                    </span>
                </div>

                {{-- RINGKASAN ORDER BENGKEL --}}
                @if($orderBengkel)
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mb-1">
                        <span class="opacity-70">Order Bengkel:</span>
                        <span class="font-medium">{{ $orderBengkel->catatan ?? '-' }}</span>
                    </div>
                @endif

                {{-- PROGRESS ORDER BENGKEL --}}
                @if($orderBengkel && $orderBengkel->progress_meta)
                    @php $p = $orderBengkel->progress_meta; @endphp

                    <div class="mt-1 flex items-center gap-2">
                        <div class="inline-flex items-center px-2 py-0.5 rounded text-[11px] {{ $p['color'] }} text-white shadow-sm">
                            <i class="{{ $p['icon'] }} text-[11px] mr-2"></i>
                            <span class="font-semibold">{{ $p['label'] }}</span>
                        </div>

                        @if(!empty($orderBengkel->keterangan_progress))
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 truncate">
                                — {{ \Illuminate\Support\Str::limit($orderBengkel->keterangan_progress, 60) }}
                            </div>
                        @endif
                    </div>

                    {{-- PROGRESS BAR --}}
                    <div class="mt-2 w-full max-w-[240px]">
                        <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                            <div
                                class="h-2 rounded-full {{ $p['percent'] === 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}"
                                style="width: {{ $p['percent'] }}%">
                            </div>
                        </div>
                        <div class="text-[10px] text-gray-500 mt-1">
                            {{ $p['percent'] }}% selesai
                        </div>
                    </div>
                @else
                    <div class="mt-1 text-[11px] text-gray-500 italic">
                        Status pekerjaan belum tersedia
                    </div>
                @endif

            </div>
        </div>
    </div>
</td>


<!-- Verifikasi Anggaran / Order Bengkel -->
<td class="px-4 py-3 align-top">
   @php
    $verif        = $notification->verifikasiAnggaran;
    $orderBengkel = $notification->orderBengkel;

    $isOrderBengkelWaitingBudget = $orderBengkel?->isWaitingBudget() ?? false;
    $showEkorinForm              = $notification->canShowEkorinForm();
@endphp


    {{-- CASE A: Jika ini triggered oleh ORDER BENGKEL WAITING BUDGET --}}
    @if($isOrderBengkelWaitingBudget)
        {{-- tampilkan ringkasan ORDER BENGKEL: status material + keterangan material --}}
        <div class="flex flex-col gap-2">
            <div>
                <span class="inline-flex items-center px-2 py-1 rounded text-white text-xs {{ ($orderBengkel->status_material ?? '') === 'Good Issue' ? 'bg-emerald-600' : 'bg-slate-500' }}">
                    {{ $orderBengkel->status_material ?? 'Pending' }}
                </span>
            </div>

            <div class="text-[11px] text-gray-600 dark:text-gray-300 leading-tight">
                <div class="font-medium"><span class="opacity-70">Keterangan :</span></div>
                <div>{{ $orderBengkel->keterangan_konfirmasi ?? '-' }}</div>
            </div>

            {{-- Form E-KORIN (simpan ke table order_bengkels) --}}
            {{-- NOTE: form action tetap route verifikasianggaran.ekorin.update (controller akan menyimpan
                      ke order_bengkels kalau kondisi order_bengkel Waiting Budget) --}}
            @if($showEkorinForm)
                <div class="mt-2 p-2 rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <form method="POST"
                          action="{{ route('verifikasianggaran.ekorin.update', $notification->notification_number) }}"
                          class="flex flex-wrap items-center gap-2">
                        @csrf
                        @method('PATCH')

                        <label class="text-[11px] text-gray-600 dark:text-gray-300">
                            No. E-KORIN
                            <input type="text" name="nomor_e_korin"
                                   value="{{ old('nomor_e_korin', $orderBengkel->nomor_e_korin ?? '') }}"
                                   placeholder="Nomor e-korin…"
                                   required
                                   class="ml-1 w-44 px-2 py-1 border rounded text-[11px]">
                        </label>

                        <label class="text-[11px] text-gray-600 dark:text-gray-300">
                            Status
                            <select name="status_e_korin" required class="ml-1 w-44 px-2 py-1 border rounded text-[11px]">
                                @php
                                    $ekorinOptions = [
                                        'waiting_korin' => 'Waiting Korin',
                                        'waiting_approval' => 'Waiting Approval',
                                        'waiting_transfer' => 'Waiting Transfer',
                                        'complete_transfer' => 'Complete Transfer'
                                    ];
                                    $currentEkorin = old('status_e_korin', $orderBengkel->status_e_korin ?? '');
                                @endphp
                                @foreach($ekorinOptions as $val => $label)
                                    <option value="{{ $val }}" {{ $currentEkorin === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <button type="submit" class="ml-auto inline-flex items-center px-3 py-1 rounded bg-indigo-600 text-white text-[11px]">
                            Simpan E-KORIN
                        </button>
                    </form>

                    @error('nomor_e_korin') <div class="mt-1 text-[11px] text-red-600">{{ $message }}</div> @enderror
                    @error('status_e_korin') <div class="mt-1 text-[11px] text-red-600">{{ $message }}</div> @enderror
                </div>
            @endif
        </div>

    {{-- CASE B: Jika ada VERIFIKASI ANGGARAN (bukan triggered dari order_bengkel) --}}
    @elseif($verif)
        <div class="flex flex-col gap-1">
            <span class="inline-flex items-center px-2 py-1 rounded text-white text-xs
                {{ $verif->status_anggaran === 'Tersedia' ? 'bg-green-500' : ($verif->status_anggaran === 'Tidak Tersedia' ? 'bg-red-500' : 'bg-yellow-400') }}">
                {{ $verif->status_anggaran ?? 'Menunggu' }}
            </span>

            {{-- Info ringkas (font kecil) --}}
            <div class="text-[11px] text-gray-600 dark:text-gray-300 leading-tight">
                <div class="font-medium">
                    <span class="opacity-70">Cost Element:</span>
                    <span class="font-mono">{{ $verif->cost_element ?? '-' }}</span>
                </div>
                <div><span class="opacity-70">Catatan:</span> {{ $verif->catatan ?? '-' }}</div>
                @if(!empty($verif->tanggal_verifikasi))
                    <div class="text-[11px] text-gray-500">Diverifikasi: {{ \Carbon\Carbon::parse($verif->tanggal_verifikasi)->format('d-m-Y') }}</div>
                @endif
            </div>

            {{-- E-KORIN: hanya saat verif->status_anggaran === 'Tidak Tersedia' --}}
            @if($verif->isUnavailable())
                <div class="mt-2 p-2 rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <form method="POST"
                          action="{{ route('verifikasianggaran.ekorin.update', $notification->notification_number) }}"
                          class="flex flex-wrap items-center gap-2">
                        @csrf
                        @method('PATCH')

                        <label class="text-[11px] text-gray-600 dark:text-gray-300">
                            No. E-KORIN
                            <input type="text" name="nomor_e_korin"
                                   value="{{ old('nomor_e_korin', $verif->nomor_e_korin ?? '') }}"
                                   required
                                   class="ml-1 w-44 px-2 py-1 border rounded text-[11px]">
                        </label>

                        <label class="text-[11px] text-gray-600 dark:text-gray-300">
                            Status
                            <select name="status_e_korin" required class="ml-1 w-44 px-2 py-1 border rounded text-[11px]">
                                @foreach(['waiting_korin','waiting_approval','waiting_transfer','complete_transfer'] as $opt)
                                    <option value="{{ $opt }}" {{ ($verif->status_e_korin ?? '') === $opt ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_',' ',$opt)) }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <button type="submit" class="ml-auto inline-flex items-center px-3 py-1 rounded bg-indigo-600 text-white text-[11px]">
                            Simpan E-KORIN
                        </button>
                    </form>

                    @error('nomor_e_korin') <div class="mt-1 text-[11px] text-red-600">{{ $message }}</div> @enderror
                    @error('status_e_korin') <div class="mt-1 text-[11px] text-red-600">{{ $message }}</div> @enderror
                </div>
            @else
                <div class="mt-2 text-[11px] text-gray-500 italic">
                    E-KORIN dapat diisi setelah <span class="font-medium">Status Dana = Tidak Tersedia</span>.
                </div>
            @endif
        </div>

    {{-- CASE C: tidak ada verif dan bukan order_bengkel waiting budget --}}
    @else
        <div class="flex flex-col gap-1">
            <span class="px-2 py-1 rounded text-white bg-gray-400 text-xs">Menunggu</span>
            <div class="text-[12px] text-gray-500 italic">Belum diverifikasi</div>
        </div>
    @endif
</td>
<td class="px-4 py-3">
    <div class="flex flex-col items-end gap-1.5">

        {{-- Baris 1: Edit & Delete --}}
        <div class="flex items-center gap-1.5">
            {{-- Edit --}}
            <button
                class="btn-edit inline-flex items-center justify-center
                       w-7 h-7 rounded
                       bg-emerald-500/90 hover:bg-emerald-600
                       text-white text-[11px]"
                data-number="{{ $notification->notification_number }}"
                title="Edit">
                <i class="fas fa-pen text-[11px]"></i>
            </button>

            {{-- Delete --}}
            <form action="{{ route('notifications.destroy', $notification->notification_number) }}"
                  method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="btn-delete inline-flex items-center justify-center
                               w-7 h-7 rounded
                               bg-rose-500/90 hover:bg-rose-600
                               text-white text-[11px]"
                        title="Hapus">
                    <i class="fas fa-trash text-[11px]"></i>
                </button>
            </form>
        </div>

        {{-- Baris 2: Lengkapi Dokumen --}}
        @if($notification->shouldShowLengkapiDokumenButton())
            <a href="{{ route('dokumen_orders.index', ['notification_number' => $notification->notification_number]) }}"
               title="Lengkapi Dokumen"
               class="
                    inline-flex items-center gap-1
                    px-2.5 py-1 rounded
                    text-[11px] font-medium
                    bg-orange-100 text-orange-700
                    hover:bg-orange-200
                    ring-1 ring-orange-200
                    animate-pulse
               ">
                <i class="fas fa-file-alt text-[11px]"></i>
                Lengkapi
            </a>
        @endif

    </div>
</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-8 text-gray-500 dark:text-gray-400">
                        Tidak ada data notifikasi.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
        {{ $notifications->withQueryString()->links() }}
    </div>
</div>

    </div>

    <!-- include partial modals -->
    @include('notifications._create_modal')
    @include('notifications._edit_modal')

  @push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="{{ asset('js/custom-notification.js') }}"></script>

  <script>
  // ---------- Modal Create: Unit -> Seksi (dependent) ----------
  function setupCreateModal() {
    const $modal = $('#modalCreate');
    const $unit  = $('#unitKerjaCreate');
    const $seksi = $('#seksiCreate');
    const $wrap  = $('#wrapSeksiCreate');

    // Helper parse aman untuk data-seksi
    const parseSeksi = (raw) => {
      if (!raw) return [];
      try { return JSON.parse(raw); }
      catch (e) {
        return String(raw).replace(/^\[|\]$/g,'')
          .split(',')
          .map(s => s.replace(/^"+|"+$/g,'').trim())
          .filter(Boolean);
      }
    };

    // Bersihkan init sebelumnya agar tidak double-init
    $unit.off('.seksi');
    if ($unit.data('select2')) $unit.select2('destroy');
    if ($seksi.data('select2')) $seksi.select2('destroy');

    // Init Select2 untuk Unit (penting: dropdownParent modal)
    $unit.select2({
      width: '100%',
      placeholder: 'Pilih Unit Kerja',
      allowClear: true,
      dropdownParent: $modal
    });

    // Isi opsi Seksi lalu init Select2-nya
    function populateSeksi(list) {
      $seksi.empty().append('<option value="">Pilih Seksi</option>');
      (list || []).forEach(s => $seksi.append(new Option(s, s)));
      $wrap.toggle(!!(list && list.length));
      $seksi.select2({
        width: '100%',
        placeholder: 'Pilih Seksi',
        allowClear: true,
        dropdownParent: $modal
      });
    }

    function onUnitChanged() {
      const el  = $unit.get(0);
      const opt = el ? el.options[el.selectedIndex] : null;
      const raw = opt ? opt.getAttribute('data-seksi') : '[]';
      populateSeksi(parseSeksi(raw));
    }

    // Bind event (native + select2), pakai namespace biar mudah di-off
    $unit.on('change.seksi select2:select.seksi', onUnitChanged);

    // Trigger sekali setelah Select2 render
    setTimeout(onUnitChanged, 0);
  }

  // Buka / tutup modal
  window.openCreate = function () {
    document.getElementById('modalCreate')?.classList.remove('hidden');
    setupCreateModal();
  };
  window.closeCreate = function () {
    document.getElementById('modalCreate')?.classList.add('hidden');
  };

  // Kaitkan tombol ke openCreate (ID: #openCreateBtn)
  document.getElementById('openCreateBtn')?.addEventListener('click', openCreate);
function setupEditModal(prefill) {
  const $modal = $('#modalEdit');
  const $form  = $('#editForm');
  const $unit  = $('#unitKerjaEdit');
  const $seksi = $('#seksiEdit');
  const $wrap  = $('#wrapSeksiEdit');

  // bersihkan double-init
  $unit.off('.seksi-edit');
  if ($unit.data('select2')) $unit.select2('destroy');
  if ($seksi.data('select2')) $seksi.select2('destroy');

  // helper parse aman
  const parseSeksi = (raw) => {
    if (!raw) return [];
    try { return JSON.parse(raw); }
    catch(e){
      return String(raw).replace(/^\[|\]$/g,'')
        .split(',').map(s=>s.replace(/^"+|"+$/g,'').trim()).filter(Boolean);
    }
  };

  function populateSeksi(list, selected=null) {
    $seksi.empty().append('<option value="">Pilih Seksi</option>');
    (list||[]).forEach(s => $seksi.append(new Option(s, s, false, s===selected)));
    $wrap.toggle(!!(list && list.length));
    $seksi.select2({ width:'100%', placeholder:'Pilih Seksi', allowClear:true, dropdownParent: $modal });
  }

  function onUnitChanged() {
    const el  = $unit.get(0);
    const opt = el ? el.options[el.selectedIndex] : null;
    const raw = opt ? opt.getAttribute('data-seksi') : '[]';
    populateSeksi(parseSeksi(raw), prefill?.seksi || null);
  }

  // init select2 unit
  $unit.select2({ width:'100%', placeholder:'Pilih Unit Kerja', allowClear:true, dropdownParent: $modal })
       .on('change.seksi-edit select2:select.seksi-edit', onUnitChanged);

  // prefill form fields
  $('#editNotifikasiNo').val(prefill.notification_number || '');
  $('#editNamaPekerjaan').val(prefill.job_name || '');
  $('#priority_edit').val(prefill.priority || 'Medium');
  $('#editInputDate').val(prefill.input_date || '');
  $('#editRencanaPemakaian').val(prefill.usage_plan_date || '');

  // set Unit, trigger load Seksi, lalu set Seksi
  if (prefill.unit_work) {
    $unit.val(prefill.unit_work).trigger('change'); // memicu onUnitChanged
  } else {
    onUnitChanged();
  }

  // set action PATCH /notifikasi/{notification_number}
  if (prefill.notification_number) {
    $form.attr('action', `{{ url('/notifikasi') }}/${encodeURIComponent(prefill.notification_number)}`);
  }
}

// buka/tutup modal edit
window.openEdit = function(prefill) {
  document.getElementById('modalEdit')?.classList.remove('hidden');
  setupEditModal(prefill || {});
};
window.closeEdit = function(){
  document.getElementById('modalEdit')?.classList.add('hidden');
};

// hook tombol Edit (pakai data-number di tabel)
document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', async (e) => {
    e.preventDefault();
    const number = btn.getAttribute('data-number');
    try {
      const res = await fetch(`{{ url('/notifikasi') }}/${encodeURIComponent(number)}/edit`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) throw new Error('Gagal ambil data');
      const data = await res.json();
      openEdit(data);
    } catch (err) {
      Swal.fire({ icon:'error', title:'Oops', text:'Tidak bisa memuat data edit.' });
    }
  });
});
  // ---------- Delete confirmation ----------
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-delete').forEach(button => {
      button.addEventListener('click', function (e) {
        e.preventDefault();
        const form = this.closest('form');
        Swal.fire({
          title: 'Yakin ingin menghapus?',
          text: 'Data akan dihapus permanen!',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Ya, hapus',
          cancelButtonText: 'Batal'
        }).then(result => { if (result.isConfirmed && form) form.submit(); });
      });
    });
  });
  </script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    // SUCCESS
    const ok = document.getElementById('flash-success');
    if (ok) {
      const msg = ok.dataset.message || 'Berhasil.';
      if (window.Swal) {
        Swal.fire({ icon:'success', title:'Sukses', text: msg, timer: 2000, showConfirmButton: false });
      }
    }

    // ERROR
    const err = document.getElementById('flash-error');
    if (err) {
      const msg = err.dataset.message || 'Terjadi kesalahan.';
      if (window.Swal) {
        Swal.fire({ icon:'error', title:'Gagal', text: msg });
      }
    }
  });
</script>

@endpush

</x-app-layout>
