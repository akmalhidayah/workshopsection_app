<x-pkm-layout>
@php
    $now = \Carbon\Carbon::now();

    $targets = collect($targetDates ?? [])
        ->map(function ($t) use ($now) {
            $d = \Carbon\Carbon::parse($t['date']);
            return [
                'date' => $d,
                'date_str' => $d->format('Y-m-d'),
                'label' => $t['description'] ?? '-',
                'is_overdue' => $d->isPast() && !$d->isToday(),
                'is_today' => $d->isToday(),
                'days_left' => $now->copy()->startOfDay()->diffInDays($d->copy()->startOfDay(), false),
            ];
        })
        ->sortBy('date')
        ->values();

    $overdueCount = $targets->where('is_overdue', true)->count();
    $todayCount   = $targets->where('is_today', true)->count();
    $soonCount    = $targets->filter(fn($t) => !$t['is_overdue'] && !$t['is_today'] && $t['days_left'] >= 0 && $t['days_left'] <= 7)->count();

    $calendarMonth = $now->copy()->startOfMonth();
    $start = $calendarMonth->copy()->startOfWeek(); // Minggu
    $end   = $calendarMonth->copy()->endOfMonth()->endOfWeek();
    $cursor = $start->copy();
@endphp

<div class="space-y-5">

    <!-- HEADER -->
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-[22px] md:text-[26px] font-extrabold tracking-tight text-slate-900">
                Dashboard PKM
            </h1>
            <p class="text-[12.5px] text-slate-600">
                Selamat datang, <span class="font-semibold text-slate-800">{{ Auth::user()->name }}</span>.
                Ringkasan pekerjaan & target penyelesaian.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('pkm.jobwaiting') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-4 py-2 text-[12.5px] font-semibold text-white shadow-sm hover:bg-orange-700 transition">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                Job Waiting
            </a>

            <a href="{{ route('pkm.laporan') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-[12.5px] font-semibold text-orange-700 ring-1 ring-orange-200 hover:bg-orange-50 transition">
                <i data-lucide="folder-open" class="w-4 h-4"></i>
                Dokumen
            </a>
        </div>
    </div>

    <!-- KPI CARDS (BERWARNA) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

        <!-- Total -->
        <div class="rounded-2xl p-[1px] bg-gradient-to-br from-orange-200 via-orange-100 to-white">
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Pekerjaan</p>
                        <p class="mt-1 text-[28px] leading-none font-extrabold text-slate-900">{{ $totalPekerjaan }}</p>
                        <p class="mt-2 text-[12px] text-slate-600">Pekerjaan yang sedang dikelola</p>
                    </div>

                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-orange-50 text-orange-700 ring-1 ring-orange-100">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </span>
                </div>
            </div>
        </div>

        <!-- Menunggu -->
        <div class="rounded-2xl p-[1px] bg-gradient-to-br from-amber-200 via-amber-100 to-white">
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Menunggu</p>
                        <p class="mt-1 text-[28px] leading-none font-extrabold text-slate-900">{{ $pekerjaanMenunggu }}</p>
                        <p class="mt-2 text-[12px] text-slate-600">Progress belum 100%</p>
                    </div>

                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-700 ring-1 ring-amber-100">
                        <i data-lucide="hourglass" class="w-5 h-5"></i>
                    </span>
                </div>
            </div>
        </div>

        <!-- Progress -->
        <div class="rounded-2xl p-[1px] bg-gradient-to-br from-orange-200 via-orange-100 to-white">
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Progress</p>
                        <p class="mt-1 text-[28px] leading-none font-extrabold text-slate-900">{{ round($totalProgress, 2) }}%</p>
                        <p class="mt-2 text-[12px] text-slate-600">Rata-rata seluruh pekerjaan</p>
                    </div>

                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-orange-50 text-orange-700 ring-1 ring-orange-100">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                    </span>
                </div>

                <div class="mt-3">
                    <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-orange-600"
                             style="width: {{ max(0, min(100, $totalProgress)) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="rounded-2xl p-[1px] bg-gradient-to-br from-emerald-200 via-emerald-100 to-white">
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Selesai</p>
                        <p class="mt-1 text-[28px] leading-none font-extrabold text-slate-900">{{ $pekerjaanSelesai }}</p>
                        <p class="mt-2 text-[12px] text-slate-600">Progress sudah 100%</p>
                    </div>

                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                        <i data-lucide="badge-check" class="w-5 h-5"></i>
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- STATUS MINI CARDS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <div class="rounded-2xl border border-red-100 bg-gradient-to-r from-red-50 to-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-red-100 text-red-700">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <p class="text-[12.5px] font-bold text-slate-900 leading-tight">Overdue</p>
                        <p class="text-[11px] text-slate-600 leading-tight">Melewati target</p>
                    </div>
                </div>
                <div class="text-[22px] font-extrabold text-slate-900">{{ $overdueCount }}</div>
            </div>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-gradient-to-r from-orange-50 to-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-orange-100 text-orange-700">
                        <i data-lucide="calendar-days" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <p class="text-[12.5px] font-bold text-slate-900 leading-tight">Hari Ini</p>
                        <p class="text-[11px] text-slate-600 leading-tight">Jatuh tempo hari ini</p>
                    </div>
                </div>
                <div class="text-[22px] font-extrabold text-slate-900">{{ $todayCount }}</div>
            </div>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-gradient-to-r from-amber-50 to-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-800">
                        <i data-lucide="timer" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <p class="text-[12.5px] font-bold text-slate-900 leading-tight">7 Hari ke Depan</p>
                        <p class="text-[11px] text-slate-600 leading-tight">Target mendekat</p>
                    </div>
                </div>
                <div class="text-[22px] font-extrabold text-slate-900">{{ $soonCount }}</div>
            </div>
        </div>
    </div>

    <!-- GRID CONTENT -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-3">

        <!-- TASK LIST -->
        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-[14px] font-extrabold text-slate-900">Target Pekerjaan</h2>
                    <a href="{{ route('pkm.jobwaiting') }}" class="text-[12px] font-semibold text-orange-700 hover:underline">
                        Lihat detail
                    </a>
                </div>
                <p class="mt-1 text-[12px] text-slate-600">Urut berdasarkan tanggal target.</p>
            </div>

            <div class="p-3 max-h-[520px] overflow-y-auto no-scrollbar">
                @forelse($targets as $t)
                    @php
                        $badge =
                            $t['is_overdue'] ? ['text'=>'Overdue','cls'=>'bg-red-100 text-red-800 ring-red-200'] :
                            ($t['is_today'] ? ['text'=>'Hari ini','cls'=>'bg-orange-100 text-orange-800 ring-orange-200'] :
                            ($t['days_left'] >= 0 && $t['days_left'] <= 7 ? ['text'=>'Soon','cls'=>'bg-amber-100 text-amber-900 ring-amber-200'] :
                            ['text'=>'Upcoming','cls'=>'bg-slate-100 text-slate-800 ring-slate-200']));
                    @endphp

                    <div class="group rounded-2xl border border-slate-200 p-4 mb-3 hover:border-orange-200 hover:bg-orange-50/40 transition">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[13px] font-bold text-slate-900 truncate">
                                    {{ $t['label'] }}
                                </p>

                                <div class="mt-1 flex flex-wrap items-center gap-2 text-[11.5px] text-slate-600">
                                    <span class="inline-flex items-center gap-1">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                        {{ $t['date']->format('d M Y') }}
                                    </span>

                                    <span class="text-slate-300">•</span>

                                    <span class="font-medium">
                                        @if($t['is_overdue'])
                                            {{ abs($t['days_left']) }} hari terlambat
                                        @elseif($t['is_today'])
                                            jatuh tempo hari ini
                                        @elseif($t['days_left'] >= 0)
                                            {{ $t['days_left'] }} hari lagi
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <span class="shrink-0 inline-flex items-center rounded-full px-2 py-1 text-[11px] font-bold ring-1 {{ $badge['cls'] }}">
                                {{ $badge['text'] }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-600">
                        <div class="mx-auto mb-2 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                            <i data-lucide="inbox" class="w-6 h-6"></i>
                        </div>
                        <p class="font-bold text-slate-800">Belum ada target pekerjaan.</p>
                        <p class="text-[12px]">Target muncul jika PO punya target penyelesaian.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- CALENDAR -->
        <div class="lg:col-span-3 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-[14px] font-extrabold text-slate-900">Kalender Target</h2>
                    <p class="mt-1 text-[12px] text-slate-600">
                        {{ $calendarMonth->translatedFormat('F Y') }}
                    </p>
                </div>

                <div class="hidden sm:flex items-center gap-3 text-[11px] text-slate-600">
                    <span class="inline-flex items-center gap-2">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-orange-500"></span> Target
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span> Overdue
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-400"></span> Today
                    </span>
                </div>
            </div>

            <div class="p-3 overflow-x-auto">
                <table class="min-w-full border-separate border-spacing-2">
                    <thead>
                        <tr class="text-[11px] text-slate-600">
                            @foreach(['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $d)
                                <th class="px-2 py-1 text-center font-bold">{{ $d }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @while($cursor <= $end)
                            <tr>
                                @for($i=0; $i<7; $i++)
                                    @php
                                        $inMonth = $cursor->month === $calendarMonth->month;
                                        $key = $cursor->format('Y-m-d');

                                        $isTarget = $targets->contains(fn($t) => $t['date_str'] === $key);
                                        $isToday  = $cursor->isToday();
                                        $isOverdueTarget = $targets->contains(fn($t) => $t['date_str'] === $key && $t['is_overdue']);

                                        $dotClass = $isOverdueTarget ? 'bg-red-500'
                                                  : ($isToday ? 'bg-amber-400' : 'bg-orange-500');
                                    @endphp

                                    <td class="align-top">
                                        <div class="h-[86px] rounded-2xl border transition
                                            {{ $inMonth ? 'border-slate-200 bg-white hover:bg-orange-50/40' : 'border-transparent bg-slate-50' }}
                                            {{ $isToday ? 'ring-2 ring-amber-300' : '' }}
                                        ">
                                            <div class="flex items-center justify-between px-2 pt-2">
                                                <span class="text-[11px] font-bold {{ $inMonth ? 'text-slate-800' : 'text-slate-400' }}">
                                                    {{ $inMonth ? $cursor->day : '' }}
                                                </span>

                                                @if($isTarget)
                                                    <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $dotClass }}"></span>
                                                @endif
                                            </div>

                                            @if($isTarget && $inMonth)
                                                <div class="px-2 pt-2">
                                                    <div class="text-[11px] text-slate-600">
                                                        Target
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    @php $cursor->addDay(); @endphp
                                @endfor
                            </tr>
                        @endwhile
                    </tbody>
                </table>
            </div>

            <div class="px-4 pb-4 text-[11.5px] text-slate-600">
                Klik “Job Waiting” untuk detail pekerjaan & update progress.
            </div>
        </div>

    </div>

</div>

<style>
    .no-scrollbar{ -ms-overflow-style:none; scrollbar-width:none; }
    .no-scrollbar::-webkit-scrollbar{ display:none; }
</style>
</x-pkm-layout>
