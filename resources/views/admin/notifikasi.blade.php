    <x-admin-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-lg text-gray-700 leading-tight">
                {{ __('Dashboard Admin') }}
            </h2>
        </x-slot>

        <!-- Tab Switch -->
<div x-data="{ tab: '{{ request('tab', 'notif') }}' }" class="mb-4">

            <!-- TAB NOTIFIKASI -->
             
            <div x-show="tab === 'notif'" class="mt-4">
                <x-filter-bar 
    :search="true"
    searchPlaceholder="Cari Nomor Notifikasi..."
    :statusOptions="['Pending' => '⏳ Pending', 'Approved' => '✅ Approved']"
    :dateFilter="false"   {{-- ⬅️ ini hilangkan tanggal --}}
>
    {{-- Custom Filter: Regu --}}
    <div class="flex flex-col">
        <label class="text-[11px] font-semibold text-gray-600 mb-1">Regu</label>
        <select name="regu"
            class="w-40 border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">-- Semua --</option>
            <option value="Regu Fabrikasi" {{ request('regu') == 'Regu Fabrikasi' ? 'selected' : '' }}>Regu Fabrikasi</option>
            <option value="Regu Bengkel (Refurbish)" {{ request('regu') == 'Regu Bengkel (Refurbish)' ? 'selected' : '' }}>Regu Bengkel (Refurbish)</option>
        </select>
    </div>
</x-filter-bar>

                <div class="hidden lg:block bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                    <table id="notificationTable" class="min-w-full text-gray-800 text-sm">
                        <thead class="bg-gray-200 text-gray-600">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase">Nomor Order</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase">Detail Pekerjaan</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase">Catatan User</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($notifications as $index => $notification)
                            <tr class="{{ $index % 2 === 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition duration-150">
                                <!-- Nomor Notifikasi -->
                                <td class="px-3 py-2 text-xs font-medium text-gray-600 notification-number">
                                    {{ $notification->notification_number }}
                                </td>
                            <!-- Detail Pekerjaan -->
                            <td class="px-3 py-2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div class="col-span-2 font-semibold text-gray-700">📌 {{ $notification->job_name }}</div>
                                 <div>🏢 <span class="font-medium">Unit:</span> {{ $notification->unit_work }}</div>

@if(!empty($notification->seksi))
    <div class="sm:col-span-2 -mt-1">
        <span
            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                   bg-indigo-100 text-indigo-800 ring-1 ring-indigo-200
                   dark:bg-indigo-900/40 dark:text-indigo-300 dark:ring-indigo-700">
            <i class="fas fa-sitemap text-[9px] opacity-80"></i>
            {{ $notification->seksi }}
        </span>
    </div>
@endif

                                    <div>📅 <span class="font-medium">Tanggal:</span> {{ $notification->input_date }}</div>

                                    <!-- Priority Selection -->
                                    <div class="col-span-2 mt-1">
                                        <form action="{{ route('notifications.updatePriority', $notification->notification_number) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <div class="flex items-center gap-2">
                                                <select name="priority" class="px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs w-28">
                                                    <option value="Urgently" {{ $notification->priority == 'Urgently' ? 'selected' : '' }}>Emergency</option>
                                                    <option value="Hard" {{ $notification->priority == 'Hard' ? 'selected' : '' }}>High</option>
                                                    <option value="Medium" {{ $notification->priority == 'Medium' ? 'selected' : '' }}>Medium</option>
                                                    <option value="Low" {{ $notification->priority == 'Low' ? 'selected' : '' }}>Low</option>
                                                </select>
                                                <button type="submit" class="bg-gray-500 text-white px-3 py-1 rounded text-xs hover:bg-gray-600 transition">Update</button>
                                            </div>
                                        </form>
                                    </div>

                                  <!-- Dokumen -->
<div class="col-span-2 flex flex-wrap gap-2 mt-2">
    {{-- Loop semua dokumen orders --}}
    @foreach($notification->dokumenOrders ?? [] as $dok)
        @php
            $label = match($dok->jenis_dokumen) {
                'abnormalitas' => '📄 Abnormalitas',
                'gambar_teknik' => '📄 Gambar Teknik',
                default => '📄 '.ucwords(str_replace('_',' ', $dok->jenis_dokumen))
            };

            $color = match($dok->jenis_dokumen) {
                'abnormalitas' => 'bg-red-500 hover:bg-red-600',
                'gambar_teknik' => 'bg-blue-400 hover:bg-blue-500',
                default => 'bg-gray-400 hover:bg-gray-500'
            };
        @endphp

        <a href="{{ route('dokumen_orders.view', [$notification->notification_number, $dok->jenis_dokumen]) }}" 
           target="_blank"
           class="{{ $color }} text-white px-3 py-1 rounded-lg text-xs transition">
           {{ $label }}
        </a>
    @endforeach

    {{-- Scope of Work tetap khusus --}}
    @if($notification->isScopeOfWorkAvailable)
        <a href="{{ route('dokumen_orders.scope.download_pdf', $notification->notification_number) }}" 
           target="_blank"
           class="bg-green-400 text-white px-3 py-1 rounded-lg text-xs hover:bg-green-500 transition">
           📄 Scope of Work
        </a>
    @endif
</div>


                                    <!-- Kondisi untuk Tombol Lihat/Buat SPK -->
                                    @if($notification->priority == 'Urgently')
                                        <div class="mt-2 flex space-x-2">
                                            @php
                                                $spk = \App\Models\SPK::where('notification_number', $notification->notification_number)->first();
                                            @endphp

                                            @if ($spk)
                                                <a href="{{ route('spk.show', ['notification_number' => $notification->notification_number]) }}" 
                                                class="bg-yellow-400 text-white px-3 py-1 rounded text-xs hover:bg-yellow-500 transition duration-150 flex items-center space-x-1" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                    <span>Lihat Initial Work</span>
                                                </a>
                                            @else
                                                <a href="{{ route('spk.create', ['notificationNumber' => $notification->notification_number]) }}" 
                                                class="bg-yellow-400 text-white px-3 py-1 rounded text-xs hover:bg-orange-500 transition duration-150 flex items-center space-x-1">
                                                    <i class="fas fa-file-alt"></i>
                                                    <span>Buat Initial Work</span>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
<!-- Catatan -->
<td class="px-3 py-2 text-right">
<form 
    action="{{ route('notifications.update', $notification->notification_number) }}?tab=notif" 
    method="POST"
    class="notif-update-form"
    data-notif="{{ $notification->notification_number }}"
>
    @csrf @method('PATCH')

        @php
            // daftar opsi
            $opsiJasa = ['Jasa Fabrikasi','Jasa Konstruksi','Jasa Pengerjaan Mesin'];
            $opsiWorkshop = ['Regu Fabrikasi','Regu Bengkel (Refurbish)'];

            // tentukan mode awal dari data lama
            $currentStatus = $notification->status ?? 'Pending';
            $currentCatatan = $notification->catatan ?? '';

            if ($currentStatus === 'Approved') {
                if (in_array($currentCatatan, $opsiJasa)) {
                    $initMode = 'approved_jasa';
                } elseif (in_array($currentCatatan, $opsiWorkshop)) {
                    $initMode = 'approved_workshop';
                } else {
                    // fallback kalau catatan kosong/lain: default ke approved_workshop
                    $initMode = 'approved_workshop';
                }
            } elseif ($currentStatus === 'Reject') {
                $initMode = 'reject';
            } else { // Pending
                $initMode = 'pending';
            }
        @endphp

       <div x-data="notifForm({
    initMode: @js($initMode),
    statusServer: @js($currentStatus),
    catatanServer: @js($currentCatatan),
    opsiJasa: @js($opsiJasa),
    opsiWorkshop: @js($opsiWorkshop),
})" x-init="init()" class="flex flex-col gap-2">

<!-- Select utama: pilih mode -->
<select x-model="mode"
        name="mode"           
        class="px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs w-full">
    <option value="approved_jasa">✅ Approved (Jasa)</option>
    <option value="approved_workshop">✅ Approved (Workshop)</option>
    <option value="pending">⏳ Pending</option>
    <option value="reject">⛔ Reject</option>
</select>

<!-- Dropdown Approved (Jasa) -->
<select x-show="mode === 'approved_jasa'"
        x-model="catatan"
        name="catatan_select_jasa"   {{-- opsional: for debug, tapi hidden catatan below is main --}}
        class="w-full px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs">
    <template x-for="opt in opsiJasa" :key="opt">
        <option :value="opt" x-text="opt"></option>
    </template>
</select>

<!-- Dropdown Approved (Workshop) -->
<select x-show="mode === 'approved_workshop'"
        x-model="catatan"
        name="catatan_select_workshop" {{-- opsional --}}
        class="w-full px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs">
    <template x-for="opt in opsiWorkshop" :key="opt">
        <option :value="opt" x-text="opt"></option>
    </template>
</select>

<!-- Textarea untuk Pending/Reject -->
<textarea x-show="mode === 'pending' || mode === 'reject'"
          x-model="catatan"
          rows="2"
          placeholder="Catatan (opsional)"
          class="w-full px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs"></textarea>

<!-- Hidden fields yang dikirim ke server (status & catatan) -->
<input type="hidden" name="status"  :value="statusForSubmit">
<input type="hidden" name="catatan" :value="catatan ?? ''">
            <button type="submit"
                    class="bg-gray-500 text-white px-3 py-1 rounded-full hover:bg-gray-600 transition">
                Save
            </button>
        </div>
                                    </form>
                                </td>
                            </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>
                <!-- Pagination khusus notifikasi -->
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            </div>
<!-- TAB KAWAT LAS -->
<div x-show="tab === 'kawatlas'" class="bg-white rounded-lg shadow-md overflow-hidden p-4 mt-4">
    {{-- 🔍 Filter --}}
    <x-filter-bar 
        :search="true"
        searchPlaceholder="Cari Jenis Kawat..."
        :statusOptions="['Waiting List', 'Good Issue']"
        :dateFilter="false"
    >
        {{-- Filter Unit --}}
        <div class="flex flex-col">
            <label class="text-[11px] font-semibold text-gray-600 mb-1">Unit Kerja</label>
            <select name="unit"
                class="w-40 border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Semua --</option>
                @foreach($units as $u)
                    <option value="{{ $u }}" {{ request('unit') == $u ? 'selected' : '' }}>
                        {{ $u }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-filter-bar>

    <h3 class="text-lg font-semibold mb-3">Daftar Order Kawat Las</h3>

    <div class="overflow-x-auto">
        <table class="min-w-full text-gray-800 text-sm mt-3 border border-gray-300 rounded-lg overflow-hidden">
            <thead class="bg-gray-700 text-white">
                <tr>
                    <th class="px-3 py-2 text-left text-xs uppercase">Nomor Order</th>
                    <th class="px-3 py-2 text-left text-xs uppercase">Tanggal</th>
                    <th class="px-3 py-2 text-left text-xs uppercase">Detail Kawat Las</th>
                    <th class="px-3 py-2 text-left text-xs uppercase">Unit Kerja</th>
                    <th class="px-3 py-2 text-left text-xs uppercase">Status & Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kawatLasOrders as $index => $order)
                    @php $grandTotal = 0; @endphp
                    <tr class="{{ $index % 2 === 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-orange-100 transition">
                        <td class="px-3 py-2 align-top font-semibold">{{ $order->order_number }}</td>
                        <td class="px-3 py-2 align-top">{{ $order->tanggal->format('d-m-Y') }}</td>

                        {{-- 🧱 Detail Kawat --}}
                        <td class="px-3 py-2">
                            <ul class="space-y-3">
                                @foreach($order->details as $detail)
                                    @php
                                        $jenis = $jenisList->firstWhere('kode', $detail->jenis_kawat);
                                        $harga = $jenis->harga ?? 0;
                                        $subtotal = $harga * $detail->jumlah;
                                        $grandTotal += $subtotal;
                                    @endphp
                                    <li class="flex items-start gap-3 border-b pb-2">
                                        {{-- Gambar --}}
                                        @if($jenis?->gambar)
                                            <img src="{{ asset('storage/'.$jenis->gambar) }}" 
                                                 alt="{{ $jenis->kode }}"
                                                 class="w-12 h-12 object-cover rounded border">
                                        @else
                                            <div class="w-12 h-12 flex items-center justify-center bg-gray-200 rounded text-xs text-gray-500">No Img</div>
                                        @endif

                                        {{-- Info Kawat --}}
                                        <div class="flex-1 text-sm">
                                            <p class="font-semibold">{{ $detail->jenis_kawat }} ({{ $detail->jumlah }})</p>
                                            <p class="text-xs text-gray-600">Deskripsi: {{ $jenis->deskripsi ?? '-' }}</p>
                                            <p class="text-xs text-gray-600">Stok: {{ $jenis->stok ?? 0 }}</p>
                                            <p class="text-xs text-gray-600">Cost Element: {{ $jenis->cost_element ?? '-' }}</p>
                                            <p class="text-xs text-gray-600">
                                                Harga: Rp {{ number_format($harga,0,',','.') }} |
                                                Subtotal: Rp {{ number_format($subtotal,0,',','.') }}
                                            </p>
                                        </div>

                                        {{-- Form Update Jumlah --}}
                                        <div class="flex flex-col gap-1">
                                            <form method="POST" action="{{ route('admin.kawatlas.updateJumlah', $detail->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="jumlah" value="{{ $detail->jumlah }}"
                                                       min="1"
                                                       class="border rounded px-1 py-1 w-16 text-center text-xs">
                                                <button type="submit"
                                                        class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] px-2 py-1 rounded shadow">
                                                    Simpan
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            {{-- Grand Total --}}
                            <div class="mt-3 text-right font-bold text-sm text-gray-800">
                                Total: Rp {{ number_format($grandTotal, 0, ',', '.') }}
                            </div>
                        </td>

                        <td class="px-3 py-2 align-top">{{ $order->unit_work }}</td>

                        {{-- 🧾 Status & Catatan --}}
                        <td class="px-3 py-2 align-top">
                            <form action="{{ route('admin.kawatlas.updateStatus', $order->id) }}" method="POST" class="flex flex-col gap-2">
                                @csrf
                                @method('PUT')

                                <select name="status" class="border-gray-300 rounded text-xs px-1 py-1">
                                    <option value="Waiting List" {{ $order->status == 'Waiting List' ? 'selected' : '' }}>Waiting List</option>
                                    <option value="Good Issue" {{ $order->status == 'Good Issue' ? 'selected' : '' }}>Good Issue</option>
                                </select>

                                <textarea name="catatan" rows="2" placeholder="Tambahkan catatan..."
                                    class="border-gray-300 rounded text-xs px-2 py-1">{{ $order->catatan }}</textarea>

                                <button type="submit"
                                    class="bg-green-600 hover:bg-green-700 text-white text-[10px] py-1 rounded shadow">
                                    Update
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- Search Filter + Notif AJAX submit -->
<script>
/**
 * Combined script:
 * 1) notifForm() — Alpine state + init
 * 2) safe search input handling
 * 3) AJAX submit hijack for forms with class .notif-update-form
 */

function notifForm({initMode, statusServer, catatanServer, opsiJasa, opsiWorkshop}) {
    return {
        // state
        mode: initMode,
        catatan: catatanServer || '',
        opsiJasa,
        opsiWorkshop,

        // computed: status yang akan dikirim
        get statusForSubmit() {
            if (this.mode === 'approved_jasa' || this.mode === 'approved_workshop') return 'Approved';
            if (this.mode === 'reject') return 'Reject';
            return 'Pending';
        },

        // init: set default catatan bila kosong sesuai mode
        init() {
            if (!this.catatan) {
                if (this.mode === 'approved_jasa' && this.opsiJasa.length) {
                    this.catatan = this.opsiJasa[0];
                } else if (this.mode === 'approved_workshop' && this.opsiWorkshop.length) {
                    this.catatan = this.opsiWorkshop[0];
                } else {
                    this.catatan = '';
                }
            }
        }
    }
}

/* ---------- safe search handler ---------- */
(function setupSearch() {
    const searchEl = document.getElementById('search');
    if (!searchEl) return;
    searchEl.addEventListener('keyup', function () {
        let query = this.value.toLowerCase();
        let rows = document.querySelectorAll('#notificationTable tbody tr');

        rows.forEach(row => {
            let cell = row.querySelector('.notification-number');
            let notificationNumber = cell ? cell.textContent.toLowerCase() : '';
            row.style.display = notificationNumber.includes(query) ? '' : 'none';
        });
    });
})();

/* ---------- AJAX submit for admin notif forms ---------- */
(function setupAjaxForms() {
    // helper toast
    function toast(msg, ok = true) {
        const el = document.createElement('div');
        el.textContent = msg;
        el.style = 'position:fixed;right:20px;bottom:20px;padding:10px 14px;border-radius:8px;color:#fff;z-index:99999;font-size:13px;';
        el.style.background = ok ? '#16a34a' : '#dc2626';
        document.body.appendChild(el);
        setTimeout(()=>el.remove(), 2400);
    }

    // attach to forms created on page load
    document.querySelectorAll('form.notif-update-form').forEach(form => {
        form.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.origText = submitBtn.textContent;
                submitBtn.textContent = 'Saving...';
            }

            // build payload
            const fd = new FormData(form);

            // Ensure fields are present: mode, status, catatan
            // (form should already contain name="mode" and hidden name="status" & name="catatan")
            if (!fd.has('mode')) {
                // try to read from x-model select
                const sel = form.querySelector('select[x-model="mode"], select[name="mode"]');
                if (sel && sel.value) fd.append('mode', sel.value);
            }

            try {
                const res = await fetch(form.action, {
                    method: (form.method || 'POST').toUpperCase(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: fd,
                    credentials: 'same-origin'
                });

                // attempt parse JSON safely
                let data = null;
                try { data = await res.json(); } catch(e) { data = null; }

                if (!res.ok) {
                    // try to show useful error message
                    let message = 'Gagal menyimpan perubahan.';
                    if (data) {
                        if (data.error) message = data.error;
                        else if (data.errors) {
                            const first = Object.values(data.errors)[0];
                            message = Array.isArray(first) ? first[0] : first;
                        } else if (data.message) message = data.message;
                    }
                    toast(message, false);
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.origText || 'Save'; }
                    return;
                }

                // success: Update DOM for that row so UI reflects saved state
                const tr = form.closest('tr');

                // 1) If server returned updated notification object, use it
                const notif = data && data.notification ? data.notification : null;

                // Update hidden inputs (status & catatan) to latest value
                const hiddenStatus = form.querySelector('input[name="status"]');
                const hiddenCatatan = form.querySelector('input[name="catatan"]');

                if (notif) {
                    if (hiddenStatus) hiddenStatus.value = notif.status ?? hiddenStatus.value;
                    if (hiddenCatatan) hiddenCatatan.value = notif.catatan ?? hiddenCatatan.value;
                } else {
                    // if controller returns only message, use current client state (Alpine already bound catatan)
                    // nothing to change
                }

                // 2) Update a small status badge in the row for immediate visual feedback
                if (tr) {
                    let badge = tr.querySelector('.notif-status-badge');
                    const statusVal = (notif && notif.status) ? notif.status : (hiddenStatus ? hiddenStatus.value : null);
                    const catVal = (notif && notif.catatan) ? notif.catatan : (hiddenCatatan ? hiddenCatatan.value : '');

                    if (!badge) {
                        // create and prepend to the rightmost cell
                        const catTd = tr.querySelector('td:last-child');
                        if (catTd) {
                            badge = document.createElement('div');
                            badge.className = 'notif-status-badge text-xs inline-flex items-center gap-1 px-2 py-1 rounded';
                            badge.style.marginBottom = '6px';
                            catTd.prepend(badge);
                        }
                    }
                    if (badge) {
                        const labelMap = { 'Approved': '✅ Approved', 'Pending': '⏳ Pending', 'Reject': '⛔ Reject' };
                        badge.textContent = labelMap[statusVal] || statusVal || '';
                        badge.style.background = statusVal === 'Approved' ? '#d1fae5' : (statusVal === 'Pending' ? '#fff7ed' : '#fee2e2');
                    }

                    // Also update visible selects / textareas inside form to reflect saved catatan
                    // find visible select or textarea for catatan inside this form
                    const visibleSelect = form.querySelector('select[x-model="catatan"], select[name^="catatan"], textarea[x-model="catatan"]');
                    if (visibleSelect) {
                        if (visibleSelect.tagName.toLowerCase() === 'select') visibleSelect.value = catVal;
                        else visibleSelect.value = catVal;
                    }
                }

                // show success toast
                toast((data && data.message) ? data.message : 'Berhasil disimpan.');

            } catch (err) {
                console.error('AJAX update error', err);
                toast('Terjadi kesalahan jaringan', false);
            } finally {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.origText || 'Save'; }
            }
        });
    });

})();
</script>
x<!-- Search Filter + Notif AJAX submit -->
<script>
/**
 * Combined script:
 * 1) notifForm() — Alpine state + init
 * 2) safe search input handling
 * 3) AJAX submit hijack for forms with class .notif-update-form
 */

function notifForm({initMode, statusServer, catatanServer, opsiJasa, opsiWorkshop}) {
    return {
        // state
        mode: initMode,
        catatan: catatanServer || '',
        opsiJasa,
        opsiWorkshop,

        // computed: status yang akan dikirim
        get statusForSubmit() {
            if (this.mode === 'approved_jasa' || this.mode === 'approved_workshop') return 'Approved';
            if (this.mode === 'reject') return 'Reject';
            return 'Pending';
        },

        // init: set default catatan bila kosong sesuai mode
        init() {
            if (!this.catatan) {
                if (this.mode === 'approved_jasa' && this.opsiJasa.length) {
                    this.catatan = this.opsiJasa[0];
                } else if (this.mode === 'approved_workshop' && this.opsiWorkshop.length) {
                    this.catatan = this.opsiWorkshop[0];
                } else {
                    this.catatan = '';
                }
            }
        }
    }
}

/* ---------- safe search handler ---------- */
(function setupSearch() {
    const searchEl = document.getElementById('search');
    if (!searchEl) return;
    searchEl.addEventListener('keyup', function () {
        let query = this.value.toLowerCase();
        let rows = document.querySelectorAll('#notificationTable tbody tr');

        rows.forEach(row => {
            let cell = row.querySelector('.notification-number');
            let notificationNumber = cell ? cell.textContent.toLowerCase() : '';
            row.style.display = notificationNumber.includes(query) ? '' : 'none';
        });
    });
})();

/* ---------- AJAX submit for admin notif forms ---------- */
(function setupAjaxForms() {
    // helper toast
    function toast(msg, ok = true) {
        const el = document.createElement('div');
        el.textContent = msg;
        el.style = 'position:fixed;right:20px;bottom:20px;padding:10px 14px;border-radius:8px;color:#fff;z-index:99999;font-size:13px;';
        el.style.background = ok ? '#16a34a' : '#dc2626';
        document.body.appendChild(el);
        setTimeout(()=>el.remove(), 2400);
    }

    // attach to forms created on page load
    document.querySelectorAll('form.notif-update-form').forEach(form => {
        form.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.origText = submitBtn.textContent;
                submitBtn.textContent = 'Saving...';
            }

            // build payload
            const fd = new FormData(form);

            // Ensure fields are present: mode, status, catatan
            // (form should already contain name="mode" and hidden name="status" & name="catatan")
            if (!fd.has('mode')) {
                // try to read from x-model select
                const sel = form.querySelector('select[x-model="mode"], select[name="mode"]');
                if (sel && sel.value) fd.append('mode', sel.value);
            }

            try {
                const res = await fetch(form.action, {
                    method: (form.method || 'POST').toUpperCase(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: fd,
                    credentials: 'same-origin'
                });

                // attempt parse JSON safely
                let data = null;
                try { data = await res.json(); } catch(e) { data = null; }

                if (!res.ok) {
                    // try to show useful error message
                    let message = 'Gagal menyimpan perubahan.';
                    if (data) {
                        if (data.error) message = data.error;
                        else if (data.errors) {
                            const first = Object.values(data.errors)[0];
                            message = Array.isArray(first) ? first[0] : first;
                        } else if (data.message) message = data.message;
                    }
                    toast(message, false);
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.origText || 'Save'; }
                    return;
                }

                // success: Update DOM for that row so UI reflects saved state
                const tr = form.closest('tr');

                // 1) If server returned updated notification object, use it
                const notif = data && data.notification ? data.notification : null;

                // Update hidden inputs (status & catatan) to latest value
                const hiddenStatus = form.querySelector('input[name="status"]');
                const hiddenCatatan = form.querySelector('input[name="catatan"]');

                if (notif) {
                    if (hiddenStatus) hiddenStatus.value = notif.status ?? hiddenStatus.value;
                    if (hiddenCatatan) hiddenCatatan.value = notif.catatan ?? hiddenCatatan.value;
                } else {
                    // if controller returns only message, use current client state (Alpine already bound catatan)
                    // nothing to change
                }

                // 2) Update a small status badge in the row for immediate visual feedback
                if (tr) {
                    let badge = tr.querySelector('.notif-status-badge');
                    const statusVal = (notif && notif.status) ? notif.status : (hiddenStatus ? hiddenStatus.value : null);
                    const catVal = (notif && notif.catatan) ? notif.catatan : (hiddenCatatan ? hiddenCatatan.value : '');

                    if (!badge) {
                        // create and prepend to the rightmost cell
                        const catTd = tr.querySelector('td:last-child');
                        if (catTd) {
                            badge = document.createElement('div');
                            badge.className = 'notif-status-badge text-xs inline-flex items-center gap-1 px-2 py-1 rounded';
                            badge.style.marginBottom = '6px';
                            catTd.prepend(badge);
                        }
                    }
                    if (badge) {
                        const labelMap = { 'Approved': '✅ Approved', 'Pending': '⏳ Pending', 'Reject': '⛔ Reject' };
                        badge.textContent = labelMap[statusVal] || statusVal || '';
                        badge.style.background = statusVal === 'Approved' ? '#d1fae5' : (statusVal === 'Pending' ? '#fff7ed' : '#fee2e2');
                    }

                    // Also update visible selects / textareas inside form to reflect saved catatan
                    // find visible select or textarea for catatan inside this form
                    const visibleSelect = form.querySelector('select[x-model="catatan"], select[name^="catatan"], textarea[x-model="catatan"]');
                    if (visibleSelect) {
                        if (visibleSelect.tagName.toLowerCase() === 'select') visibleSelect.value = catVal;
                        else visibleSelect.value = catVal;
                    }
                }

                // show success toast
                toast((data && data.message) ? data.message : 'Berhasil disimpan.');

            } catch (err) {
                console.error('AJAX update error', err);
                toast('Terjadi kesalahan jaringan', false);
            } finally {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.origText || 'Save'; }
            }
        });
    });

})();
</script>

</x-admin-layout>
