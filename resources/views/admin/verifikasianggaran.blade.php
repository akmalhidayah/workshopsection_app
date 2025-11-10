<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-[11px] text-gray-800 leading-tight">
            Verifikasi Anggaran
        </h2>
    </x-slot>

@php
    /* MINI PRESET – no logic changed, fix clipped text */
    // pakai min-height + padding-end + line-height sedikit lebih longgar
    $baseSel = 'min-h-[26px] text-[10px] leading-[1.3] px-2 pe-9 rounded-[6px] appearance-none focus:ring-1 truncate';
    $baseInp = 'h-6 text-[9px] leading-[1.15] px-2 rounded-[6px] focus:ring-1';
    $baseBtn = 'h-6 text-[9px] leading-[1.15] px-2 rounded-[6px]';

    $selBlue   = $baseSel.' bg-sky-100  text-sky-800  border border-sky-600  focus:ring-sky-500  focus:border-sky-600';
    $selIndigo = $baseSel.' bg-indigo-100 text-indigo-800 border border-indigo-600 focus:ring-indigo-500 focus:border-indigo-600';
    $selGreen  = $baseSel.' bg-emerald-100 text-emerald-800 border border-emerald-600 focus:ring-emerald-500 focus:border-emerald-600';
    $selAmber  = $baseSel.' bg-amber-100 text-amber-800 border border-amber-600 focus:ring-amber-500 focus:border-amber-600';
    $selSlate  = $baseSel.' bg-slate-100 text-slate-800 border border-slate-600 focus:ring-slate-500 focus:border-slate-600';

    $inpSlate  = $baseInp.' bg-white border border-slate-600 focus:ring-indigo-500 focus:border-indigo-600';

    $chip = 'px-1.5 py-[2px] rounded-md border text-[9px] leading-none';

    $danaColor = fn($v)=>match($v){
        'Tersedia'        => 'bg-emerald-100 text-emerald-700 border-emerald-600',
        'Tidak Tersedia'  => 'bg-rose-100    text-rose-700    border-rose-600',
        'Menunggu'        => 'bg-amber-100   text-amber-700   border-amber-600',
        default           => 'bg-slate-100   text-slate-700   border-slate-600',
    };
    $ekorinColor = fn($v)=>match($v){
        'waiting_korin'     => 'bg-amber-100   text-amber-700   border-amber-600',
        'waiting_approval'     => 'bg-amber-300   text-amber-700   border-amber-600',
        'waiting_transfer'  => 'bg-indigo-100  text-indigo-700  border-indigo-600',
        'complete_transfer' => 'bg-emerald-100 text-emerald-700 border-emerald-600',
        default             => 'bg-slate-100   text-slate-700   border-slate-600',
    };
@endphp

    <div class="py-3">
        <div class="w-full max-w-none mx-auto"> {{-- FULL WIDTH --}}

            {{-- HEADER + FILTER (warna beda-beda) --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-3 p-3">
                <div class="mb-2">
                    <h3 class="font-semibold text-[11px] text-slate-900 leading-tight">Verifikasi Anggaran</h3>
                    <p class="text-[9px] text-slate-500 leading-tight">Filter data berdasarkan nomor notifikasi, unit, kategori item, status e-korin, dan jumlah entri.</p>
                </div>

                <form method="GET" action="{{ route('admin.verifikasianggaran.index') }}"
                      class="flex items-center gap-2 overflow-x-auto whitespace-nowrap">

                    {{-- Search (Indigo) --}}
                    <div class="relative">
                        <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3 h-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                        <input name="search" value="{{ request('search') }}" placeholder="Cari No. Notifikasi"
                               class="{{ $selIndigo }} pl-6 w-56">
                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-indigo-600 text-[10px]">⌕</span>
                    </div>

                    {{-- Unit (Sky) --}}
                    <select name="unit" class="{{ $selBlue }} w-32">
                        <option value="">Semua Unit</option>
                        @foreach($units as $u)
                            <option value="{{ $u }}" {{ request('unit')==$u?'selected':'' }}>{{ $u }}</option>
                        @endforeach
                    </select>

                    {{-- Kategori Item (Green) --}}
                    <select name="kategori_item" class="{{ $selGreen }} w-40">
                        <option value="">Kategori Item</option>
                        <option value="spare part" {{ request('kategori_item')=='spare part'?'selected':'' }}>Spare Part</option>
                        <option value="jasa" {{ request('kategori_item')=='jasa'?'selected':'' }}>Jasa</option>
                    </select>

                    {{-- Status E-KORIN (Amber) --}}
                    <select name="status_e_korin" class="{{ $selAmber }} w-40">
                        <option value="">Status E-KORIN</option>
                        <option value="waiting_korin" {{ request('status_e_korin')=='waiting_korin'?'selected':'' }}>Waiting Korin</option>
                        <option value="waiting_approval" {{ request('status_e_korin')=='waiting_approval'?'selected':'' }}>Waiting Approval</option>
                        <option value="waiting_transfer" {{ request('status_e_korin')=='waiting_transfer'?'selected':'' }}>Waiting Transfer</option>
                        <option value="complete_transfer" {{ request('status_e_korin')=='complete_transfer'?'selected':'' }}>Complete Transfer</option>
                    </select>

                    {{-- Entries (Slate) --}}
                    <select name="entries" class="{{ $selSlate }} w-16">
                        <option value="10" {{ request('entries',10)==10?'selected':'' }}>10</option>
                        <option value="25" {{ request('entries')==25?'selected':'' }}>25</option>
                        <option value="50" {{ request('entries')==50?'selected':'' }}>50</option>
                    </select>

                    <button class="ml-auto {{ $baseBtn }} bg-indigo-600 text-white hover:bg-indigo-700">Terapkan</button>
                    <a href="{{ route('admin.verifikasianggaran.index') }}"
                       class="{{ $baseBtn }} border border-slate-600 text-slate-700 hover:bg-slate-50 inline-flex items-center">Reset</a>
                </form>
            </div>

            {{-- TABEL --}}
            <div class="bg-white shadow-sm rounded-xl border border-slate-200">
                {{-- Tambahan wrapper agar scroll horizontal tetap di dalam kartu --}}
                <div class="overflow-x-auto w-full">
                    <table class="min-w-full table-fixed text-[9px] leading-[1.15]">
                        <colgroup>
                            <col class="w-24" />
                            <col class="w-[200px]" />
                            <col class="w-[220px]" />
                            <col class="w-[200px]" />
                            <col class="w-[260px]" />   {{-- lebar untuk nomor e-korin --}}
                            <col class="w-24" />
                            <col class="w-[260px]" />
                            <col class="w-[220px]" />
                            <col class="w-24" />
                        </colgroup>

                        <thead class="bg-slate-50 text-slate-700 uppercase">
                            <tr>
                                <th class="px-2 py-1 text-left font-semibold">Nomor Order</th>
                                <th class="px-2 py-1 text-left font-semibold">Dokumen</th>
                                <th class="px-2 py-1 text-left font-semibold">Dana</th>        {{-- HPP + status_anggaran --}}
                                <th class="px-2 py-1 text-left font-semibold">Kategori Item</th>    {{-- kategori_item --}}
                                <th class="px-2 py-1 text-left font-semibold">E-KORIN</th>     {{-- nomor + status_e_korin --}}
                                <th class="px-2 py-1 text-left font-semibold">Cost</th>        {{-- cost_element --}}
                                <th class="px-2 py-1 text-left font-semibold">Kategori Biaya</th>       {{-- kategori_biaya --}}
                                <th class="px-2 py-1 text-left font-semibold">Catatan</th>
                                <th class="px-2 py-1 text-left font-semibold">Update</th>
                            </tr>
                        </thead>

                        <tbody class="text-slate-900">
                        @forelse($notifications as $n)
                            @if($n->isScopeOfWorkAvailable && $n->isHppAvailable && $n->dokumenOrders->isNotEmpty())
                            <tr class="border-t hover:bg-slate-50 align-middle">
                                {{-- NOTIFIKASI --}}
                                <td class="px-2 py-1">
                                    <span class="inline-block px-1.5 py-[2px] rounded-md bg-indigo-100 text-indigo-700 border border-indigo-600 font-mono whitespace-nowrap">
                                        {{ $n->notification_number }}
                                    </span>
                                </td>

                                {{-- DOKUMEN --}}
                                <td class="px-2 py-1">
                                    <div class="flex flex-wrap gap-1">
                                        @if($n->isAbnormalAvailable)
                                            <a href="{{ route('dokumen_orders.view', [$n->notification_number, 'abnormalitas']) }}"
                                               class="{{ $chip }} bg-slate-100 text-slate-700 border-slate-600 hover:bg-slate-200">Abnormalitas</a>
                                        @endif
                                        @if($n->isGambarTeknikAvailable)
                                            <a href="{{ route('dokumen_orders.view', [$n->notification_number, 'gambar_teknik']) }}"
                                               class="{{ $chip }} bg-slate-100 text-slate-700 border-slate-600 hover:bg-slate-200">Gambar Teknik</a>
                                        @endif
                                        @if($n->isScopeOfWorkAvailable)
                                            <a href="{{ route('dokumen_orders.scope.download_pdf', $n->notification_number) }}"
                                               class="{{ $chip }} bg-slate-100 text-slate-700 border-slate-600 hover:bg-slate-200">Scope of Work</a>
                                        @endif
                                    </div>
                                </td>

                                {{-- DANA (HPP + Status Anggaran) --}}
                                <td class="px-2 py-1">
                                    @php
                                        $routes = [
                                            'createhpp1' => route('admin.inputhpp.download_hpp1', ['notification_number' => $n->notification_number]),
                                            'createhpp2' => route('admin.inputhpp.download_hpp2', ['notification_number' => $n->notification_number]),
                                            'createhpp3' => route('admin.inputhpp.download_hpp3', ['notification_number' => $n->notification_number]),
                                        ];
                                        $hppRoute = $routes[$n->source_form] ?? null;
                                    @endphp
                                    <div class="flex items-center gap-1 mb-1 min-h-[18px]">
                                        @if($hppRoute)
                                            <a href="{{ $hppRoute }}"
                                               class="px-2 py-[2px] rounded-md border bg-emerald-100 text-emerald-700 border-emerald-600 hover:bg-emerald-200 whitespace-nowrap">
                                                Rp{{ number_format($n->total_amount, 0, ',', '.') }}
                                            </a>
                                        @else
                                            <span class="text-slate-500">-</span>
                                        @endif
                                    </div>

                                    <form action="{{ route('admin.verifikasianggaran.update', $n->notification_number) }}" method="POST" class="relative">
                                        @csrf @method('PATCH')
                                        <select name="status_anggaran" class="{{ $baseSel.' '.$danaColor($n->status_anggaran) }} w-full" onchange="this.form.submit()">
                                            <option value="">Status Dana…</option>
                                            <option value="Tersedia" {{ $n->status_anggaran==='Tersedia'?'selected':'' }}>Tersedia</option>
                                            <option value="Tidak Tersedia" {{ $n->status_anggaran==='Tidak Tersedia'?'selected':'' }}>Tidak Tersedia</option>
                                            <option value="Menunggu" {{ $n->status_anggaran==='Menunggu'?'selected':'' }}>Menunggu</option>
                                        </select>
                                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-slate-600 text-[10px]">▾</span>
                                    </form>
                                </td>

                                {{-- KATEGORI (Green) --}}
                                <td class="px-2 py-1">
                                    <div class="min-h-[26px] flex items-center">
                                        <form action="{{ route('admin.verifikasianggaran.update', $n->notification_number) }}" method="POST" class="relative w-full">
                                            @csrf @method('PATCH')
                                            <select name="kategori_item" class="{{ $selGreen }} w-full" onchange="this.form.submit()">
                                                <option value="">Pilih…</option>
                                                <option value="spare part" {{ ($n->kategori_item ?? '')==='spare part'?'selected':'' }}>Spare Part</option>
                                                <option value="jasa" {{ ($n->kategori_item ?? '')==='jasa'?'selected':'' }}>Jasa</option>
                                            </select>
                                            <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-emerald-700 text-[10px]">▾</span>
                                        </form>
                                    </div>
                                </td>

                                {{-- E-KORIN (readonly: nomor + status) --}}
                                <td class="px-2 py-1">
                                    @php
                                        $statusMap = [
                                            'waiting_korin'     => 'Waiting Korin',
                                            'waiting_transfer'  => 'Waiting Transfer',
                                            'complete_transfer' => 'Complete Transfer',
                                        ];
                                        $statusLabel = $statusMap[$n->status_e_korin ?? ''] ?? 'Menunggu';
                                    @endphp

                                    <div class="flex flex-col gap-1 min-h-[26px] justify-center">
                                        {{-- Nomor E-KORIN --}}
                                        <div class="leading-tight">
                                            @if(!empty($n->nomor_e_korin))
                                                <span class="inline-flex items-center px-2 py-[2px] rounded-md border bg-slate-100 text-slate-700 border-slate-600 font-mono text-[9px]">
                                                    {{ $n->nomor_e_korin }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 italic text-[9px]">—</span>
                                            @endif
                                        </div>

                                        {{-- Status E-KORIN --}}
                                        <div class="leading-tight">
                                            <span class="inline-flex items-center px-1.5 py-[2px] rounded-md border text-[9px] leading-none {{ $ekorinColor($n->status_e_korin) }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- COST (Slate) --}}
                                <td class="px-2 py-1">
                                    <div class="min-h-[26px] flex items-center">
                                        <form action="{{ route('admin.verifikasianggaran.update', $n->notification_number) }}" method="POST" class="flex items-center gap-1 w-full">
                                            @csrf @method('PATCH')
                                            <input type="text" name="cost_element" value="{{ $n->cost_element ?? '' }}" placeholder="Cost…"
                                                   class="{{ $inpSlate }} w-16"
                                                   onkeydown="if(event.key==='Enter'){ this.closest('form').submit(); }">
                                            <button class="w-5 h-5 inline-flex items-center justify-center rounded border border-slate-600 hover:bg-slate-50" title="Simpan">✓</button>
                                        </form>
                                    </div>
                                </td>

                                {{-- BIAYA (Indigo) --}}
                                <td class="px-2 py-1">
                                    <div class="min-h-[26px] flex items-center">
                                        <form action="{{ route('admin.verifikasianggaran.update', $n->notification_number) }}" method="POST" class="relative w-full">
                                            @csrf @method('PATCH')
                                            <select name="kategori_biaya" class="{{ $selIndigo }} w-full" onchange="this.form.submit()">
                                                <option value="">Pilih…</option>
                                                <option value="pemeliharaan" {{ ($n->kategori_biaya ?? '')==='pemeliharaan'?'selected':'' }}>Pemeliharaan</option>
                                                <option value="non pemeliharaan" {{ ($n->kategori_biaya ?? '')==='non pemeliharaan'?'selected':'' }}>Non Pemeliharaan</option>
                                                <option value="capex" {{ ($n->kategori_biaya ?? '')==='capex'?'selected':'' }}>Capex</option>
                                            </select>
                                            <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-indigo-700 text-[10px]">▾</span>
                                        </form>
                                    </div>
                                </td>

                                {{-- CATATAN --}}
                                <td class="px-2 py-1">
                                    <div class="min-h-[26px] flex items-center">
                                        <form action="{{ route('admin.verifikasianggaran.update', $n->notification_number) }}" method="POST" class="flex items-center gap-1 w-full">
                                            @csrf @method('PATCH')
                                            <input type="text" name="catatan" value="{{ $n->catatan ?? '' }}" placeholder="Catatan…"
                                                   class="{{ $inpSlate }} w-full"
                                                   onkeydown="if(event.key==='Enter'){ this.closest('form').submit(); }">
                                            <button class="w-5 h-5 inline-flex items-center justify-center rounded border border-slate-600 hover:bg-slate-50" title="Simpan">✓</button>
                                        </form>
                                    </div>
                                </td>

                                {{-- UPDATE --}}
                                <td class="px-2 py-1 text-slate-600 whitespace-nowrap">
                                    {{ $n->tanggal_verifikasi ? \Carbon\Carbon::parse($n->tanggal_verifikasi)->format('Y-m-d') : '-' }}
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-3 text-center text-slate-500 text-[9px]">Tidak ada data ditemukan.</td>
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
