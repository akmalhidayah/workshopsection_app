<script>
document.addEventListener('DOMContentLoaded', () => {
  const selectNotif = document.getElementById('notifikasi');
  const deskripsi   = document.getElementById('deskripsi');
  const unitKerja   = document.getElementById('unit_kerja_peminta');
  const container   = document.getElementById('pekerjaan-container');
  const tambahBtn   = document.getElementById('tambah-pekerjaan-btn');
  const totalAllEl  = document.getElementById('total_keseluruhan');

  // ==== Auto-fill dari notifikasi (create mode)
  if (selectNotif) {
    selectNotif.addEventListener('change', () => {
      const opt = selectNotif.selectedOptions[0];
      deskripsi.value = opt?.getAttribute('data-job')  || '';
      unitKerja.value = opt?.getAttribute('data-unit') || '';
    });
  }

  // ==== State index kelompok
  let gCounter = 0;

  // Tambah kelompok (Uraian Pekerjaan)
  tambahBtn?.addEventListener('click', () => addGroup());

  function addGroup(preset = null) {
    const g = gCounter++;
    const wrap = document.createElement('div');
    wrap.className = 'border border-gray-300 rounded-lg p-4 bg-gray-50 shadow-sm';

    wrap.innerHTML = `
      <div class="flex justify-between items-center mb-3">
        <input type="text"
               name="uraian_pekerjaan[${g}]"
               placeholder="Uraian Pekerjaan (cth: Pekerjaan Mekanik, Pengadaan Material, dst.)"
               class="w-1/2 border-gray-300 rounded-md text-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
               value="${preset?.title ?? ''}">
        <button type="button" class="hapus-pekerjaan text-red-500 hover:text-red-700 text-xs">
          <i class="fas fa-times-circle"></i> Hapus Uraian
        </button>
      </div>

      <div class="flex gap-3 items-center mb-3">
        <button type="button"
                class="tambah-item bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-md text-sm shadow">
          <i class="fas fa-plus"></i> Tambah Item
        </button>
        <button type="button"
                class="hapus-item-semua bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-sm shadow">
          <i class="fas fa-trash-alt mr-1"></i> Hapus Semua Item
        </button>
      </div>

      <div class="uraian-container space-y-3" data-g="${g}"></div>

      <div class="mt-4 text-right text-sm text-gray-700">
        <span>Subtotal Uraian: </span>
        <span class="subtotal font-semibold text-blue-600" data-raw="0">0</span>
      </div>
    `;

    container.appendChild(wrap);
    attachGroupHandlers(wrap);

    // preset (edit mode)
    if (preset?.items && Array.isArray(preset.items)) {
      const list  = wrap.querySelector('.uraian-container');
      const subEl = wrap.querySelector('.subtotal');
      preset.items.forEach(it => addItem(list, subEl, it)); // it berisi field2 item
      recalcSubtotal(list, subEl);
    }
  }

  function attachGroupHandlers(block) {
    const list       = block.querySelector('.uraian-container');
    const subtotalEl = block.querySelector('.subtotal');

    block.querySelector('.tambah-item').addEventListener('click', () => {
      addItem(list, subtotalEl, null);
    });

    block.querySelector('.hapus-pekerjaan').addEventListener('click', () => {
      block.remove();
      updateGrandTotal();
    });

    block.querySelector('.hapus-item-semua').addEventListener('click', () => {
      if (confirm('Hapus semua item pada uraian ini?')) {
        list.innerHTML = '';
        recalcSubtotal(list, subtotalEl);
      }
    });
  }

  // Tambah item di dalam kelompok g (jenis opsional)
  function addItem(list, subtotalEl, data = null) {
    const g = list.dataset.g;
    const item = document.createElement('div');
    item.className = 'uraian-item border border-gray-200 rounded-md p-3 bg-white shadow-sm';

    item.innerHTML = `
      <div class="flex justify-between items-center mb-2">
        <h4 class="font-semibold text-gray-800">Item</h4>
        <button type="button" class="remove-item text-red-500 hover:text-red-700 text-xs">
          <i class="fas fa-times-circle"></i> Hapus
        </button>
      </div>

      <!-- BARIS 1: TIPE (opsional) + NAMA ITEM -->
      <div class="grid grid-cols-4 gap-2 mb-2">
        <input type="text"   name="jenis_item[${g}][]"   value="${data?.jenis_item ?? ''}"
               class="border-gray-300 rounded-md text-sm px-2 py-1" placeholder="Tipe (opsional)">
        <input type="text"   name="nama_item[${g}][]"    value="${data?.nama_item ?? ''}"
               class="border-gray-300 rounded-md text-sm px-2 py-1 col-span-3" placeholder="Nama Item">
      </div>

      <!-- BARIS 2: QTY + SATUAN + HARGA SATUAN -->
      <div class="grid grid-cols-3 gap-2 mb-2">
        <input type="number" name="qty[${g}][]"          value="${data?.qty ?? ''}" min="0" step="0.01"
               class="qty border-gray-300 rounded-md text-sm px-2 py-1" placeholder="Qty">
        <input type="text"   name="satuan[${g}][]"       value="${data?.satuan ?? ''}"
               class="border-gray-300 rounded-md text-sm px-2 py-1" placeholder="Satuan">
        <input type="number" name="harga_satuan[${g}][]" value="${data?.harga_satuan ?? ''}" min="0" step="0.01"
               class="harga-satuan border-gray-300 rounded-md text-sm px-2 py-1" placeholder="Harga Satuan">
      </div>

      <!-- BARIS 3: HARGA TOTAL + KETERANGAN -->
      <div class="grid grid-cols-2 gap-2">
        <input type="number" name="harga_total[${g}][]"  value="${data?.harga_total ?? ''}"
               class="harga-total border-gray-300 rounded-md text-sm px-2 py-1 bg-gray-50" placeholder="Harga Total" readonly>
        <input type="text"   name="keterangan[${g}][]"   value="${data?.keterangan ?? ''}"
               class="border-gray-300 rounded-md text-sm px-2 py-1" placeholder="Keterangan (opsional)">
      </div>
    `;

    list.appendChild(item);

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
    hsEl.addEventListener('input',  recompute);

    item.querySelector('.remove-item').addEventListener('click', () => {
      item.remove();
      recalcSubtotal(list, subtotalEl);
    });

    // initial compute untuk preset data
    recompute();
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

  // ==== EDIT MODE (rebuild dari window.hppEditData)
  // Bentuk d:
  // {
  //   uraian_pekerjaan: ["Kel 1", ...],
  //   jenis_item:       [ ["", "Sparepart"], ... ],   // boleh kosong
  //   nama_item:        [ ["Plate","Teknisi"], ... ],
  //   qty:              [ [1,2], ... ],
  //   satuan:           [ ["pcs","jam"], ... ],
  //   harga_satuan:     [ [1000,2000], ... ],
  //   harga_total:      [ [1000,4000], ... ],
  //   keterangan:       [ ["A36","Shift 1"], ... ]
  // }
  if (window.hppEditData) {
    const d = window.hppEditData;

    // isi header
    if (d.description)       deskripsi.value = d.description;
    if (d.requesting_unit)   unitKerja.value = d.requesting_unit;
    const cc = document.querySelector('input[name="cost_centre"]');
    if (cc && d.cost_centre) cc.value = d.cost_centre;
    const oa = document.querySelector('input[name="outline_agreement"]');
    if (oa && d.outline_agreement) oa.value = d.outline_agreement;

    if (Array.isArray(d.uraian_pekerjaan)) {
      d.uraian_pekerjaan.forEach((title, g) => {
        addGroup({ title, items: [] });

        const lastGroup = container.lastElementChild;
        const list  = lastGroup.querySelector('.uraian-container');
        const subEl = lastGroup.querySelector('.subtotal');

        // tentukan panjang dari nama_item (bukan jenis_item)
        const len = (d.nama_item?.[g] || []).length;
        for (let i = 0; i < len; i++) {
          addItem(list, subEl, {
            jenis_item:    d.jenis_item?.[g]?.[i]    ?? '',
            nama_item:     d.nama_item?.[g]?.[i]     ?? '',
            qty:           d.qty?.[g]?.[i]           ?? '',
            satuan:        d.satuan?.[g]?.[i]        ?? '',
            harga_satuan:  d.harga_satuan?.[g]?.[i]  ?? '',
            harga_total:   d.harga_total?.[g]?.[i]   ?? '',
            keterangan:    d.keterangan?.[g]?.[i]    ?? ''
          });
        }
        recalcSubtotal(list, subEl);
      });
    }
  }
});

</script>
