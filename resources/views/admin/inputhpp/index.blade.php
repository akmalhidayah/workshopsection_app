<x-admin-layout>
    <div class="py-6">
        <div class="w-full max-w-[98%] mx-auto">

            <!-- HEADER + ACTION -->
            <div class="admin-card p-5 mb-4">
                <div class="admin-header">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h1 class="admin-title">Form Harga Perkiraan Perancangan (HPP)</h1>
                            <p class="admin-subtitle">Kelola dan monitoring semua dokumen HPP</p>
                        </div>
                    </div>

                    <div class="relative admin-actions" x-data="{}">
                        <button id="dropdownButton"
                                aria-haspopup="true"
                                aria-expanded="false"
                                class="admin-btn admin-btn-primary"
                                type="button">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            Buat HPP Baru
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </button>

                        <!-- Menu Dropdown -->
                        <div id="dropdownMenu"
                             class="hidden absolute right-0 mt-2 w-60 bg-white border border-slate-200 rounded-xl shadow-xl z-20 overflow-hidden"
                             role="menu" aria-label="Buat HPP Baru Menu">
                            <a href="{{ route('admin.inputhpp.create_hpp1') }}"
                               role="menuitem"
                               class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                <i data-lucide="file-text" class="w-4 h-4 text-emerald-600"></i>
                                HPP di Atas 250 Juta
                            </a>

                            <a href="{{ route('admin.inputhpp.create_hpp2') }}"
                               role="menuitem"  
                               class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition">
                                <i data-lucide="file-down" class="w-4 h-4 text-sky-600"></i>
                                HPP di Bawah 250 Juta
                            </a>

                            <a href="{{ route('admin.inputhpp.create_hpp3') }}"
                               role="menuitem"
                               class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                                <i data-lucide="settings" class="w-4 h-4 text-blue-600"></i>
                                HPP Bengkel Mesin > 250 Juta
                            </a>

                            <a href="{{ route('admin.inputhpp.create_hpp4') }}"
                               role="menuitem"
                               class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-amber-50 hover:text-amber-700 transition">
                                <i data-lucide="settings" class="w-4 h-4 text-amber-600"></i>
                                HPP Bengkel Mesin < 250 Juta
                            </a>
                            <a href="{{ route('admin.inputhpp.create_hpp5') }}"
                               role="menuitem"
                               class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                <i data-lucide="file-text" class="w-4 h-4 text-emerald-600"></i>
                                HPP Khusus di atas 250 Juta
                            </a>
                            <a href="{{ route('admin.inputhpp.create_hpp6') }}"
                               role="menuitem"
                               class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition">
                                <i data-lucide="file-text" class="w-4 h-4 text-indigo-600"></i>
                                HPP Khusus di bawah 250 Juta
                            </a>
                           
                        </div>
                    </div>
                </div>

                <!-- FILTER -->
                <form method="GET" action="{{ route('admin.inputhpp.index') }}"
                      class="admin-filter mt-4 overflow-x-auto whitespace-nowrap">

                    <!-- Search -->
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari Nomor Order / Unit..."
                               class="admin-input pl-9 w-64" />
                    </div>

                    <!-- Jenis HPP -->
                    <div class="relative">
                        <select name="jenis_hpp" class="admin-select w-52">
                            <option value="">Semua Jenis HPP</option>
                            <option value="createhpp1" {{ request('jenis_hpp')=='createhpp1'?'selected':'' }}>Atas 250 Juta</option>
                            <option value="createhpp2" {{ request('jenis_hpp')=='createhpp2'?'selected':'' }}>Bawah 250 Juta</option>
                            <option value="createhpp3" {{ request('jenis_hpp')=='createhpp3'?'selected':'' }}>Bengkel Mesin Atas 250 Juta</option>
                            <option value="createhpp4" {{ request('jenis_hpp')=='createhpp4'?'selected':'' }}>Bengkel Mesin Bawah 250 Juta</option>
                            <option value="createhpp5" {{ request('jenis_hpp')=='createhpp5'?'selected':'' }}>HPP Khusus di atas 250 Juta</option>
                            <option value="createhpp6" {{ request('jenis_hpp')=='createhpp6'?'selected':'' }}>HPP Khusus di bawah 250 Juta</option>
                        </select>
                    </div>

                    <!-- Unit Kerja -->
                    <div class="relative">
                        <select name="unit_kerja" class="admin-select w-60">
                            <option value="">Semua Unit Kerja</option>
                            @foreach($unitKerjaOptions as $unit)
                                <option value="{{ $unit }}" {{ request('unit_kerja')==$unit?'selected':'' }}>
                                    {{ Str::limit($unit, 40) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tombol -->
                    <button type="submit" class="admin-btn admin-btn-primary ml-auto">
                        <i data-lucide="filter" class="w-4 h-4"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.inputhpp.index') }}" class="admin-btn admin-btn-ghost">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                    </a>
                </form>
            </div>

<!-- TABEL (tetap) -->
            <div class="admin-card p-4">
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
    @elseif ($data->source_form === 'createhpp5')
        @include('admin.inputhpp.partials._status_hpp5')
    @elseif ($data->source_form === 'createhpp6')
        @include('admin.inputhpp.partials._status_hpp6')
    @else
        <span class="text-slate-400">Tidak Diketahui</span>
    @endif

    {{-- === Token approval aktif (jika ada) === --}}
    @php
        $key      = (string) $data->notification_number;
        $tok      = isset($activeTokens) ? $activeTokens->get($key) : null;
        $hasTok   = (bool) $tok;
        $isExpired = $hasTok && $tok->expires_at && $tok->expires_at->isPast();
        $showTokenActions = true;
    @endphp

    @if ($showTokenActions)
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
                    <i class="fas fa-clock text-[9px]"></i> Token kedaluwarsa – perlu re-issue
                </div>
            @endif
        @elseif ($data->status === 'submitted')
            <div class="mt-1 text-[10px] text-slate-400">
                Menunggu token (belum tersedia atau sudah kedaluwarsa).
            </div>
        @endif
    @endif
</td>


                                    <td class="px-3 py-2">{{ number_format($data->total_amount, 0, ',', '.') }}</td>
{{-- status cell sudah ada di file partial lain --}}
@include('admin.inputhpp.partials._actions_hpp_generic', ['data' => $data])
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
  function copyTextToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text);
    }
    const temp = document.createElement('textarea');
    temp.value = text;
    temp.setAttribute('readonly', '');
    temp.style.position = 'absolute';
    temp.style.left = '-9999px';
    document.body.appendChild(temp);
    temp.select();
    temp.setSelectionRange(0, temp.value.length);
    const ok = document.execCommand('copy');
    document.body.removeChild(temp);
    return ok ? Promise.resolve() : Promise.reject();
  }

  document.querySelectorAll('.copy-next-link').forEach(btn=>{
    btn.addEventListener('click', (ev)=>{
      const link = ev.currentTarget.getAttribute('data-link');
      if (!link) return;
      copyTextToClipboard(link).then(()=>{
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
