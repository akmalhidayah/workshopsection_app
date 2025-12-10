<script>
/* =========================
   KAWAT LAS - MAIN SCRIPT
   ========================= */

/* Helper: cari elemen berdasarkan beberapa kemungkinan selector/id */
function findEl(...selectors) {
  for (const s of selectors) {
    if (!s) continue;
    // getElementById faster for plain ids
    if (typeof s === 'string' && !s.includes(' ') && document.getElementById(s)) return document.getElementById(s);
    const el = document.querySelector(s);
    if (el) return el;
  }
  return null;
}

/* =========================
   Modal Create
   ========================= */
function openForm() {
    const df = findEl('dataForm', '#dataForm', '.data-form');
    if (df) df.classList.remove('hidden');
    try { updateGrandTotal(); } catch(e) {}
    try { initSeksiForCreate(); } catch(e) { console.warn('initSeksiForCreate error', e); }
}
function closeForm() {
    const df = findEl('dataForm', '#dataForm', '.data-form');
    if (df) df.classList.add('hidden');
}

/* =========================
   Modal Edit
   ========================= */
function openEditForm(id) {
  fetch(`/kawatlas/${encodeURIComponent(id)}/edit`)
    .then(res => {
      if (!res.ok) throw new Error('Gagal memuat data');
      return res.json();
    })
    .then(data => {
      // format helper untuk input type="date"
      const formatDate = d => d ? String(d).substring(0, 10) : '';

      // Ambil elemen-elemen utama (safe via findEl)
      const orderEl   = findEl('edit_order_number', '#edit_order_number', 'input[name="order_number"]');
      const tanggalEl = findEl('edit_tanggal', '#edit_tanggal', 'input[name="tanggal"]');
      const unitSelect= findEl('edit_unit_work', '#edit_unit_work', 'select[name="unit_work"]');
      const formEl    = findEl('editFormElement', '#editFormElement', 'form#editFormElement', 'form#editForm');
      const modal     = findEl('editForm', '#editForm', '.modal-edit', '#modalEdit');

      // set values (safely)
      if (orderEl)  orderEl.value = data.order_number ?? '';
      if (tanggalEl) tanggalEl.value = formatDate(data.tanggal);

      if (formEl) formEl.action = `/kawatlas/${encodeURIComponent(id)}`;

      // Seksi elements (try multiple possible ids/names)
      const seksiSelect = findEl('edit_seksi', '#edit_seksi', '#seksiEdit', 'select[name="seksi"]');
      const seksiWrap   = findEl('wrapSeksiEdit', '#wrapSeksiEdit', '.wrap-seksi-edit');

      // parse helper (local copy)
      function parseSeksiRaw(raw) {
        if (!raw) return [];
        if (Array.isArray(raw)) return raw;
        try {
          const p = JSON.parse(raw);
          return Array.isArray(p) ? p : [];
        } catch (e) {
          return String(raw).replace(/^\[|\]$/g,'').split(',').map(s => s.replace(/^"+|"+$/g,'').trim()).filter(Boolean);
        }
      }

      function safePopulateSeksi(sel, arr, selectedValue) {
        if (!sel) {
          if (seksiWrap) seksiWrap.style.display = 'none';
          return;
        }
        sel.innerHTML = '<option value="">Pilih Seksi</option>';
        if (!arr || !arr.length) {
          if (seksiWrap) seksiWrap.style.display = 'none';
          return;
        }
        arr.forEach(s => {
          const o = document.createElement('option');
          o.value = s;
          o.textContent = s;
          if (selectedValue !== undefined && selectedValue !== null && String(selectedValue) === String(s)) o.selected = true;
          sel.appendChild(o);
        });
        if (seksiWrap) seksiWrap.style.display = '';
      }

      // populate seksi based on selected unit option
      if (unitSelect) {
        // set unit value from server if present
        if (data.unit_work) {
          let matched = false;
          for (let i=0;i<unitSelect.options.length;i++){
            if (String(unitSelect.options[i].value) === String(data.unit_work)) {
              unitSelect.selectedIndex = i;
              matched = true;
              break;
            }
          }
          if (!matched) unitSelect.value = data.unit_work;
        }

        const opt = unitSelect.options[unitSelect.selectedIndex];
        const raw = opt ? opt.getAttribute('data-seksi') : null;
        const arr = parseSeksiRaw(raw);
        safePopulateSeksi(seksiSelect, arr, data.seksi ?? null);

        // attach change handler only once
        const handlerKey = '__kawatlas_unit_change_handler';
        if (!unitSelect[handlerKey]) {
          unitSelect[handlerKey] = function() {
            const o = unitSelect.options[unitSelect.selectedIndex];
            const r = o ? o.getAttribute('data-seksi') : null;
            const a = parseSeksiRaw(r);
            safePopulateSeksi(seksiSelect, a, null);
          };
          unitSelect.addEventListener('change', unitSelect[handlerKey]);
        }
      } else {
        if (seksiWrap) seksiWrap.style.display = 'none';
      }

      // details
      const container = findEl('edit-detail-container', '#edit-detail-container', '.edit-detail-container');
      if (container) {
        container.innerHTML = '';
        let i = 0;
        (data.details || []).forEach(det => {
          container.insertAdjacentHTML('beforeend', renderRow(i, det.jenis_kawat, det.jumlah));
          // set the select value for the newly inserted row if possible
          const selects = container.querySelectorAll('select[name^="detail_kawat"]');
          if (selects && selects.length) {
            const last = selects[selects.length - 1];
            if (last) last.value = det.jenis_kawat;
            try { updateInfo(last); } catch (_) {}
          }
          i++;
        });
      }

      try { updateGrandTotal(); } catch(e) {}

      // show modal
      if (modal) modal.classList.remove('hidden');
      else {
        const fallback = document.getElementById('modalEdit') || document.getElementById('editForm');
        if (fallback) fallback.classList.remove('hidden');
      }
    })
    .catch(err => {
      console.error('openEditForm error', err);
      alert('Gagal memuat data edit. Periksa console untuk detail.');
    });
}

function closeEditForm() {
  const modal = findEl('modalEdit', '#modalEdit', '.modal-edit');
  if (modal) modal.classList.add('hidden');
}

/* =========================
   Add / Remove Rows
   ========================= */
function addRow() {
    const c = findEl('detail-container', '#detail-container', '.detail-container');
    if (!c) return;
    c.insertAdjacentHTML('beforeend', renderRow(c.querySelectorAll('.detail-row').length));
}
function addRowEdit() {
    const c = findEl('edit-detail-container', '#edit-detail-container', '.edit-detail-container');
    if (!c) return;
    c.insertAdjacentHTML('beforeend', renderRow(c.querySelectorAll('.detail-row').length));
}
function removeRow(b) {
    const row = b.closest('.detail-row');
    if (!row) return;
    row.remove();
    updateGrandTotal();
}

/* =========================
   Row template
   ========================= */
function renderRow(i, selected = '', jumlah = '') {
    let opts = `<option value="">Pilih Jenis</option>`;
    @foreach ($jenisList as $jenis)
        opts += `<option 
                    value="{{ $jenis->kode }}" 
                    data-stok="{{ $jenis->stok }}" 
                    data-deskripsi="{{ $jenis->deskripsi }}"
                    data-harga="{{ $jenis->harga }}"
                    data-cost="{{ $jenis->cost_element }}"
                    data-gambar="{{ $jenis->gambar ? asset('storage/'.$jenis->gambar) : '' }}">
                    {{ $jenis->kode }}
                 </option>`;
    @endforeach

    return `
    <div class="detail-row flex flex-col gap-1 mb-3">
        <div class="flex gap-2 items-center">
            <select name="detail_kawat[${i}][jenis_kawat]" 
                    class="border rounded p-2 w-1/2 jenis-kawat-select"
                    onchange="updateInfo(this)" required>
                ${opts}
            </select>
            <input type="number" name="detail_kawat[${i}][jumlah]" 
                   class="border rounded p-2 w-1/3" min="1" required 
                   placeholder="Jumlah" value="${jumlah}" 
                   oninput="updateInfo(this.closest('.detail-row').querySelector('select'))">
            <button type="button" onclick="removeRow(this)" 
                    class="px-2 bg-red-500 text-white rounded">-</button>
        </div>
        <div class="flex items-center gap-2 mt-1 info-box hidden">
            <img src="" alt="preview" class="w-12 h-12 object-cover rounded border hidden">
            <small class="text-xs text-gray-500 info-text"></small>
        </div>
    </div>`;
}

/* =========================
   Update Info per row
   ========================= */
function updateInfo(sel) {
    if (!sel) return;
    const opt = sel.selectedOptions ? sel.selectedOptions[0] : null;
    const stok = opt?.dataset.stok || 0;
    const desk = opt?.dataset.deskripsi || '-';
    const harga = parseFloat(opt?.dataset.harga || 0);
    const cost = opt?.dataset.cost || '-';
    const gambar = opt?.dataset.gambar || '';

    const row = sel.closest('.detail-row');
    if (!row) return;
    const jumlah = parseInt(row.querySelector('input[type=number]').value) || 0;

    const infoBox = row.querySelector('.info-box');
    const infoText = row.querySelector('.info-text');
    const img = row.querySelector('img');

    if (sel.value) {
        const hargaRp = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(harga);
        const totalRp = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(jumlah * harga);

        if (infoText) infoText.textContent = `Stok: ${stok} | Harga: ${hargaRp} | Cost Element: ${cost} | Deskripsi: ${desk} | Total: ${totalRp}`;
        if (img) {
          if (gambar) {
            img.src = gambar;
            img.classList.remove('hidden');
          } else {
            img.classList.add('hidden');
          }
        }
        if (infoBox) infoBox.classList.remove('hidden');
    } else {
        if (infoBox) infoBox.classList.add('hidden');
    }

    updateGrandTotal();
}

/* =========================
   Grand total
   ========================= */
function updateGrandTotal() {
    let grandTotal = 0;
    const rows = document.querySelectorAll('.detail-row');
    rows.forEach(row => {
        const select = row.querySelector('select');
        const opt = select ? (select.selectedOptions ? select.selectedOptions[0] : select.options[select.selectedIndex]) : null;
        const harga = parseFloat(opt?.dataset.harga || 0);
        const jumlah = parseInt(row.querySelector('input[type=number]').value) || 0;
        grandTotal += harga * jumlah;
    });
    const el = findEl('grand-total', '#grand-total', '#grand-total-edit', '.grand-total');
    if (el) el.textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
}

/* =========================
   Seksi handling (Create + Edit)
   ========================= */
function _parseSeksiRaw(raw) {
    if (!raw) return [];
    if (Array.isArray(raw)) return raw;
    try {
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
        return String(raw).replace(/^\[|\]$/g,'').split(',').map(s => s.replace(/^"+|"+$/g,'').trim()).filter(Boolean);
    }
}

function _populateSeksiSelect(seksiSelect, arr, selectedValue) {
    if (!seksiSelect) return;
    // clear
    seksiSelect.innerHTML = '';
    const empty = document.createElement('option');
    empty.value = '';
    empty.textContent = 'Pilih Seksi';
    seksiSelect.appendChild(empty);

    if (!arr || !arr.length) {
        const wrap = seksiSelect.closest('[id^="wrapSeksi"]') || seksiSelect.closest('.mb-4');
        if (wrap) wrap.style.display = 'none';
        return;
    }

    arr.forEach(s => {
        const o = document.createElement('option');
        o.value = s;
        o.textContent = s;
        if (selectedValue !== undefined && selectedValue !== null && String(selectedValue) === String(s)) o.selected = true;
        seksiSelect.appendChild(o);
    });

    const wrap = seksiSelect.closest('[id^="wrapSeksi"]') || seksiSelect.closest('.mb-4');
    if (wrap) wrap.style.display = '';
}

function initSeksiForCreate() {
    const unitSel = findEl('unitKerjaCreate', '#unitKerjaCreate', '#dataForm select[name="unit_work"]', 'select[name="unit_work"]');
    const seksiSel = findEl('seksiCreate', '#seksiCreate', '#dataForm select[name="seksi"]', 'select[name="seksi"]');
    if (!unitSel || !seksiSel) return;

    // bind once
    const handlerKey = '__kawatlas_create_unit_change';
    if (!unitSel[handlerKey]) {
      unitSel[handlerKey] = function() {
        const selOpt = unitSel.options[unitSel.selectedIndex];
        const raw = selOpt ? selOpt.getAttribute('data-seksi') : null;
        const arr = _parseSeksiRaw(raw);
        // try restore server old value on initial change (only)
        let oldSeksi = null;
        try { oldSeksi = "{{ old('seksi') }}"; } catch(e) { oldSeksi = null; }
        const selected = (oldSeksi && oldSeksi.length) ? oldSeksi : null;
        _populateSeksiSelect(seksiSel, arr, selected);
      };
      unitSel.addEventListener('change', unitSel[handlerKey]);
    }

    // initial trigger
    unitSel[unitSel[handlerKey]]?.();
}

function initSeksiForEdit(prefillSelectedSeksi = null) {
    const unitSel = findEl('edit_unit_work', '#edit_unit_work', '#editForm select[name="unit_work"]', 'select[name="unit_work"]');
    const seksiSel = findEl('edit_seksi', '#edit_seksi', '#seksiEdit', '#editForm select[name="seksi"]', 'select[name="seksi"]');
    if (!unitSel || !seksiSel) return;

    const handlerKey = '__kawatlas_edit_unit_change';
    if (!unitSel[handlerKey]) {
      unitSel[handlerKey] = function(preset = null) {
        const selOpt = unitSel.options[unitSel.selectedIndex];
        const raw = selOpt ? selOpt.getAttribute('data-seksi') : null;
        const arr = _parseSeksiRaw(raw);
        // prefer preset over old()
        let selected = preset ?? null;
        if (!selected) {
          try { selected = "{{ old('seksi') }}"; } catch(e) { selected = null; }
        }
        _populateSeksiSelect(seksiSel, arr, selected);
      };
      unitSel.addEventListener('change', function(){ unitSel[handlerKey](null); });
    }

    // initial call (if preset provided)
    unitSel[handlerKey](prefillSelectedSeksi ?? null);
}

/* =========================
   Init on DOMContentLoaded
   ========================= */
document.addEventListener('DOMContentLoaded', function () {
    try { initSeksiForCreate(); } catch(e) { /* ignore */ }
    try { initSeksiForEdit(); } catch(e) { /* ignore */ }

    // If the page initially rendered rows (old input), ensure updateInfo is called for each select
    document.querySelectorAll('.detail-row .jenis-kawat-select').forEach(sel => {
      try { updateInfo(sel); } catch(e) {}
    });
});
</script>
