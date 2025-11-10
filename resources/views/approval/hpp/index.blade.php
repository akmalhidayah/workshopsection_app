<x-admin-layout>
  @php
    $btn = 'inline-flex items-center gap-1 px-3 py-1.5 text-[11px] rounded-md';
    // Normalisasi magic payload (biar aman)
    $magicNotif = data_get($magic, 'notification_number');
    $magicType  = data_get($magic, 'sign_type');
    $magicToken = data_get($magic, 'token');
  @endphp

  <div class="py-6 max-w-[1100px] mx-auto">
    <h2 class="font-semibold text-[12px] text-slate-800 mb-3">Approval HPP</h2>

    {{-- Flash --}}
    @if(session('success'))
      <div class="mb-3 p-2 rounded bg-emerald-50 text-emerald-700 text-[11px]">
        {{ session('success') }}
        @if(session('hpp.next_url'))
          <button type="button"
                  class="{{ $btn }} ml-2 border border-emerald-600 text-emerald-700 copy-next-link"
                  data-link="{{ session('hpp.next_url') }}">
            Salin link tahap berikutnya
          </button>
        @endif
      </div>
    @endif
    @if(session('error'))
      <div class="mb-3 p-2 rounded bg-rose-50 text-rose-700 text-[11px]">{{ session('error') }}</div>
    @endif

    {{-- Panel TTD otomatis dari magic-link (hanya tampil kalau 3 kunci terisi) --}}
    @if($magicNotif && $magicType && $magicToken)
      <div class="mb-5 p-4 rounded-lg border border-indigo-200 bg-indigo-50">
        <div class="text-[11px] text-indigo-800 mb-2">
          <div><b>Nomor Order:</b> {{ $magicNotif }}</div>
          <div><b>Peran:</b> {{ str_replace('_',' ', $magicType) }}</div>
          <div><b>Token:</b> <code class="text-[10px]">{{ $magicToken }}</code></div>
        </div>

        <form id="signForm"
              method="POST"
              action="{{ route('approval.hpp.saveSignature', [$magicType, $magicNotif]) }}"
              class="bg-white p-3 rounded border border-indigo-200">
          @csrf
          <input type="hidden" name="token" value="{{ $magicToken }}">
          <input type="hidden" name="signature" id="signatureInput">

          <div class="mb-2 text-[11px] text-slate-700">Tanda Tangan</div>
          <div class="border rounded bg-slate-50 overflow-hidden">
            <canvas id="pad" class="w-full" style="height:160px"></canvas>
          </div>

          <div class="mt-2 flex items-center gap-2">
            <button type="button" id="clearBtn" class="{{ $btn }} border border-slate-400 text-slate-700">Bersihkan</button>
            <button type="submit" class="{{ $btn }} bg-indigo-600 text-white">Simpan TTD</button>
          </div>
        </form>
      </div>
    @endif

    {{-- Daftar tugas TTD saya (token aktif) --}}
    <div class="bg-white border border-slate-200 rounded-lg">
      <div class="px-3 py-2 border-b text-[11px] text-slate-600">Daftar Permintaan Tanda Tangan (aktif)</div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-[11px]">
          <thead class="bg-slate-50">
            <tr>
              <th class="text-left px-3 py-2">Order</th>
              <th class="text-left px-3 py-2">Jenis</th>
              <th class="text-left px-3 py-2">Kedaluwarsa</th>
              <th class="text-left px-3 py-2">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tokens as $t)
              @php $doc = $docs[$t->notification_number] ?? null; @endphp
              <tr class="border-t">
                <td class="px-3 py-2 font-medium text-slate-800">
                  {{ $t->notification_number }}
                  <div class="text-[10px] text-slate-500">
                    {{ $doc?->description ?? '-' }}
                  </div>
                </td>
                <td class="px-3 py-2">{{ str_replace('_',' ', $t->sign_type) }}</td>
                <td class="px-3 py-2">{{ $t->expires_at?->format('d/m/Y H:i') }}</td>
                <td class="px-3 py-2">
                  <a href="{{ route('approval.hpp.magic', ['token' => $t->id]) }}"
                     class="{{ $btn }} bg-emerald-600 text-white">Buka & TTD</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-3 py-4 text-center text-slate-500">Tidak ada tugas TTD.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- SignaturePad CDN --}}
  <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>
  <script>
    // Copy next-link (kalau ada)
    document.addEventListener('click', async (e)=>{
      const btn = e.target.closest('.copy-next-link'); if(!btn) return;
      try {
        await navigator.clipboard.writeText(btn.dataset.link);
        if (window.Swal) Swal.fire({icon:'success',title:'Disalin',text:'Link tahap berikutnya sudah disalin.',timer:1500,showConfirmButton:false});
        else alert('Link tahap berikutnya disalin.');
      } catch(_) {}
    });

    // SignaturePad init (hanya kalau canvas ada)
    const c = document.getElementById('pad');
    if (c) {
      const resize = () => {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = c.getBoundingClientRect();
        c.width = rect.width * ratio;
        c.height = rect.height * ratio;
        c.getContext('2d').scale(ratio, ratio);
      };
      resize(); window.addEventListener('resize', resize);

      const pad = new SignaturePad(c, { minWidth: 0.7, maxWidth: 1.8, throttle: 0 });

      const clearBtn = document.getElementById('clearBtn');
      if (clearBtn) clearBtn.addEventListener('click', ()=> pad.clear());

      const form = document.getElementById('signForm');
      if (form) form.addEventListener('submit', (e)=>{
        if (pad.isEmpty()) { e.preventDefault(); alert('Tanda tangan belum diisi.'); return; }
        document.getElementById('signatureInput').value = pad.toDataURL('image/png');
      });
    }
  </script>
</x-admin-layout>
