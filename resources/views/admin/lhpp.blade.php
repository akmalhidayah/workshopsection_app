<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-[11px] text-gray-800 leading-tight">LHPP List</h2>
    </x-slot>

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

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-4 lg:px-4">

            <!-- HEADER + FILTER -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-3 p-3">
                <div class="mb-2">
                    <h3 class="font-semibold text-[11px] text-slate-900 leading-tight">Laporan Hasil Pertanggung Jawaban Vendor</h3>
                    <p class="text-[9px] text-slate-500 leading-tight">Cari dokumen berdasarkan nomor order, PO, unit, dan lainnya.</p>
                </div>

                <form action="{{ route('admin.lhpp.index') }}" method="GET" class="flex items-center gap-2 overflow-x-auto whitespace-nowrap">
                    <div class="relative">
                        <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3 h-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari dokumen…" class="{{ $selIndigo }} pl-6 w-72" />
                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-indigo-600 text-[10px]">⌕</span>
                    </div>

                    <button type="submit" class="{{ $btnPrimary }} ml-auto inline-flex items-center">
                        <i class="fas fa-filter mr-1 text-[10px]"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.lhpp.index') }}" class="{{ $btnGhost }} inline-flex items-center">
                        <i class="fas fa-undo mr-1 text-[10px]"></i> Reset
                    </a>
                </form>
            </div>

            <!-- TABLE: modern, responsive, tanpa ubah logic data -->
            <div class="bg-white overflow-x-auto shadow-xl rounded-lg">
                <table class="min-w-full bg-white text-sm rounded-lg shadow-lg">
                    <thead class="bg-gray-200 text-gray-700 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left">Nomor Order</th>
                            <th class="px-4 py-3 text-left">Nomor PO</th>
                            <th class="px-4 py-3 text-left">Unit Kerja</th>
                            <th class="px-4 py-3 text-left">Tanggal Selesai</th>
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-right">Total Biaya</th>
                            <th class="px-4 py-3 text-center">Garansi</th>
                            <th class="px-4 py-3 text-center">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach ($lhpps as $lhpp)
                            @php
                                // tetap gunakan per-row lookup LPJ sesuai behavior lama (tidak mengubah controller)
                                $lpj = \App\Models\Lpj::where('notification_number', $lhpp->notification_number)->first();
                                $total_biaya = (float) ($lhpp->total_biaya ?? 0);
                            @endphp

                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-4 py-3 font-medium">{{ $lhpp->notification_number }}</td>
                                <td class="px-4 py-3">{{ $lhpp->purchase_order_number }}</td>
                                <td class="px-4 py-3">{{ $lhpp->unit_kerja }}</td>
                                <td class="px-4 py-3">{{ $lhpp->tanggal_selesai }}</td>
                                <td class="px-4 py-3">{{ $lhpp->waktu_pengerjaan }} Hari</td>
                                <td class="px-4 py-3 text-right">Rp{{ number_format($total_biaya, 0, ',', '.') }}</td>

                  <!-- GARANSI (DIPINDAHKAN DARI LPJ KE GARANSI) -->
<td class="px-4 py-3 text-center align-top w-64">
    <form id="garansi-form-{{ $lhpp->notification_number }}"
          action="{{ route('admin.lhpp.storeGaransi', $lhpp->notification_number) }}"
          method="POST">
        @csrf

        <!-- redirect_to agar kembali ke halaman LHPP admin -->
        <input type="hidden" name="redirect_to" value="{{ route('admin.lhpp.index') }}">

        @php
            // Ambil garansi jika ada
            $garansi = \App\Models\Garansi::where('notification_number', $lhpp->notification_number)->first();
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
                                <td class="px-4 py-3 text-center flex justify-center space-x-2">
                                    @if ($lhpp->status_approve === 'Pending')
                                        <form action="{{ route('admin.lhpp.approve', ['notification_number' => $lhpp->notification_number]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded-lg hover:bg-green-700 transition">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>

                                        <button onclick="openRejectModal('{{ $lhpp->notification_number }}')" class="bg-red-500 text-white px-2 py-1 rounded-lg hover:bg-red-700 transition">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif

                                    <a href="{{ route('admin.lhpp.download_pdf', ['notification_number' => $lhpp->notification_number]) }}"
                                       target="_blank"
                                       rel="noopener"
                                       title="Lihat LHPP (PDF)"
                                       aria-label="Lihat LHPP PDF"
                                       class="inline-flex items-center justify-center w-9 h-9 rounded-md bg-red-600 hover:bg-red-700 text-white shadow-sm transition"
                                    >
                                        <i class="fas fa-file-pdf text-lg"></i>
                                    </a>

                                </td>
                            </tr>

                            @if($lhpp->status_approve === 'Rejected' || in_array('rejected', [$lhpp->manager_signature, $lhpp->manager_signature_requesting, $lhpp->manager_pkm_signature]))
                                <tr class="bg-red-50">
                                    <td colspan="8" class="px-4 py-3 text-sm text-red-600">
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
