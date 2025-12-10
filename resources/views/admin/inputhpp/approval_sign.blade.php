<x-approval>
  @php
    // Pilih URL PDF berdasar source_form
    $pdfUrl = match($hpp->source_form ?? 'createhpp1') {
        'createhpp1' => route('approval.hpp.download_hpp1', $hpp->notification_number),
        'createhpp2' => route('approval.hpp.download_hpp2', $hpp->notification_number),
        'createhpp3' => route('approval.hpp.download_hpp3', $hpp->notification_number),
        'createhpp4' => route('approval.hpp.download_hpp4', $hpp->notification_number),
        default      => route('approval.hpp.download_hpp1', $hpp->notification_number),
    };

    // Default target catatan berdasar sign_type
    $defaultNoteTarget = in_array(($sign_type ?? ''), ['manager','sm','gm','dir'])
        ? 'controlling'
        : 'requesting';

    // URL dokumen abnormalitas kalau ada
    $abnormalUrl = !empty($dokumenAbnormalitas ?? null)
        ? route('dokumen_orders.view', [$hpp->notification_number, 'abnormalitas'])
        : null;
  @endphp

  <div class="max-w-6xl mx-auto py-5 px-3 md:px-0 space-y-5">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
      <div class="space-y-0.5">
        <h2 class="text-base md:text-xl font-semibold text-slate-800">
          Approval HPP – {{ $signTypeLabel }}
        </h2>
        <p class="text-[11px] md:text-xs text-slate-500">
          Tinjau pratinjau dokumen, lalu tanda tangani di bawah.
        </p>
      </div>
      <span class="inline-flex w-max items-center rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-medium text-slate-600">
        {{ strtoupper($hpp->source_form ?? 'createhpp1') }}
      </span>
    </div>

    <!-- Info ringkas + Toolbar PDF -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white border border-slate-200 rounded-xl p-4 md:col-span-2">
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-[12px] md:text-sm">
          <div>
            <dt class="text-slate-500">No. Notifikasi</dt>
            <dd class="font-semibold text-slate-800 break-all">
              {{ $hpp->notification_number }}
            </dd>
          </div>
          <div>
            <dt class="text-slate-500">Nilai Total</dt>
            <dd class="font-semibold text-slate-800">
              Rp {{ number_format((float)$hpp->total_amount, 2, ',', '.') }}
            </dd>
          </div>
          <div class="sm:col-span-2">
            <dt class="text-slate-500">Deskripsi</dt>
            <dd class="font-medium text-slate-800">
              {{ $hpp->description }}
            </dd>
          </div>
        </dl>
      </div>

      <!-- Toolbar PDF (tanpa fullscreen) -->
      <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col gap-2">
        <div class="text-sm md:text-[15px] font-semibold text-slate-800">Dokumen HPP</div>
        <div class="flex flex-wrap gap-2">
          <a href="{{ $pdfUrl }}" target="_blank"
             class="px-3 py-1.5 text-xs rounded-lg bg-slate-900 text-white hover:bg-slate-800">
            Buka di Tab Baru
          </a>
          <a href="{{ $pdfUrl }}" download
             class="px-3 py-1.5 text-xs rounded-lg bg-slate-100 text-slate-800 hover:bg-slate-200">
            Download PDF
          </a>
        </div>
        <div class="text-[11px] text-slate-500">
          Tip: gunakan zoom di viewer (Ctrl +/–) bila perlu.
        </div>
      </div>
    </div>

    <!-- PDF Preview (HPP) + Dokumen Abnormalitas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">

      <!-- HPP PDF utama (lebih besar, 2/3 lebar) -->
      <div class="md:col-span-2 bg-white border border-slate-200 rounded-xl overflow-hidden">
        <div class="h-10 md:h-12 flex items-center justify-between px-3 md:px-4 border-b">
          <div class="text-sm md:text-[15px] font-semibold text-slate-800">Pratinjau HPP</div>
          <div class="text-[10px] md:text-[11px] text-slate-500">
            Jika PDF tidak tampil, klik “Buka di Tab Baru”.
          </div>
        </div>

        <div id="pdfWrap" class="relative">
          <!-- skeleton loading -->
          <div id="pdfSkeleton" class="absolute inset-0 animate-pulse bg-slate-50">
            <div class="h-full w-full grid place-items-center">
              <div class="text-xs text-slate-400">Memuat pratinjau…</div>
            </div>
          </div>

          <!-- iframe HPP (cukup besar) -->
          <iframe
            id="pdfFrame"
            src="{{ $pdfUrl }}#zoom=page-width"
            class="w-full h-[60vh] md:h-[70vh]"
            loading="eager"
            onload="document.getElementById('pdfSkeleton')?.remove();"
          ></iframe>
        </div>
      </div>

      <!-- Panel: Dokumen terlampir (Abnormalitas, 1/3 lebar) -->
      <div class="bg-white border border-slate-200 rounded-xl p-4">
        <div class="flex items-center justify-between gap-2">
          <div>
            <div class="text-sm font-semibold text-slate-800">Dokumen Terlampir</div>
            <p class="text-[11px] text-slate-500">
              Dokumen pendukung order untuk notifikasi ini.
            </p>
          </div>
          <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
            ABNORMALITAS
          </span>
        </div>

        @if($abnormalUrl)
          <div class="mt-3 border rounded-lg overflow-hidden bg-slate-50">
            <div class="px-3 py-2 border-b flex items-center justify-between">
              <span class="text-[11px] font-medium text-slate-700">
                Abnormalitas / Dokumen Order
              </span>
              <span class="text-[10px] text-slate-400">
                {{ $dokumenAbnormalitas->keterangan ?? 'Abnormalitas' }}
              </span>
            </div>

            {{-- preview kecil --}}
            <iframe
              src="{{ $abnormalUrl }}#zoom=page-width"
              class="w-full h-48 md:h-60"
              loading="lazy"
            ></iframe>
          </div>

          <div class="mt-3 flex flex-wrap gap-2">
            <a href="{{ $abnormalUrl }}" target="_blank"
               class="flex-1 px-3 py-1.5 text-xs rounded-lg bg-slate-900 text-white text-center hover:bg-slate-800">
              Buka Dokumen
            </a>
            <a href="{{ $abnormalUrl }}" download
               class="flex-1 px-3 py-1.5 text-xs rounded-lg bg-slate-100 text-slate-800 text-center hover:bg-slate-200">
              Download
            </a>
          </div>
        @else
          <p class="mt-3 text-[11px] text-slate-500">
            Belum ada dokumen abnormalitas yang terupload untuk notifikasi ini.
          </p>
        @endif
      </div>
    </div>

    <!-- Form TTD -->
    <form method="POST" action="{{ route('approval.hpp.do', $token) }}" onsubmit="return submitSignature()">
      @csrf

      <!-- Hidden target untuk catatan -->
      <input type="hidden" name="note_target" id="note_target" value="{{ $defaultNoteTarget }}">

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Panel Signature -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl p-4">
          <div class="mb-2 text-sm font-semibold text-slate-800">Tanda Tangan</div>

          <div class="border rounded-lg bg-slate-50 overflow-hidden">
            <div class="p-3">
              <canvas id="pad" class="block w-full" style="height:240px;"></canvas>
            </div>
            <div class="px-3 pb-3 flex flex-wrap gap-2">
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
                        class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 text-white hover:bg-emerald-500 ml-auto">
                  Gunakan TTD Terakhir
                </button>
              @endif
            </div>
          </div>
          <input type="hidden" name="signature_base64" id="signature_base64">
          <input type="hidden" name="use_old_signature" id="use_old_signature" value="0">

          @if(!empty($hasOldSignature))
            <p class="mt-1 text-[11px] text-slate-500">
              Anda memiliki tanda tangan yang sudah tersimpan. Klik <span class="font-semibold">"Gunakan TTD Terakhir"</span> bila tidak ingin menggambar ulang.
            </p>
          @endif
        </div>

        <!-- Panel Catatan & Aksi -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-800">Catatan (Optional)</label>
            <p class="text-[11px] text-slate-500 mb-2">
              Akan disimpan ke
              <span class="font-medium" id="noteTargetLabel">
                {{ $defaultNoteTarget === 'controlling' ? 'Controlling Notes' : 'Requesting Notes' }}
              </span>.
            </p>
            <textarea name="note" rows="5"
                      class="w-full border rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200"
                      placeholder="Tulis catatan singkat approval (opsional)…"></textarea>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-800">Alasan Penolakan</label>
            <p class="text-[11px] text-slate-500 mb-2">
              Wajib diisi bila menekan <b>Tolak</b>.
            </p>
            <textarea name="reason" rows="4"
                      class="w-full border rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-200"
                      placeholder="Tulis alasan penolakan (jika menolak)…"></textarea>
          </div>

          <div class="flex flex-col sm:flex-row gap-2">
            <button name="action" value="approve"
                    class="flex-1 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">
              Setujui
            </button>
            <button name="action" value="reject"
                    class="flex-1 px-4 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-500"
                    onclick="return confirm('Tolak dokumen ini?')">
              Tolak
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>

  {{-- ======== Signature Canvas (tanpa lib eksternal) ======== --}}
  <script>
    // Auto-set label target notes berdasar sign_type (fallback JS)
    (function() {
      const signType = @json($sign_type ?? '');
      const noteTargetEl = document.getElementById('note_target');
      const lbl = document.getElementById('noteTargetLabel');
      if (signType) {
        const isCtrl = ['manager','sm','gm','dir'].includes(signType);
        const val = isCtrl ? 'controlling' : 'requesting';
        if (noteTargetEl) noteTargetEl.value = val;
        if (lbl) lbl.textContent = isCtrl ? 'Controlling Notes' : 'Requesting Notes';
      }
    })();

    // ======== SignaturePad minimal dengan Undo + HiDPI ========
    const canvas = document.getElementById('pad');
    const ctx = canvas.getContext('2d');

    let drawing = false;
    const strokes = [];
    let current = [];

    const VIEW_WIDTH   = 2;
    const EXPORT_WIDTH = 4.2;
    const EXPORT_SCALE = 2.0;
    const EXPORT_FILTER = 'contrast(300%) brightness(0.45) saturate(120%)';

    function resizeCanvas() {
      const dpr  = window.devicePixelRatio || 1;
      const cssW = canvas.clientWidth || 800;
      const cssH = parseInt(getComputedStyle(canvas).height, 10) || 240;
      canvas.width  = Math.floor(cssW * dpr);
      canvas.height = Math.floor(cssH * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      ctx.lineWidth = VIEW_WIDTH;
      ctx.lineCap   = 'round';
      ctx.lineJoin  = 'round';
      redraw();
    }

    function redraw() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      for (const path of strokes) {
        ctx.beginPath();
        for (let i = 0; i < path.length; i++) {
          const p = path[i];
          if (i === 0) ctx.moveTo(p.x, p.y);
          else ctx.lineTo(p.x, p.y);
        }
        ctx.stroke();
      }
    }

    function getPos(e) {
      const r = canvas.getBoundingClientRect();
      return { x: e.clientX - r.left, y: e.clientY - r.top };
    }
    function getTouchPos(e) {
      const t = e.touches[0];
      const r = canvas.getBoundingClientRect();
      return { x: t.clientX - r.left, y: t.clientY - r.top };
    }

    function start(p) {
      const useOld = document.getElementById('use_old_signature');
      if (useOld) useOld.value = '0'; // mulai gambar → batal pakai TTD lama
      drawing = true;
      current = [p];
    }
    function move(p) {
      if (!drawing) return;
      const last = current[current.length - 1];
      ctx.beginPath();
      ctx.moveTo(last.x, last.y);
      ctx.lineTo(p.x, p.y);
      ctx.stroke();
      current.push(p);
    }
    function end() {
      if (!drawing) return;
      drawing = false;
      if (current.length > 1) strokes.push(current);
      current = [];
    }

    canvas.addEventListener('mousedown', e => start(getPos(e)));
    canvas.addEventListener('mousemove', e => move(getPos(e)));
    window.addEventListener('mouseup', end);

    canvas.addEventListener('touchstart', e => { e.preventDefault(); start(getTouchPos(e)); }, { passive: false });
    canvas.addEventListener('touchmove',  e => { e.preventDefault(); move(getTouchPos(e)); }, { passive: false });
    window.addEventListener('touchend', end);

    document.getElementById('btnClear')?.addEventListener('click', () => {
      strokes.length = 0;
      redraw();
      const useOld = document.getElementById('use_old_signature');
      if (useOld) useOld.value = '0';
    });

    document.getElementById('btnUndo')?.addEventListener('click', () => {
      strokes.pop();
      redraw();
    });

    // Tombol gunakan TTD terakhir
    document.getElementById('btnUseOldSignature')?.addEventListener('click', () => {
      if (!confirm('Gunakan tanda tangan terakhir yang tersimpan?')) return;

      const flag = document.getElementById('use_old_signature');
      if (flag) flag.value = '1';

      // optional: bersihkan canvas agar jelas tidak sedang dipakai
      strokes.length = 0;
      redraw();

      alert('Tanda tangan lama akan digunakan saat Anda menekan Setujui / Tolak.');
    });

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function isBlank() { return strokes.length === 0; }

    function exportPNG() {
      const w = canvas.clientWidth || 800;
      const h = parseInt(getComputedStyle(canvas).height, 10) || 240;

      const big  = document.createElement('canvas');
      big.width  = Math.floor(w * EXPORT_SCALE);
      big.height = Math.floor(h * EXPORT_SCALE);
      const bctx = big.getContext('2d');

      bctx.lineWidth = EXPORT_WIDTH * EXPORT_SCALE;
      bctx.lineCap   = 'round';
      bctx.lineJoin  = 'round';
      bctx.filter    = EXPORT_FILTER;

      for (const path of strokes) {
        bctx.beginPath();
        for (let i = 0; i < path.length; i++) {
          const p = path[i];
          if (i === 0) bctx.moveTo(p.x * EXPORT_SCALE, p.y * EXPORT_SCALE);
          else         bctx.lineTo(p.x * EXPORT_SCALE, p.y * EXPORT_SCALE);
        }
        bctx.stroke();
      }

      const out  = document.createElement('canvas');
      out.width  = w;
      out.height = h;
      const octx = out.getContext('2d');
      octx.imageSmoothingEnabled = true;
      octx.imageSmoothingQuality = 'high';
      octx.drawImage(big, 0, 0, w, h);

      return out.toDataURL('image/png');
    }

    window.submitSignature = function () {
      const useOld = document.getElementById('use_old_signature');
      if (useOld && useOld.value === '1') {
        // Pakai tanda tangan lama → tidak perlu cek canvas / base64
        return true;
      }

      if (isBlank()) {
        alert('Silakan gambar tanda tangan terlebih dahulu.');
        return false;
      }
      document.getElementById('signature_base64').value = exportPNG();
      return true;
    };
  </script>
</x-approval>
