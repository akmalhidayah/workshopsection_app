<x-approval>
@php
    $pdfUrl = route('spk.show', $spk->notification_number);

    $abnormalUrl = !empty($dokumenAbnormalitas ?? null)
        ? route('dokumen_orders.view', [$spk->notification_number, 'abnormalitas'])
        : null;
@endphp

<div class="max-w-6xl mx-auto py-5 px-3 md:px-0 space-y-5">

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
        <div>
            <h2 class="text-base md:text-lg font-semibold text-slate-800">
                Approval SPK – {{ $signTypeLabel }}
            </h2>
            <p class="text-xs text-slate-500">
                Tinjau dokumen SPK kemudian lakukan tanda tangan.
            </p>
        </div>
        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
            SPK
        </span>
    </div>

    {{-- ================= INFO + TOOLBAR ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- INFO SPK --}}
        <div class="md:col-span-2 bg-white border border-slate-200 rounded-xl p-4">
            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">Nomor SPK</dt>
                    <dd class="font-semibold text-slate-800 break-all">
                        {{ $spk->nomor_spk }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tanggal SPK</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $spk->tanggal_spk ? \Carbon\Carbon::parse($spk->tanggal_spk)->format('d-m-Y') : '-' }}
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">Perihal</dt>
                    <dd class="font-medium text-slate-800">
                        {{ $spk->perihal }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- TOOLBAR --}}
        <div class="bg-white border border-slate-200 rounded-xl p-4 space-y-2">
            <div class="text-sm font-semibold text-slate-800">Dokumen SPK</div>
            <div class="flex gap-2">
                <a href="{{ $pdfUrl }}" target="_blank"
                   class="flex-1 px-3 py-1.5 text-xs rounded-lg bg-slate-900 text-white text-center hover:bg-slate-800">
                    Buka
                </a>
                <a href="{{ $pdfUrl }}" download
                   class="flex-1 px-3 py-1.5 text-xs rounded-lg bg-slate-100 text-slate-800 text-center hover:bg-slate-200">
                    Download
                </a>
            </div>
        </div>
    </div>

    {{-- ================= PREVIEW ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- PREVIEW SPK --}}
        <div class="md:col-span-2 bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="h-10 flex items-center px-4 border-b text-sm font-semibold text-slate-800">
                Pratinjau SPK
            </div>
            <iframe
                src="{{ $pdfUrl }}#zoom=page-width"
                class="w-full h-[65vh]"
                loading="eager">
            </iframe>
        </div>

        {{-- PREVIEW ABNORMALITAS --}}
        <div class="bg-white border border-slate-200 rounded-xl p-4 space-y-2">
            <div class="text-sm font-semibold text-slate-800">Dokumen Abnormalitas</div>

            @if($abnormalUrl)
                <iframe
                    src="{{ $abnormalUrl }}#zoom=page-width"
                    class="w-full h-56 border rounded-lg"
                    loading="lazy">
                </iframe>

                <div class="flex gap-2">
                    <a href="{{ $abnormalUrl }}" target="_blank"
                       class="flex-1 px-3 py-1.5 text-xs rounded-lg bg-slate-900 text-white text-center">
                        Buka
                    </a>
                    <a href="{{ $abnormalUrl }}" download
                       class="flex-1 px-3 py-1.5 text-xs rounded-lg bg-slate-100 text-slate-800 text-center">
                        Download
                    </a>
                </div>
            @else
                <p class="text-xs text-slate-500">
                    Tidak ada dokumen abnormalitas.
                </p>
            @endif
        </div>
    </div>

    {{-- ================= FORM SIGNATURE ================= --}}
    <form method="POST"
          action="{{ route('approval.spk.do', $token) }}"
          onsubmit="return submitSignature()">
        @csrf

        <input type="hidden" name="signature_base64" id="signature_base64">
        <input type="hidden" name="use_old_signature" id="use_old_signature" value="0">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- CANVAS --}}
            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl p-4">
                <div class="text-sm font-semibold text-slate-800 mb-2">
                    Tanda Tangan
                </div>

                <div class="border rounded-lg bg-slate-50 overflow-hidden">
                    <canvas id="pad" class="block w-full" style="height:240px;"></canvas>
                </div>

                <div class="flex flex-wrap gap-2 mt-2">
                    <button type="button" id="btnUndo"
                            class="px-3 py-1.5 text-xs rounded-lg bg-slate-200 hover:bg-slate-300">
                        Undo
                    </button>
                    <button type="button" id="btnClear"
                            class="px-3 py-1.5 text-xs rounded-lg bg-slate-200 hover:bg-slate-300">
                        Clear
                    </button>

                    @if(!empty($hasOldSignature))
                        <button type="button" id="btnUseOldSignature"
                                class="ml-auto px-3 py-1.5 text-xs rounded-lg bg-emerald-600 text-white hover:bg-emerald-500">
                            Gunakan TTD Terakhir
                        </button>
                    @endif
                </div>
            </div>

            {{-- ACTION --}}
            <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col gap-3">
                <button name="action" value="approve"
                        class="w-full px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">
                    Setujui
                </button>
            </div>
        </div>
    </form>
</div>

{{-- ================= CANVAS SCRIPT (INLINE – STABIL) ================= --}}
<script>
    const canvas = document.getElementById('pad');
    const ctx = canvas.getContext('2d');

    let drawing = false;
    const strokes = [];
    let current = [];

    function resizeCanvas() {
        const dpr = window.devicePixelRatio || 1;
        const w = canvas.clientWidth;
        const h = 240;
        canvas.width = w * dpr;
        canvas.height = h * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        redraw();
    }

    function redraw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (const path of strokes) {
            ctx.beginPath();
            path.forEach((p, i) => i ? ctx.lineTo(p.x, p.y) : ctx.moveTo(p.x, p.y));
            ctx.stroke();
        }
    }

    function pos(e) {
        const r = canvas.getBoundingClientRect();
        return { x: e.clientX - r.left, y: e.clientY - r.top };
    }

    canvas.onmousedown = e => { drawing = true; current = [pos(e)]; };
    canvas.onmousemove = e => {
        if (!drawing) return;
        const p = pos(e);
        const last = current[current.length - 1];
        ctx.beginPath();
        ctx.moveTo(last.x, last.y);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        current.push(p);
    };
    window.onmouseup = () => {
        if (drawing && current.length > 1) strokes.push(current);
        drawing = false;
    };

    document.getElementById('btnClear').onclick = () => { strokes.length = 0; redraw(); };
    document.getElementById('btnUndo').onclick  = () => { strokes.pop(); redraw(); };

    function isBlank() { return strokes.length === 0; }

    function exportPNG() {
        return canvas.toDataURL('image/png');
    }

    window.submitSignature = function () {
        if (isBlank()) {
            alert('Silakan gambar tanda tangan terlebih dahulu.');
            return false;
        }
        document.getElementById('signature_base64').value = exportPNG();
        return true;
    };

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
</script>
</x-approval>
