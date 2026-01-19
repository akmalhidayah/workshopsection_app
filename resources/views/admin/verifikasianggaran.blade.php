<x-admin-layout>
@php
    /* MINI PRESET - no logic changed */
    $baseSel = 'min-h-[26px] text-[10px] leading-[1.3] px-2 pe-9 rounded-[6px] appearance-none focus:ring-1 truncate';
    $baseInp = 'h-6 text-[9px] leading-[1.15] px-2 rounded-[6px] focus:ring-1';
    $baseBtn = 'h-6 text-[9px] leading-[1.15] px-2 rounded-[6px]';

    $selBlue   = $baseSel.' bg-white text-sky-700 border border-sky-600 focus:ring-sky-500 focus:border-sky-600';
    $selIndigo = $baseSel.' bg-white text-indigo-700 border border-indigo-600 focus:ring-indigo-500 focus:border-indigo-600';
    $selGreen  = $baseSel.' bg-white text-emerald-700 border border-emerald-600 focus:ring-emerald-500 focus:border-emerald-600';
    $selAmber  = $baseSel.' bg-white text-amber-700 border border-amber-600 focus:ring-amber-500 focus:border-amber-600';
    $selSlate  = $baseSel.' bg-white text-slate-800 border border-slate-600 focus:ring-slate-500 focus:border-slate-600';

    $inpSlate  = $baseInp.' bg-white border border-slate-600 focus:ring-indigo-500 focus:border-indigo-600';

    $danaColor = fn($v)=>match($v){
        'Tersedia'        => 'bg-emerald-600 text-white border-emerald-700',
        'Tidak Tersedia'  => 'bg-rose-600    text-white border-rose-700',
        'Menunggu'        => 'bg-amber-500   text-white border-amber-600',
        default           => 'bg-slate-600   text-white border-slate-700',
    };

    $ekorinColor = fn($v)=>match($v){
        'waiting_korin'     => 'bg-amber-500 text-white border-amber-600',
        'waiting_approval'  => 'bg-amber-600 text-white border-amber-700',
        'waiting_transfer'  => 'bg-indigo-600 text-white border-indigo-700',
        'complete_transfer' => 'bg-emerald-600 text-white border-emerald-700',
        default             => 'bg-slate-600 text-white border-slate-700',
    };
@endphp

<div class="py-6">
    <div class="w-full max-w-none mx-auto">

        <!-- HEADER + FILTER -->
        <div class="admin-card p-5 mb-4">
            <div class="admin-header">
                <div class="flex items-center gap-3">
                    <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-emerald-600 text-white">
                        <i data-lucide="wallet" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h1 class="admin-title">Verifikasi Anggaran</h1>
                        <p class="admin-subtitle">Filter data berdasarkan nomor notifikasi, unit, kategori item, status e-korin, dan jumlah entri.</p>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.verifikasianggaran.index') }}"
                  class="admin-filter mt-4 overflow-x-auto whitespace-nowrap">

                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input name="search" value="{{ request('search') }}" placeholder="Cari No. Notifikasi"
                           class="admin-input pl-9 w-56">
                </div>

                <select name="unit" class="admin-select w-36">
                    <option value="">Semua Unit</option>
                    @foreach($units as $u)
                        <option value="{{ $u }}" {{ request('unit') == $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>

                <select name="kategori_item" class="admin-select w-44">
                    <option value="">Kategori Item</option>
                    <option value="spare part" {{ request('kategori_item') == 'spare part' ? 'selected' : '' }}>Spare Part</option>
                    <option value="jasa" {{ request('kategori_item') == 'jasa' ? 'selected' : '' }}>Jasa</option>
                </select>

                <select name="status_e_korin" class="admin-select w-48">
                    <option value="">Status E-KORIN</option>
                    <option value="waiting_korin" {{ request('status_e_korin') == 'waiting_korin' ? 'selected' : '' }}>Waiting Korin</option>
                    <option value="waiting_approval" {{ request('status_e_korin') == 'waiting_approval' ? 'selected' : '' }}>Waiting Approval</option>
                    <option value="waiting_transfer" {{ request('status_e_korin') == 'waiting_transfer' ? 'selected' : '' }}>Waiting Transfer</option>
                    <option value="complete_transfer" {{ request('status_e_korin') == 'complete_transfer' ? 'selected' : '' }}>Complete Transfer</option>
                </select>

                <select name="entries" class="admin-select w-20">
                    <option value="10" {{ (int)request('entries', 10) === 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ (int)request('entries', 10) === 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ (int)request('entries', 10) === 50 ? 'selected' : '' }}>50</option>
                </select>

                <button class="admin-btn admin-btn-primary ml-auto" type="submit">
                    <i data-lucide="filter" class="w-4 h-4"></i> Terapkan
                </button>
                <a href="{{ route('admin.verifikasianggaran.index') }}" class="admin-btn admin-btn-ghost">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                </a>
            </form>
        </div>

        {{-- TABEL --}}
        <div class="admin-card">
            <div class="overflow-x-auto w-full">
                <table class="min-w-full table-fixed text-[9px] leading-[1.15]">
                    <colgroup>
                        <col class="w-24" />
                        <col class="w-[200px]" />
                        <col class="w-[220px]" />
                        <col class="w-[300px]" />
                        <col class="w-[260px]" />
                        <col class="w-24" />
                        <col class="w-[220px]" />
                    </colgroup>

                    <thead class="bg-slate-200 text-slate-900 uppercase text-[11px]">
                        <tr>
                            <th class="px-2 py-1 text-left font-bold">Nomor Order</th>
                            <th class="px-2 py-1 text-left font-bold">Dokumen</th>
                            <th class="px-2 py-1 text-left font-bold">Dana</th>
                            <th class="px-2 py-1 text-left font-bold">Kategori Item / Biaya</th>
                            <th class="px-2 py-1 text-left font-bold">E-KORIN</th>
                            <th class="px-2 py-1 text-left font-bold">Cost Element</th>
                            <th class="px-2 py-1 text-left font-bold">Catatan</th>
                        </tr>
                    </thead>

                    <tbody class="text-slate-900">
                    @forelse($notifications as $n)
                        @if($n->isScopeOfWorkAvailable && $n->isHppAvailable && $n->dokumenOrders->isNotEmpty())
                        <tr class="border-t hover:bg-slate-100 align-middle">

                            {{-- NOTIFIKASI --}}
                            <td class="px-2 py-1">
                                <span class="inline-block px-1.5 py-[2px] rounded-md bg-indigo-600 text-white border border-indigo-700 font-mono whitespace-nowrap">
                                    {{ $n->notification_number }}
                                </span>
                                <div class="mt-1 text-[9px] text-slate-500">
                                    Update:
                                    {{ $n->tanggal_verifikasi ? \Carbon\Carbon::parse($n->tanggal_verifikasi)->format('Y-m-d') : '-' }}
                                </div>
                            </td>

                            {{-- DOKUMEN --}}
                            <td class="px-2 py-1">
                                <div class="flex gap-2 flex-nowrap">
                                    @if($n->isAbnormalAvailable)
                                        <a href="{{ route('dokumen_orders.view', [$n->notification_number, 'abnormalitas']) }}"
                                           class="inline-flex flex-col items-center justify-center gap-1 px-2 py-1.5 w-20 rounded-lg border border-red-600 text-[9px] text-red-700 hover:bg-red-600 hover:text-white">
                                            <i class="fas fa-exclamation-triangle text-[12px]"></i>
                                            <span class="leading-none">Abnormalitas</span>
                                        </a>
                                    @endif

                                    @if($n->isGambarTeknikAvailable)
                                        <a href="{{ route('dokumen_orders.view', [$n->notification_number, 'gambar_teknik']) }}"
                                           class="inline-flex flex-col items-center justify-center gap-1 px-2 py-1.5 w-20 rounded-lg border border-blue-600 text-[9px] text-blue-700 hover:bg-blue-600 hover:text-white">
                                            <i class="fas fa-image text-[12px]"></i>
                                            <span class="leading-none">Gbr Teknik</span>
                                        </a>
                                    @endif

                                    @if($n->isScopeOfWorkAvailable)
                                        <a href="{{ route('dokumen_orders.scope.download_pdf', $n->notification_number) }}"
                                           class="inline-flex flex-col items-center justify-center gap-1 px-2 py-1.5 w-20 rounded-lg border border-emerald-600 text-[9px] text-emerald-700 hover:bg-emerald-600 hover:text-white">
                                            <i class="fas fa-clipboard-check text-[12px]"></i>
                                            <span class="leading-none">Scope Work</span>
                                        </a>
                                    @endif
                                </div>
                            </td>

                            {{-- DANA --}}
                            <td class="px-2 py-1">
                                @php
                                    $routes = [
                                        'createhpp1' => route('admin.inputhpp.download_hpp1', ['notification_number' => $n->notification_number]),
                                        'createhpp2' => route('admin.inputhpp.download_hpp2', ['notification_number' => $n->notification_number]),
                                        'createhpp3' => route('admin.inputhpp.download_hpp3', ['notification_number' => $n->notification_number]),
                                    ];
                                    $hppRoute = $routes[$n->source_form ?? ''] ?? null;
                                @endphp

                                <div class="flex items-center gap-1 mb-1 min-h-[18px]">
                                    @if($hppRoute)
                                        <a href="{{ $hppRoute }}"
                                           class="px-2 py-[2px] rounded-md border bg-emerald-600 text-white border-emerald-700 hover:bg-emerald-700 whitespace-nowrap">
                                            Rp{{ number_format((float)($n->total_amount ?? 0), 0, ',', '.') }}
                                        </a>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </div>

                                <form action="{{ route('admin.verifikasianggaran.update', $n->notification_number) }}" method="POST" class="relative">
                                    @csrf @method('PATCH')
                                    <select name="status_anggaran"
                                            class="{{ $baseSel.' '.$danaColor($n->status_anggaran) }} w-full"
                                            onchange="this.form.submit()">
                                        <option value="">Status Dana</option>
                                        <option value="Tersedia" {{ $n->status_anggaran === 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                                        <option value="Tidak Tersedia" {{ $n->status_anggaran === 'Tidak Tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                                        <option value="Menunggu" {{ $n->status_anggaran === 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                                    </select>
                                    <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-slate-600 text-[10px]">v</span>
                                </form>
                            </td>

                            {{-- KATEGORI ITEM / BIAYA --}}
                            <td class="px-2 py-1">
                                <div class="min-h-[26px] flex flex-col gap-2">

                                    <form action="{{ route('admin.verifikasianggaran.update', $n->notification_number) }}" method="POST" class="relative">
                                        @csrf @method('PATCH')
                                        <select name="kategori_item" class="{{ $selGreen }} w-32" onchange="this.form.submit()">
                                            <option value="">Pilih</option>
                                            <option value="spare part" {{ (($n->kategori_item ?? '') === 'spare part') ? 'selected' : '' }}>Spare Part</option>
                                            <option value="jasa" {{ (($n->kategori_item ?? '') === 'jasa') ? 'selected' : '' }}>Jasa</option>
                                        </select>
                                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-emerald-700 text-[10px]"></span>
                                    </form>

                                    <form action="{{ route('admin.verifikasianggaran.update', $n->notification_number) }}" method="POST" class="relative">
                                        @csrf @method('PATCH')
                                        <select name="kategori_biaya" class="{{ $selIndigo }} w-40" onchange="this.form.submit()">
                                            <option value="">Pilih</option>
                                            <option value="pemeliharaan" {{ (($n->kategori_biaya ?? '') === 'pemeliharaan') ? 'selected' : '' }}>Pemeliharaan</option>
                                            <option value="non pemeliharaan" {{ (($n->kategori_biaya ?? '') === 'non pemeliharaan') ? 'selected' : '' }}>Non Pemeliharaan</option>
                                            <option value="capex" {{ (($n->kategori_biaya ?? '') === 'capex') ? 'selected' : '' }}>Capex</option>
                                        </select>
                                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-indigo-700 text-[10px]"></span>
                                    </form>

                                </div>
                            </td>

                            {{-- E-KORIN --}}
                            <td class="px-2 py-1">
                                @php
                                    $statusMap = [
                                        'waiting_korin'       => 'Waiting Korin',
                                        'waiting_approval'    => 'Waiting Approval',
                                        'waiting_transfer'    => 'Waiting Transfer',
                                        'complete_transfer'   => 'Complete Transfer',
                                    ];
                                    $statusLabel = $statusMap[$n->status_e_korin ?? ''] ?? 'Menunggu';
                                @endphp

                                <div class="flex flex-col gap-1 min-h-[26px] justify-center">
                                    <div class="leading-tight">
                                        @if(!empty($n->nomor_e_korin))
                                            <span class="inline-flex items-center px-2 py-[2px] rounded-md border bg-white text-slate-700 border-slate-400 font-mono text-[9px]">
                                                {{ $n->nomor_e_korin }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic text-[9px]">-</span>
                                        @endif
                                    </div>

                                    <div class="leading-tight">
                                        <span class="inline-flex items-center px-1.5 py-[2px] rounded-md border text-[9px] leading-none {{ $ekorinColor($n->status_e_korin) }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- COST ELEMENT --}}
                            <td class="px-2 py-1">
                                <div class="min-h-[26px] flex items-center">
                                    <form action="{{ route('admin.verifikasianggaran.update', $n->notification_number) }}" method="POST" class="flex items-center gap-1 w-full">
                                        @csrf @method('PATCH')
                                        <input type="text" name="cost_element" value="{{ $n->cost_element ?? '' }}" placeholder="Cost..."
                                               class="{{ $inpSlate }} w-16"
                                               onkeydown="if(event.key==='Enter'){ this.closest('form').submit(); }">
                                        <button class="w-5 h-5 inline-flex items-center justify-center rounded border border-slate-600 hover:bg-slate-100" title="Simpan">
                                            OK
                                        </button>
                                    </form>
                                </div>
                            </td>

                            {{-- CATATAN --}}
                            <td class="px-2 py-1">
                                <div class="min-h-[26px] flex items-center">
                                    <form action="{{ route('admin.verifikasianggaran.update', $n->notification_number) }}" method="POST" class="flex items-center gap-1 w-full">
                                        @csrf @method('PATCH')
                                        <input type="text" name="catatan" value="{{ $n->catatan ?? '' }}" placeholder="Catatan..."
                                               class="{{ $inpSlate }} w-full"
                                               onkeydown="if(event.key==='Enter'){ this.closest('form').submit(); }">
                                        <button class="w-5 h-5 inline-flex items-center justify-center rounded border border-slate-600 hover:bg-slate-100" title="Simpan">
                                            OK
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-3 text-center text-slate-500 text-[9px]">Tidak ada data ditemukan.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-2 px-1 text-[9px]">
            {{ $notifications->appends(request()->query())->links() }}
        </div>
    </div>
</div>
</x-admin-layout>
