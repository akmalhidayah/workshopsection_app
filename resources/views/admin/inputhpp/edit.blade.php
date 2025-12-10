<x-admin-layout>
    @php
        $baseInp = 'min-h-[26px] text-[10px] leading-[1.3] px-2 rounded-[6px] focus:ring-1';
        $btnPrimary = 'min-h-[26px] text-[10px] leading-[1.3] px-3 rounded-[6px] bg-indigo-600 text-white hover:bg-indigo-700';
        $safeCostCentre = is_array($hpp->cost_centre ?? null)
            ? json_encode($hpp->cost_centre, JSON_UNESCAPED_UNICODE)
            : ($hpp->cost_centre ?? '');
        if (is_array($hpp->description ?? null)) {
            $descPieces = array_map(function($v){
                return is_scalar($v) ? (string)$v : json_encode($v, JSON_UNESCAPED_UNICODE);
            }, $hpp->description);
            $safeDescription = implode("\n", $descPieces);
        } else {
            $safeDescription = $hpp->description ?? '';
        }
        $safeRequestingUnit = is_array($hpp->requesting_unit ?? null)
            ? json_encode($hpp->requesting_unit, JSON_UNESCAPED_UNICODE)
            : ($hpp->requesting_unit ?? '');
        $safeControllingUnit = is_array($hpp->controlling_unit ?? null)
            ? json_encode($hpp->controlling_unit, JSON_UNESCAPED_UNICODE)
            : ($hpp->controlling_unit ?? 'Unit of Workshop');
        $safeOutlineAgreement = is_array($hpp->outline_agreement ?? null)
            ? json_encode($hpp->outline_agreement, JSON_UNESCAPED_UNICODE)
            : ($hpp->outline_agreement ?? '');
        $safeRequestingNotes = '';
        if (is_array($hpp->requesting_notes ?? null)) {
            foreach ($hpp->requesting_notes as $n) {
                if (is_array($n) && array_key_exists('note', $n)) {
                    $safeRequestingNotes .= ($n['note'] ?? '-') . "\n";
                } elseif (is_string($n)) {
                    $safeRequestingNotes .= $n . "\n";
                } else {
                    $safeRequestingNotes .= json_encode($n, JSON_UNESCAPED_UNICODE) . "\n";
                }
            }
            $safeRequestingNotes = trim($safeRequestingNotes);
        } else {
            $safeRequestingNotes = $hpp->requesting_notes ?? '';
        }
        $safeTotalAmount = is_array($hpp->total_amount ?? null) ? 0 : ($hpp->total_amount ?? 0);
    @endphp

  <div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
      <!-- Kembali -->
      <a href="{{ route('admin.inputhpp.index') }}"
         class="inline-flex items-center mb-4 {{ $btnPrimary }} bg-blue-500 hover:bg-blue-600">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
      </a>

      <div class="bg-white shadow-lg rounded-lg p-6">
      @php
    $labels = [
        'createhpp1' => ['title' => 'Edit HPP di atas 250 Juta', 'sub' => 'Form HPP untuk nilai > 250 juta'],
        'createhpp2' => ['title' => 'Edit HPP di bawah 250 Juta', 'sub' => 'Form HPP untuk nilai < 250 juta'],
        'createhpp3' => ['title' => 'Edit HPP Bengkel Mesin > 250 Juta', 'sub' => 'Bengkel Mesin, > 250 jt'],
        'createhpp4' => ['title' => 'Edit HPP Bengkel Mesin < 250 Juta', 'sub' => 'Bengkel Mesin, < 250 jt'],
    ];
    $key = $hpp->source_form ?? 'createhpp1';
    $meta = $labels[$key] ?? ['title' => 'Edit HPP', 'sub' => ''];
@endphp

<h2 class="font-semibold text-2xl text-gray-800 leading-tight mb-1 border-b pb-1">
  {{ $meta['title'] }}
</h2>

        <form action="{{ route('admin.inputhpp.update_hpp1', $hpp->notification_number) }}" method="POST">
          @csrf
          @method('PUT')

          <input type="hidden" name="source_form" value="{{ $hpp->source_form ?? 'createhpp1' }}">
          <input type="hidden" name="status" value="{{ $hpp->status ?? 'draft' }}">

          <!-- Bagian 1: Informasi Dasar -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nomor Order</label>
              <input type="text" value="{{ $hpp->notification_number }}" disabled
                     class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-100 text-sm">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Cost Centre</label>
              <input type="text" name="cost_centre" value="{{ $safeCostCentre }}"
                     class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 {{ $baseInp }} text-sm">
            </div>

            <!-- Deskripsi -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
              <textarea name="description" id="deskripsi" rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm {{ $baseInp }} bg-gray-50 text-sm">{{ $safeDescription }}</textarea>
            </div>
          </div>

          <!-- Bagian 2: Unit Kerja -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">Unit Kerja Peminta</label>
              <input type="text" id="unit_kerja_peminta" name="requesting_unit"
                     value="{{ $safeRequestingUnit }}"
                     class="mt-1 block w-full border-gray-300 rounded-md shadow-sm {{ $baseInp }} bg-gray-50 text-sm">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Unit Kerja Pengendali</label>
              <input type="text" name="controlling_unit" value="{{ $safeControllingUnit }}"
                     class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
          </div>

          <!-- Outline Agreement -->
          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700">Outline Agreement (OA)</label>
            <input type="text" id="outline_agreement" name="outline_agreement"
                   value="{{ $safeOutlineAgreement }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 text-sm">
          </div>

          <!-- Kontrol Grup (sesuai script baru: 'jenis') -->
          <div class="mt-8 flex gap-3 items-center">
            <button type="button" id="tambah-jenis-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm shadow flex items-center gap-2">
              <i class="fas fa-plus"></i> Tambah Jenis (mis. Jasa)
            </button>
            <span class="text-sm text-gray-500">Gunakan nama jenis untuk mengelompokkan item (nilai ini akan dikirim ke backend sebagai jenis_item).</span>
          </div>

          <!-- Container jenis (HARUS sesuai dengan partial JS: id="jenis-container") -->
          <div id="jenis-container" class="mt-6 space-y-6"></div>

          <!-- Total Keseluruhan -->
          <div class="mt-6 border-t pt-4">
            <label class="block text-sm font-medium text-gray-700">Total Keseluruhan (Rp)</label>
            <input type="number" name="total_amount" id="total_keseluruhan"
                   value="{{ $safeTotalAmount }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 text-sm" readonly>
          </div>

          <!-- Keterangan Umum -->
          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700">Keterangan Umum</label>
            <textarea name="requesting_notes" id="requesting_notes" rows="3"
                      placeholder="Tambahkan catatan atau keterangan tambahan terkait pekerjaan..."
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm {{ $baseInp }} bg-gray-50 text-sm">{{ $safeRequestingNotes }}</textarea>
          </div>

          <!-- Submit -->
          <div class="mt-6">
            <button type="submit"
                    class="w-full md:w-auto bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition shadow">
              <i class="fas fa-save mr-1"></i> Update HPP
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Inject edit data untuk script dinamis (EDIT MODE) --}}
  <script>
    window.hppEditData = {
      uraian_pekerjaan: @json($hpp->uraian_pekerjaan ?? []),
      jenis_item       : @json($hpp->jenis_item ?? []),
      nama_item        : @json($hpp->nama_item ?? []),
      jumlah_item      : @json($hpp->jumlah_item ?? []),
      qty              : @json($hpp->qty ?? []),
      satuan           : @json($hpp->satuan ?? []),
      harga_satuan     : @json($hpp->harga_satuan ?? []),
      harga_total      : @json($hpp->harga_total ?? []),
      keterangan       : @json($hpp->keterangan ?? []),
      description      : @json($hpp->description ?? ''),
      requesting_unit  : @json($hpp->requesting_unit ?? ''),
      cost_centre      : @json($hpp->cost_centre ?? ''),
      outline_agreement: @json($hpp->outline_agreement ?? '')
    };
  </script>

  {{-- gunakan partial JS yang sama dengan create (sudah support edit mode via window.hppEditData) --}}
  @include('admin.inputhpp.partials._hpp_form_script')

  @if(session('error'))
    <script>
      Swal.fire({ icon: 'error', title: 'Oops...', text: @json(session('error')) });
    </script>
  @endif

  @if(session('success'))
    <script>
      Swal.fire({ icon: 'success', title: 'Berhasil!', text: @json(session('success')) });
    </script>
  @endif
</x-admin-layout>
