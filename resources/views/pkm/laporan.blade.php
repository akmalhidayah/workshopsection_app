<x-pkm-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-sm text-gray-800 leading-tight flex items-center space-x-2">
            <i class="fas fa-clipboard-list text-orange-600"></i>
            <span>{{ __('Laporan PKM') }}</span>
        </h2>
    </x-slot>

    {{-- Jika Alpine belum dimuat di layout utama, uncomment ini --}}
    {{-- <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script> --}}

    <div class="py-6">
        <div class="w-full max-w-[98%] mx-auto space-y-4">

            {{-- Header + Filter (compact) --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Laporan Dokumen</h3>
                    <p class="text-xs text-gray-500">Tampilan compact — ikon dokumen & dropdown termin</p>
                </div>

                <form method="GET" action="{{ route('pkm.laporan') }}" class="flex items-end gap-2">
                    <input name="notification_number" value="{{ request('notification_number') }}" placeholder="No. Order" class="text-xs px-2 py-1 border rounded" />
                    <select name="status" class="text-xs px-2 py-1 border rounded">
                        <option value="">Semua</option>
                        <option value="complete" {{ request('status')=='complete' ? 'selected':'' }}>Lengkap</option>
                        <option value="incomplete" {{ request('status')=='incomplete' ? 'selected':'' }}>Belum</option>
                    </select>
                    <button class="bg-orange-600 text-white text-xs px-3 py-1 rounded">Filter</button>
                    <a href="{{ route('pkm.laporan') }}" class="text-xs px-3 py-1 bg-gray-200 rounded">Reset</a>
                </form>
            </div>

            {{-- Compact table (small font) --}}
            <div class="bg-white border rounded-lg overflow-x-auto">
                <table class="min-w-full text-[12px]">
                    <thead class="bg-orange-400">
                        <tr>
                            <th class="px-3 py-2 text-left w-[10%] text-xs font-medium text-white">No. Order</th>
                            <th class="px-3 py-2 text-left w-[25%] text-xs font-medium text-white">Deskripsi</th>
                            <th class="px-3 py-2 text-center w-[22%] text-xs font-medium text-white">Dokumen</th>
                            <th class="px-3 py-2 text-center w-[12%] text-xs font-medium text-white">LPJ / PPL</th>
                            <th class="px-3 py-2 text-center w-[12%] text-xs font-medium text-white">Pembayaran</th>
                            <th class="px-3 py-2 text-center w-[9%] text-xs font-medium text-white">Garansi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($notifications as $notification)
                            <tr class="hover:bg-gray-50">
                                {{-- No Order --}}
                                <td class="px-3 py-2 align-top">
                                    <div class="flex items-center gap-2">
                                        <div class="font-medium text-xs">{{ $notification->notification_number }}</div>

                                        @if(!empty($notification->is_complete))
                                            <span class="text-[10px] px-2 py-0.5 bg-green-50 text-green-800 rounded">Lengkap</span>
                                        @else
                                            <span class="text-[10px] px-2 py-0.5 bg-yellow-50 text-yellow-800 rounded">Belum</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-1">{{ optional($notification->created_at)->format('Y-m-d') }}</div>
                                </td>

                                {{-- Deskripsi --}}
                                <td class="px-3 py-2 align-top text-[12px] text-gray-600">
                                    <div class="truncate max-w-[260px]">{{ $notification->job_name ?? '-' }}</div>
                                    @if(!empty(optional($notification->purchaseOrder)->purchase_order_number))
                                        <div class="text-[10px] text-gray-400 mt-1">PO#: {{ $notification->purchaseOrder->purchase_order_number }}</div>
                                    @endif
                                </td>

                                {{-- Dokumen icons (HPP / PO / LHPP) --}}
                                <td class="px-3 py-2 text-center align-top">
                                    <div class="flex items-center justify-center gap-3">

                                        {{-- HPP --}}
                                        @php
                                            $source = $notification->source_form ?? '';
                                            $hppBg = match($source) {
                                                'createhpp1' => 'bg-red-600',
                                                'createhpp2' => 'bg-blue-600',
                                                'createhpp3' => 'bg-green-600',
                                                'createhpp4' => 'bg-green-700',
                                                default => 'bg-gray-600',
                                            };
                                        @endphp
                                        <div class="flex flex-col items-center text-[10px]">
                                            @if(!empty($notification->isHppAvailable) || !empty($notification->has_hpp_fallback))
                                                @if(!empty($notification->download_route_name) && \Illuminate\Support\Facades\Route::has($notification->download_route_name))
                                                    <a href="{{ route($notification->download_route_name, $notification->notification_number) }}" title="HPP" target="_blank"
                                                       class="w-7 h-7 inline-flex items-center justify-center rounded {{ $hppBg }} text-white text-[11px]">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                @elseif(!empty($notification->has_hpp_fallback) && !empty($notification->hpp_file_path))
                                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($notification->hpp_file_path) }}" title="HPP" target="_blank"
                                                       class="w-7 h-7 inline-flex items-center justify-center rounded {{ $hppBg }} text-white text-[11px]">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                @else
                                                    <span title="HPP tersedia" class="w-7 h-7 inline-flex items-center justify-center rounded {{ $hppBg }}/80 text-white text-[11px] opacity-80">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </span>
                                                @endif
                                            @else
                                                <span title="HPP - tidak ada" class="w-7 h-7 inline-flex items-center justify-center rounded bg-gray-100 text-gray-400 text-[11px]">
                                                    <i class="fas fa-file-pdf"></i>
                                                </span>
                                            @endif
                                            <div class="mt-1 text-[10px] text-center text-gray-700">HPP</div>
                                        </div>

                                        {{-- PO --}}
                                        <div class="flex flex-col items-center text-[10px]">
                                            @if(!empty(optional($notification->purchaseOrder)->po_document_path))
                                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($notification->purchaseOrder->po_document_path) }}" title="PO" target="_blank"
                                                   class="w-7 h-7 inline-flex items-center justify-center rounded bg-purple-600 text-white text-[11px]">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                            @else
                                                <span title="PO - tidak ada" class="w-7 h-7 inline-flex items-center justify-center rounded bg-gray-100 text-gray-300 text-[11px]">
                                                    <i class="fas fa-file-alt"></i>
                                                </span>
                                            @endif
                                            <div class="mt-1 text-[10px] text-center text-gray-700">PO</div>
                                        </div>

                                        {{-- LHPP --}}
                                        <div class="flex flex-col items-center text-[10px]">
                                            @if(!empty($notification->has_lhpp))
                                                <a href="{{ route('pkm.lhpp.download_pdf', $notification->notification_number) }}" title="LHPP" target="_blank"
                                                   class="w-7 h-7 inline-flex items-center justify-center rounded bg-green-600 text-white text-[11px]">
                                                    <i class="fas fa-file-contract"></i>
                                                </a>
                                            @else
                                                <span title="LHPP - tidak ada" class="w-7 h-7 inline-flex items-center justify-center rounded bg-gray-100 text-gray-300 text-[11px]">
                                                    <i class="fas fa-file-contract"></i>
                                                </span>
                                            @endif
                                            <div class="mt-1 text-[10px] text-center text-gray-700">LHPP</div>
                                        </div>

                                    </div>
                                </td>

                                {{-- LPJ / PPL with termin dropdown (compact) --}}
                                <td class="px-3 py-2 text-center align-top">
                                    <div x-data="{ t: '1' }" class="flex flex-col items-center gap-1">
                                        {{-- small termin select --}}
                                        <select x-model="t" class="text-[11px] px-2 py-0.5 border rounded">
                                            <option value="1">Termin 1</option>
                                            <option value="2">Termin 2</option>
                                        </select>

                                        {{-- icons shown depending on termin --}}
                                        <div class="flex items-center gap-1 mt-1">
                                            <template x-if="t==='1'">
                                                <div class="flex items-center gap-1">
                                                    {{-- LPJ T1 --}}
                                                    @if(!empty($notification->lpj_path_termin1))
                                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($notification->lpj_path_termin1) }}" title="LPJ T1" target="_blank" class="w-7 h-7 inline-flex items-center justify-center rounded bg-blue-600 text-white text-[11px]">
                                                            <i class="fas fa-file-alt"></i>
                                                        </a>
                                                    @else
                                                        <span title="LPJ T1 - tidak ada" class="w-7 h-7 inline-flex items-center justify-center rounded bg-gray-100 text-gray-300 text-[11px]">
                                                            <i class="fas fa-file-alt"></i>
                                                        </span>
                                                    @endif

                                                    {{-- PPL T1 --}}
                                                    @if(!empty($notification->ppl_path_termin1))
                                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($notification->ppl_path_termin1) }}" title="PPL T1" target="_blank" class="w-7 h-7 inline-flex items-center justify-center rounded bg-orange-600 text-white text-[11px]">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </a>
                                                    @else
                                                        <span title="PPL T1 - tidak ada" class="w-7 h-7 inline-flex items-center justify-center rounded bg-gray-100 text-gray-300 text-[11px]">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </template>

                                            <template x-if="t==='2'">
                                                <div class="flex items-center gap-1">
                                                    {{-- LPJ T2 --}}
                                                    @if(!empty($notification->lpj_path_termin2))
                                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($notification->lpj_path_termin2) }}" title="LPJ T2" target="_blank" class="w-7 h-7 inline-flex items-center justify-center rounded bg-blue-500 text-white text-[11px]">
                                                            <i class="fas fa-file-alt"></i>
                                                        </a>
                                                    @else
                                                        <span title="LPJ T2 - tidak ada" class="w-7 h-7 inline-flex items-center justify-center rounded bg-gray-100 text-gray-300 text-[11px]">
                                                            <i class="fas fa-file-alt"></i>
                                                        </span>
                                                    @endif

                                                    {{-- PPL T2 --}}
                                                    @if(!empty($notification->ppl_path_termin2))
                                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($notification->ppl_path_termin2) }}" title="PPL T2" target="_blank" class="w-7 h-7 inline-flex items-center justify-center rounded bg-orange-500 text-white text-[11px]">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </a>
                                                    @else
                                                        <span title="PPL T2 - tidak ada" class="w-7 h-7 inline-flex items-center justify-center rounded bg-gray-100 text-gray-300 text-[11px]">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- label LPJ/PPL kecil --}}
                                    <div class="mt-1 text-[10px] text-gray-600">LPJ / PPL</div>
                                </td>

                                {{-- Pembayaran --}}
                                <td class="px-3 py-2 text-center align-top">
                                    <div class="text-[12px]">
                                        <div class="text-gray-500 text-[10px]">Rp</div>
                                        <div class="font-medium text-xs">{{ number_format($notification->total_biaya ?? 0,0,',','.') }}</div>
                                        <div class="text-[10px] mt-1">
                                            @if(!empty($notification->paid_percent) && $notification->paid_percent > 0)
                                                <span class="text-[11px]">{{ $notification->paid_percent }}% — Rp {{ number_format($notification->paid_amount ?? 0,0,',','.') }}</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                            {{-- Garansi (server fallback + client-side countdown) --}}
<td class="px-3 py-2 text-center align-top">
    @php
        // server fallback values (string or null)
        $startStr = null;
        $endStr = null;
        try {
            if (!empty($notification->garansi_start)) {
                $startStr = \Carbon\Carbon::parse($notification->garansi_start)->format('Y-m-d');
            }
        } catch (\Throwable $e) { $startStr = null; }

        try {
            if (!empty($notification->garansi_end)) {
                $endStr = \Carbon\Carbon::parse($notification->garansi_end)->format('Y-m-d');
            }
        } catch (\Throwable $e) { $endStr = null; }
    @endphp

    <div class="text-[11px]">
        {{-- Tanggal mulai --}}
        @if($startStr)
            <div class="text-gray-600">{{ $startStr }}</div>
        @else
            <div class="text-gray-400">-</div>
        @endif

        {{-- Tanggal berakhir --}}
        <div class="text-[10px] text-gray-400 mt-1">
            @if($endStr)
                {{ $endStr }}
            @else
                -
            @endif
        </div>

        {{-- Countdown container: JS will update .garansi-countdown --}}
        <div class="mt-2">
            @if($endStr)
                <span
                    class="garansi-countdown inline-flex items-center px-2 py-0.5 rounded text-xs"
                    data-end="{{ $endStr }}"
                    data-start="{{ $startStr ?? '' }}"
                    aria-live="polite"
                    >
                    {{-- server-side temporary text while JS boots --}}
                    Menghitung...
                </span>
            @else
                <span class="text-gray-400 text-xs">-</span>
            @endif
        </div>
    </div>
</td>


                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-gray-500 italic text-xs">Tidak ada data laporan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex justify-center">
                {{ $notifications->links('pagination::tailwind') }}
            </div>
        </div>
    </div>

    {{-- small event listener for Update LPJ (optional hook) --}}
   <script>
document.addEventListener('DOMContentLoaded', function () {
    function updateGaransiBadge(el) {
        if (!el) return;
        const endStr = el.getAttribute('data-end');
        if (!endStr) {
            el.className = 'text-gray-400 text-xs';
            el.textContent = '-';
            return;
        }

        // parse as local date at midnight
        const end = new Date(endStr + 'T00:00:00');
        const now = new Date();
        // normalize to days difference (floor)
        const msPerDay = 24 * 60 * 60 * 1000;
        // compute diff in days (end - today)
        const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const startOfEnd = new Date(end.getFullYear(), end.getMonth(), end.getDate());
        const diffDays = Math.floor((startOfEnd - startOfToday) / msPerDay);

        // choose badge text + classes
        let text = '';
        let cls = 'inline-flex items-center px-2 py-0.5 rounded text-xs ';
        if (diffDays > 1) {
            text = diffDays + ' hari tersisa';
            cls += 'bg-green-50 text-green-800';
        } else if (diffDays === 1) {
            text = 'Besok (1 hari)';
            cls += 'bg-yellow-50 text-yellow-800';
        } else if (diffDays === 0) {
            text = 'Terakhir hari ini';
            cls += 'bg-yellow-100 text-yellow-800';
        } else {
            text = 'Habis';
            cls += 'bg-red-50 text-red-800';
        }

        el.className = cls;
        el.textContent = text;
    }

    // init all badges
    const els = document.querySelectorAll('.garansi-countdown');
    els.forEach(updateGaransiBadge);

    // OPTIONAL: update every 6 hours to keep it fresh (not required)
    // setInterval(() => els.forEach(updateGaransiBadge), 1000 * 60 * 60 * 6);
});
</script>

</x-pkm-layout>
