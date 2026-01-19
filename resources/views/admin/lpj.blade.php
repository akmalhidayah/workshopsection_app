<x-admin-layout>
    <div class="py-6">
        <div class="w-full max-w-[98%] mx-auto">

            <div class="admin-card p-5 mb-4">
                <div class="admin-header">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                            <i data-lucide="folder-open" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h1 class="admin-title">LPJ / PPL</h1>
                            <p class="admin-subtitle">Kelola unggah dan status pembayaran LPJ / PPL</p>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.lpj') }}" class="admin-filter mt-4">
                    <div class="w-full">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
                            <div>
                                <label class="text-xs text-slate-600 block mb-1">Pencarian</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nomor Order"
                                    class="admin-input w-full" />
                            </div>

                            <div>
                                <label class="text-xs text-slate-600 block mb-1">PO</label>
                                <select name="po" class="admin-select w-full">
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
                                <label class="text-xs text-slate-600 block mb-1">Tampilkan</label>
                                <select name="entries" class="admin-select w-full">
                                    <option value="10" {{ request('entries',10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('entries') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('entries') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2 justify-start md:justify-end">
                                <button type="submit" class="admin-btn admin-btn-primary">
                                    <i data-lucide="filter" class="w-4 h-4"></i>
                                    <span>Filter</span>
                                </button>

                                <a href="{{ route('admin.lpj') }}" class="admin-btn admin-btn-ghost">
                                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

<!-- MAIN TABLE -->
            <div class="admin-card p-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-[11px] text-slate-700">
                        <thead class="bg-slate-50 text-slate-600 uppercase tracking-wide border-b border-slate-200">
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
                            @php $rendered = 0; @endphp
                            @forelse($notifications as $notification)
                                @php
                                    $lpj = $lpjMap[$notification->notification_number] ?? null;
                                    $lhpp = $lhppMap[$notification->notification_number] ?? null;
                                    $po = $poMap[$notification->notification_number] ?? null;
                                    $total_biaya = (float) ($lhpp->total_biaya ?? 0);
                                @endphp

                                @if($lpj || $lhpp)
                                    @php $rendered++; @endphp
                                    <tr class="odd:bg-white even:bg-slate-50 border-b align-top"
                                        data-notif="{{ $notification->notification_number }}"
                                        data-lpj-t1="{{ optional($lpj)->lpj_document_path_termin1 ? e(Storage::url($lpj->lpj_document_path_termin1)) : '' }}"
                                        data-ppl-t1="{{ optional($lpj)->ppl_document_path_termin1 ? e(Storage::url($lpj->ppl_document_path_termin1)) : '' }}"
                                        data-lpj-t2="{{ optional($lpj)->lpj_document_path_termin2 ? e(Storage::url($lpj->lpj_document_path_termin2)) : '' }}"
                                        data-ppl-t2="{{ optional($lpj)->ppl_document_path_termin2 ? e(Storage::url($lpj->ppl_document_path_termin2)) : '' }}"
                                        data-lpj-num-t1="{{ e(optional($lpj)->lpj_number_termin1 ?? '') }}"
                                        data-ppl-num-t1="{{ e(optional($lpj)->ppl_number_termin1 ?? '') }}"
                                        data-lpj-num-t2="{{ e(optional($lpj)->lpj_number_termin2 ?? '') }}"
                                        data-ppl-num-t2="{{ e(optional($lpj)->ppl_number_termin2 ?? '') }}">
                                        <!-- NOMOR ORDER -->
                                        <td class="px-3 py-2 align-top">
                                            <div class="text-sm font-semibold text-slate-800">{{ $notification->notification_number }}</div>

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
                                        <td class="px-3 py-2 align-top text-sm text-slate-700">
                                            @if(optional($lpj)->update_date)
                                                {{ \Carbon\Carbon::parse($lpj->update_date)->format('Y-m-d H:i') }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <!-- NOMOR LPJ / PPL -->
                                        <td class="px-3 py-2 align-top w-72">
                                            <form id="form-{{ $notification->notification_number }}" action="{{ route('lpj.update', $notification->notification_number) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="flex flex-col gap-2 text-[11px]">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <label class="text-[11px] text-slate-500">Termin</label>
                                                        <select id="termin_select_{{ $notification->notification_number }}" data-notif="{{ $notification->notification_number }}" class="px-2 py-1 text-[11px] border border-slate-300 rounded bg-white"
                                                                onchange="onTerminChange('{{ $notification->notification_number }}')">
                                                            <option value="1" {{ ( ($lpj && ($lpj->lpj_number_termin1 || $lpj->lpj_document_path_termin1)) || ! $lpj ) ? 'selected' : '' }}>Termin 1</option>
                                                            <option value="2" {{ ($lpj && ($lpj->lpj_number_termin2 || $lpj->lpj_document_path_termin2)) ? 'selected' : '' }}>Termin 2</option>
                                                        </select>
                                                    </div>

                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div class="flex-1">
                                                            <label class="text-[11px] text-slate-500">Nomor LPJ</label>
                                                            <input id="lpj_input_{{ $notification->notification_number }}" type="text" name="lpj_number"
                                                                value="{{ old('lpj_number') ?? ($lpj->lpj_number_termin1 ?? '') }}"
                                                                placeholder="Nomor LPJ"
                                                                class="w-full px-2 py-1 border border-slate-200 rounded text-[11px]" required>
                                                        </div>

                                                        <div class="flex-1">
                                                            <label class="text-[11px] text-slate-500">Nomor PPL</label>
                                                            <input id="ppl_input_{{ $notification->notification_number }}" type="text" name="ppl_number"
                                                                value="{{ old('ppl_number') ?? ($lpj->ppl_number_termin1 ?? '') }}"
                                                                placeholder="Nomor PPL"
                                                                class="w-full px-2 py-1 border border-slate-200 rounded text-[11px]">
                                                        </div>
                                                    </div>

                                                    <p class="text-[10px] text-slate-400">Pilih termin untuk menampilkan data terkait termin.</p>

                                                    <input type="hidden" id="selected_termin_input_{{ $notification->notification_number }}" name="selected_termin" value="1" />
                                                </div>
                                            </form>
                                        </td>

                                        <!-- DOKUMEN (STACKED VERTICAL + SINGLE UPLOAD PER TERMIN) -->
                                        <td class="px-3 py-2 align-top w-96">
                                            <div class="flex flex-col gap-3">
                                                {{-- LPJ stacked --}}
                                            <div class="flex flex-col gap-2">
                                                <div class="flex items-center justify-between">
                                                    <div class="text-[11px] font-medium text-slate-600">LPJ</div>

                                                    <div class="flex items-center gap-2 flex-wrap justify-end">
                                                        {{-- small upload controls (only one shown by JS) --}}
                                                        <div class="flex items-center gap-1">
                                                            <label id="lpj_upload_label_t1_{{ $notification->notification_number }}" for="lpj_document_{{ $notification->notification_number }}_t1"
                                                                   class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white border border-emerald-700 rounded text-[10px] cursor-pointer hidden">
                                                                <i class="fas fa-upload text-[10px]"></i>
                                                                <span class="text-[10px]" id="lpj_upload_text_t1_{{ $notification->notification_number }}">Upload LPJ T1</span>
                                                            </label>
                                                            <input id="lpj_document_{{ $notification->notification_number }}_t1" type="file" name="lpj_document" form="form-{{ $notification->notification_number }}" accept=".pdf,.doc,.docx" class="hidden" onchange="showFileName(this,'lpj_filename_{{ $notification->notification_number }}')">

                                                            <label id="lpj_upload_label_t2_{{ $notification->notification_number }}" for="lpj_document_{{ $notification->notification_number }}_t2"
                                                                   class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white border border-emerald-700 rounded text-[10px] cursor-pointer hidden">
                                                                <i class="fas fa-upload text-[10px]"></i>
                                                                <span class="text-[10px]" id="lpj_upload_text_t2_{{ $notification->notification_number }}">Upload LPJ T2</span>
                                                            </label>
                                                            <input id="lpj_document_{{ $notification->notification_number }}_t2" type="file" name="lpj_document_termin2" form="form-{{ $notification->notification_number }}" accept=".pdf,.doc,.docx" class="hidden" onchange="showFileName(this,'lpj_filename_{{ $notification->notification_number }}')">
                                                        </div>

                                                        <!-- PDF badge (compact) -->
                                                        <a id="lpj_pdf_badge_{{ $notification->notification_number }}" href="#" target="_blank"
                                                           class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-600 text-white rounded text-[10px] max-w-[170px] hidden">
                                                            <i class="fas fa-file-pdf text-[11px]"></i>
                                                            <span id="lpj_pdf_name_{{ $notification->notification_number }}" class="truncate max-w-[120px] block">LPJ.pdf</span>
                                                            <span id="lpj_pdf_tag_{{ $notification->notification_number }}" class="ml-1 bg-white text-red-600 px-1 rounded text-[10px] font-semibold">T1</span>
                                                        </a>
                                                    </div>
                                                </div>

                                                <div class="text-[10px] text-slate-500"><span id="lpj_filename_{{ $notification->notification_number }}"></span></div>
                                            </div>

                                                {{-- PPL stacked --}}
                                                <div class="flex flex-col gap-2">
                                                    <div class="flex items-center justify-between">
                                                        <div class="text-[11px] font-medium text-slate-600">PPL</div>

                                                        <div class="flex items-center gap-2 flex-wrap justify-end">
                                                            <div class="flex items-center gap-1">
                                                                <label id="ppl_upload_label_t1_{{ $notification->notification_number }}" for="ppl_document_{{ $notification->notification_number }}_t1"
                                                                       class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white border border-emerald-700 rounded text-[10px] cursor-pointer hidden">
                                                                    <i class="fas fa-upload text-[10px]"></i>
                                                                    <span class="text-[10px]" id="ppl_upload_text_t1_{{ $notification->notification_number }}">Upload PPL T1</span>
                                                                </label>
                                                                <input id="ppl_document_{{ $notification->notification_number }}_t1" type="file" name="ppl_document" form="form-{{ $notification->notification_number }}" accept=".pdf,.doc,.docx" class="hidden" onchange="showFileName(this,'ppl_filename_{{ $notification->notification_number }}')">

                                                                <label id="ppl_upload_label_t2_{{ $notification->notification_number }}" for="ppl_document_{{ $notification->notification_number }}_t2"
                                                                       class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white border border-emerald-700 rounded text-[10px] cursor-pointer hidden">
                                                                    <i class="fas fa-upload text-[10px]"></i>
                                                                    <span class="text-[10px]" id="ppl_upload_text_t2_{{ $notification->notification_number }}">Upload PPL T2</span>
                                                                </label>
                                                                <input id="ppl_document_{{ $notification->notification_number }}_t2" type="file" name="ppl_document_termin2" form="form-{{ $notification->notification_number }}" accept=".pdf,.doc,.docx" class="hidden" onchange="showFileName(this,'ppl_filename_{{ $notification->notification_number }}')">
                                                            </div>

                                                            <a id="ppl_pdf_badge_{{ $notification->notification_number }}" href="#" target="_blank"
                                                               class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-600 text-white rounded text-[10px] max-w-[170px] hidden">
                                                                <i class="fas fa-file-pdf text-[11px]"></i>
                                                                <span id="ppl_pdf_name_{{ $notification->notification_number }}" class="truncate max-w-[120px] block">PPL.pdf</span>
                                                                <span id="ppl_pdf_tag_{{ $notification->notification_number }}" class="ml-1 bg-white text-red-600 px-1 rounded text-[10px] font-semibold">T1</span>
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <div class="text-[10px] text-slate-500"><span id="ppl_filename_{{ $notification->notification_number }}"></span></div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- PEMBAYARAN -->
                                        <td class="px-3 py-2 align-top">
                                            <div class="text-sm text-slate-600">
                                                <div class="text-[10px] text-slate-500">Total LHPP</div>
                                                <div class="font-semibold text-slate-800">Rp {{ number_format($total_biaya,0,',','.') }}</div>
                                            </div>

                                            <div class="mt-2 text-[11px] space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-20 text-[10px] text-slate-500">Termin 1</div>
                                                    <select id="pay_termin1_{{ $notification->notification_number }}" name="termin1" form="form-{{ $notification->notification_number }}" class="text-[10px] px-2 py-1 border border-slate-300 rounded">
                                                        <option value="belum" {{ ($lpj->termin1 ?? 'belum') === 'belum' ? 'selected' : '' }}>Belum</option>
                                                        <option value="sudah" {{ ($lpj->termin1 ?? '') === 'sudah' ? 'selected' : '' }}>Sudah</option>
                                                    </select>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <div class="w-20 text-[10px] text-slate-500">Termin 2</div>
                                                    <select id="pay_termin2_{{ $notification->notification_number }}" name="termin2" form="form-{{ $notification->notification_number }}" class="text-[10px] px-2 py-1 border border-slate-300 rounded">
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
                                        <td class="px-3 py-2 text-center align-top">
                                            <div class="flex flex-col items-center gap-2">
                                                <button type="button" onclick="submitLpjForm('{{ $notification->notification_number }}', this)" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs flex items-center gap-2">
                                                    <i class="fas fa-save"></i> Simpan
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
                            @if($rendered === 0)
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-gray-500">Tidak ada data LPJ</td>
                                </tr>
                            @endif
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
    function applyTerminState(row, termin) {
        const notif = row.getAttribute('data-notif');
        if (!notif) return;

        const selInput = document.getElementById('selected_termin_input_' + notif);
        const lpjInput = document.getElementById('lpj_input_' + notif);
        const pplInput = document.getElementById('ppl_input_' + notif);

        const lpjBadge = document.getElementById('lpj_pdf_badge_' + notif);
        const lpjName  = document.getElementById('lpj_pdf_name_' + notif);
        const lpjTag   = document.getElementById('lpj_pdf_tag_' + notif);

        const pplBadge = document.getElementById('ppl_pdf_badge_' + notif);
        const pplName  = document.getElementById('ppl_pdf_name_' + notif);
        const pplTag   = document.getElementById('ppl_pdf_tag_' + notif);

        const lpjUploadT1 = document.getElementById('lpj_upload_label_t1_' + notif);
        const lpjUploadT2 = document.getElementById('lpj_upload_label_t2_' + notif);
        const pplUploadT1 = document.getElementById('ppl_upload_label_t1_' + notif);
        const pplUploadT2 = document.getElementById('ppl_upload_label_t2_' + notif);

        const pay1 = document.getElementById('pay_termin1_' + notif);
        const pay2 = document.getElementById('pay_termin2_' + notif);

        const lpj_t1 = row.getAttribute('data-lpj-t1') || '';
        const ppl_t1 = row.getAttribute('data-ppl-t1') || '';
        const lpj_t2 = row.getAttribute('data-lpj-t2') || '';
        const ppl_t2 = row.getAttribute('data-ppl-t2') || '';
        const lpj_num_t1 = row.getAttribute('data-lpj-num-t1') || '';
        const ppl_num_t1 = row.getAttribute('data-ppl-num-t1') || '';
        const lpj_num_t2 = row.getAttribute('data-lpj-num-t2') || '';
        const ppl_num_t2 = row.getAttribute('data-ppl-num-t2') || '';

        if (selInput) selInput.value = termin;

        if (termin === '1') {
            if (lpjInput) lpjInput.value = lpj_num_t1;
            if (pplInput) pplInput.value = ppl_num_t1;

            if (lpj_t1) {
                lpjBadge.href = lpj_t1;
                lpjName.textContent = lpj_t1.split('/').pop();
                lpjTag.textContent = 'T1';
                lpjBadge.classList.remove('hidden');
            } else if (lpjBadge) {
                lpjBadge.classList.add('hidden');
            }

            if (ppl_t1) {
                pplBadge.href = ppl_t1;
                pplName.textContent = ppl_t1.split('/').pop();
                pplTag.textContent = 'T1';
                pplBadge.classList.remove('hidden');
            } else if (pplBadge) {
                pplBadge.classList.add('hidden');
            }

            if (pay1) pay1.classList.add('ring-2','ring-gray-300');
            if (pay2) pay2.classList.remove('ring-2','ring-gray-300');
            if (lpjUploadT1) lpjUploadT1.classList.remove('hidden');
            if (lpjUploadT2) lpjUploadT2.classList.add('hidden');
            if (pplUploadT1) pplUploadT1.classList.remove('hidden');
            if (pplUploadT2) pplUploadT2.classList.add('hidden');
        } else {
            if (lpjInput) lpjInput.value = lpj_num_t2;
            if (pplInput) pplInput.value = ppl_num_t2;

            if (lpj_t2) {
                lpjBadge.href = lpj_t2;
                lpjName.textContent = lpj_t2.split('/').pop();
                lpjTag.textContent = 'T2';
                lpjBadge.classList.remove('hidden');
            } else if (lpjBadge) {
                lpjBadge.classList.add('hidden');
            }

            if (ppl_t2) {
                pplBadge.href = ppl_t2;
                pplName.textContent = ppl_t2.split('/').pop();
                pplTag.textContent = 'T2';
                pplBadge.classList.remove('hidden');
            } else if (pplBadge) {
                pplBadge.classList.add('hidden');
            }

            if (pay2) pay2.classList.add('ring-2','ring-gray-300');
            if (pay1) pay1.classList.remove('ring-2','ring-gray-300');
            if (lpjUploadT1) lpjUploadT1.classList.add('hidden');
            if (lpjUploadT2) lpjUploadT2.classList.remove('hidden');
            if (pplUploadT1) pplUploadT1.classList.add('hidden');
            if (pplUploadT2) pplUploadT2.classList.remove('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('tr[data-notif]').forEach((row) => {
            const lpj_num_t2 = row.getAttribute('data-lpj-num-t2') || '';
            const ppl_num_t2 = row.getAttribute('data-ppl-num-t2') || '';
            const lpj_t2 = row.getAttribute('data-lpj-t2') || '';
            const ppl_t2 = row.getAttribute('data-ppl-t2') || '';
            const prefer2 = (lpj_num_t2.length || ppl_num_t2.length || lpj_t2.length || ppl_t2.length);
            const initial = prefer2 ? '2' : '1';

            const notif = row.getAttribute('data-notif');
            const sel = document.getElementById('termin_select_' + notif);
            if (sel) sel.value = initial;
            applyTerminState(row, initial);
        });
    });

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
        const row = sel.closest('tr[data-notif]');
        if (!row) return;
        applyTerminState(row, sel.value);
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
