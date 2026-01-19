<x-pkm-layout>
    @php
        /* ==== MINI PRESET GLOBAL (konsisten) ==== */
        $baseSel = 'min-h-[26px] text-[10px] leading-[1.3] px-2 pr-9 rounded-[6px] appearance-none focus:ring-1 truncate';
        $baseInp = 'min-h-[26px] text-[10px] leading-[1.3] px-2 rounded-[6px] focus:ring-1';
        $baseBtn = 'min-h-[26px] text-[10px] leading-[1.3] px-3 rounded-[6px]';

        // Palet warna (PKM vibes: orange / slate)
        $selOrange = $baseSel.' bg-orange-100 text-orange-800 border border-orange-600 focus:ring-orange-500 focus:border-orange-600';
        $selBlue   = $baseSel.' bg-sky-100    text-sky-800    border border-sky-600    focus:ring-sky-500    focus:border-sky-600';
        $selSlate  = $baseSel.' bg-slate-100  text-slate-800  border border-slate-600  focus:ring-slate-500  focus:border-slate-600';

        $inpSlate  = $baseInp.' bg-white border border-slate-600 focus:ring-orange-500 focus:border-orange-600';

        $btnPrimary = $baseBtn.' bg-orange-600 text-white hover:bg-orange-700';
        $btnGhost   = $baseBtn.' border border-slate-600 text-slate-700 hover:bg-slate-50';
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-sm text-gray-800 leading-tight">
            📑 Laporan Hasil Pekerjaan (LHPP) — PKM
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-[98%] mx-auto">

            {{-- HEADER + ACTION --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-3 p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-semibold text-[11px] text-slate-900 leading-tight">
                            📄 Daftar LHPP Kontrak PKM
                        </h2>
                        <p class="text-[9px] text-slate-500 leading-tight">
                            Monitoring laporan hasil pekerjaan per notifikasi & kontrak PKM.
                        </p>
                    </div>

                    {{-- Tombol Buat LHPP --}}
                    <a href="{{ route('pkm.lhpp.create') }}"
                       class="{{ $btnPrimary }} inline-flex items-center gap-2 text-[12px] px-3 py-2 rounded-md shadow-sm transition">
                        <i class="fas fa-plus-circle text-[12px]"></i>
                        Buat Form LHPP
                    </a>
                </div>

                {{-- FILTER BAR (mini, rapi) --}}
                <form action="{{ route('pkm.lhpp.index') }}" method="GET"
                      class="mt-3 flex flex-wrap items-center gap-2 overflow-x-auto whitespace-nowrap">

                    {{-- Search --}}
                    <div class="relative">
                        <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3 h-3 text-orange-500" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Cari Nomor Notif / PO / Unit..."
                               class="{{ $selOrange }} pl-6 w-64" />
                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-orange-600 text-[10px]">⌕</span>
                    </div>

                    {{-- Unit Kerja --}}
                    <div class="relative">
                        <select name="unit_kerja" class="{{ $selBlue }} w-48">
                            <option value="">Semua Unit Kerja</option>
                            @foreach($units as $u)
                                <option value="{{ $u }}" @selected(($filters['unit_kerja'] ?? '') == $u)>
                                    {{ \Illuminate\Support\Str::limit($u, 40) }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-sky-700 text-[10px]">▾</span>
                    </div>

                    {{-- Nomor PO --}}
                    <div class="relative">
                        <select name="purchase_order_number" class="{{ $selSlate }} w-52">
                            <option value="">Semua Nomor PO</option>
                            @foreach($pos as $p)
                                <option value="{{ $p }}" @selected(($filters['purchase_order_number'] ?? '') == $p)>
                                    {{ $p }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-slate-700 text-[10px]">▾</span>
                    </div>

                    {{-- Status Termin --}}
                    <div class="relative">
                        <select name="termin_status" class="{{ $selSlate }} w-52">
                            <option value="all" @selected(($filters['termin_status'] ?? 'all') == 'all')>
                                Semua Status Termin
                            </option>
                            <option value="t1_paid" @selected(($filters['termin_status'] ?? '') == 't1_paid')>
                                Termin 1 — Sudah
                            </option>
                            <option value="t1_unpaid" @selected(($filters['termin_status'] ?? '') == 't1_unpaid')>
                                Termin 1 — Belum
                            </option>
                            <option value="t2_paid" @selected(($filters['termin_status'] ?? '') == 't2_paid')>
                                Termin 2 — Sudah
                            </option>
                            <option value="t2_unpaid" @selected(($filters['termin_status'] ?? '') == 't2_unpaid')>
                                Termin 2 — Belum
                            </option>
                        </select>
                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-slate-700 text-[10px]">▾</span>
                    </div>

                    {{-- Tombol --}}
                    <button type="submit" class="{{ $btnPrimary }} ml-auto inline-flex items-center">
                        <i class="fas fa-filter mr-1 text-[10px]"></i> Terapkan
                    </button>
                    <a href="{{ route('pkm.lhpp.index') }}" class="{{ $btnGhost }} inline-flex items-center">
                        <i class="fas fa-undo mr-1 text-[10px]"></i> Reset
                    </a>
                </form>
            </div>

            {{-- TABEL LHPP --}}
            <div class="bg-white overflow-hidden shadow-sm border border-slate-200 rounded-xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-slate-200 text-[11px] text-slate-800">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Order / PO</th>
                                <th class="px-3 py-2 text-left font-semibold">Unit Kerja</th>
                                <th class="px-3 py-2 text-left font-semibold">Tanggal Selesai</th>
                                <th class="px-3 py-2 text-right font-semibold">Total Biaya</th>
                                <th class="px-3 py-2 text-left font-semibold">Status LHPP</th>
                                <th class="px-3 py-2 text-left font-semibold">Status Payment</th>
                                <th class="px-3 py-2 text-center font-semibold w-32">Aksi</th>
                            </tr>
                        </thead>

                       <tbody class="bg-white divide-y divide-slate-100">
    @forelse($lhpps as $row)
        @php
            $t1 = $row->termin1 ?? null;
            $t2 = $row->termin2 ?? null;

            // ===== STATUS TANDA TANGAN (bukan status_approve) =====
            $hasUserSign = !empty($row->manager_signature_requesting) ||
                           !empty($row->manager_signature_requesting_user_id);

            $hasWsSign   = !empty($row->manager_signature) ||
                           !empty($row->manager_signature_user_id);

            $hasPkmSign  = !empty($row->manager_pkm_signature) ||
                           !empty($row->manager_pkm_signature_user_id);

            // Default tahap
            if (! $hasUserSign && ! $hasWsSign && ! $hasPkmSign) {
                $signStage = 'waiting_user';
            } elseif ($hasUserSign && ! $hasWsSign && ! $hasPkmSign) {
                $signStage = 'waiting_workshop';
            } elseif ($hasUserSign && $hasWsSign && ! $hasPkmSign) {
                $signStage = 'waiting_pkm';
            } elseif ($hasUserSign && $hasWsSign && $hasPkmSign) {
                $signStage = 'completed';
            } else {
                // kasus “aneh” (misal workshop sudah sign tapi user belum, dll)
                $signStage = 'partial';
            }

            $signLabel = match($signStage) {
                'waiting_user'      => 'Menunggu TTD Manager User',
                'waiting_workshop'  => 'Menunggu TTD Manager Workshop',
                'waiting_pkm'       => 'Menunggu TTD Manager PKM',
                'completed'         => 'Dokumen Telah di Tandatangani',
                'partial'           => 'Proses Tanda Tangan',
                default             => 'Proses Tanda Tangan',
            };

            $signClr = match($signStage) {
                'waiting_user'      => 'bg-slate-100 text-slate-800 ring-slate-200',
                'waiting_workshop'  => 'bg-amber-100 text-amber-800 ring-amber-200',
                'waiting_pkm'       => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
                'completed'         => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
                'partial'           => 'bg-sky-100 text-sky-800 ring-sky-200',
                default             => 'bg-slate-100 text-slate-800 ring-slate-200',
            };

            // Token approval aktif (opsional, kalau controller kirim $activeTokens)
            $key       = (string) $row->notification_number;
            $tok       = isset($activeTokens) ? $activeTokens->get($key) : null;
            $hasTok    = (bool) $tok;
            $isExpired = $hasTok && $tok->expires_at && $tok->expires_at->isPast();
        @endphp

        <tr class="hover:bg-slate-50 transition">
            {{-- Order / PO --}}
            <td class="px-3 py-2">
                <div class="font-semibold text-slate-900">
                    {{ $row->notification_number }}
                </div>
                <div class="text-[10px] text-slate-500">
                    PO:
                    <span class="font-medium text-slate-700">
                        {{ $row->purchase_order_number ?? '-' }}
                    </span>
                </div>
            </td>

            {{-- Unit Kerja --}}
            <td class="px-3 py-2">
                <div class="text-slate-800">{{ $row->unit_kerja ?? '-' }}</div>
                @php $seksi = $row->seksi ?? null; @endphp
                @if(!empty($seksi))
                    <div class="mt-1">
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                                     bg-slate-100 text-slate-700 ring-1 ring-slate-200">
                            <i class="fas fa-sitemap text-[9px] opacity-80"></i>
                            {{ $seksi }}
                        </span>
                    </div>
                @endif
            </td>

            {{-- Tanggal Selesai --}}
            <td class="px-3 py-2">
                @if($row->tanggal_selesai)
                    {{ \Carbon\Carbon::parse($row->tanggal_selesai)->format('d-m-Y') }}
                    ({{ $row->waktu_pengerjaan ? $row->waktu_pengerjaan.' Hari' : '-' }})
                @else
                    <span class="text-[10px] text-slate-400">-</span>
                @endif
            </td>

            {{-- Total Biaya --}}
            <td class="px-3 py-2 text-right">
                <div class="font-semibold">
                    Rp {{ number_format($row->total_biaya ?? 0, 2, ',', '.') }}
                </div>
            </td>

            {{-- Status Tanda Tangan & Token Aktif --}}
            <td class="px-3 py-2">
                {{-- Badge status tanda tangan --}}
                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] ring-1 {{ $signClr }}">
                    <i class="fas fa-signature text-[9px]"></i>
                    {{ $signLabel }}
                </div>

           {{-- Info token approval aktif (jika ada) --}}
@if($hasTok && $signStage !== 'completed')
    @if(!$isExpired)
        <div class="mt-1 flex items-center gap-2 text-[10px]">
            <button type="button"
                    class="copy-next-link inline-flex items-center gap-1 px-2 py-0.5 rounded-md
                           bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200"
                    data-link="{{ route('approval.lhpp.sign', $tok->id) }}">
                <i class="fas fa-copy text-[9px]"></i> Salin Link Approve
            </button>
            <span class="font-medium text-slate-700">
                kadaluarsa: {{ $tok->expires_at?->format('d/m H:i') }}
            </span>
        </div>
    @else
        <div class="mt-1 inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-md
                    bg-amber-100 text-amber-800 ring-1 ring-amber-200">
            <i class="fas fa-clock text-[9px]"></i> Token kedaluwarsa
        </div>
    @endif
@endif

            </td>

            {{-- Status Payment --}}
            <td class="px-3 py-2">
                <div class="flex flex-col gap-1">
                    <div>
                        <span class="text-[10px] text-slate-600">Termin 1:</span>
                        @if($t1 === 'sudah')
                            <span class="inline-block ml-1 px-2 py-0.5 text-[10px] bg-emerald-100 text-emerald-800 rounded-md">
                                Sudah Dibayar
                            </span>
                        @else
                            <span class="inline-block ml-1 px-2 py-0.5 text-[10px] bg-amber-100 text-amber-800 rounded-md">
                                Belum Dibayar
                            </span>
                        @endif
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-600">Termin 2:</span>
                        @if($t2 === 'sudah')
                            <span class="inline-block ml-1 px-2 py-0.5 text-[10px] bg-emerald-100 text-emerald-800 rounded-md">
                                Sudah Dibayar
                            </span>
                        @else
                            <span class="inline-block ml-1 px-2 py-0.5 text-[10px] bg-amber-100 text-amber-800 rounded-md">
                                Belum Dibayar
                            </span>
                        @endif
                    </div>
                </div>
            </td>

            {{-- Aksi --}}
            <td class="px-3 py-2 text-center">
                <div class="flex items-center justify-center gap-1">
                    {{-- Edit --}}
                    <a href="{{ route('pkm.lhpp.edit', $row->notification_number) }}"
                       class="action-btn bg-emerald-500 hover:bg-emerald-600"
                       title="Edit LHPP">
                        <i class="fas fa-edit"></i>
                    </a>

                    {{-- PDF --}}
                    <a href="{{ route('pkm.lhpp.download_pdf', $row->notification_number) }}"
                       class="action-btn bg-blue-500 hover:bg-blue-600"
                       title="Download PDF LHPP">
                        <i class="fas fa-file-pdf"></i>
                    </a>

                    {{-- Hapus --}}
                    <form action="{{ route('pkm.lhpp.destroy', $row->notification_number) }}"
                          method="POST"
                          class="inline-block delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                class="action-btn bg-red-500 hover:bg-red-600 delete-button"
                                title="Hapus LHPP">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="px-4 py-8 text-center text-slate-500 text-[11px]">
                Belum ada data LHPP.
                <a href="{{ route('pkm.lhpp.create') }}" class="text-orange-500 underline">
                    Buat LHPP baru
                </a>
            </td>
        </tr>
    @endforelse
</tbody>

                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="mt-3 text-center text-[10px]">
                    {{ $lhpps->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .action-btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:26px;
            height:26px;
            border-radius:6px;
            color:white;
            transition:.2s;
        }
        table th,table td{white-space:nowrap}
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {

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

            // COPY LINK TOKEN (kalau ada token PKM)
            document.querySelectorAll('.copy-next-link').forEach(btn => {
                btn.addEventListener('click', (ev) => {
                    const link = ev.currentTarget.getAttribute('data-link');
                    if (!link) return;
                    copyTextToClipboard(link).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Tersalin',
                            text: 'Link approval LHPP disalin',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }).catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Tidak dapat menyalin link'
                        });
                    });
                });
            });

            // DELETE CONFIRMATION
            document.querySelectorAll('.delete-button').forEach(btn => {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    const form = btn.closest('form');
                    Swal.fire({
                        title: 'Hapus LHPP ini?',
                        text: 'Data LHPP akan dihapus permanen dan tidak dapat dikembalikan!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
                    }).then(r => {
                        if (r.isConfirmed && form) form.submit();
                    });
                });
            });

            // FLASH MESSAGE
            @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 2000,
                showConfirmButton: false
            });
            @endif

            @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}'
            });
            @endif
        });
    </script>
</x-pkm-layout>
