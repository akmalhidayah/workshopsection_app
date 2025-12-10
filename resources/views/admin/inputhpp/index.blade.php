<x-admin-layout>
    @php
        /* ==== MINI PRESET GLOBAL (konsisten) ==== */
        $baseSel = 'min-h-[26px] text-[10px] leading-[1.3] px-2 pr-9 rounded-[6px] appearance-none focus:ring-1 truncate';
        $baseInp = 'min-h-[26px] text-[10px] leading-[1.3] px-2 rounded-[6px] focus:ring-1';
        $baseBtn = 'min-h-[26px] text-[10px] leading-[1.3] px-3 rounded-[6px]';

        // Palet warna
        $selIndigo = $baseSel.' bg-indigo-100 text-indigo-800 border border-indigo-600 focus:ring-indigo-500 focus:border-indigo-600';
        $selBlue   = $baseSel.' bg-sky-100    text-sky-800    border border-sky-600    focus:ring-sky-500    focus:border-sky-600';
        $selGreen  = $baseSel.' bg-emerald-100 text-emerald-800 border border-emerald-600 focus:ring-emerald-500 focus:border-emerald-600';
        $selSlate  = $baseSel.' bg-slate-100  text-slate-800  border border-slate-600  focus:ring-slate-500  focus:border-slate-600';

        $inpSlate  = $baseInp.' bg-white border border-slate-600 focus:ring-indigo-500 focus:border-indigo-600';

        // Utility chip tombol menu
        $btnPrimary = $baseBtn.' bg-indigo-600 text-white hover:bg-indigo-700';
        $btnGhost   = $baseBtn.' border border-slate-600 text-slate-700 hover:bg-slate-50';
    @endphp

    <div class="py-6">
        <!-- WRAPPER UTAMA -->
        <div class="w-full max-w-[98%] mx-auto">

            <!-- HEADER + ACTION -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-3 p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-semibold text-[11px] text-slate-900 leading-tight">
                            📄 Form Harga Perkiraan Perancangan (HPP)
                        </h2>
                        <p class="text-[9px] text-slate-500 leading-tight">Kelola dan monitoring semua dokumen HPP</p>
                    </div>

                    <!-- Dropdown Buat HPP Baru — versi modern & rapi -->
                    <div class="relative" x-data="{}">
                        <button id="dropdownButton"
                                aria-haspopup="true"
                                aria-expanded="false"
                                class="{{ $btnPrimary }} inline-flex items-center gap-2 text-[12px] px-3 py-2 rounded-md shadow-sm transition"
                                type="button">
                            <i class="fas fa-plus-circle text-[12px]"></i>
                            Buat HPP Baru
                            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Menu Dropdown -->
                        <div id="dropdownMenu"
                             class="hidden absolute right-0 mt-2 w-60 bg-white border border-slate-200 rounded-lg shadow-xl z-20 overflow-hidden"
                             role="menu" aria-label="Buat HPP Baru Menu">
                            <a href="{{ route('admin.inputhpp.create_hpp1') }}"
                               role="menuitem"
                               class="flex items-center px-3 py-2 text-[12px] text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                <i class="fas fa-file-contract text-emerald-500 w-4 mr-2"></i>
                                HPP di Atas 250 Juta
                            </a>

                            <a href="{{ route('admin.inputhpp.create_hpp2') }}"
                               role="menuitem"
                               class="flex items-center px-3 py-2 text-[12px] text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition">
                                <i class="fas fa-file-invoice-dollar text-sky-500 w-4 mr-2"></i>
                                HPP di Bawah 250 Juta
                            </a>

                            <a href="{{ route('admin.inputhpp.create_hpp3') }}"
                               role="menuitem"
                               class="flex items-center px-3 py-2 text-[12px] text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                                <i class="fas fa-tools text-indigo-500 w-4 mr-2"></i>
                                HPP Bengkel Mesin > 250 Juta
                            </a>

                            <a href="{{ route('admin.inputhpp.create_hpp4') }}"
                               role="menuitem"
                               class="flex items-center px-3 py-2 text-[12px] text-slate-700 hover:bg-amber-50 hover:text-amber-700 transition">
                                <i class="fas fa-cogs text-amber-500 w-4 mr-2"></i>
                                HPP Bengkel Mesin < 250 Juta
                            </a>
                        </div>
                    </div>

                </div>

                <!-- FILTER (konsisten mini + warna) -->
                <form method="GET" action="{{ route('admin.inputhpp.index') }}"
                      class="mt-3 flex items-center gap-2 overflow-x-auto whitespace-nowrap">

                    <!-- Search (Indigo) -->
                    <div class="relative">
                        <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3 h-3 text-indigo-500" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari Nomor Order / Unit..."
                               class="{{ $selIndigo }} pl-6 w-64" />
                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-indigo-600 text-[10px]">⌕</span>
                    </div>

                    <!-- Jenis HPP (Green) -->
                    <div class="relative">
                        <select name="jenis_hpp" class="{{ $selGreen }} w-40">
                            <option value="">Semua Jenis HPP</option>
                            <option value="createhpp1" {{ request('jenis_hpp')=='createhpp1'?'selected':'' }}>Atas 250 Juta</option>
                            <option value="createhpp2" {{ request('jenis_hpp')=='createhpp2'?'selected':'' }}>Bawah 250 Juta</option>
                            <option value="createhpp3" {{ request('jenis_hpp')=='createhpp3'?'selected':'' }}>Bengkel Mesin Atas 250 Juta</option>
                             <option value="createhpp4" {{ request('jenis_hpp')=='createhpp4'?'selected':'' }}>Bengkel Mesin Bawah 250 Juta</option>
                        </select>
                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-emerald-700 text-[10px]">▾</span>
                    </div>

                    <!-- Unit Kerja (Sky) -->
                    <div class="relative">
                        <select name="unit_kerja" class="{{ $selBlue }} w-56">
                            <option value="">Semua Unit Kerja</option>
                            @foreach($unitKerjaOptions as $unit)
                                <option value="{{ $unit }}" {{ request('unit_kerja')==$unit?'selected':'' }}>
                                    {{ Str::limit($unit, 40) }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-sky-700 text-[10px]">▾</span>
                    </div>

                    <!-- Tombol -->
                    <button type="submit" class="{{ $btnPrimary }} ml-auto inline-flex items-center">
                        <i class="fas fa-filter mr-1 text-[10px]"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.inputhpp.index') }}" class="{{ $btnGhost }} inline-flex items-center">
                        <i class="fas fa-undo mr-1 text-[10px]"></i> Reset
                    </a>
                </form>
            </div>

            <!-- TABEL (tetap) -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-slate-200 text-[11px] text-slate-800">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                            <tr class="uppercase">
                                <th class="px-3 py-2 text-left font-semibold">Nomor Order</th>
                                <th class="px-3 py-2 text-left font-semibold">Rencana Pemakaian</th>
                                <th class="px-3 py-2 text-left font-semibold">Unit Kerja</th>
                                <th class="px-3 py-2 text-left font-semibold">Status Approval</th>
                                <th class="px-3 py-2 text-left font-semibold">Total HPP</th>
                                <th class="px-3 py-2 text-center font-semibold w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($hpp as $data)
                                <tr class="hover:bg-slate-50 border-b border-slate-100 transition">
                                    <td class="px-3 py-2 font-medium text-slate-900">{{ $data->notification_number }}</td>
                                    <td class="px-3 py-2">{{ $data->notification->usage_plan_date ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        <div class="text-slate-800">
                                            {{ $data->notification->unit_work ?? '-' }}
                                        </div>
                                        @php $seksi = optional($data->notification)->seksi; @endphp
                                        @if(!empty($seksi))
                                            <div class="mt-1">
                                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                                                             bg-indigo-100 text-indigo-800 ring-1 ring-indigo-200">
                                                    <i class="fas fa-sitemap text-[9px] opacity-80"></i>
                                                    {{ $seksi }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                 <td class="px-3 py-2">
    {{-- Status badge sesuai jenis HPP (logika persetujuan & upload direktur ada di partial) --}}
    @if ($data->source_form === 'createhpp1')
        @include('admin.inputhpp.partials._status_hpp1')
    @elseif ($data->source_form === 'createhpp2')
        @include('admin.inputhpp.partials._status_hpp2')
    @elseif ($data->source_form === 'createhpp3')
        @include('admin.inputhpp.partials._status_hpp3')
    @elseif ($data->source_form === 'createhpp4')
        @include('admin.inputhpp.partials._status_hpp4')
    @else
        <span class="text-slate-400">Tidak Diketahui</span>
    @endif

    {{-- === Token approval aktif (jika ada) === --}}
    @php
        $key      = (string) $data->notification_number;
        $tok      = isset($activeTokens) ? $activeTokens->get($key) : null;
        $hasTok   = (bool) $tok;
        $isExpired = $hasTok && $tok->expires_at && $tok->expires_at->isPast();
    @endphp

    @if ($hasTok)
        @if (!$isExpired)
            <div class="mt-1 flex items-center gap-2 text-[10px]">

                {{-- Tombol SALIN hanya muncul jika BELUM ada file direktur --}}
                @if (empty($data->director_uploaded_file))
                    <button type="button"
                            class="copy-next-link inline-flex items-center gap-1 px-2 py-0.5 rounded-md
                                   bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200"
                            data-link="{{ route('approval.hpp.sign', $tok->id) }}">
                        <i class="fas fa-copy text-[9px]"></i> Salin
                    </button>
                @endif
                <form action="{{ route('admin.inputhpp.reissue_token', $data->notification_number) }}" method="POST" class="inline-block reissue-form">
    @csrf
    <button type="button" class="reissue-btn action-btn bg-indigo-500 hover:bg-indigo-600" data-notif="{{ $data->notification_number }}" title="Generate Ulang Token">
        <i class="fas fa-redo"></i>
    </button>
</form>


                <span class="text-slate-500">
                    kadaluarsa: {{ $tok->expires_at->format('d/m H:i') }}
                </span>
            </div>
        @else
            <div class="mt-1 inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-md
                        bg-amber-100 text-amber-800 ring-1 ring-amber-200">
                <i class="fas fa-clock text-[9px]"></i> Token kedaluwarsa — perlu re-issue
            </div>
        @endif
    @elseif ($data->status === 'submitted')
        <div class="mt-1 text-[10px] text-slate-400">
            Menunggu token (belum tersedia atau sudah kedaluwarsa).
        </div>
    @endif
</td>


                                    <td class="px-3 py-2">{{ number_format($data->total_amount, 0, ',', '.') }}</td>
{{-- status cell sudah ada di file partial lain --}}
@include('admin.inputhpp.partials._actions_hpp1')
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-slate-500 py-4">Tidak ada data HPP tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-center text-[10px]">
                    {{ $hpp->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- STYLE (unchanged, cuma warna diselaraskan) -->
    <style>
        .action-btn{
            display:inline-flex;align-items:center;justify-content:center;
            width:26px;height:26px;border-radius:6px;color:white;transition:.2s
        }
        table th,table td{white-space:nowrap}
    </style>

    <!-- SCRIPT (tetap) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- SCRIPT (tetap + sudah ditambah upload direktur) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

  /* ============================================
     COPY LINK TOKEN
  ============================================ */
  document.querySelectorAll('.copy-next-link').forEach(btn=>{
    btn.addEventListener('click', (ev)=>{
      const link = ev.currentTarget.getAttribute('data-link');
      navigator.clipboard.writeText(link).then(()=>{
        Swal.fire({icon:'success', title:'Tersalin', text:'Link approval disalin', timer:1500, showConfirmButton:false});
      }).catch(()=>{
        Swal.fire({icon:'error', title:'Gagal', text:'Tidak dapat menyalin link'});
      });
    });
  });



  /* ============================================
     DROPDOWN BUTTON (improved)
  ============================================ */
  const dropdownButton = document.getElementById('dropdownButton');
  const dropdownMenu = document.getElementById('dropdownMenu');

  if (dropdownButton && dropdownMenu) {
    dropdownButton.addEventListener('click', (e) => {
      e.stopPropagation();
      const isHidden = dropdownMenu.classList.contains('hidden');
      if (isHidden) {
        dropdownMenu.classList.remove('hidden');
        dropdownButton.setAttribute('aria-expanded','true');
      } else {
        dropdownMenu.classList.add('hidden');
        dropdownButton.setAttribute('aria-expanded','false');
      }
    });

    dropdownMenu.addEventListener('click', (e) => e.stopPropagation());

    window.addEventListener('click', (e) => {
      if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.add('hidden');
        dropdownButton.setAttribute('aria-expanded','false');
      }
    });

    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        dropdownMenu.classList.add('hidden');
        dropdownButton.setAttribute('aria-expanded','false');
        dropdownButton.focus();
      }
    });
  }

// Delegate: reissue button
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.reissue-btn');
  if (!btn) return;
  e.preventDefault();

  const notif = btn.getAttribute('data-notif');
  if (!notif) return;

  // confirm
  const { isConfirmed } = await Swal.fire({
    title: 'Generate ulang token?',
    text: `Token sebelumnya akan dibatalkan. Lanjutkan untuk ${notif}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Generate',
    cancelButtonText: 'Batal'
  });

  if (!isConfirmed) return;

  // show loading
  Swal.fire({
    title: 'Menerbitkan token...',
    didOpen: () => Swal.showLoading(),
    allowOutsideClick: false,
  });

  try {
    const res = await fetch(`{{ url('/admin/inputhpp') }}/${notif}/reissue-token`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({}) // optional: sign_type or target_user_id
    });

    const data = await res.json();
    Swal.close();

    if (!res.ok) {
      Swal.fire({ icon: 'error', title: 'Gagal', text: data.error || 'Gagal menerbitkan token.' });
      return;
    }

    // copy link to clipboard
    const url = data.url;
    try {
      await navigator.clipboard.writeText(url);
      Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Token diterbitkan & link disalin ke clipboard.' });
    } catch (err) {
      // fallback: show link in modal so user copy manual
      Swal.fire({
        title: 'Token diterbitkan',
        html: `<div class="text-left"><p>Link: <a href="${url}" target="_blank">${url}</a></p><textarea rows="3" style="width:100%">${url}</textarea></div>`,
        confirmButtonText: 'Tutup'
      });
    }

    // optionally reload page or update the token cell via DOM to show new expiration
    setTimeout(() => location.reload(), 1200);

  } catch (err) {
    Swal.close();
    console.error(err);
    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' });
  }
});


  /* ============================================
     DELETE CONFIRMATION
  ============================================ */
  document.querySelectorAll('.delete-button').forEach(btn=>{
    btn.addEventListener('click', e=>{
        e.preventDefault();
        const form = btn.closest('form');
        Swal.fire({
            title:'Yakin ingin menghapus?',
            text:'Data HPP akan dihapus permanen dan tidak dapat dikembalikan!',
            icon:'warning',
            showCancelButton:true,
            confirmButtonColor:'#d33',
            cancelButtonColor:'#3085d6',
            confirmButtonText:'Ya, Hapus',
            cancelButtonText:'Batal'
        }).then(r=>{
            if(r.isConfirmed && form) form.submit();
        });
    });
  });



  /* ============================================
     UPLOAD DOKUMEN DIREKTUR (FITUR BARU)
     Tidak mengubah logic lama sama sekali.
  ============================================ */
  document.querySelectorAll('.director-upload-btn').forEach(btn => {
    btn.addEventListener('click', (ev) => {
      const form = ev.currentTarget.closest('form');
      if (!form) return;

      const input = form.querySelector('.director-input');
      if (!input) return;

      // trigger file select
      input.click();

      input.onchange = function () {
        if (!input.files || input.files.length === 0) return;

        const file = input.files[0];
        const maxMB = 10;

        // size check
        if (file.size > maxMB * 1024 * 1024) {
          Swal.fire({
            icon: 'error',
            title: 'File terlalu besar',
            text: 'Maksimum 10MB diperbolehkan.'
          });
          input.value = '';
          return;
        }

        // pdf only
        if (file.type !== 'application/pdf') {
          Swal.fire({
            icon: 'error',
            title: 'Format tidak didukung',
            text: 'Hanya file PDF yang diperbolehkan.'
          });
          input.value = '';
          return;
        }

        Swal.fire({
          title: 'Upload HPP Direktur?',
          text: 'File akan disimpan sebagai dokumen resmi direktur.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, Upload',
          cancelButtonText: 'Batal'
        }).then(res => {
          if (res.isConfirmed) {
            form.submit();
          } else {
            input.value = '';
          }
        });
      };
    });
  });



  /* ============================================
     FLASH MESSAGE (success / error)
  ============================================ */
  @if (session('success'))
    Swal.fire({icon:'success',title:'Berhasil!',text:'{{ session('success') }}',timer:2000,showConfirmButton:false});
  @endif

  @if (session('error'))
    Swal.fire({icon:'error',title:'Gagal!',text:'{{ session('error') }}'});
  @endif

});
</script>

</x-admin-layout>
