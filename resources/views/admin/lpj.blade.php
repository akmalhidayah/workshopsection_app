<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            {{ __('LPJ Management') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-[98%] mx-auto">

            <!-- FILTER -->
            <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200 mb-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="font-semibold text-md text-gray-800">LPJ / PPL</h3>
                        <p class="text-xs text-gray-500">Kelola unggah & status pembayaran LPJ / PPL</p>
                    </div>

                    <form method="GET" action="{{ route('admin.lpj') }}" class="w-full md:w-3/4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 items-end">
                            <div>
                                <label class="text-[11px] text-gray-600 block mb-1">Pencarian</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nomor Order"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm" />
                            </div>

                            <div>
                                <label class="text-[11px] text-gray-600 block mb-1">PO</label>
                                <select name="po" class="w-full px-2 py-1 border border-gray-300 rounded text-sm bg-white">
                                    <option value="">-- Semua PO --</option>
                                    @isset($poOptions)
                                        @foreach($poOptions as $poNum)
                                            <option value="{{ $poNum }}" {{ request('po') == $poNum ? 'selected' : '' }}>
                                                {{ $poNum }}
                                            </option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>

                            <div>
                                <label class="text-[11px] text-gray-600 block mb-1">Tampilkan</label>
                                <select name="entries" class="w-full px-2 py-1 border border-gray-300 rounded text-sm bg-white">
                                    <option value="10" {{ request('entries',10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('entries') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('entries') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2 justify-start md:justify-end">
                                <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                    <i class="fas fa-filter text-xs"></i>
                                    <span>Filter</span>
                                </button>

                                <a href="{{ route('admin.lpj') }}" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1 rounded text-sm">
                                    <i class="fas fa-undo text-xs"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- END FILTER -->

            <!-- MAIN TABLE -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-3">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-gray-800">
                        <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Nomor Order</th>
                                <th class="px-3 py-2 text-left font-semibold">Tanggal Update</th>
                                <th class="px-3 py-2 text-left font-semibold">Nomor LPJ / PPL</th>
                                <th class="px-3 py-2 text-left font-semibold">Dokumen (Termin)</th>
                                <th class="px-3 py-2 text-left font-semibold">Pembayaran</th>
                                <th class="px-3 py-2 text-center font-semibold w-24">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($notifications as $notification)
                                @php
                                    $lpj = $lpjMap[$notification->notification_number] ?? null;
                                    $lhpp = $lhppMap[$notification->notification_number] ?? null;
                                    $po = $poMap[$notification->notification_number] ?? null;
                                    $total_biaya = (float) ($lhpp->total_biaya ?? 0);
                                @endphp

                                @if($lpj || $lhpp)
                                    <tr class="odd:bg-white even:bg-gray-50 border-b align-top">
                                        <!-- NOMOR ORDER -->
                                        <td class="px-3 py-3 align-top">
                                            <div class="text-sm font-semibold text-gray-800">{{ $notification->notification_number }}</div>

                                            @if($po && $po->purchase_order_number)
                                                <div class="mt-1 inline-flex items-center gap-1 bg-green-50 text-green-700 border border-green-100 px-2 py-0.5 rounded text-[11px]">
                                                    <span class="uppercase text-[10px] tracking-wide">PO</span>
                                                    <span class="font-semibold text-[12px]">{{ $po->purchase_order_number }}</span>
                                                </div>
                                            @else
                                                <div class="mt-1 text-[11px] text-gray-400">PO: -</div>
                                            @endif
                                        </td>

                                        <!-- TANGGAL UPDATE -->
                                        <td class="px-3 py-3 align-top text-sm text-gray-700">
                                            @if(optional($lpj)->update_date)
                                                {{ \Carbon\Carbon::parse($lpj->update_date)->format('Y-m-d H:i') }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <!-- NOMOR LPJ / PPL -->
                                        <td class="px-3 py-3 align-top w-72">
                                            <form id="form-{{ $notification->notification_number }}" action="{{ route('lpj.update', $notification->notification_number) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="flex flex-col gap-2 text-[12px]">
                                                    <div class="flex gap-2 items-center">
                                                        <label class="text-[12px] text-gray-600 w-20">Termin</label>
                                                        <select id="termin_select_{{ $notification->notification_number }}" data-notif="{{ $notification->notification_number }}" class="px-2 py-1 text-sm border rounded bg-white"
                                                                onchange="onTerminChange('{{ $notification->notification_number }}')">
                                                            <option value="1" {{ ( ($lpj && ($lpj->lpj_number_termin1 || $lpj->lpj_document_path_termin1)) || ! $lpj ) ? 'selected' : '' }}>Termin 1</option>
                                                            <option value="2" {{ ($lpj && ($lpj->lpj_number_termin2 || $lpj->lpj_document_path_termin2)) ? 'selected' : '' }}>Termin 2</option>
                                                        </select>
                                                    </div>

                                                    <div class="flex gap-2">
                                                        <div class="flex-1">
                                                            <label class="text-[11px] text-gray-600">Nomor LPJ</label>
                                                            <input id="lpj_input_{{ $notification->notification_number }}" type="text" name="lpj_number"
                                                                value="{{ old('lpj_number') ?? ($lpj->lpj_number_termin1 ?? '') }}"
                                                                placeholder="Nomor LPJ"
                                                                class="w-full px-2 py-1 border border-gray-200 rounded text-sm" required>
                                                        </div>

                                                        <div class="flex-1">
                                                            <label class="text-[11px] text-gray-600">Nomor PPL</label>
                                                            <input id="ppl_input_{{ $notification->notification_number }}" type="text" name="ppl_number"
                                                                value="{{ old('ppl_number') ?? ($lpj->ppl_number_termin1 ?? '') }}"
                                                                placeholder="Nomor PPL"
                                                                class="w-full px-2 py-1 border border-gray-200 rounded text-sm">
                                                        </div>
                                                    </div>

                                                    <p class="text-[11px] text-gray-400">Pilih termin untuk menampilkan semua data terkait termin tersebut.</p>

                                                    <input type="hidden" id="selected_termin_input_{{ $notification->notification_number }}" name="selected_termin" value="1" />
                                                </div>
                                            </form>
                                        </td>

                                        <!-- DOKUMEN (STACKED VERTICAL + SINGLE UPLOAD PER TERMIN) -->
                                        <td class="px-3 py-3 align-top w-96">
                                            <div class="flex flex-col gap-3">
                                                {{-- LPJ stacked --}}
                                                <div class="flex flex-col gap-2">
                                                    <div class="flex items-center justify-between">
                                                        <div class="text-[11px] font-medium text-gray-600">LPJ</div>

                                                        {{-- small upload controls (only one shown by JS) --}}
                                                        <div class="flex items-center gap-1">
                                                            <label id="lpj_upload_label_t1_{{ $notification->notification_number }}" for="lpj_document_{{ $notification->notification_number }}_t1"
                                                                   class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-600 hover:bg-green-700 text-white border border-green-700 rounded text-xs cursor-pointer hidden">
                                                                <i class="fas fa-upload text-[10px]"></i>
                                                                <span class="ml-1 text-[11px]" id="lpj_upload_text_t1_{{ $notification->notification_number }}">(T1)</span>
                                                            </label>
                                                            <input id="lpj_document_{{ $notification->notification_number }}_t1" type="file" name="lpj_document" form="form-{{ $notification->notification_number }}" accept=".pdf,.doc,.docx" class="hidden" onchange="showFileName(this,'lpj_filename_{{ $notification->notification_number }}')">

                                                            <label id="lpj_upload_label_t2_{{ $notification->notification_number }}" for="lpj_document_{{ $notification->notification_number }}_t2"
                                                                   class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-600 hover:bg-green-700 text-white border border-green-700 rounded text-xs cursor-pointer hidden">
                                                                <i class="fas fa-upload text-[10px]"></i>
                                                                <span class="ml-1 text-[11px]" id="lpj_upload_text_t2_{{ $notification->notification_number }}">(T2)</span>
                                                            </label>
                                                            <input id="lpj_document_{{ $notification->notification_number }}_t2" type="file" name="lpj_document_termin2" form="form-{{ $notification->notification_number }}" accept=".pdf,.doc,.docx" class="hidden" onchange="showFileName(this,'lpj_filename_{{ $notification->notification_number }}')">
                                                        </div>
                                                    </div>

                                                    <!-- PDF badge (compact) -->
                                                    <a id="lpj_pdf_badge_{{ $notification->notification_number }}" href="#" target="_blank"
                                                       class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-600 text-white rounded text-xs max-w-[150px] hidden">
                                                        <i class="fas fa-file-pdf text-[11px]"></i>
                                                        <span id="lpj_pdf_name_{{ $notification->notification_number }}" class="truncate max-w-[110px] block">LPJ.pdf</span>
                                                        <span id="lpj_pdf_tag_{{ $notification->notification_number }}" class="ml-1 bg-white text-red-600 px-1 rounded text-[10px] font-semibold">T1</span>
                                                    </a>

                                                    <div class="text-[11px] text-gray-600 mt-0"><span id="lpj_filename_{{ $notification->notification_number }}"></span></div>
                                                </div>

                                                {{-- PPL stacked --}}
                                                <div class="flex flex-col gap-2">
                                                    <div class="flex items-center justify-between">
                                                        <div class="text-[11px] font-medium text-gray-600">PPL</div>

                                                        <div class="flex items-center gap-1">
                                                            <label id="ppl_upload_label_t1_{{ $notification->notification_number }}" for="ppl_document_{{ $notification->notification_number }}_t1"
                                                                   class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-600 hover:bg-green-700 text-white border border-green-700 rounded text-xs cursor-pointer hidden">
                                                                <i class="fas fa-upload text-[10px]"></i>
                                                                <span class="ml-1 text-[11px]" id="ppl_upload_text_t1_{{ $notification->notification_number }}">(T1)</span>
                                                            </label>
                                                            <input id="ppl_document_{{ $notification->notification_number }}_t1" type="file" name="ppl_document" form="form-{{ $notification->notification_number }}" accept=".pdf,.doc,.docx" class="hidden" onchange="showFileName(this,'ppl_filename_{{ $notification->notification_number }}')">

                                                            <label id="ppl_upload_label_t2_{{ $notification->notification_number }}" for="ppl_document_{{ $notification->notification_number }}_t2"
                                                                   class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-600 hover:bg-green-700 text-white border border-green-700 rounded text-xs cursor-pointer hidden">
                                                                <i class="fas fa-upload text-[10px]"></i>
                                                                <span class="ml-1 text-[11px]" id="ppl_upload_text_t2_{{ $notification->notification_number }}">(T2)</span>
                                                            </label>
                                                            <input id="ppl_document_{{ $notification->notification_number }}_t2" type="file" name="ppl_document_termin2" form="form-{{ $notification->notification_number }}" accept=".pdf,.doc,.docx" class="hidden" onchange="showFileName(this,'ppl_filename_{{ $notification->notification_number }}')">
                                                        </div>
                                                    </div>

                                                    <a id="ppl_pdf_badge_{{ $notification->notification_number }}" href="#" target="_blank"
                                                       class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-600 text-white rounded text-xs max-w-[150px] hidden">
                                                        <i class="fas fa-file-pdf text-[11px]"></i>
                                                        <span id="ppl_pdf_name_{{ $notification->notification_number }}" class="truncate max-w-[110px] block">PPL.pdf</span>
                                                        <span id="ppl_pdf_tag_{{ $notification->notification_number }}" class="ml-1 bg-white text-red-600 px-1 rounded text-[10px] font-semibold">T1</span>
                                                    </a>

                                                    <div class="text-[11px] text-gray-600 mt-0"><span id="ppl_filename_{{ $notification->notification_number }}"></span></div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- PEMBAYARAN -->
                                        <td class="px-3 py-3 align-top">
                                            <div class="text-sm text-gray-600">
                                                <div class="text-xs text-gray-500">Total LHPP</div>
                                                <div class="font-semibold text-slate-800">Rp {{ number_format($total_biaya,0,',','.') }}</div>
                                            </div>

                                            <div class="mt-2 text-sm space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-20 text-[11px] text-gray-500">Termin 1</div>
                                                    <select id="pay_termin1_{{ $notification->notification_number }}" name="termin1" form="form-{{ $notification->notification_number }}" class="text-xs px-2 py-1 border rounded">
                                                        <option value="belum" {{ ($lpj->termin1 ?? 'belum') === 'belum' ? 'selected' : '' }}>Belum</option>
                                                        <option value="sudah" {{ ($lpj->termin1 ?? '') === 'sudah' ? 'selected' : '' }}>Sudah</option>
                                                    </select>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <div class="w-20 text-[11px] text-gray-500">Termin 2</div>
                                                    <select id="pay_termin2_{{ $notification->notification_number }}" name="termin2" form="form-{{ $notification->notification_number }}" class="text-xs px-2 py-1 border rounded">
                                                        <option value="belum" {{ ($lpj->termin2 ?? 'belum') === 'belum' ? 'selected' : '' }}>Belum</option>
                                                        <option value="sudah" {{ ($lpj->termin2 ?? '') === 'sudah' ? 'selected' : '' }}>Sudah</option>
                                                    </select>
                                                </div>

                                                @php
                                                    $t1 = ($lpj->termin1 ?? '') === 'sudah';
                                                    $t2 = ($lpj->termin2 ?? '') === 'sudah';
                                                    $paidAmount = null;
                                                    $paidLabel = null;
                                                    if ($total_biaya > 0) {
                                                        if ($t1 && $t2) { $paidAmount = $total_biaya; $paidLabel = '100%'; }
                                                        elseif ($t1) { $paidAmount = (int) round($total_biaya * 0.95); $paidLabel = '95%'; }
                                                    }
                                                @endphp

                                                <div class="text-[12px]">
                                                    @if(!is_null($paidAmount))
                                                        Terbayar ({{ $paidLabel }}): <span class="font-semibold">Rp {{ number_format($paidAmount,0,',','.') }}</span>
                                                    @else
                                                        Terbayar: <span class="text-gray-400">-</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <!-- AKSI -->
                                        <td class="px-3 py-3 text-center align-top">
                                            <div class="flex flex-col items-center gap-2">
                                                <button type="button" onclick="submitLpjForm('{{ $notification->notification_number }}', this)" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm flex items-center gap-2">
                                                    <i class="fas fa-save"></i> Simpan
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- per-row script: termin switching + preview handling + upload-button toggle -->
                                    <script>
                                        (function(){
                                            const notif = "{{ $notification->notification_number }}";

                                            // file urls (null or full URL)
                                            const lpj_t1 = @json(optional($lpj)->lpj_document_path_termin1 ? Storage::url($lpj->lpj_document_path_termin1) : null);
                                            const ppl_t1 = @json(optional($lpj)->ppl_document_path_termin1 ? Storage::url($lpj->ppl_document_path_termin1) : null);
                                            const lpj_t2 = @json(optional($lpj)->lpj_document_path_termin2 ? Storage::url($lpj->lpj_document_path_termin2) : null);
                                            const ppl_t2 = @json(optional($lpj)->ppl_document_path_termin2 ? Storage::url($lpj->ppl_document_path_termin2) : null);

                                            // nomor per termin
                                            const lpj_num_t1 = @json(optional($lpj)->lpj_number_termin1 ?? '');
                                            const ppl_num_t1 = @json(optional($lpj)->ppl_number_termin1 ?? '');
                                            const lpj_num_t2 = @json(optional($lpj)->lpj_number_termin2 ?? '');
                                            const ppl_num_t2 = @json(optional($lpj)->ppl_number_termin2 ?? '');

                                            window.addEventListener('DOMContentLoaded', function(){
                                                const sel = document.getElementById('termin_select_' + notif);
                                                const selInput = document.getElementById('selected_termin_input_' + notif);
                                                const lpjInput = document.getElementById('lpj_input_' + notif);
                                                const pplInput = document.getElementById('ppl_input_' + notif);

                                                // badges
                                                const lpjBadge = document.getElementById('lpj_pdf_badge_' + notif);
                                                const lpjName = document.getElementById('lpj_pdf_name_' + notif);
                                                const lpjTag  = document.getElementById('lpj_pdf_tag_' + notif);

                                                const pplBadge = document.getElementById('ppl_pdf_badge_' + notif);
                                                const pplName = document.getElementById('ppl_pdf_name_' + notif);
                                                const pplTag  = document.getElementById('ppl_pdf_tag_' + notif);

                                                // upload labels (t1 / t2) for LPJ
                                                const lpjUploadT1 = document.getElementById('lpj_upload_label_t1_' + notif);
                                                const lpjUploadT2 = document.getElementById('lpj_upload_label_t2_' + notif);
                                                // upload labels (t1 / t2) for PPL
                                                const pplUploadT1 = document.getElementById('ppl_upload_label_t1_' + notif);
                                                const pplUploadT2 = document.getElementById('ppl_upload_label_t2_' + notif);

                                                // payment selects
                                                const pay1 = document.getElementById('pay_termin1_' + notif);
                                                const pay2 = document.getElementById('pay_termin2_' + notif);

                                                function toggleUploadButtons(termin) {
                                                    // LPJ
                                                    if (termin === '1') {
                                                        lpjUploadT1.classList.remove('hidden');
                                                        lpjUploadT2.classList.add('hidden');

                                                        pplUploadT1.classList.remove('hidden');
                                                        pplUploadT2.classList.add('hidden');
                                                    } else {
                                                        lpjUploadT1.classList.add('hidden');
                                                        lpjUploadT2.classList.remove('hidden');

                                                        pplUploadT1.classList.add('hidden');
                                                        pplUploadT2.classList.remove('hidden');
                                                    }
                                                }

                                                function showForTermin(termin) {
                                                    selInput.value = termin;

                                                    if (termin === '1') {
                                                        lpjInput.value = lpj_num_t1 || '';
                                                        pplInput.value = ppl_num_t1 || '';

                                                        if (lpj_t1) {
                                                            lpjBadge.href = lpj_t1;
                                                            lpjName.textContent = lpj_t1.split('/').pop();
                                                            lpjTag.textContent = 'T1';
                                                            lpjBadge.classList.remove('hidden');
                                                        } else lpjBadge.classList.add('hidden');

                                                        if (ppl_t1) {
                                                            pplBadge.href = ppl_t1;
                                                            pplName.textContent = ppl_t1.split('/').pop();
                                                            pplTag.textContent = 'T1';
                                                            pplBadge.classList.remove('hidden');
                                                        } else pplBadge.classList.add('hidden');

                                                        pay1.classList.add('ring-2','ring-gray-300');
                                                        pay2.classList.remove('ring-2','ring-gray-300');
                                                    } else {
                                                        lpjInput.value = lpj_num_t2 || '';
                                                        pplInput.value = ppl_num_t2 || '';

                                                        if (lpj_t2) {
                                                            lpjBadge.href = lpj_t2;
                                                            lpjName.textContent = lpj_t2.split('/').pop();
                                                            lpjTag.textContent = 'T2';
                                                            lpjBadge.classList.remove('hidden');
                                                        } else lpjBadge.classList.add('hidden');

                                                        if (ppl_t2) {
                                                            pplBadge.href = ppl_t2;
                                                            pplName.textContent = ppl_t2.split('/').pop();
                                                            pplTag.textContent = 'T2';
                                                            pplBadge.classList.remove('hidden');
                                                        } else pplBadge.classList.add('hidden');

                                                        pay2.classList.add('ring-2','ring-gray-300');
                                                        pay1.classList.remove('ring-2','ring-gray-300');
                                                    }

                                                    // also toggle upload buttons
                                                    toggleUploadButtons(termin);
                                                }

                                                // default prefer termin2 if has any data
                                                const prefer2 = (lpj_num_t2 && lpj_num_t2.length) || (ppl_num_t2 && ppl_num_t2.length) || lpj_t2 || ppl_t2;
                                                const initial = prefer2 ? '2' : '1';
                                                sel.value = initial;
                                                showForTermin(initial);

                                                sel.addEventListener('change', function(){
                                                    showForTermin(this.value);
                                                });
                                            });
                                        })();
                                    </script>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-gray-500">Tidak ada data LPJ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-center">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>

<script>
    function showFileName(input, spanId) {
        try {
            const file = input.files && input.files[0];
            const el = document.getElementById(spanId);
            if (el) el.textContent = file ? 'File: ' + file.name : '';
        } catch (e) { console.error(e); }
    }

    function submitLpjForm(notificationNumber, btnEl) {
        const formId = 'form-' + notificationNumber;
        const form = document.getElementById(formId);
        if (!form) return;

        if (form.dataset.submitted === '1') {
            btnEl.disabled = true;
            return;
        }

        form.dataset.submitted = '1';
        btnEl.disabled = true;
        const originalHTML = btnEl.innerHTML;
        btnEl.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...';

        try {
            form.submit();
        } catch (err) {
            console.error(err);
            form.dataset.submitted = '0';
            btnEl.disabled = false;
            btnEl.innerHTML = originalHTML;
            alert('Gagal mengirim form, periksa console.');
        }
    }

    // fallback trigger for select (used by some older calls)
    function onTerminChange(notificationNumber) {
        const sel = document.getElementById('termin_select_' + notificationNumber);
        if (!sel) return;
        sel.dispatchEvent(new Event('change'));
    }
</script>

<!-- SweetAlert for success -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('success'))
    <script>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '{{ session("success") }}' });
    </script>
@endif
</x-admin-layout>
