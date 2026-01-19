<x-admin-layout>
    <div class="admin-card p-5 mb-4">
        <div class="admin-header">
            <div class="flex items-center gap-3">
                <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <i data-lucide="inbox" class="w-5 h-5"></i>
                </span>
                <div>
                    <h1 class="admin-title">Order </h1>
                    <p class="admin-subtitle">Pantau order pekerjaan dan kawat las dengan filter cepat.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Switch -->
    <div x-data="{ tab: '{{ request('tab', 'notif') }}' }" class="mb-4">
        <!-- TAB NOTIFIKASI -->
        <div x-show="tab === 'notif'" class="mt-4">
            @php
                $statusOptions = [
                    \App\Models\Notification::STATUS_PENDING                => '⏳ Pending',
                    \App\Models\Notification::STATUS_APPROVED_WORKSHOP      => '✅ Approved (Workshop)',
                    \App\Models\Notification::STATUS_APPROVED_JASA          => '✅ Approved (Jasa)',
                    \App\Models\Notification::STATUS_APPROVED_WORKSHOP_JASA => '✅ Approved (Workshop + Jasa)',
                    \App\Models\Notification::STATUS_REJECT                 => '⛔ Reject',
                ];
            @endphp

            <x-filter-bar 
                :search="true"
                searchPlaceholder="Cari Nomor Notifikasi..."
                :statusOptions="$statusOptions"
                :dateFilter="false"
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

            <div class="admin-card overflow-hidden">
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
                                            <div class="col-span-2 font-semibold text-gray-700">
                                                📌 {{ $notification->job_name }}
                                            </div>

                                            <div>
                                                🏢 <span class="font-medium">Unit:</span> {{ $notification->unit_work }}
                                            </div>

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

                                            <div>
                                                📅 <span class="font-medium">Tanggal:</span> {{ $notification->input_date }}
                                            </div>

                                            <!-- Priority Selection -->
                                            <div class="col-span-2 mt-1">
                                                <form action="{{ route('notifications.updatePriority', $notification->notification_number) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="flex items-center gap-2">
                                                        <select name="priority" class="px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs w-28">
                                                            <option value="Urgently" {{ $notification->priority == 'Urgently' ? 'selected' : '' }}>Emergency</option>
                                                            <option value="Hard" {{ $notification->priority == 'Hard' ? 'selected' : '' }}>High</option>
                                                            <option value="Medium" {{ $notification->priority == 'Medium' ? 'selected' : '' }}>Medium</option>
                                                            <option value="Low" {{ $notification->priority == 'Low' ? 'selected' : '' }}>Low</option>
                                                        </select>
                                                        <button type="submit" class="bg-gray-500 text-white px-3 py-1 rounded text-xs hover:bg-gray-600 transition">
                                                            Update
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- Dokumen -->
                                            <div class="col-span-2 flex flex-wrap gap-2 mt-2">
                                                {{-- Loop semua dokumen orders --}}
                                                @foreach($notification->dokumenOrders ?? [] as $dok)
                                                    @php
                                                        $label = match($dok->jenis_dokumen) {
                                                            'abnormalitas'  => '📄 Abnormalitas',
                                                            'gambar_teknik' => '📄 Gambar Teknik',
                                                            default         => '📄 '.ucwords(str_replace('_',' ', $dok->jenis_dokumen))
                                                        };

                                                        $color = match($dok->jenis_dokumen) {
                                                            'abnormalitas'  => 'bg-red-500 hover:bg-red-600',
                                                            'gambar_teknik' => 'bg-blue-400 hover:bg-blue-500',
                                                            default         => 'bg-gray-400 hover:bg-gray-500'
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

                                            @php
                                                $tok = $activeSpkTokens[$notification->notification_number] ?? null;
                                                $hasTok = $tok && (
                                                    is_null($tok->expires_at) || $tok->expires_at->isFuture()
                                                );
                                                $isEmergency = $notification->priority === 'Urgently';
                                            @endphp

                                            <!-- Kondisi untuk Tombol Lihat/Buat SPK -->
                                            <div class="flex items-center gap-2 mt-2">
                                                {{-- ICON TOKEN APPROVAL SPK --}}
                                                @if($hasTok)
                                                    <button type="button"
                                                        class="copy-spk-token inline-flex items-center justify-center
                                                               w-6 h-6 rounded-md
                                                               bg-slate-100 text-slate-700
                                                               ring-1 ring-slate-300
                                                               hover:bg-slate-200"
                                                        title="Salin Link Approval SPK"
                                                        data-link="{{ route('approval.spk.sign', $tok->id) }}">
                                                        <i class="fas fa-link text-[10px]"></i>
                                                    </button>
                                                @endif

                                                {{-- STATUS SPK (RINGKAS) --}}
                                                @if ($notification->spk)
                                                    <div class="text-[10px] mb-1
                                                        @if($notification->spk->isFullyApproved())
                                                            text-green-700
                                                        @elseif($notification->spk->isManagerSigned())
                                                            text-blue-700
                                                        @else
                                                            text-gray-500
                                                        @endif
                                                    ">
                                                        @if($notification->spk->isFullyApproved())
                                                            ✅ Dokumen Telah ditandatangani
                                                        @elseif($notification->spk->isManagerSigned())
                                                            ✍️ Menunggu Tandatangan
                                                        @else
                                                            ⏳ Belum Ditandatangani
                                                        @endif
                                                    </div>
                                                @endif

                                                {{-- TOMBOL SPK --}}
                                                @if ($notification->spk)
                                                    <a href="{{ route('spk.show', ['notification_number' => $notification->notification_number]) }}"
                                                       class="bg-blue-500 text-white px-3 py-1 rounded text-xs
                                                              hover:bg-blue-600 transition
                                                              flex items-center gap-1"
                                                       target="_blank">
                                                        <i class="fas fa-eye text-[11px]"></i>
                                                        <span>Lihat Initial Work</span>
                                                    </a>
                                                @elseif ($isEmergency)
                                                    <a href="{{ route('spk.create', ['notificationNumber' => $notification->notification_number]) }}"
                                                       class="bg-blue-500 text-white px-3 py-1 rounded text-xs
                                                              hover:bg-blue-600 transition
                                                              flex items-center gap-1">
                                                        <i class="fas fa-file-alt text-[11px]"></i>
                                                        <span>Buat Initial Work</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Catatan -->
                                    <td class="px-3 py-2 text-right align-top">
                                        <form 
                                            action="{{ route('notifications.update', $notification->notification_number) }}?tab=notif" 
                                            method="POST"
                                            class="notif-update-form"
                                            data-notif="{{ $notification->notification_number }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            @php
                                                // daftar opsi
                                                $opsiJasa     = ['Jasa Fabrikasi','Jasa Konstruksi','Jasa Pengerjaan Mesin'];
                                                $opsiWorkshop = ['Regu Fabrikasi','Regu Bengkel (Refurbish)'];

                                                // status & catatan dari DB (pakai default 'pending' enum)
                                                $currentStatus  = $notification->status ?? \App\Models\Notification::STATUS_PENDING;
                                                $currentCatatan = $notification->catatan ?? '';

                                                // mapping status → mode di dropdown
                                                switch ($currentStatus) {
                                                    case \App\Models\Notification::STATUS_APPROVED_JASA:
                                                        $initMode = 'approved_jasa';
                                                        break;

                                                    case \App\Models\Notification::STATUS_APPROVED_WORKSHOP:
                                                        $initMode = 'approved_workshop';
                                                        break;

                                                    case \App\Models\Notification::STATUS_APPROVED_WORKSHOP_JASA:
                                                        $initMode = 'approved_both';
                                                        break;

                                                    case \App\Models\Notification::STATUS_REJECT:
                                                        $initMode = 'reject';
                                                        break;

                                                    default: // pending atau nilai lain
                                                        $initMode = 'pending';
                                                        break;
                                                }
                                            @endphp

                                            <div x-data="notifForm({
                                                    initMode: @js($initMode),
                                                    statusServer: @js($currentStatus),
                                                    catatanServer: @js($currentCatatan),
                                                    opsiJasa: @js($opsiJasa),
                                                    opsiWorkshop: @js($opsiWorkshop),
                                                })"
                                                 x-init="init()"
                                                 class="flex flex-col gap-2">

                                                <!-- Select utama: pilih mode -->
                                                <select x-model="mode"
                                                        name="mode"
                                                        class="px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs w-full">
                                                    <option value="approved_jasa">✅ Approved (Jasa)</option>
                                                    <option value="approved_workshop">✅ Approved (Workshop)</option>
                                                    <option value="approved_both">✅ Approved (Workshop + Jasa)</option>
                                                    <option value="pending">⏳ Pending</option>
                                                    <option value="reject">⛔ Reject</option>
                                                </select>

                                                <!-- Dropdown Approved (Jasa) -->
                                                <select x-show="mode === 'approved_jasa'"
                                                        x-model="catatan"
                                                        name="catatan_select_jasa"
                                                        class="w-full px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs">
                                                    <option value="" x-bind:selected="catatan === ''">
                                                        - Pilih jenis jasa (opsional) -
                                                    </option>

                                                    <template x-for="opt in opsiJasa" :key="opt">
                                                        <option :value="opt"
                                                                x-text="opt"
                                                                :selected="catatan === opt">
                                                        </option>
                                                    </template>
                                                </select>

                                                <!-- Dropdown Approved (Workshop) -->
                                                <select x-show="mode === 'approved_workshop'"
                                                        x-model="catatan"
                                                        name="catatan_select_workshop"
                                                        class="w-full px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs">
                                                    <option value="" x-bind:selected="catatan === ''">
                                                        - Pilih regu workshop (opsional) -
                                                    </option>

                                                    <template x-for="opt in opsiWorkshop" :key="opt">
                                                        <option :value="opt"
                                                                x-text="opt"
                                                                :selected="catatan === opt">
                                                        </option>
                                                    </template>
                                                </select>

                                                <!-- Dropdown Approved (Workshop + Jasa) -->
                                                <select x-show="mode === 'approved_both'"
                                                        x-model="catatan"
                                                        name="catatan_select_both"
                                                        class="w-full px-2 py-1 rounded bg-gray-100 border-gray-300 text-xs">
                                                    <option value="" x-bind:selected="catatan === ''">
                                                        - Pilih (opsional) -
                                                    </option>

                                                    <template x-for="opt in opsiGabungan" :key="opt">
                                                        <option :value="opt"
                                                                x-text="opt"
                                                                :selected="catatan === opt">
                                                        </option>
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
                                                        class="bg-gray-500 text-white px-3 py-1 rounded-full hover:bg-gray-600 transition text-xs">
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
        <div x-show="tab === 'kawatlas'" class="admin-card overflow-hidden p-4 mt-4">
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

                                {{-- Detail Kawat --}}
                                <td class="px-3 py-2">
                                    <ul class="space-y-3">
                                        @foreach($order->details as $detail)
                                            @php
                                                $jenis    = $jenisList->firstWhere('kode', $detail->jenis_kawat);
                                                $harga    = $jenis->harga ?? 0;
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
                                                    <div class="w-12 h-12 flex items-center justify-center bg-gray-200 rounded text-xs text-gray-500">
                                                        No Img
                                                    </div>
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

                                <td class="px-3 py-2 align-top">
                                    <div class="flex flex-col gap-1">
                                        <div class="font-medium text-[10px] text-gray-800">
                                            {{ $order->unit_work }}
                                        </div>

                                        @if(!empty($order->seksi))
                                            <div class="mt-1">
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[8px] font-semibold
                                                           bg-indigo-100 text-indigo-800 ring-1 ring-indigo-200">
                                                    <i class="fas fa-sitemap text-[8px] opacity-80"></i>
                                                    {{ $order->seksi }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-500 italic mt-1">
                                                Seksi: —
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status & Catatan --}}
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

            {{-- Pagination Kawat Las (kalau mau ditambah, tinggal aktifkan) --}}
            {{-- <div class="mt-4">
                {{ $kawatLasOrders->links() }}
            </div> --}}
        </div>
    </div>

    <!-- Search Filter + Notif AJAX submit -->
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

            document.querySelectorAll('.copy-spk-token').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const link = btn.dataset.link;
                    if (!link) return;

                    try {
                        await copyTextToClipboard(link);

                        if (window.Swal) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Tautan Disalin',
                                text: 'Link approval SPK berhasil disalin.',
                                timer: 1400,
                                showConfirmButton: false
                            });
                        } else {
                            alert('Link approval SPK berhasil disalin');
                        }
                    } catch (e) {
                        if (window.Swal) {
                            Swal.fire('Gagal', 'Tidak bisa menyalin link', 'error');
                        } else {
                            alert('Gagal menyalin link');
                        }
                    }
                });
            });
        });

        /**
         * Combined script:
         * 1) notifForm() — Alpine state + init
         * 2) safe search input handling
         * 3) AJAX submit hijack for forms with class .notif-update-form
         */
        function notifForm(config) {
            return {
                mode: config.initMode || 'pending',
                statusServer: config.statusServer || 'pending',
                catatanServer: config.catatanServer || '',
                opsiJasa: config.opsiJasa || [],
                opsiWorkshop: config.opsiWorkshop || [],

                catatan: '',

                get opsiGabungan() {
                    return [...this.opsiJasa, ...this.opsiWorkshop];
                },

                get statusForSubmit() {
                    if (this.mode === 'approved_jasa')     return 'approved_jasa';
                    if (this.mode === 'approved_workshop') return 'approved_workshop';
                    if (this.mode === 'approved_both')     return 'approved_workshop_jasa';
                    if (this.mode === 'reject')            return 'reject';
                    return 'pending';
                },

                init() {
                    this.catatan = this.catatanServer || '';

                    if (this.statusServer && this.statusServer.startsWith('approved_') && !this.catatan) {
                        if (this.mode === 'approved_jasa' || this.mode === 'approved_workshop') {
                            this.mode = 'approved_both';
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
                setTimeout(() => el.remove(), 2400);
            }

            // attach ke semua form notifikasi
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

                    // pastikan field "mode" tetap ada
                    if (!fd.has('mode')) {
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

                        let data = null;
                        try { data = await res.json(); } catch (e) { data = null; }

                        if (!res.ok) {
                            let message = 'Gagal menyimpan perubahan.';
                            if (data) {
                                if (data.error) message = data.error;
                                else if (data.errors) {
                                    const first = Object.values(data.errors)[0];
                                    message = Array.isArray(first) ? first[0] : first;
                                } else if (data.message) message = data.message;
                            }
                            toast(message, false);
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.textContent = submitBtn.dataset.origText || 'Save';
                            }
                            return;
                        }

                        // sukses: update DOM baris ini
                        const tr = form.closest('tr');
                        const notif = data && data.notification ? data.notification : null;

                        const hiddenStatus  = form.querySelector('input[name="status"]');
                        const hiddenCatatan = form.querySelector('input[name="catatan"]');

                        if (notif) {
                            if (hiddenStatus)  hiddenStatus.value  = notif.status  ?? hiddenStatus.value;
                            if (hiddenCatatan) hiddenCatatan.value = notif.catatan ?? hiddenCatatan.value;
                        }

                        if (tr) {
                            let badge = tr.querySelector('.notif-status-badge');

                            const statusVal = (notif && notif.status)
                                ? notif.status
                                : (hiddenStatus ? hiddenStatus.value : null);

                            const catVal = (notif && notif.catatan)
                                ? notif.catatan
                                : (hiddenCatatan ? hiddenCatatan.value : '');

                            if (!badge) {
                                const catTd = tr.querySelector('td:last-child');
                                if (catTd) {
                                    badge = document.createElement('div');
                                    badge.className = 'notif-status-badge text-xs inline-flex items-center gap-1 px-2 py-1 rounded';
                                    badge.style.marginBottom = '6px';
                                    catTd.prepend(badge);
                                }
                            }

                            if (badge) {
                                const labelMap = {
                                    'approved_workshop'      : '✅ Approved (Workshop)',
                                    'approved_jasa'          : '✅ Approved (Jasa)',
                                    'approved_workshop_jasa' : '✅ Approved (Workshop + Jasa)',
                                    'pending'                : '⏳ Pending',
                                    'reject'                 : '⛔ Reject',
                                };

                                badge.textContent = labelMap[statusVal] || statusVal || '';

                                if (statusVal && statusVal.startsWith('approved')) {
                                    badge.style.background = '#d1fae5';   // hijau muda
                                } else if (statusVal === 'pending') {
                                    badge.style.background = '#fff7ed';   // oranye muda
                                } else if (statusVal === 'reject') {
                                    badge.style.background = '#fee2e2';   // merah muda
                                } else {
                                    badge.style.background = '#e5e7eb';   // abu-abu default
                                }
                            }

                            // sinkronkan select/textarea catatan yang kelihatan
                            const visibleSelect = form.querySelector(
                                'select[x-model="catatan"], select[name^="catatan"], textarea[x-model="catatan"]'
                            );
                            if (visibleSelect) {
                                visibleSelect.value = catVal;
                            }
                        }

                        toast((data && data.message) ? data.message : 'Berhasil disimpan.');

                    } catch (err) {
                        console.error('AJAX update error', err);
                        toast('Terjadi kesalahan jaringan', false);
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = submitBtn.dataset.origText || 'Save';
                        }
                    }
                });
            });

        })();
    </script>
</x-admin-layout>
