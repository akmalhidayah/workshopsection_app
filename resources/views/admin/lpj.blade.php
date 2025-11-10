<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('LPJ Management') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-[98%] mx-auto">
           <!-- FILTER CONTAINER (simple: search by notification_number only) -->
<div class="bg-white shadow-sm rounded-lg p-5 border border-gray-200 mb-5">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h3 class="font-semibold text-lg text-gray-800">LPJ / PPL</h3>
            <p class="text-xs text-gray-500">Kelola unggah & status pembayaran LPJ / PPL</p>
        </div>

        <form method="GET" action="{{ route('admin.lpj') }}" class="flex flex-wrap items-end gap-3 w-full md:w-auto">

            <!-- Pencarian (notification_number) -->
            <div>
                <label class="text-xs text-gray-600 block mb-1">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nomor Order"
                    class="px-3 py-2 border border-gray-300 rounded-md text-sm w-64 focus:ring-2 focus:ring-indigo-200">
            </div>

            <!-- Tampilkan (entries) -->
            <div>
                <label class="text-xs text-gray-600 block mb-1">Tampilkan</label>
                <select name="entries" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="10" {{ request('entries',10) == 10 ? 'selected' : '' }}>10 Data</option>
                    <option value="25" {{ request('entries') == 25 ? 'selected' : '' }}>25 Data</option>
                    <option value="50" {{ request('entries') == 50 ? 'selected' : '' }}>50 Data</option>
                </select>
            </div>

            <!-- Tombol -->
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.lpj') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>
<!-- END FILTER CONTAINER -->


            <!-- MAIN TABLE -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-sm text-gray-800">
                        <thead class="bg-gray-100 border-b border-gray-200 text-gray-700 text-xs uppercase">
                            <tr>
                                <th class="px-3 py-3 text-left font-semibold">Nomor Order</th>
                                <th class="px-3 py-3 text-left font-semibold">Tanggal Update</th>
                                <th class="px-3 py-3 text-left font-semibold">Nomor LPJ / PPL</th>
                                <th class="px-3 py-3 text-left font-semibold">Dokumen</th>
                                <th class="px-3 py-3 text-left font-semibold">Pembayaran</th>
                                <th class="px-3 py-3 text-center font-semibold w-28">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($notifications as $notification)
                                @php
                                    // tetap query per baris sesuai behavior lama
                                    $lpj = App\Models\Lpj::where('notification_number', $notification->notification_number)->first();
                                    $lhpp = App\Models\LHPP::where('notification_number', $notification->notification_number)->first();
                                    $has_lhpp = (bool) $lhpp;
                                    $total_biaya = (float) ($lhpp->total_biaya ?? 0);
                                    $jumlah_terbayarkan = $total_biaya ? round($total_biaya * 0.9) : 0;
                                @endphp

                                @if($lpj || $has_lhpp)
                                    <tr class="hover:bg-gray-50 border-b border-gray-100 align-top">
                                        <!-- Nomor Order -->
                                        <td class="px-3 py-3 align-top font-medium">
                                            {{ $notification->notification_number }}
                                        </td>

                                        <!-- Update date -->
                                        <td class="px-3 py-3 align-top text-sm text-gray-700">
                                            @if(optional($lpj)->update_date)
                                                {{ \Carbon\Carbon::parse($lpj->update_date)->format('Y-m-d H:i:s') }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <!-- Nomor LPJ / PPL -->
                                        <td class="px-3 py-3 align-top w-64">
                                            <form id="form-{{ $notification->notification_number }}" action="{{ route('lpj.update', $notification->notification_number) }}" method="POST" enctype="multipart/form-data">
                                                @csrf

                                                <div class="space-y-2">
                                                    <div class="flex items-center gap-2">
                                                        <input type="text" name="lpj_number"
                                                            value="{{ old('lpj_number') ?? ($lpj->lpj_number ?? '') }}"
                                                            class="w-full px-2 py-1 text-sm border border-gray-200 rounded" placeholder="Nomor LPJ" required>
                                                    </div>

                                                    <div class="flex items-center gap-2 mt-1">
                                                        <input type="text" name="ppl_number"
                                                            value="{{ old('ppl_number') ?? ($lpj->ppl_number ?? '') }}"
                                                            class="w-full px-2 py-1 text-sm border border-gray-200 rounded" placeholder="Nomor PPL">
                                                    </div>
                                                </div>
                                            </form>
                                        </td>

                                        <!-- Dokumen: jika ada show 'Lihat' + 'Ubah', jika belum tampil 'Upload' -->
                                        <td class="px-3 py-3 align-top w-80">
                                            <div class="space-y-2">
                                                {{-- LPJ --}}
                                                <div class="flex items-center gap-2">
                                                    @if(optional($lpj)->lpj_document_path)
                                                        <a href="{{ Storage::url($lpj->lpj_document_path) }}" target="_blank" class="inline-flex items-center gap-2 px-2 py-1 text-xs bg-white border border-gray-200 rounded hover:bg-gray-50">
                                                            <i class="fas fa-file-alt text-[12px]"></i>
                                                            <span class="text-[12px]">Lihat LPJ</span>
                                                        </a>

                                                        {{-- small "Ubah" button --}}
                                                        <label for="lpj_document_{{ $notification->notification_number }}" class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded cursor-pointer">
                                                            <i class="fas fa-upload text-[11px]"></i>
                                                            <span class="text-[12px]">Ubah</span>
                                                        </label>
                                                    @else
                                                        <label for="lpj_document_{{ $notification->notification_number }}" class="inline-flex items-center gap-2 px-2 py-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded cursor-pointer">
                                                            <i class="fas fa-upload text-[11px]"></i>
                                                            <span class="text-[12px]">Upload LPJ</span>
                                                        </label>
                                                    @endif

                                                    <div class="text-gray-500 text-xs ml-2">
                                                        {{ optional($lpj)->lpj_document_path ? basename($lpj->lpj_document_path) : 'Belum ada' }}
                                                    </div>

                                                    <input id="lpj_document_{{ $notification->notification_number }}" type="file" name="lpj_document" form="form-{{ $notification->notification_number }}" accept=".pdf,.doc,.docx" class="hidden" onchange="showFileName(this,'lpj_filename_{{ $notification->notification_number }}')">
                                                </div>

                                                {{-- PPL --}}
                                                <div class="flex items-center gap-2 mt-1">
                                                    @if(optional($lpj)->ppl_document_path)
                                                        <a href="{{ Storage::url($lpj->ppl_document_path) }}" target="_blank" class="inline-flex items-center gap-2 px-2 py-1 text-xs bg-white border border-gray-200 rounded hover:bg-gray-50">
                                                            <i class="fas fa-file-alt text-[12px]"></i>
                                                            <span class="text-[12px]">Lihat PPL</span>
                                                        </a>

                                                        <label for="ppl_document_{{ $notification->notification_number }}" class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded cursor-pointer">
                                                            <i class="fas fa-upload text-[11px]"></i>
                                                            <span class="text-[12px]">Ubah</span>
                                                        </label>
                                                    @else
                                                        <label for="ppl_document_{{ $notification->notification_number }}" class="inline-flex items-center gap-2 px-2 py-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded cursor-pointer">
                                                            <i class="fas fa-upload text-[11px]"></i>
                                                            <span class="text-[12px]">Upload PPL</span>
                                                        </label>
                                                    @endif

                                                    <div class="text-gray-500 text-xs ml-2">
                                                        {{ optional($lpj)->ppl_document_path ? basename($lpj->ppl_document_path) : 'Belum ada' }}
                                                    </div>

                                                    <input id="ppl_document_{{ $notification->notification_number }}" type="file" name="ppl_document" form="form-{{ $notification->notification_number }}" accept=".pdf,.doc,.docx" class="hidden" onchange="showFileName(this,'ppl_filename_{{ $notification->notification_number }}')">
                                                </div>

                                                <div class="text-[11px] text-gray-600 mt-1">
                                                    <span id="lpj_filename_{{ $notification->notification_number }}"></span>
                                                    <span id="ppl_filename_{{ $notification->notification_number }}"></span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Pembayaran: hanya Total LHPP + Terbayar(90%) + selects Termin1/2 -->
                                        <td class="px-3 py-3 align-top w-48">
                                            <div class="space-y-2 text-sm">
                                                <div>
                                                    <div class="text-[12px] text-gray-500">Total LHPP</div>
                                                    <div class="font-medium text-sm text-slate-800">Rp {{ number_format($total_biaya,0,',','.') }}</div>
                                                </div>

                                                <div class="mt-2">
                                                    <div class="flex items-center gap-2">
                                                        <div class="text-[11px] text-gray-500 w-20">Termin 1</div>
                                                        <select name="termin1" form="form-{{ $notification->notification_number }}" class="text-xs px-2 py-1 border rounded">
                                                            <option value="belum" {{ ($lpj->termin1 ?? 'belum') === 'belum' ? 'selected' : '' }}>Belum</option>
                                                            <option value="sudah" {{ ($lpj->termin1 ?? '') === 'sudah' ? 'selected' : '' }}>Sudah</option>
                                                        </select>
                                                    </div>

                                                    <div class="flex items-center gap-2 mt-2">
                                                        <div class="text-[11px] text-gray-500 w-20">Termin 2</div>
                                                        <select name="termin2" form="form-{{ $notification->notification_number }}" class="text-xs px-2 py-1 border rounded">
                                                            <option value="belum" {{ ($lpj->termin2 ?? 'belum') === 'belum' ? 'selected' : '' }}>Belum</option>
                                                            <option value="sudah" {{ ($lpj->termin2 ?? '') === 'sudah' ? 'selected' : '' }}>Sudah</option>
                                                        </select>
                                                    </div>
                                                </div>

                                              @php
    // hitung jumlah terbayarkan berdasarkan termin
    $paidAmount = null;
    $paidLabel = null;

    $t1 = ($lpj->termin1 ?? '') === 'sudah';
    $t2 = ($lpj->termin2 ?? '') === 'sudah';

    if ($total_biaya > 0) {
        if ($t1 && $t2) {
            // kedua termin sudah -> 100%
            $paidAmount = $total_biaya;
            $paidLabel = '100%';
        } elseif ($t1) {
            // hanya termin1 -> 90%
            $paidAmount = (int) round($total_biaya * 0.95);
            $paidLabel = '95%';
        }
    }
@endphp

<div class="mt-2">
    @if(!is_null($paidAmount))
        <div class="text-[12px] text-gray-600">
            Terbayar ({{ $paidLabel }}): <span class="font-medium text-slate-800">Rp {{ number_format($paidAmount,0,',','.') }}</span>
        </div>
    @else
        <div class="text-[12px] text-gray-400">Terbayar: -</div>
    @endif
</div>


                                                {{-- Garansi --}}
                                                <div class="mt-2">
                                                    <div class="flex items-center gap-2">
                                                        <select name="garansi_months" form="form-{{ $notification->notification_number }}" class="text-xs px-2 py-1 border rounded">
                                                            <option value="">-- Garansi (Bulan) --</option>
                                                            @for($m=1;$m<=12;$m++)
                                                                <option value="{{ $m }}" {{ (optional($lpj)->garansi_months == $m) ? 'selected' : '' }}>{{ $m }} Bulan</option>
                                                            @endfor
                                                        </select>

                                                        <input name="garansi_label" form="form-{{ $notification->notification_number }}" value="{{ old('garansi_label') ?? (optional($lpj)->garansi_label ?? '') }}" placeholder="Keterangan (opsional)" class="text-xs px-2 py-1 border rounded w-36">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Aksi -->
                                        <td class="px-3 py-3 text-center align-top">
                                            <div class="flex items-center justify-center gap-2 h-full">
                                                <!-- NOTE: type changed to button to avoid native double-submit -->
                                                <button type="button" onclick="submitLpjForm('{{ $notification->notification_number }}', this)" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">
                                                    <i class="fas fa-save mr-1"></i> Simpan
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-gray-500">Tidak ada data LPJ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-center">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS: file name display, label click handler, and single-entry submit function -->
<script>
    function showFileName(input, spanId) {
        try {
            const file = input.files && input.files[0];
            const el = document.getElementById(spanId);
            if (el) el.textContent = file ? 'File: ' + file.name : '';
        } catch (e) { console.error(e); }
    }

    // delegated label click -> open file chooser (tetap aman)
document.addEventListener('click', function (e) {
    const label = e.target.closest('label[for^="lpj_document_"], label[for^="ppl_document_"]');
    if (!label) return;

    // HENTIKAN perilaku default label yang juga memicu click ke input file
    e.preventDefault();
    e.stopPropagation();

    const id = label.getAttribute('for');
    if (!id) return;
    const inp = document.getElementById(id);
    if (inp) inp.click(); // sekarang hanya sekali
});


    // central submit function: only one submit call per form
    function submitLpjForm(notificationNumber, btnEl) {
        const formId = 'form-' + notificationNumber;
        const form = document.getElementById(formId);
        if (!form) {
            console.error('Form tidak ditemukan:', formId);
            return;
        }

        // prevent double-click: jika sudah dikirim, tolak
        if (form.dataset.submitted === '1') {
            // optional: beri feedback singkat
            btnEl.disabled = true;
            return;
        }

        // beri tanda submitted & disable tombol
        form.dataset.submitted = '1';
        btnEl.disabled = true;

        // tambahkan loading kecil (opsional)
        const originalHTML = btnEl.innerHTML;
        btnEl.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...';

        // submit via JS (single)
        try {
            form.submit();
        } catch (err) {
            console.error('Gagal submit form via JS:', err);
            // restore agar user bisa coba lagi
            form.dataset.submitted = '0';
            btnEl.disabled = false;
            btnEl.innerHTML = originalHTML;
            alert('Gagal mengirim, periksa console.');
        }
    }
</script>

    <!-- SweetAlert (preserve behavior) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('success'))
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ session("success") }}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif
</x-admin-layout>
