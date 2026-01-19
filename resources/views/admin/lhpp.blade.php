<x-admin-layout>
@php
        /* STYLE PRESETS (konsisten dengan LPJ) */
        $baseSel = 'min-h-[26px] text-[10px] leading-[1.3] px-2 pr-9 rounded-[6px] appearance-none focus:ring-1 truncate';
        $baseInp = 'min-h-[26px] text-[10px] leading-[1.3] px-2 rounded-[6px] focus:ring-1';
        $baseBtn = 'min-h-[26px] text-[10px] leading-[1.3] px-3 rounded-[6px]';

        $selIndigo = $baseSel.' bg-indigo-100 text-indigo-800 border border-indigo-600 focus:ring-indigo-500 focus:border-indigo-600';
        $inpSlate  = $baseInp.' bg-white border border-slate-600 focus:ring-indigo-500 focus:border-indigo-600';

        $btnPrimary = $baseBtn.' bg-indigo-600 text-white hover:bg-indigo-700';
        $btnGhost   = $baseBtn.' border border-slate-600 text-slate-700 hover:bg-slate-50';
    @endphp

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-4 lg:px-4">

            <!-- HEADER + FILTER -->
            <div class="admin-card p-5 mb-4">
                <div class="admin-header">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h1 class="admin-title">LHPP</h1>
                            <p class="admin-subtitle">Cari dokumen berdasarkan nomor order, PO, unit, dan lainnya.</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.lhpp.index') }}" method="GET" class="admin-filter mt-4 overflow-x-auto whitespace-nowrap">
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari dokumen" class="admin-input pl-9 w-72" />
                    </div>

                    <button type="submit" class="admin-btn admin-btn-primary ml-auto">
                        <i data-lucide="filter" class="w-4 h-4"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.lhpp.index') }}" class="admin-btn admin-btn-ghost">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                    </a>
                </form>
            </div>

            <!-- TABLE: modern, responsive, tanpa ubah logic data -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-[11px] text-slate-700">
                    <thead class="bg-slate-100 text-slate-700 uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">Nomor Order</th>
                            <th class="px-4 py-2 text-left font-semibold">Nomor PO</th>
                            <th class="px-4 py-2 text-left font-semibold">Unit Kerja</th>
                            <th class="px-4 py-2 text-left font-semibold">Tanggal Selesai</th>
                            <th class="px-4 py-2 text-left font-semibold">Waktu</th>
                            <th class="px-4 py-2 text-right font-semibold">Total Biaya</th>
                            <th class="px-4 py-2 text-center font-semibold">Garansi</th>
                            <th class="px-4 py-2 text-center font-semibold">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @foreach ($lhpps as $lhpp)
                            @php
                                $total_biaya = (float) ($lhpp->total_biaya ?? 0);
                                $tanggalSelesai = $lhpp->tanggal_selesai
                                    ? \Carbon\Carbon::parse($lhpp->tanggal_selesai)->format('d-m-Y')
                                    : '-';
                                $waktuPengerjaan = $lhpp->waktu_pengerjaan ? $lhpp->waktu_pengerjaan.' Hari' : '-';
                            @endphp

                            <tr class="hover:bg-slate-50 transition duration-150">
                                <td class="px-4 py-2 font-semibold text-slate-900">{{ $lhpp->notification_number }}</td>
                                <td class="px-4 py-2">{{ $lhpp->purchase_order_number }}</td>
                                <td class="px-4 py-2">{{ $lhpp->unit_kerja }}</td>
                                <td class="px-4 py-2">{{ $tanggalSelesai }}</td>
                                <td class="px-4 py-2">{{ $waktuPengerjaan }}</td>
                                <td class="px-4 py-2 text-right">Rp{{ number_format($total_biaya, 0, ',', '.') }}</td>

                  <!-- GARANSI (DIPINDAHKAN DARI LPJ KE GARANSI) -->
<td class="px-4 py-2 text-center align-top w-64">
    <form id="garansi-form-{{ $lhpp->notification_number }}"
          action="{{ route('admin.lhpp.storeGaransi', $lhpp->notification_number) }}"
          method="POST">
        @csrf

        <!-- redirect_to agar kembali ke halaman LHPP admin -->
        <input type="hidden" name="redirect_to" value="{{ route('admin.lhpp.index') }}">

        @php
            $garansi = isset($garansiMap)
                ? $garansiMap->get($lhpp->notification_number)
                : null;
        @endphp

        <div class="flex items-center gap-2 justify-center">

            <!-- Durasi garansi -->
            <select name="garansi_months" class="text-xs px-2 py-1 border rounded">
                <option value="">-- Garansi (Bulan) --</option>
                @for($m=0; $m<=12; $m++)
                    <option value="{{ $m }}"
                        {{ (optional($garansi)->garansi_months == $m) ? 'selected' : '' }}>
                        {{ $m }} Bulan
                    </option>
                @endfor
            </select>

            <!-- Label (opsional) -->
            <input hidden type="text"
                   name="garansi_label"
                   value="{{ old('garansi_label') ?? (optional($garansi)->garansi_label ?? '') }}"
                   placeholder="Keterangan"
                   class="text-xs px-2 py-1 border rounded w-36">

            <button type="button"
                    onclick="submitGaransiForm('{{ $lhpp->notification_number }}', this)"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-2 py-1 rounded text-xs">
                Simpan
            </button>
        </div>
    </form>
</td>


                                <!-- ACTIONS: approve/reject/pdf sama seperti sebelumnya -->
                                <td class="px-4 py-2 text-center">
                                    <div class="inline-flex items-center gap-2">
                                    @if ($lhpp->status_approve === 'Pending')
                                        <form action="{{ route('admin.lhpp.approve', ['notification_number' => $lhpp->notification_number]) }}" method="POST" class="lhpp-approve-form">
                                            @csrf
                                        </form>

                                        <select class="text-[11px] px-2 py-1 rounded border border-slate-300 bg-white lhpp-approval-select"
                                                data-notif="{{ $lhpp->notification_number }}">
                                            <option value="">Pilih Aksi</option>
                                            <option value="approve">Setujui</option>
                                            <option value="reject">Tolak</option>
                                        </select>
                                    @endif

                                    <a href="{{ route('admin.lhpp.download_pdf', ['notification_number' => $lhpp->notification_number]) }}"
                                       target="_blank"
                                       rel="noopener"
                                       title="Lihat LHPP (PDF)"
                                       aria-label="Lihat LHPP PDF"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-slate-700 hover:bg-slate-800 text-white shadow-sm transition"
                                    >
                                        <i class="fas fa-file-pdf text-lg"></i>
                                    </a>
                                    </div>

                                </td>
                            </tr>

                            @if($lhpp->status_approve === 'Rejected' || in_array('rejected', [$lhpp->manager_signature, $lhpp->manager_signature_requesting, $lhpp->manager_pkm_signature]))
                                <tr class="bg-red-50">
                                    <td colspan="8" class="px-4 py-3 text-xs text-red-600">
                                        <strong>Dokumen ditolak</strong> -
                                        @php
                                            $rejecter = match (true) {
                                                $lhpp->status_approve === 'Rejected' => 'Admin',
                                                $lhpp->manager_signature === 'rejected' => 'Manager',
                                                $lhpp->manager_signature_requesting === 'rejected' => 'Manager Peminta',
                                                $lhpp->manager_pkm_signature === 'rejected' => 'Manager PKM',
                                                default => 'Tidak diketahui'
                                            };
                                        @endphp
                                        Ditolak oleh {{ $rejecter }} - Alasan: {{ $lhpp->rejection_reason }}

                                        @if($lhpp->status_approve !== 'Rejected')
                                            <p class="text-yellow-600">Harap buat ulang dokumen LHPP.</p>
                                        @endif
                                    </td>
                                </tr>
                            @endif

                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">{{ $lhpps->links() }}</div>
        </div>
    </div>

    <!-- Modal Reject -->
    <div id="rejectModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-lg font-semibold mb-4">Tolak LHPP</h2>
            <form id="rejectForm" method="POST">
                @csrf
                <textarea name="rejection_reason" class="w-full border border-gray-300 rounded-md p-2" placeholder="Alasan penolakan..." required></textarea>
                <div class="mt-4 flex justify-end">
                    <button type="button" onclick="closeRejectModal()" class="bg-gray-500 text-white px-3 py-1 rounded-md hover:bg-gray-700">Batal</button>
                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-700 ml-2">Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JS: submit garansi form (single submit), modal handlers, alerts -->
    <script>
        function submitGaransiForm(notificationNumber, btnEl) {
            const formId = 'garansi-form-' + notificationNumber;
            const form = document.getElementById(formId);
            if (!form) return console.error('Form tidak ditemukan', formId);

            if (form.dataset.submitted === '1') {
                btnEl.disabled = true;
                return;
            }

            form.dataset.submitted = '1';
            btnEl.disabled = true;
            const original = btnEl.innerHTML;
            btnEl.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...';

            try {
                form.submit();
            } catch (err) {
                console.error(err);
                form.dataset.submitted = '0';
                btnEl.disabled = false;
                btnEl.innerHTML = original;
                alert('Gagal mengirim, periksa console.');
            }
        }

        function openRejectModal(notificationNumber) {
            document.getElementById('rejectForm').action = "/admin/lhpp/" + notificationNumber + "/reject";
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        document.querySelectorAll('.lhpp-approval-select').forEach((sel) => {
            sel.addEventListener('change', () => {
                const action = sel.value;
                const row = sel.closest('td');
                if (!row) return;

                if (action === 'approve') {
                    const form = row.querySelector('.lhpp-approve-form');
                    if (form) form.submit();
                } else if (action === 'reject') {
                    const notif = sel.getAttribute('data-notif');
                    if (notif) openRejectModal(notif);
                }

                sel.value = '';
            });
        });
    </script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('success'))
        <script>
            Swal.fire({ title: 'Berhasil!', text: "{{ session('success') }}", icon: 'success', confirmButtonText: 'OK' });
        </script>
    @endif
    @if(session('error'))
        <script>
            Swal.fire({ title: 'Gagal!', text: "{{ session('error') }}", icon: 'error', confirmButtonText: 'OK' });
        </script>
    @endif
</x-admin-layout>
