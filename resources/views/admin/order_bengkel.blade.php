<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-sm text-gray-700 leading-tight">Order Pekerjaan Bengkel</h2>
    </x-slot>

    @php
        $chipBase = 'inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full text-white shadow-sm';
        $progressOptions = [
            'menunggu_jadwal' => 'Menunggu Jadwal',
            'in_progress' => 'Sementara Proses',
            'done' => 'Selesai',
        ];
        $materialOptions = [
            'Good Issue' => 'Good Issue',
            'Transport Material' => 'Transport Material',
        ];
        $reguOptions = $reguOptions ?? ['Regu Fabrikasi', 'Regu Bengkel (Refurbish)'];
    @endphp

    <div class="py-6">
        <div class="w-full max-w-[100%] mx-auto">
            <!-- Header: title + filters -->
            <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-200 mb-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Order Pekerjaan Bengkel</h3>
                        <p class="text-xs text-slate-500 mt-1">Daftar order yang diarahkan ke Bengkel Mesin</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <input id="searchOrder" name="search" value="{{ request('search') }}"
                                   type="text" placeholder="Cari nomor / deskripsi / unit..."
                                   class="text-[11px] rounded-md px-2.5 py-1.5 w-64 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                            <button id="clearSearch" type="button" class="absolute right-1 top-1/2 -translate-y-1/2 text-[10px] text-gray-600">Clear</button>
                        </div>

                        <select id="filterProgress" name="progress" class="text-[11px] rounded-md px-2 py-1.5">
                            <option value="">Semua Progress</option>
                            @foreach($progressOptions as $val => $label)
                                <option value="{{ $val }}" {{ request('progress') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>

                        <select id="perPage" name="perPage" class="text-[11px] rounded-md px-2 py-1.5">
                            <option value="10" {{ request('perPage', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        </select>

                        <select id="filterRegu" name="regu" class="text-[11px] rounded-md px-2 py-1.5 border">
                            <option value="">Semua Regu</option>
                            @foreach($reguOptions as $r)
                                <option value="{{ $r }}" {{ request('regu') == $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-[10px] order-table">
                        <thead class="bg-gray-200">
                            <tr class="text-left text-[10px] text-gray-600 uppercase">
                                <th class="px-3 py-2 w-36">Nomor</th>
                                <th class="px-3 py-2">Deskripsi & Dokumen</th>
                                <th class="px-3 py-2 w-44">Unit / Seksi</th>
                                <th class="px-3 py-2 w-44">Konfirmasi Anggaran</th>
                                <th class="px-3 py-2 w-36">Status Material</th>
                                <th class="px-3 py-2 w-36">Progress Pekerjaan</th>
                                <th class="px-3 py-2 w-52">Catatan</th>
                            </tr>
                        </thead>

                        <tbody id="orderTableBody" class="bg-white divide-y divide-gray-100">
                            @forelse($orders as $order)
                                @php
                                    $n = $order->notification;
                                    $notifNumber = $order->notification_number;
                                    $jobName = $n?->job_name ?? '-';
                                    $unitWork = $n?->unit_work ?? '-';
                                    $seksi = $n?->seksi ?? '-';
                                    $inputDate = $n?->input_date ? \Carbon\Carbon::parse($n->input_date)->format('d-m-Y') : '-';
                                    $dokumenOrders = $n?->dokumenOrders ?? collect();

                                    // flags from controller
                                    $showMaterial = $order->show_material ?? false;
                                    $showProgress = $order->show_progress ?? false;
                                    $showEkorin = $order->show_ekorin ?? false;

                                    $konfirmasi_anggaran = $order->konfirmasi_anggaran ?? '';
                                    $keterangan_konfirmasi = $order->keterangan_konfirmasi ?? '';

                                    $status_material = $order->status_material ?? null;
                                    $keterangan_material = $order->keterangan_material ?? '';

                                    $progress_status = $order->progress_status ?? null;
                                    $keterangan_progress = $order->keterangan_progress ?? '';

                                    $catatan_order = $order->catatan_order ?? '-';
                                @endphp

                                <tr class="hover:bg-gray-50 align-top" data-notif="{{ $notifNumber }}">
                                    <!-- Nomor -->
                                    <td class="px-3 py-3 align-top w-36">
                                        <div class="text-[10px] font-medium text-slate-800">{{ $notifNumber }}</div>
                                        <div class="text-[9px] text-gray-400 mt-1">Tanggal: {{ $inputDate }}</div>
                                    </td>

                                    <!-- Deskripsi + dokumen (vertical) -->
                                    <td class="px-3 py-3 align-top">
                                        <div class="font-semibold text-[10px] text-slate-800">{{ \Illuminate\Support\Str::limit($jobName, 200) }}</div>

                                        @if($dokumenOrders->isNotEmpty() || ($n && $n->isScopeOfWorkAvailable))
                                            <div class="mt-2 flex flex-col gap-2">
                                                @foreach($dokumenOrders as $dok)
                                                    @php
                                                        $label = \Illuminate\Support\Str::title(str_replace(['_','-'], ' ', $dok->jenis_dokumen));
                                                        $color = match($dok->jenis_dokumen) {
                                                            'abnormalitas' => 'bg-red-500 hover:bg-red-600',
                                                            'gambar_teknik' => 'bg-blue-400 hover:bg-blue-500',
                                                            default => 'bg-gray-400 hover:bg-gray-500'
                                                        };
                                                    @endphp
                                                    <a href="{{ route('dokumen_orders.view', [$notifNumber, $dok->jenis_dokumen]) }}" target="_blank"
                                                       class="{{ $color }} text-white px-3 py-1 rounded-lg text-xs transition w-max inline-flex items-center gap-2">
                                                        <i class="fas fa-file-alt text-[11px]"></i>
                                                        <span class="text-[10px]">{{ $label }}</span>
                                                    </a>
                                                @endforeach

                                                @if($n && $n->isScopeOfWorkAvailable)
                                                    <a href="{{ route('dokumen_orders.scope.download_pdf', $notifNumber) }}" target="_blank"
                                                       class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-xs transition w-max inline-flex items-center gap-2">
                                                        <i class="fas fa-file-pdf text-[11px]"></i>
                                                        <span class="text-[10px]">Scope of Work</span>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Unit -->
                                    <td class="px-3 py-3 w-44 align-top">
                                        <div class="text-[10px] font-medium text-slate-800">{{ $unitWork }}</div>
                                        <div class="text-[9px] text-gray-400 mt-1">{{ $seksi }}</div>
                                    </td>

                                    <!-- Konfirmasi Anggaran (always shown) -->
                                    <td class="px-2 py-3 align-top w-44">
                                        <input type="hidden" class="notif-number" value="{{ $notifNumber }}">
                                        <div class="space-y-2">
                                            <div class="relative">
                                                <select name="konfirmasi_anggaran"
                                                        class="auto-save-select block w-full text-[10px] text-slate-800 h-9 px-2.5 pr-8 rounded-md border shadow-sm bg-white"
                                                        data-field="konfirmasi_anggaran">
                                                    <option value="" {{ $konfirmasi_anggaran === '' ? 'selected' : '' }}>Pilih Status Konfirmasi</option>
                                                    <option value="Material Ready" {{ $konfirmasi_anggaran === 'Material Ready' ? 'selected' : '' }}>Material Ready</option>
                                                    <option value="Material Not Ready" {{ $konfirmasi_anggaran === 'Material Not Ready' ? 'selected' : '' }}>Material Not Ready</option>
                                                </select>
                                                <div class="absolute right-2 top-1 save-indicator hidden text-[9px] text-gray-500">...</div>
                                            </div>

                                            <div class="flex items-start gap-2 mt-1">
                                                <textarea name="keterangan_konfirmasi"
                                                          class="note-textarea flex-1 text-[10px] text-slate-800 h-10 rounded-md border px-2 py-1 resize-none"
                                                          placeholder="Keterangan konfirmasi...">{{ $keterangan_konfirmasi }}</textarea>

                                                <button type="button"
                                                        class="save-note-btn inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-600 text-white shadow"
                                                        data-field="keterangan_konfirmasi" title="Simpan keterangan konfirmasi">
                                                    <i class="fas fa-save text-[11px]"></i>
                                                </button>
                                            </div>

                                            @if($showEkorin)
                                                <div class="mt-2">
                                                    <div class="text-[9px] text-gray-600 mb-1">Tindakan saat material belum ready</div>
                                                    <div class="relative mb-2">
                                                        <select name="status_anggaran"
                                                                class="auto-save-select block w-full text-[10px] text-slate-800 h-9 px-2.5 pr-8 rounded-md border shadow-sm bg-white"
                                                                data-field="status_anggaran">
                                                            <option value="Waiting Budget" {{ ($order->status_anggaran ?? '') === 'Waiting Budget' ? 'selected' : '' }}>Waiting Budget</option>
                                                            <option value="Pending" {{ ($order->status_anggaran ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                                        </select>
                                                        <div class="absolute right-2 top-1 save-indicator hidden text-[9px] text-gray-500">...</div>
                                                    </div>

                                                    <div class="mt-1 p-2 rounded-md bg-slate-50 border border-slate-200 shadow-sm space-y-1">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <i class="fas fa-file-invoice text-indigo-600 text-[12px]"></i>
                                                            <span class="text-[10px] font-semibold text-slate-700">E-KORIN</span>
                                                        </div>

                                                        <div class="flex items-center gap-2 text-[9px]">
                                                            <i class="fas fa-hashtag text-slate-500"></i>
                                                            <span class="opacity-60">Nomor:</span>
                                                            @if($order->nomor_e_korin)
                                                                <span class="font-mono font-semibold text-slate-800 text-[10px]">{{ $order->nomor_e_korin }}</span>
                                                            @else
                                                                <span class="italic text-gray-400">Belum diinput</span>
                                                            @endif
                                                        </div>

                                                        <div class="flex items-center gap-2 text-[9px]">
                                                            <i class="fas fa-info-circle text-slate-500"></i>
                                                            <span class="opacity-60">Status:</span>
                                                            @if($order->status_e_korin)
                                                                <span class="px-2 py-0.5 rounded text-white bg-green-500 text-[9px] font-medium shadow-sm">{{ ucwords(str_replace('_',' ', $order->status_e_korin)) }}</span>
                                                            @else
                                                                <span class="italic text-gray-400">Belum diinput</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status Material: if not shown, display muted dash so header aligns -->
                                    <td class="px-2 py-3 align-top w-36">
                                        @if($showMaterial)
                                            <div class="space-y-1">
                                                <div class="relative">
                                                    <select name="status_material"
                                                            class="auto-save-select block w-full text-[10px] text-slate-800 h-9 px-2.5 pr-8 rounded-md border shadow-sm bg-white"
                                                            data-field="status_material">
                                                        @foreach($materialOptions as $val => $label)
                                                            <option value="{{ $val }}" {{ $status_material === $val ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="absolute right-2 top-1 save-indicator hidden text-[9px] text-gray-500">...</div>
                                                </div>

                                                <div class="flex items-start gap-2 mt-1">
                                                    <textarea name="keterangan_material"
                                                              class="note-textarea flex-1 text-[10px] text-slate-800 h-10 rounded-md border px-2 py-1 resize-none"
                                                              placeholder="Catatan...">{{ $keterangan_material }}</textarea>

                                                    <button type="button"
                                                            class="save-note-btn inline-flex items-center justify-center w-8 h-8 rounded-md bg-sky-600 text-white shadow"
                                                            data-field="keterangan_material" title="Simpan catatan material">
                                                        <i class="fas fa-save text-[11px]"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-[10px] text-gray-400 italic">—</div>
                                        @endif
                                    </td>

                                    <!-- Progress (show placeholder if hidden) -->
                                    <td class="px-2 py-3 align-top w-36">
                                        @if($showProgress)
                                            <div class="space-y-1">
                                                <div class="relative">
                                                    <select name="progress_status"
                                                            class="auto-save-select block w-full text-[10px] text-slate-800 h-9 px-2.5 pr-8 rounded-md border shadow-sm bg-white"
                                                            data-field="progress_status">
                                                        @foreach($progressOptions as $val => $label)
                                                            <option value="{{ $val }}" {{ $progress_status === $val ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="absolute right-2 top-1 save-indicator hidden text-[9px] text-gray-500">...</div>
                                                </div>

                                                <div class="flex items-start gap-2 mt-1">
                                                    <textarea name="keterangan_progress"
                                                              class="note-textarea flex-1 text-[10px] text-slate-800 h-10 rounded-md border px-2 py-1 resize-none"
                                                              placeholder="Catatan...">{{ $keterangan_progress }}</textarea>

                                                    <button type="button"
                                                            class="save-note-btn inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-600 text-white shadow"
                                                            data-field="keterangan_progress" title="Simpan catatan progress">
                                                        <i class="fas fa-save text-[11px]"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-[10px] text-gray-400 italic">—</div>
                                        @endif
                                    </td>

                                    <!-- Catatan box -->
                                    <td class="px-3 py-3 text-[10px] text-gray-700 w-52 align-top">
                                        <div class="bg-slate-50 border border-slate-200 rounded-md p-3 shadow-sm">
                                            <strong class="text-[10px] text-slate-800">{{ $catatan_order }}</strong>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-gray-500 text-[11px]">Tidak ada order bengkel untuk ditampilkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-3 py-2 bg-gray-50 flex items-center justify-between">
                    <div class="text-[11px] text-gray-600">
                        Menampilkan <strong>{{ $orders->firstItem() ?: 0 }}</strong> - <strong>{{ $orders->lastItem() ?: 0 }}</strong> dari <strong>{{ $orders->total() }}</strong>
                    </div>
                    <div>
                        {{ $orders->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const updateUrlTemplate = "{{ route('admin.orderbengkel.update', ':notification') }}";

        function toast(msg, type = 'success') {
            Swal.fire({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1300, icon: type, title: msg });
        }

        function setIndicator(el, show) {
            const ind = el.closest('div.relative')?.querySelector('.save-indicator');
            if (!ind) return;
            ind.classList.toggle('hidden', !show);
        }

        function setControlDisabled(el, disabled) {
            const container = el.closest('tr');
            if (!container) return;
            container.querySelectorAll('select, button, textarea').forEach(i => i.disabled = disabled);
        }

        async function sendPatch(url, payload, indicatorEl = null) {
            if (indicatorEl) { setIndicator(indicatorEl, true); setControlDisabled(indicatorEl, true); }
            try {
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json().catch(()=>null);
                if (!res.ok) { toast(data?.error || data?.message || 'Gagal menyimpan', 'error'); return null; }
                toast(data?.message || 'Berhasil disimpan', 'success');
                return data;
            } catch (err) { console.error(err); toast('Kesalahan jaringan', 'error'); return null; }
            finally { if (indicatorEl) { setIndicator(indicatorEl, false); setControlDisabled(indicatorEl, false); } }
        }

        function urlForNotification(notificationNumber) { return updateUrlTemplate.replace(':notification', encodeURIComponent(notificationNumber)); }

        // autosave selects
        document.querySelectorAll('.auto-save-select').forEach(sel => {
            sel.addEventListener('change', async function () {
                const row = sel.closest('tr');
                const notificationNumber = row.querySelector('.notif-number')?.value;
                if (!notificationNumber) return;
                const field = sel.dataset.field || sel.name;
                const payload = {}; payload[field] = sel.value;
                await sendPatch(urlForNotification(notificationNumber), payload, sel);
                // reload to let controller recompute flags and keep UI consistent
                setTimeout(()=> location.reload(), 600);
            });
        });

        // save-note buttons
        document.querySelectorAll('.save-note-btn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const field = btn.dataset.field;
                const row = btn.closest('tr');
                const notificationNumber = row.querySelector('.notif-number')?.value;
                if (!notificationNumber) return;
                const ta = row.querySelector(`textarea[name="${field}"]`);
                if (!ta) return;
                const payload = {}; payload[field] = ta.value.trim();
                await sendPatch(urlForNotification(notificationNumber), payload, btn);
            });
        });

        // filters
        const searchInput = document.getElementById('searchOrder');
        const clearBtn = document.getElementById('clearSearch');
        const progressSel = document.getElementById('filterProgress');
        const perPageSel = document.getElementById('perPage');
        const reguSel = document.getElementById('filterRegu');

        function applyFilters() {
            const params = new URLSearchParams(window.location.search);
            if (searchInput.value.trim()) params.set('search', searchInput.value.trim()); else params.delete('search');
            if (progressSel.value) params.set('progress', progressSel.value); else params.delete('progress');
            if (perPageSel.value) params.set('perPage', perPageSel.value); else params.delete('perPage');
            if (reguSel.value) params.set('regu', reguSel.value); else params.delete('regu');
            params.delete('page');
            window.location.search = params.toString();
        }

        searchInput.addEventListener('keypress', function (e) { if (e.key === 'Enter') { e.preventDefault(); applyFilters(); } });
        clearBtn.addEventListener('click', function (e) { e.preventDefault(); searchInput.value=''; progressSel.value=''; perPageSel.value='10'; reguSel.value=''; applyFilters(); });
        progressSel.addEventListener('change', applyFilters);
        perPageSel.addEventListener('change', applyFilters);
        reguSel.addEventListener('change', applyFilters);
    })();
    </script>

    <style>
        .order-table select { color: #0f172a !important; -webkit-text-fill-color: #0f172a !important; -webkit-appearance: auto !important; appearance: auto !important; line-height: 1.2 !important; height: auto !important; min-height: 1.8rem !important; padding-right: 2.25rem !important; background-image: none !important; }
        .order-table select option { color: #0f172a !important; background: #ffffff !important; }
        .order-table .save-indicator, .order-table .absolute { z-index: 2; }
        .inline-flex.items-center.gap-1 { gap: .35rem; padding-left: .45rem; padding-right:.45rem; }
        .bg-slate-50 { background-color: #f8fafc; }
        /* smaller table font overall */
        .order-table { font-size: 10px; }
    </style>
</x-admin-layout>
