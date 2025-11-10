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

                    <!-- Dropdown Buat HPP Baru (tetap, hanya dirapikan ukuran) -->
                    <div class="relative">
                        <button id="dropdownButton" class="{{ $btnPrimary }} inline-flex items-center">
                            <i class="fas fa-plus-circle mr-2 text-[11px]"></i> Buat HPP Baru
                            <svg class="ml-2 w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="dropdownMenu"
                             class="hidden absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-md shadow-lg z-10">
                            <a href="{{ route('admin.inputhpp.create_hpp1') }}"
                               class="flex items-center px-3 py-2 text-[11px] text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                                <i class="fas fa-file-invoice-dollar text-emerald-500 mr-2"></i> HPP di Atas 250 Juta
                            </a>
                            <a href="{{ route('admin.inputhpp.create_hpp2') }}"
                               class="flex items-center px-3 py-2 text-[11px] text-slate-700 hover:bg-sky-50 hover:text-sky-700">
                                <i class="fas fa-file-invoice-dollar text-sky-500 mr-2"></i> HPP di Bawah 250 Juta
                            </a>
                            <a href="{{ route('admin.inputhpp.create_hpp3') }}"
                               class="flex items-center px-3 py-2 text-[11px] text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">
                                <i class="fas fa-tools text-indigo-500 mr-2"></i> HPP Bengkel Mesin
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
                            <option value="createhpp3" {{ request('jenis_hpp')=='createhpp3'?'selected':'' }}>Bengkel Mesin</option>
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
    {{-- Status badge sesuai jenis HPP (tetap) --}}
    @if ($data->source_form === 'createhpp1')
        @include('admin.inputhpp.partials._status_hpp1')
    @elseif ($data->source_form === 'createhpp2')
        @include('admin.inputhpp.partials._status_hpp2')
    @elseif ($data->source_form === 'createhpp3')
        @include('admin.inputhpp.partials._status_hpp3')
    @else
        <span class="text-slate-400">Tidak Diketahui</span>
    @endif

    {{-- === Token approval aktif (jika ada) === --}}
@php
    $key = (string) $data->notification_number;
    $tok = isset($activeTokens) ? $activeTokens->get($key) : null;
@endphp


    @if ($tok)
        @php
            $approvalUrl = route('approval.hpp.sign', $tok->id);
            $isExpired   = $tok->expires_at && $tok->expires_at->isPast();
        @endphp

        @if (!$isExpired)
            <div class="mt-1 flex items-center gap-2">
                <a href="{{ $approvalUrl }}" target="_blank"
                   class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-md
                          bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200 hover:bg-emerald-200">
                    <i class="fas fa-link text-[9px]"></i> Buka Link Approval
                </a>
                <button type="button"
                        class="copy-next-link inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-md
                               bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200"
                        data-link="{{ $approvalUrl }}">
                    <i class="fas fa-copy text-[9px]"></i> Salin
                </button>
                <span class="text-[10px] text-slate-500">
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
                                    <td class="px-3 py-2 text-center space-x-1">
                                        @php
                                            $pdfRoute = match($data->source_form) {
                                                'createhpp1' => route('admin.inputhpp.download_hpp1', $data->notification_number),
                                                'createhpp2' => route('admin.inputhpp.download_hpp2', $data->notification_number),
                                                'createhpp3' => route('admin.inputhpp.download_hpp3', $data->notification_number),
                                                default => '#',
                                            };
                                            $editRoute = match($data->source_form) {
                                                'createhpp1' => route('admin.inputhpp.edit_hpp1', $data->notification_number),
                                                'createhpp2' => route('admin.inputhpp.edit_hpp2', $data->notification_number),
                                                'createhpp3' => route('admin.inputhpp.edit_hpp3', $data->notification_number),
                                                default => '#',
                                            };
                                            $deleteRoute = match($data->source_form) {
                                                'createhpp1' => route('admin.inputhpp.destroy_hpp1', $data->notification_number),
                                                'createhpp2' => route('admin.inputhpp.destroy_hpp2', $data->notification_number),
                                                'createhpp3' => route('admin.inputhpp.destroy_hpp3', $data->notification_number),
                                                default => '#',
                                            };
                                        @endphp

                                        <!-- Tombol Download -->
                                        <a href="{{ $pdfRoute }}" class="action-btn bg-red-500 hover:bg-red-600" title="Download PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <!-- Tombol Edit -->
                                        <a href="{{ $editRoute }}" class="action-btn bg-yellow-500 hover:bg-yellow-600" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- Tombol Hapus -->
                                        <form action="{{ $deleteRoute }}" method="POST" class="inline-block delete-form">
                                            @csrf @method('DELETE')
                                            <button type="button" class="action-btn bg-slate-500 hover:bg-slate-600 delete-button" title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
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
    <script>

document.querySelectorAll('.copy-next-link').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const link = btn.getAttribute('data-link');
    navigator.clipboard.writeText(link).then(()=>{
      Swal.fire({icon:'success', title:'Tersalin', text:'Link approval disalin', timer:1500, showConfirmButton:false});
    }).catch(()=>{
      Swal.fire({icon:'error', title:'Gagal', text:'Tidak dapat menyalin link'});
    });
  });
});

    const dropdownButton=document.getElementById('dropdownButton');
    const dropdownMenu=document.getElementById('dropdownMenu');
    if(dropdownButton&&dropdownMenu){
        dropdownButton.addEventListener('click',()=>dropdownMenu.classList.toggle('hidden'));
        window.addEventListener('click',e=>{ if(!dropdownButton.contains(e.target)) dropdownMenu.classList.add('hidden');});
    }
    document.querySelectorAll('.delete-button').forEach(btn=>{
        btn.addEventListener('click',e=>{
            e.preventDefault(); const form=btn.closest('form');
            Swal.fire({title:'Yakin ingin menghapus?',text:'Data HPP akan dihapus permanen dan tidak dapat dikembalikan!',
                icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',cancelButtonColor:'#3085d6',
                confirmButtonText:'Ya, Hapus',cancelButtonText:'Batal'
            }).then(r=>{ if(r.isConfirmed && form) form.submit();});
        });
    });
    @if (session('success'))
      Swal.fire({icon:'success',title:'Berhasil!',text:'{{ session('success') }}',timer:2000,showConfirmButton:false});
    @endif
    @if (session('error'))
      Swal.fire({icon:'error',title:'Gagal!',text:'{{ session('error') }}'});
    @endif
    </script>
</x-admin-layout>
