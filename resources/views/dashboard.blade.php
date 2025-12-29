<x-app-layout>
    <x-slot name="header">
    <h2 class="font-semibold text-lg text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 lg:px-6">
<!-- ===== GRAFIK STATISTIK (3 cards) ===== -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <!-- Chart 1: Notification Approved -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow border border-gray-200 dark:border-gray-700 h-56">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <i class="fas fa-chart-bar text-blue-500"></i>
                Top 10 Unit Kerja — Approved
            </h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">Jumlah order disetujui</span>
        </div>
        <div class="relative h-40">
            <canvas id="chartNotifikasi"></canvas>
        </div>
    </div>

    <!-- Chart 2: Total Biaya LHPP -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow border border-gray-200 dark:border-gray-700 h-56">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <i class="fas fa-coins text-emerald-500"></i>
                Top 10 Unit Kerja — Total Biaya LHPP
            </h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">Total biaya pekerjaan (Rp)</span>
        </div>
        <div class="relative h-40">
            <canvas id="chartBiaya"></canvas>
        </div>
    </div>

    <!-- Chart 3: Kawat Las (Good Issue) -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow border border-gray-200 dark:border-gray-700 h-56">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <i class="fas fa-wrench text-indigo-500"></i>
                Kawat Las — Good Issue (Top Units)
            </h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">Jumlah order (Good Issue)</span>
        </div>

        <div class="flex items-center justify-between mb-2">
            <div class="text-xs text-gray-600 dark:text-gray-300">
                Total Good Issue: <strong class="text-gray-800 dark:text-gray-100">{{ $jumlahGoodIssue ?? 0 }}</strong>
            </div>
        </div>

        <div class="relative h-36">
            <canvas id="chartKawat"></canvas>
        </div>
    </div>

</div>

</div>

            <!-- ===== FILTER BAR ===== -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 mb-5 border border-gray-200 dark:border-gray-700">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
                    <!-- Notification number (search) -->
                    <div class="w-full sm:w-1/3">
                        <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300 mb-1 block">Cari Nomor Order</label>
                        <input type="text" name="notification_number" value="{{ request('notification_number') }}"
                               placeholder="Masukkan nomor Order..."
                               class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 text-sm">
                    </div>

                    <!-- Unit Work filter -->
                    <div class="w-full sm:w-1/4">
                        <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300 mb-1 block">Unit Kerja</label>
                        <select name="unit_work" class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 text-sm">
                            <option value="">-- Semua Unit --</option>
                            @foreach($units as $u)
                                <option value="{{ $u }}" {{ request('unit_work') == $u ? 'selected' : '' }}>
                                    {{ $u }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Entries & Sort -->
                    <div class="flex gap-2 items-end w-full sm:w-auto">
                        <div>
                            <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300 mb-1 block">Urutkan</label>
                            <select name="sortOrder" class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm">
                                <option  value="latest" {{ request('sortOrder') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="oldest" {{ request('sortOrder') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300 mb-1 block">Per halaman</label>
                            <select name="entries" class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm">
                                @foreach([10,25,50,100] as $n)
                                    <option value="{{ $n }}" {{ (int) request('entries', 10) === $n ? 'selected' : '' }}>{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2 mt-1">
                            <button type="submit" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
                                <i class="fas fa-filter mr-2"></i> Filter
                            </button>
                            <a href="{{ route('dashboard') }}" class="mt-6 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ===== TABLE SECTION ===== -->
            <div class="bg-white dark:bg-gray-900 p-5 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
                <h3 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                    <i class="fas fa-list"></i> Order Details
                </h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs border border-gray-200 dark:border-gray-700 rounded-lg">
                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold uppercase">Order Number</th>
                                <th class="px-3 py-2 text-left font-semibold uppercase">Job Name</th>
                                <th class="px-3 py-2 text-left font-semibold uppercase">Informasi Order</th>
                                <th class="px-3 py-2 text-left font-semibold uppercase">Approval HPP</th>
                                <th class="px-3 py-2 text-left font-semibold uppercase">Progress Pekerjaan</th>
                                <th class="px-3 py-2 text-left font-semibold uppercase">Total Biaya (LHPP)</th>
                                <th class="px-3 py-2 text-left font-semibold uppercase">Dokumen Laporan</th>
                            </tr>
                        </thead>
<tbody class="divide-y divide-gray-200 dark:divide-gray-700">
@forelse($notifications as $index => $notification)
    <tr class="{{ $index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900' }} hover:bg-gray-100 dark:hover:bg-gray-800 transition">
        {{-- Order Number --}}
        <td class="px-3 py-2 text-gray-800 dark:text-gray-200 font-medium">
            {{ $notification->notification_number }}
        </td>

        {{-- Job Name --}}
        <td class="px-4 py-3 align-top">
            <div class="space-y-2">
                <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100 tracking-tight">
                    {{ Str::limit($notification->job_name, 70) }}
                </span>

                <span class="inline-flex items-start w-full max-w-[34rem] px-3 py-2 rounded-2xl ring-1
                             bg-blue-50 ring-blue-200 text-blue-800
                             dark:bg-slate-800 dark:ring-slate-700 dark:text-blue-300">
                    <i class="fas fa-building mt-0.5 text-[12px] opacity-90"></i>
                    <span class="ml-2 leading-tight">
                        <span class="block text-[12.5px] font-semibold">
                            {{ $notification->unit_work }}
                        </span>

                        @if(!empty($notification->seksi))
                            <span class="block text-[11px] font-medium text-blue-700/90 dark:text-blue-300/80">
                                <i class="fas fa-sitemap text-[10px] mr-1 opacity-80"></i>
                                {{ $notification->seksi }}
                            </span>
                        @endif
                    </span>
                </span>
            </div>
        </td>

        {{-- Informasi Order + Verifikasi Anggaran --}}
        <td class="px-4 py-3 align-top">
            @php
                $status = $notification->status ?? 'Pending';
                $verif = $notification->verifikasiAnggaran ?? null;
            @endphp

            <div class="flex items-start gap-2 mb-2">
                <span class="px-2 py-1 rounded text-white text-xs
                    {{ $status === 'Pending' ? 'bg-yellow-500' : ($status === 'Reject' ? 'bg-red-500' : 'bg-green-500') }}">
                    {{ $status }}
                </span>
                <div class="text-[12px] leading-tight text-gray-600 dark:text-gray-300">
                    <div><strong>Catatan:</strong> {{ $notification->catatan ?? 'Tidak Ada Catatan' }}</div>
                </div>
            </div>

            <hr class="border-gray-300 dark:border-gray-700 my-2">

            @if($verif)
                <div class="flex flex-col gap-1">
                    <span class="inline-flex items-center px-2 py-1 rounded text-white text-xs
                        {{ ($verif->status_anggaran ?? '') === 'Tersedia' ? 'bg-green-500' : ((($verif->status_anggaran ?? '') === 'Tidak Tersedia') ? 'bg-red-500' : 'bg-yellow-400') }}">
                        {{ $verif->status_anggaran ?? 'Menunggu' }}
                    </span>

                    <div class="text-[12px] text-gray-600 dark:text-gray-300">
                        <div><strong>Cost Element:</strong> {{ $verif->cost_element ?? '-' }}</div>
                        <div><strong>Catatan:</strong> {{ $verif->catatan ?? '-' }}</div>
                    </div>
                </div>
            @else
                <div class="flex flex-col gap-1">
                    <span class="px-2 py-1 rounded text-white bg-gray-400 text-xs">Menunggu</span>
                    <div class="text-[12px] text-gray-500 italic">Belum diverifikasi</div>
                </div>
            @endif
        </td>

{{-- =======================
   APPROVAL HPP (USER)
======================= --}}
<td class="px-3 py-2 text-gray-700 dark:text-gray-300 text-sm leading-tight">
    @php
        $hpp    = $notification->hpp1 ?? null;
        $source = $hpp->source_form ?? null;

        // helper badge
        $badgeWaiting = 'inline-flex items-center gap-1 px-2 py-0.5 rounded
                         text-[11px] bg-red-100 text-red-700 ring-1 ring-red-200';
        $badgeDone    = 'inline-flex items-center gap-1 px-2 py-0.5 rounded
                         text-[11px] bg-green-100 text-green-700 ring-1 ring-green-200 font-semibold';
    @endphp

    @if (! $hpp)
        <span class="text-gray-500 italic">Belum dibuat</span>

    @else

        {{-- =================================================
           CREATE HPP 1
        ================================================= --}}
        @if ($source === 'createhpp1')
            @php
                $steps = [
                    ['field'=>'manager_signature','label'=>'Manager Bengkel Mesin'],
                    ['field'=>'senior_manager_signature','label'=>'Senior Manager Workshop'],
                    ['field'=>'manager_signature_requesting_unit','label'=>'Manager Peminta'],
                    ['field'=>'senior_manager_signature_requesting_unit','label'=>'Senior Manager Peminta'],
                    ['field'=>'general_manager_signature_requesting_unit','label'=>'General Manager Peminta'],
                    ['field'=>'general_manager_signature','label'=>'General Manager'],
                    ['field'=>'__director__','label'=>'Direktur Operasional'],
                ];

                $pending = null;
                foreach ($steps as $s) {
                    if ($s['field'] === '__director__') {
                        if (empty($hpp->director_uploaded_file)) {
                            $pending = $s['label'];
                        }
                        break;
                    }
                    if (empty($hpp->{$s['field']})) {
                        $pending = $s['label'];
                        break;
                    }
                }
            @endphp

            @if ($pending)
                <span class="{{ $badgeWaiting }}">
                    <i class="fas fa-clock text-[9px]"></i>
                    Menunggu TTD: {{ $pending }}
                </span>
            @else
                <span class="{{ $badgeDone }}">
                    <i class="fas fa-check-circle text-[9px]"></i>
                    Telah Ditandatangani Semua
                </span>
            @endif


        {{-- =================================================
           CREATE HPP 2 (TETAP PARTIAL)
        ================================================= --}}
        @elseif ($source === 'createhpp2')
            @include('admin.inputhpp.partials._status_hpp2', ['data' => $hpp])


        {{-- =================================================
           CREATE HPP 3 (KONSISTEN, TANPA UPLOAD)
        ================================================= --}}
        @elseif ($source === 'createhpp3')
            @php
                if (empty($hpp->manager_signature)) {
                    $pending = 'Manager Bengkel Mesin';
                } elseif (empty($hpp->senior_manager_signature)) {
                    $pending = 'Senior Manager Workshop';
                } elseif (empty($hpp->general_manager_signature)) {
                    $pending = 'General Manager';
                } elseif (empty($hpp->director_uploaded_file)) {
                    $pending = 'Direktur Operasional';
                } else {
                    $pending = null;
                }
            @endphp

            @if ($pending)
                <span class="{{ $badgeWaiting }}">
                    <i class="fas fa-clock text-[9px]"></i>
                    Menunggu TTD: {{ $pending }}
                </span>
            @else
                <span class="{{ $badgeDone }}">
                    <i class="fas fa-check-circle text-[9px]"></i>
                    Telah Ditandatangani Semua
                </span>
            @endif


        {{-- =================================================
           CREATE HPP 4 (TETAP PARTIAL)
        ================================================= --}}
        @elseif ($source === 'createhpp4')
            @include('admin.inputhpp.partials._status_hpp4', ['data' => $hpp])

        @else
            <span class="text-slate-400 italic">Tidak diketahui</span>
        @endif
    @endif
</td>


        {{-- Progress --}}
        <td class="px-3 py-2 text-center">
            @php
                $progress = optional($notification->purchaseOrder)->progress_pekerjaan ?? 0;
                $catatanPo = optional($notification->purchaseOrder)->catatan;
                $target = optional($notification->purchaseOrder)->target_penyelesaian;
            @endphp

            <div class="w-full bg-gray-300 dark:bg-gray-700 rounded-full h-2 overflow-hidden mb-1">
                <div class="h-2 bg-green-500 rounded-full" style="width: {{ $progress }}%;"></div>
            </div>
            <div class="text-xs text-gray-700 dark:text-gray-300">
                <span class="font-semibold">{{ $progress }}%</span>
                <div class="text-[11px] text-gray-500">{{ $catatanPo ?? 'Tidak ada catatan' }}</div>
                <div class="text-[11px]">
                    Target:
                    @if($target)
                        {{ \Carbon\Carbon::parse($target)->format('d M Y') }}
                    @else
                        <span class="text-red-500">Belum Ditentukan</span>
                    @endif
                </div>
            </div>
        </td>

        {{-- Total Biaya (LHPP) --}}
        <td class="px-3 py-2 text-gray-800 dark:text-gray-200 text-right font-medium">
            @if(optional($notification->lhpp)->total_biaya)
                Rp {{ number_format(optional($notification->lhpp)->total_biaya, 0, ',', '.') }}
            @else
                <span class="text-gray-500 italic">-</span>
            @endif
        </td>

        {{-- Dokumen Laporan: pakai Lpj helper + relasi Garansi --}}
        <td class="px-3 py-2 align-top">
            <div class="flex flex-col gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    @if($notification->lhpp)
                        <a href="{{ route('lhpp.show', $notification->notification_number) }}"
                           class="inline-flex items-center gap-1 bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs transition">
                            <i class="fas fa-file-alt"></i> LHPP
                        </a>
                    @else
                        <span class="text-gray-500 italic text-xs">LHPP: Tidak ada</span>
                    @endif

                    @if(optional($notification->purchaseOrder)->po_document_path)
                        <a href="{{ Storage::url(optional($notification->purchaseOrder)->po_document_path) }}" target="_blank"
                           class="inline-flex items-center gap-1 bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-xs transition">
                            <i class="fas fa-file-contract"></i> PO
                        </a>
                    @else
                        <span class="text-gray-500 italic text-xs">PO: Tidak ada</span>
                    @endif
                </div>

                @php $lpj = optional($notification->lpj); @endphp

                <div x-data="{ t: '1' }" class="flex items-center gap-2">
                    <select x-model="t" class="text-[11px] dark:text-gray-100 px-2 py-1 border rounded bg-white dark:bg-gray-800">
                        <option value="1">Termin 1</option>
                        <option value="2">Termin 2</option>
                    </select>

                    <div class="flex items-center gap-2">
                        <template x-if="t==='1'">
                            <div class="flex items-center gap-2">
                                @php $lpjPath1 = $lpj ? $lpj->getLpjPathForTermin(1) : null; @endphp
                                @if(!empty($lpjPath1))
                                    <a href="{{ Storage::url($lpjPath1) }}" target="_blank"
                                       class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition">
                                        <i class="fas fa-file-alt"></i> LPJ T1
                                    </a>
                                @else
                                    <span class="text-gray-500 italic text-xs">LPJ T1: -</span>
                                @endif

                                @php $pplPath1 = $lpj ? $lpj->getPplPathForTermin(1) : null; @endphp
                                @if(!empty($pplPath1))
                                    <a href="{{ Storage::url($pplPath1) }}" target="_blank"
                                       class="inline-flex items-center gap-1 bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded text-xs transition">
                                        <i class="fas fa-file-invoice"></i> PPL T1
                                    </a>
                                @else
                                    <span class="text-gray-500 italic text-xs">PPL T1: -</span>
                                @endif
                            </div>
                        </template>

                        <template x-if="t==='2'">
                            <div class="flex items-center gap-2">
                                @php $lpjPath2 = $lpj ? $lpj->getLpjPathForTermin(2) : null; @endphp
                                @if(!empty($lpjPath2))
                                    <a href="{{ Storage::url($lpjPath2) }}" target="_blank"
                                       class="inline-flex items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs transition">
                                        <i class="fas fa-file-alt"></i> LPJ T2
                                    </a>
                                @else
                                    <span class="text-gray-500 italic text-xs">LPJ T2: -</span>
                                @endif

                                @php $pplPath2 = $lpj ? $lpj->getPplPathForTermin(2) : null; @endphp
                                @if(!empty($pplPath2))
                                    <a href="{{ Storage::url($pplPath2) }}" target="_blank"
                                       class="inline-flex items-center gap-1 bg-orange-500 hover:bg-orange-600 text-white px-3 py-1 rounded text-xs transition">
                                        <i class="fas fa-file-invoice"></i> PPL T2
                                    </a>
                                @else
                                    <span class="text-gray-500 italic text-xs">PPL T2: -</span>
                                @endif
                            </div>
                        </template>
                    </div>
                </div>

              @php
    $garansi = optional($notification->garansi);
    $garansiStart = $garansi->start_date ?? null;
    $garansiEnd   = $garansi->end_date ?? null;

    if (!$garansiEnd && $lpj && ($lpj->garansi_months ?? 0) > 0) {
        $startCandidate = $lpj->update_date ?? null;
        $calcEnd = $lpj->calculateGaransiEndDate($startCandidate);
        if ($calcEnd) {
            $garansiStart = $garansiStart ?? ($startCandidate ? \Carbon\Carbon::parse($startCandidate)->format('Y-m-d') : null);
            $garansiEnd = $calcEnd->format('Y-m-d'); // pastikan Y-m-d
        }
    }

    // buat versi untuk tampilan dan versi untuk data-end (Y-m-d)
    try {
        $garansiStartFmt = $garansiStart ? \Carbon\Carbon::parse($garansiStart)->format('d M Y') : null;
    } catch(\Throwable $e) { $garansiStartFmt = null; }

    try {
        $garansiEndFmt = $garansiEnd ? \Carbon\Carbon::parse($garansiEnd)->format('d M Y') : null;
    } catch(\Throwable $e) { $garansiEndFmt = null; }

    // data-end must be Y-m-d or null
    $garansiEndForData = $garansiEnd ? \Carbon\Carbon::parse($garansiEnd)->format('Y-m-d') : null;
    $garansiStartForData = $garansiStart ? \Carbon\Carbon::parse($garansiStart)->format('Y-m-d') : '';
@endphp

                <div class="mt-2 text-xs text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <div>
                            <div class="text-[11px] text-gray-500">Mulai</div>
                            <div class="font-medium">{{ $garansiStartFmt ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-[11px] text-gray-500">Berakhir</div>
                            <div class="font-medium">{{ $garansiEndFmt ?? '-' }}</div>
                        </div>

                        <div>
@if($garansiEndForData)
    <span class="garansi-countdown inline-flex items-center px-2 py-0.5 rounded text-xs"
          data-end="{{ $garansiEndForData }}"
          data-start="{{ $garansiStartForData }}"
          aria-live="polite">Menghitung...</span>
@else
    <span class="text-gray-400 text-xs">-</span>
@endif
                        </div>
                    </div>
                </div>

            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="py-8 text-center text-gray-500 dark:text-gray-400">
            Tidak ada data order ditemukan.
        </td>
    </tr>
@endforelse
</tbody>

                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $notifications->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
  @push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // detect dark mode by presence of 'dark' class on html (Tailwind typical)
    const root = document.documentElement;
    const isDark = root.classList.contains('dark');

    // colors tuned for readability
    const textColor = isDark ? '#D1D5DB' : '#374151';         // light: gray-700, dark: gray-300
    const gridColor = isDark ? 'rgba(255,255,255,0.04)' : 'rgba(15,23,42,0.05)';

    // apply defaults globally
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily || 'Inter, system-ui, sans-serif';
    Chart.defaults.color = textColor;
    Chart.defaults.plugins.legend.labels.color = textColor;

    // helper to build bar chart with shared options
    function makeBarChart(canvasId, labels, dataset, yTickCallback = null) {
        const el = document.getElementById(canvasId);
        if (!el) return;
        const ctx = el.getContext('2d');

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [dataset]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                layout: { padding: { top: 6, bottom: 6 } },
                scales: {
                    x: {
                        ticks: {
                            color: textColor,
                            maxRotation: 0,
                            minRotation: 0,
                            callback: function(value) {
                                const label = this.getLabelForValue(value) || '';
                                return label.length > 24 ? label.slice(0, 24) + '…' : label;
                            }
                        },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            callback: yTickCallback ? yTickCallback : (v) => v
                        },
                        grid: { color: gridColor }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        labels: { color: textColor }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.dataset && ctx.dataset._tooltipIsCurrency) {
                                    const val = ctx.raw ?? 0;
                                    return ctx.dataset.label + ': Rp ' + Number(val).toLocaleString('id-ID');
                                }
                                return ctx.dataset.label + ': ' + ctx.raw;
                            }
                        }
                    }
                }
            }
        });
    }

    // prepare chart data (from controller)
    const labels = @json($chartData['labels'] ?? []);
    const notifCounts = @json($chartData['notification_counts'] ?? []);
    const totalBiaya = @json($chartData['total_biaya'] ?? []);
    const kawatLabels = @json($chartKawat['labels'] ?? []);
    const kawatCounts = @json($chartKawat['counts'] ?? []);

    // dataset 1: notification counts
    const dsNotif = {
        label: 'Jumlah Notification Approved',
        data: notifCounts,
        backgroundColor: isDark ? 'rgba(99,102,241,0.85)' : 'rgba(59,130,246,0.85)', // indigo / blue
        borderColor: isDark ? 'rgba(99,102,241,1)' : 'rgba(59,130,246,1)',
        borderWidth: 1,
        borderRadius: 6,
        barThickness: 'flex'
    };

    // dataset 2: total biaya (mark tooltip as currency)
    const dsBiaya = {
        label: 'Total Biaya LHPP (Rp)',
        data: totalBiaya,
        backgroundColor: isDark ? 'rgba(16,185,129,0.85)' : 'rgba(16,185,129,0.75)', // emerald
        borderColor: isDark ? 'rgba(16,185,129,1)' : 'rgba(5,150,105,1)',
        borderWidth: 1,
        borderRadius: 6,
        barThickness: 'flex',
        _tooltipIsCurrency: true // custom flag so tooltip callback knows to format currency
    };

    // dataset 3: kawat las (Good Issue)
    const dsKawat = {
        label: 'Jumlah Order Good Issue',
        data: kawatCounts,
        backgroundColor: isDark ? 'rgba(99,102,241,0.75)' : 'rgba(99,102,241,0.65)', // blue/purple tone
        borderColor: isDark ? 'rgba(79,70,229,1)' : 'rgba(37,99,235,1)',
        borderWidth: 1,
        borderRadius: 6,
        barThickness: 'flex'
    };

    // === Create Charts ===
    makeBarChart('chartNotifikasi', labels, dsNotif, (v) => v);
    makeBarChart('chartBiaya', labels, dsBiaya, (v) => 'Rp ' + Number(v).toLocaleString('id-ID'));
    makeBarChart('chartKawat', kawatLabels, dsKawat, (v) => v);
});
// ===== Garansi countdown badge (sama seperti di laporan) =====
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
        const msPerDay = 24 * 60 * 60 * 1000;
        const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const startOfEnd = new Date(end.getFullYear(), end.getMonth(), end.getDate());
        const diffDays = Math.floor((startOfEnd - startOfToday) / msPerDay);

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

    // init badges (works for multiple rows)
    const els = document.querySelectorAll('.garansi-countdown');
    els.forEach(updateGaransiBadge);

    // optional: refresh every 6 hours
    // setInterval(() => document.querySelectorAll('.garansi-countdown').forEach(updateGaransiBadge), 1000 * 60 * 60 * 6);
});

</script>

<style>
canvas {
    display: block;
    width: 100% !important;
    height: 100% !important;
}
</style>
@endpush

</x-app-layout>
