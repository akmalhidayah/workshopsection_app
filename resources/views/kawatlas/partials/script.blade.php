<script>
    // ===== Modal Create =====
    function openForm() {
        document.getElementById('dataForm').classList.remove('hidden');
        updateGrandTotal(); // reset total saat buka
    }
    function closeForm() {
        document.getElementById('dataForm').classList.add('hidden');
    }

    // ===== Modal Edit =====
    function openEditForm(id) {
        fetch(`/kawatlas/${id}/edit`)
            .then(res => res.json())
            .then(data => {
                // isi data form utama
                document.getElementById('edit_order_number').value = data.order_number;
                document.getElementById('edit_tanggal').value = data.tanggal;
                document.getElementById('edit_unit_work').value = data.unit_work;
                document.getElementById('editFormElement').action = `/kawatlas/${id}`;

                // reset container detail
                const container = document.getElementById('edit-detail-container');
                container.innerHTML = '';

                // render ulang detail rows
                data.details.forEach((d, i) => {
                    container.insertAdjacentHTML('beforeend', renderRow(i, d.jenis_kawat, d.jumlah));
                    const select = container.querySelectorAll('select')[i];
                    select.value = d.jenis_kawat;
                    updateInfo(select); // tampilkan info stok + harga + cost + total
                });

                updateGrandTotal();
                document.getElementById('editForm').classList.remove('hidden');
            });
    }
    function closeEditForm() {
        document.getElementById('editForm').classList.add('hidden');
    }

    // ===== Tambah / Hapus Row =====
    function addRow() {
        const c = document.getElementById('detail-container');
        c.insertAdjacentHTML('beforeend', renderRow(c.children.length));
    }
    function addRowEdit() {
        const c = document.getElementById('edit-detail-container');
        c.insertAdjacentHTML('beforeend', renderRow(c.children.length));
    }
    function removeRow(b) {
        b.closest('.detail-row').remove();
        updateGrandTotal();
    }

    // ===== Template Row =====
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

    // ===== Update Info per Row =====
    function updateInfo(sel) {
        const opt = sel.selectedOptions[0];
        const stok = opt?.dataset.stok || 0;
        const desk = opt?.dataset.deskripsi || '-';
        const harga = parseFloat(opt?.dataset.harga || 0);
        const cost = opt?.dataset.cost || '-';
        const gambar = opt?.dataset.gambar || '';

        const row = sel.closest('.detail-row');
        const jumlah = parseInt(row.querySelector('input[type=number]').value) || 0;

        const infoBox = row.querySelector('.info-box');
        const infoText = row.querySelector('.info-text');
        const img = row.querySelector('img');

        if (sel.value) {
            const hargaRp = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(harga);
            const totalRp = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(jumlah * harga);

            infoText.textContent = `Stok: ${stok} | Harga: ${hargaRp} | Cost Element: ${cost} | Deskripsi: ${desk} | Total: ${totalRp}`;
            if (gambar) {
                img.src = gambar;
                img.classList.remove('hidden');
            } else {
                img.classList.add('hidden');
            }
            infoBox.classList.remove('hidden');
        } else {
            infoBox.classList.add('hidden');
        }

        updateGrandTotal();
    }

    // ===== Hitung Grand Total =====
    function updateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.detail-row').forEach(row => {
            const select = row.querySelector('select');
            const opt = select.selectedOptions[0];
            const harga = parseFloat(opt?.dataset.harga || 0);
            const jumlah = parseInt(row.querySelector('input[type=number]').value) || 0;
            grandTotal += harga * jumlah;
        });
        const el = document.getElementById('grand-total');
        if (el) {
            el.textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
        }
    }
</script>
