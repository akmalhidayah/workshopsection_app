<script>
document.addEventListener('DOMContentLoaded', () => {
  const selectNotif = document.getElementById('notifikasi');
  const deskripsi   = document.getElementById('deskripsi');
  const unitKerja   = document.getElementById('unit_kerja_peminta');
  const container   = document.getElementById('jenis-container');
  const tambahJenisBtn = document.getElementById('tambah-jenis-btn');
  const totalAllEl  = document.getElementById('total_keseluruhan');

  if (selectNotif) {
    selectNotif.addEventListener('change', () => {
      const opt = selectNotif.selectedOptions[0];
      deskripsi.value = opt?.getAttribute('data-job')  || '';
      unitKerja.value = opt?.getAttribute('data-unit') || '';
    });
  }

  let jenisCounter = 0;
  tambahJenisBtn?.addEventListener('click', () => addJenis(null));

  function addJenis(preset = null) {
    const g = jenisCounter++;
    const wrap = document.createElement('div');
    wrap.className = 'jenis-block border border-gray-300 rounded-lg p-4 bg-gray-50 shadow-sm';
    const titleVal = preset?.title ?? `Material/Jasa ${g+1}`;

    wrap.innerHTML = `
      <div class="flex justify-between items-start mb-3 gap-2">
        <div class="flex-1">
          <label class="text-xs text-gray-600">JENIS ITEM</label>
          <input type="text" name="jenis_label_visible[${g}]" class="jenis-label mt-1 block w-full border-gray-300 rounded-md px-2 py-1 text-sm" value="${escapeAttr(titleVal)}" placeholder="Contoh: Material/Jasa">
        </div>
        <div class="flex flex-col items-end gap-2">
          <button type="button" class="hapus-jenis text-red-500 hover:text-red-700 text-xs bg-white border px-2 py-1 rounded">
            <i class="fas fa-trash"></i> Hapus Jenis
          </button>
          <button type="button" class="tambah-item mt-1 bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
            <i class="fas fa-plus"></i> Tambah Item
          </button>
        </div>
      </div>

      <div class="items-container space-y-3" data-g="${g}"></div>

      <div class="mt-3 text-right text-sm text-gray-700">
        <span>Subtotal: </span>
        <span class="subtotal font-semibold text-blue-600" data-raw="0">0</span>
      </div>
    `;

    container.appendChild(wrap);

    const itemsContainer = wrap.querySelector('.items-container');
    const subtotalEl     = wrap.querySelector('.subtotal');
    const labelInput     = wrap.querySelector('.jenis-label');

    // when label changes, update all hidden jenis_item inputs inside this group
    labelInput.addEventListener('input', () => {
      updateHiddenJenisForGroup(g, labelInput.value);
    });

    wrap.querySelector('.tambah-item').addEventListener('click', () => {
      addItem(itemsContainer, subtotalEl, g, null, labelInput.value);
    });

    wrap.querySelector('.hapus-jenis').addEventListener('click', () => {
      if (!confirm('Hapus seluruh jenis beserta itemnya?')) return;
      wrap.remove();
      updateGrandTotal();
    });

    // preset items (edit mode)
    if (preset?.items && Array.isArray(preset.items) && preset.items.length) {
      preset.items.forEach(it => addItem(itemsContainer, subtotalEl, g, it, titleVal));
      recalcSubtotal(itemsContainer, subtotalEl);
    }

    return wrap;
  }

  // addItem now also inserts a hidden jenis_item[g][] input (value = group label)
  function addItem(list, subtotalEl, gIndex, data = null, groupLabel = '') {
    const item = document.createElement('div');
    item.className = 'uraian-item border border-gray-200 rounded-md p-3 bg-white shadow-sm';

    item.innerHTML = `
      <div class="flex justify-between items-center mb-2">
        <h4 class="font-semibold text-gray-800">Item</h4>
        <button type="button" class="remove-item text-red-500 hover:text-red-700 text-xs">
          <i class="fas fa-times-circle"></i> Hapus
        </button>
      </div>

      <!-- HIDDEN: jenis_item per-item (controller expects jenis_item[ g ][ i ]) -->
      <input type="hidden" name="jenis_item[${gIndex}][]" class="jenis-hidden" value="${escapeAttr(groupLabel)}">

      <div class="grid grid-cols-3 gap-2 mb-2">
        <div>
          <input type="text" name="nama_item[${gIndex}][]" value="${escapeAttr(data?.nama_item ?? '')}" class="border-gray-300 rounded-md text-sm px-2 py-2 w-full" placeholder="Nama Item (plate/besi/dll)">
        </div>

        <div>
          <!-- jumlah_item di samping nama_item (deskriptif) -->
          <input type="text" name="jumlah_item[${gIndex}][]" value="${escapeAttr(data?.jumlah_item ?? '')}" class="border-gray-300 rounded-md text-sm px-2 py-2 w-full" placeholder=" Quantity">
        </div>

      </div>

      <div class="grid grid-cols-4 gap-2 mb-2">
        <input type="number" name="qty[${gIndex}][]" value="${escapeAttr(data?.qty ?? '')}" min="0" step="0.01" class="qty border-gray-300 rounded-md text-sm px-2 py-2" placeholder="Berat/Jmlh Jam/jmlh luasan">
        <input type="text" name="satuan[${gIndex}][]" value="${escapeAttr(data?.satuan ?? '')}" class="border-gray-300 rounded-md text-sm px-2 py-2" placeholder="Satuan">
        <input type="number" name="harga_satuan[${gIndex}][]" value="${escapeAttr(data?.harga_satuan ?? '')}" min="0" step="0.01" class="harga-satuan border-gray-300 rounded-md text-sm px-2 py-2" placeholder="Harga Satuan">
        <input type="number" name="harga_total[${gIndex}][]" value="${escapeAttr(data?.harga_total ?? '')}" class="harga-total border-gray-300 rounded-md text-sm px-2 py-2 bg-gray-50" placeholder="Harga Total" readonly>
      </div>

      <div class="mb-0">
        <input type="text" name="keterangan[${gIndex}][]" value="${escapeAttr(data?.keterangan ?? '')}" class="border-gray-300 rounded-md text-sm px-2 py-2 w-full" placeholder="Keterangan (opsional)">
      </div>
    `;

    list.appendChild(item);

    // ensure hidden jenis_item value equals current group label (label input)
    const wrapBlock = list.closest('.jenis-block');
    const labelEl = wrapBlock ? wrapBlock.querySelector('.jenis-label') : null;
    if (labelEl) {
      const hidden = item.querySelector('.jenis-hidden');
      hidden.value = labelEl.value || '';
    }

    const qtyEl = item.querySelector('.qty');
    const hsEl  = item.querySelector('.harga-satuan');
    const htEl  = item.querySelector('.harga-total');

    function recompute() {
      const qty  = parseFloat(qtyEl.value) || 0;
      const hs   = parseFloat(hsEl.value)  || 0;
      htEl.value = (qty * hs).toFixed(2);
      recalcSubtotal(list, subtotalEl);
    }

    qtyEl.addEventListener('input', recompute);
    hsEl.addEventListener('input', recompute);

    item.querySelector('.remove-item').addEventListener('click', () => {
      if (!confirm('Hapus item ini?')) return;
      item.remove();
      recalcSubtotal(list, subtotalEl);
    });

    // initial compute for preset
    recompute();
  }

  // update all hidden jenis inputs inside group g to newLabel
  function updateHiddenJenisForGroup(gIndex, newLabel) {
    const groupWrap = container.querySelector(`.items-container[data-g="${gIndex}"]`);
    if (!groupWrap) return;
    groupWrap.querySelectorAll('.jenis-hidden').forEach(h => h.value = newLabel);
  }

  function recalcSubtotal(list, subtotalEl) {
    let subtotal = 0;
    list.querySelectorAll('.harga-total').forEach(ht => {
      subtotal += parseFloat(ht.value) || 0;
    });
    subtotalEl.dataset.raw = String(subtotal);
    subtotalEl.textContent = subtotal.toLocaleString('id-ID');
    updateGrandTotal();
  }

  function updateGrandTotal() {
    let grand = 0;
    document.querySelectorAll('.subtotal').forEach(s => {
      grand += parseFloat(s.dataset.raw || '0') || 0;
    });
    if (totalAllEl) totalAllEl.value = grand.toFixed(2);
  }

  // EDIT MODE: rebuild groups/items from server payload (2D arrays expected)
  if (window.hppEditData) {
    const d = window.hppEditData;
    if (d.description) deskripsi.value = d.description;
    if (d.requesting_unit) unitKerja.value = d.requesting_unit;
    if (d.cost_centre) document.querySelector('input[name="cost_centre"]').value = d.cost_centre ?? '';
    if (d.outline_agreement) document.querySelector('input[name="outline_agreement"]').value = d.outline_agreement ?? '';

    const names2d = Array.isArray(d.nama_item) ? d.nama_item : [];
    // derive labels from jenis_item (fallback handled elsewhere)
    const jenis_labels = Array.isArray(d.jenis_item) ? d.jenis_item.map(group => {
      // take first non-empty value in group as label, or empty
      if (!Array.isArray(group)) return '';
      for (let v of group) if (v !== null && String(v).trim() !== '') return String(v).trim();
      return '';
    }) : [];

    if (names2d.length === 0) {
      addJenis();
    } else {
      names2d.forEach((groupArr, gIndex) => {
        const items = [];
        const len = Array.isArray(groupArr) ? groupArr.length : 0;
        for (let i = 0; i < len; i++) {
          items.push({
            nama_item:    d.nama_item?.[gIndex]?.[i]     ?? '',
            jumlah_item:  d.jumlah_item?.[gIndex]?.[i]   ?? '',
            qty:          d.qty?.[gIndex]?.[i]           ?? '',
            satuan:       d.satuan?.[gIndex]?.[i]        ?? '',
            harga_satuan: d.harga_satuan?.[gIndex]?.[i]  ?? '',
            harga_total:  d.harga_total?.[gIndex]?.[i]   ?? '',
            keterangan:   d.keterangan?.[gIndex]?.[i]    ?? ''
          });
        }
        const title = jenis_labels[gIndex] ?? `Jenis ${gIndex+1}`;
        addJenis({ title, items });
      });
    }
  } else {
    addJenis();
  }

  function escapeAttr(s) {
    if (s == null) return '';
    return String(s).replace(/"/g, '&quot;').replace(/</g, '&lt;');
  }
});
</script>
