<x-pkm-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight flex items-center space-x-2">
            <i class="fas fa-clipboard-list text-orange-600"></i>
            <span>{{ __('Laporan PKM') }}</span>
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-[98%] mx-auto space-y-5">

            <!-- HEADER / TITLE CARD -->
            <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Laporan Dokumen</h3>
                        <p class="text-sm text-gray-500">Daftar Riwayat Order Pekerjaan Jasa Vendor</p>
                    </div>
                </div>
            </div>

            <!-- FILTER CONTAINER (HANYA PENCARIAN & STATUS) -->
            <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-200">
                <form method="GET" action="{{ route('pkm.laporan') }}" class="flex flex-col md:flex-row md:items-end gap-4">
                    <div class="flex-1">
                        <label class="text-xs text-gray-600 block mb-1">Nomor Order</label>
                        <div class="relative">
                            <input
                                type="text"
                                name="notification_number"
                                value="{{ request('notification_number') }}"
                                placeholder="Cari Nomor Order..."
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-orange-200"
                            >
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
                        </div>
                    </div>

                    <div class="w-56">
                        <label class="text-xs text-gray-600 block mb-1">Status Dokumen</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <option value="">-- Semua --</option>
                            <option value="complete" {{ request('status') == 'complete' ? 'selected' : '' }}>Lengkap</option>
                            <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>Belum Lengkap</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('pkm.laporan') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-sync-alt mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- TABLE: LAPORAN -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-100">
                        <thead class="bg-orange-600 text-white text-left">
                            <tr>
                                <th class="px-4 py-3 font-semibold w-1/6">Nomor Order</th>
                                <th class="px-4 py-3 font-semibold w-2/6">Deskripsi</th>
                                <th class="px-4 py-3 font-semibold w-3/6 text-center">Dokumen & Info Pembayaran</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($notifications as $notification)
                                @php
                                    // ambil relasi safe
                                    $lhpp = $notification->lhpp;
                                    $lpj  = $notification->lpj;
                                    $po   = $notification->purchaseOrder;

                                    // total biaya: coba dari LHPP.total_biaya lalu fallback ke HPP.total_amount
                                    $totalBiaya = (float) ($lhpp->total_biaya ?? $notification->total_amount ?? 0);

                                    // termin status
                                    $termin1Paid = optional($lpj)->termin1 === 'sudah';
                                    $termin2Paid = optional($lpj)->termin2 === 'sudah';

                                    // hitung terbayar berdasarkan aturan:
                                    // - jika kedua termin dibayar => 100%
                                    // - elseif termin1 dibayar => 95% (sesuai penjelasan)
                                    // - else => 0
                                    $paidPercent = null;
                                    if ($termin1Paid && $termin2Paid) {
                                        $paidPercent = 100;
                                    } elseif ($termin1Paid) {
                                        $paidPercent = 95;
                                    } else {
                                        $paidPercent = 0;
                                    }
                                    $paidAmount = (int) round($totalBiaya * $paidPercent / 100);

                                    // tanggal mulai garansi: prioritas LHPP ttd date jika ada, fallback ke lpj.update_date
                                    $garansiStart = null;
                                    if (!empty($lhpp->tanggal_ttd)) {
                                        try { $garansiStart = \Carbon\Carbon::parse($lhpp->tanggal_ttd); } catch (\Exception $e) { $garansiStart = null; }
                                    }
                                    if (!$garansiStart && !empty($lpj->update_date)) {
                                        try { $garansiStart = \Carbon\Carbon::parse($lpj->update_date); } catch (\Exception $e) { $garansiStart = null; }
                                    }

                                    // durasi garansi (bulan) dari lpj->garansi_months
                                    $garansiMonths = (int) optional($lpj)->garansi_months;
                                    $garansiEnd = null;
                                    if ($garansiStart && $garansiMonths > 0) {
                                        // addMonthsNoOverflow untuk aman
                                        $garansiEnd = (clone $garansiStart)->addMonthsNoOverflow($garansiMonths);
                                    }
                                @endphp

                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 font-medium text-gray-800 align-top">
                                        {{ $notification->notification_number }}
                                    </td>

                                    <td class="px-4 py-4 text-gray-600 align-top">
                                        {{ $notification->job_name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-center align-top">
                                        <div class="flex flex-col items-center gap-3">

                                            {{-- Row 1: dokumen badges --}}
                                            <div class="flex flex-wrap justify-center gap-2">
                                                {{-- HPP --}}
                                                @if(!empty($notification->isHppAvailable))
                                                    @php
                                                        $hppColor = match($notification->source_form ?? '') {
                                                            'createhpp1' => 'bg-red-500',
                                                            'createhpp2' => 'bg-blue-500',
                                                            'createhpp3' => 'bg-green-500',
                                                            default => 'bg-gray-500',
                                                        };
                                                    @endphp
                                                    <a href="{{ route('pkm.download_hpp', ['notification_number' => $notification->notification_number]) }}"
                                                       target="_blank" rel="noopener noreferrer"
                                                       class="{{ $hppColor }} text-white px-3 py-1 rounded text-xs flex items-center gap-2"
                                                       title="Download HPP {{ $notification->notification_number }}">
                                                        <i class="fas fa-file-pdf"></i><span>HPP</span>
                                                    </a>
                                                @endif

                                                {{-- PO --}}
                                                @if(optional($po)->po_document_path)
                                                    <a href="{{ Storage::url($po->po_document_path) }}"
                                                       target="_blank" rel="noopener noreferrer"
                                                       class="bg-purple-500 text-white px-3 py-1 rounded text-xs flex items-center gap-2"
                                                       title="Buka PO {{ $notification->notification_number }}">
                                                        <i class="fas fa-file-alt"></i><span>PO</span>
                                                    </a>
                                                @endif

                                                {{-- LHPP --}}
                                                @if($lhpp)
                                                    <a href="{{ route('pkm.lhpp.download_pdf', $notification->notification_number) }}"
                                                       target="_blank" rel="noopener noreferrer"
                                                       class="bg-green-500 text-white px-3 py-1 rounded text-xs flex items-center gap-2"
                                                       title="Download LHPP {{ $notification->notification_number }}">
                                                        <i class="fas fa-file-alt"></i><span>LHPP</span>
                                                    </a>
                                                @endif

                                                {{-- LPJ --}}
                                                @if(optional($lpj)->lpj_document_path)
                                                    <a href="{{ Storage::url($lpj->lpj_document_path) }}"
                                                       target="_blank" rel="noopener noreferrer"
                                                       class="bg-blue-500 text-white px-3 py-1 rounded text-xs flex items-center gap-2"
                                                       title="Buka LPJ {{ $notification->notification_number }}">
                                                        <i class="fas fa-file-alt"></i><span>LPJ</span>
                                                    </a>
                                                @endif

                                                {{-- PPL --}}
                                                @if(optional($lpj)->ppl_document_path)
                                                    <a href="{{ Storage::url($lpj->ppl_document_path) }}"
                                                       target="_blank" rel="noopener noreferrer"
                                                       class="bg-orange-500 text-white px-3 py-1 rounded text-xs flex items-center gap-2"
                                                       title="Buka PPL {{ $notification->notification_number }}">
                                                        <i class="fas fa-file-alt"></i><span>PPL</span>
                                                    </a>
                                                @endif

                                                @if(
                                                    empty($notification->isHppAvailable) &&
                                                    empty(optional($po)->po_document_path) &&
                                                    empty($lhpp) &&
                                                    empty(optional($lpj)->lpj_document_path) &&
                                                    empty(optional($lpj)->ppl_document_path)
                                                )
                                                    <span class="text-gray-400 italic text-xs">Belum ada dokumen</span>
                                                @endif
                                            </div>

                                            {{-- Row 2: info pembayaran (termin) --}}
                                            <div class="w-full max-w-2xl border rounded-md px-3 py-2 bg-gray-50">
                                                <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                                                    <div class="flex items-center gap-3">
                                                        <div class="text-xs text-gray-500">Total LHPP</div>
                                                        <div class="font-medium text-slate-800">Rp {{ number_format($totalBiaya,0,',','.') }}</div>
                                                    </div>

                                                    <div class="flex items-center gap-3">
                                                        {{-- Termin 1 badge --}}
                                                        <div class="flex items-center gap-2">
                                                            <div class="text-xs text-gray-500">Termin 1</div>
                                                            @if($termin1Paid)
                                                                <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Sudah</span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-1 bg-yellow-50 text-yellow-800 text-xs rounded">Belum</span>
                                                            @endif
                                                        </div>

                                                        {{-- Termin 2 badge --}}
                                                        <div class="flex items-center gap-2">
                                                            <div class="text-xs text-gray-500">Termin 2</div>
                                                            @if($termin2Paid)
                                                                <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Sudah</span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-1 bg-yellow-50 text-yellow-800 text-xs rounded">Belum</span>
                                                            @endif
                                                        </div>

                                                        {{-- Paid amount --}}
                                                        <div class="flex items-center gap-2">
                                                            <div class="text-xs text-gray-500">Terbayar</div>
                                                            @if($paidPercent > 0)
                                                                <div class="font-medium text-slate-800">({{ $paidPercent }}%) Rp {{ number_format($paidAmount,0,',','.') }}</div>
                                                            @else
                                                                <div class="text-gray-400">-</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Catatan kecil tentang kebijakan pembayaran --}}
                                                <div class="mt-2 text-xs text-gray-500">
                                                    Catatan: Pembayaran Termin 1 = <strong>95%</strong> (selama garansi masih berlaku). Setelah Termin 2 dibayar penuh → total menjadi <strong>100%</strong>.
                                                </div>
                                            </div>

                                            {{-- Row 3: Garansi mulai / berakhir --}}
                                            <div class="w-full max-w-2xl flex items-center justify-between text-sm text-gray-700">
                                                <div>
                                                    <div class="text-xs text-gray-500">Mulai Garansi</div>
                                                    @if($garansiStart)
                                                        <div class="font-medium">{{ $garansiStart->format('Y-m-d') }}</div>
                                                    @else
                                                        <div class="text-gray-400">-</div>
                                                    @endif
                                                </div>

                                                <div>
                                                    <div class="text-xs text-gray-500">Berakhir Garansi</div>
                                                    @if($garansiEnd)
                                                        <div class="font-medium">{{ $garansiEnd->format('Y-m-d') }} <span class="text-xs text-gray-500">({{ $garansiMonths }} bln)</span></div>
                                                    @elseif($garansiMonths > 0)
                                                        <div class="text-gray-500 text-xs">Belum ada tanggal mulai untuk hitung akhir</div>
                                                    @else
                                                        <div class="text-gray-400">-</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-500 italic">Tidak ada data laporan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINATION -->
            <div class="mt-4">
                {{ $notifications->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</x-pkm-layout>
