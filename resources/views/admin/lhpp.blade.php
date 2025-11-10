<x-admin-layout> 
    <x-slot name="header">
        <h2 class="font-semibold text-[11px] text-gray-800 leading-tight">
            LHPP List
        </h2>
    </x-slot>

    @php
        /* ==== MINI PRESET (konsisten dgn Verifikasi Anggaran) ==== */
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

            <!-- HEADER + FILTER (mini & konsisten) -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-3 p-3">
                <div class="mb-2">
                    <h3 class="font-semibold text-[11px] text-slate-900 leading-tight">Laporan Hasil Pertanggung Jawaban Vendor</h3>
                    <p class="text-[9px] text-slate-500 leading-tight">Cari dokumen berdasarkan nomor order, PO, unit, dan lainnya.</p>
                </div>

                <form action="{{ route('admin.lhpp.index') }}" method="GET"
                      class="flex items-center gap-2 overflow-x-auto whitespace-nowrap">

                    <!-- Search (Indigo) -->
                    <div class="relative">
                        <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3 h-3 text-indigo-500" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari dokumen…"
                               class="{{ $selIndigo }} pl-6 w-72" />
                        <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-indigo-600 text-[10px]">⌕</span>
                    </div>

                    <!-- Tombol -->
                    <button type="submit" class="{{ $btnPrimary }} ml-auto inline-flex items-center">
                        <i class="fas fa-filter mr-1 text-[10px]"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.lhpp.index') }}" class="{{ $btnGhost }} inline-flex items-center">
                        <i class="fas fa-undo mr-1 text-[10px]"></i> Reset
                    </a>
                </form>
            </div>

            <!-- Bagian Tabel (TIDAK DIUBAH LOGICNYA) -->
            <div class="bg-white overflow-x-auto shadow-xl rounded-lg">
                <table class="min-w-full bg-white text-sm rounded-lg shadow-lg">
                    <thead class="bg-gray-500 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left">Nomor Order</th>
                            <th class="px-4 py-3 text-left">Nomor PO</th>
                            <th class="px-4 py-3 text-left">Unit Kerja</th>
                            <th class="px-4 py-3 text-left">Tanggal Selesai</th>
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-left">Total Biaya</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($lhpps as $lhpp)
                            <tr class="hover:bg-gray-100 transition duration-150">
                                <td class="px-4 py-3">{{ $lhpp->notification_number }}</td>
                                <td class="px-4 py-3">{{ $lhpp->purchase_order_number }}</td>
                                <td class="px-4 py-3">{{ $lhpp->unit_kerja }}</td>
                                <td class="px-4 py-3">{{ $lhpp->tanggal_selesai }}</td>
                                <td class="px-4 py-3">{{ $lhpp->waktu_pengerjaan }} Hari</td>
                                <td class="px-4 py-3">Rp{{ number_format($lhpp->total_biaya, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center flex justify-center space-x-2">
                                    @if ($lhpp->status_approve === 'Pending')
                                        <form action="{{ route('admin.lhpp.approve', ['notification_number' => $lhpp->notification_number]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded-lg hover:bg-green-700 transition">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>

                                        <button onclick="openRejectModal('{{ $lhpp->notification_number }}')" 
                                            class="bg-red-500 text-white px-2 py-1 rounded-lg hover:bg-red-700 transition">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif

                                    <a href="{{ route('admin.lhpp.download_pdf', ['notification_number' => $lhpp->notification_number]) }}" 
                                       class="bg-blue-500 text-white px-2 py-1 rounded-lg hover:bg-blue-700 transition flex items-center">
                                        Lihat LHPP
                                    </a>
                                </td>
                            </tr>

                            @if($lhpp->status_approve === 'Rejected' || in_array('rejected', [$lhpp->manager_signature, $lhpp->manager_signature_requesting, $lhpp->manager_pkm_signature]))
                                <tr class="bg-red-100">
                                    <td colspan="7" class="px-4 py-3 text-sm text-red-600">
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
            <div class="mt-4">
                {{ $lhpps->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Reject (TIDAK DIUBAH) -->
    <div id="rejectModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-lg font-semibold mb-4">Tolak LHPP</h2>
            <form id="rejectForm" method="POST">
                @csrf
                <textarea name="rejection_reason" class="w-full border border-gray-300 rounded-md p-2" 
                    placeholder="Alasan penolakan..." required></textarea>
                <div class="mt-4 flex justify-end">
                    <button type="button" onclick="closeRejectModal()" class="bg-gray-500 text-white px-3 py-1 rounded-md hover:bg-gray-700">
                        Batal
                    </button>
                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-700 ml-2">
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript untuk modal (TIDAK DIUBAH) -->
    <script>
        function openRejectModal(notificationNumber) {
            document.getElementById('rejectForm').action = "/admin/lhpp/" + notificationNumber + "/reject";
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>

    <!-- SweetAlert (TIDAK DIUBAH) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('success'))
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            Swal.fire({
                title: 'Gagal!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
</x-admin-layout>
