// public/js/custom-notification.js

// fallback jika window.NotificationBase tidak diset dari Blade
if (!window.NotificationBase) {
    window.NotificationBase = '/notifikasi';
}

document.addEventListener('DOMContentLoaded', function () {
    // init select2 jika tersedia
    if (typeof $ !== 'undefined' && $.fn && $.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }

    // open create modal
    const openCreateBtn = document.getElementById('openCreateBtn');
    if (openCreateBtn) openCreateBtn.addEventListener('click', confirmCreate);

    // attach edit button listeners
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function () {
            const number = this.getAttribute('data-number');
            openEditForm(number);
        });
    });

    // delete confirm
    document.querySelectorAll('form[data-number]').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const f = this;
            Swal.fire({
                title: 'Anda yakin?',
                text: "Data ini akan dihapus dan tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    f.submit();
                }
            });
        });
    });

    // flash success
    const flash = document.getElementById('flash-success');
    if (flash) {
        const msg = flash.dataset.message;
        if (msg) {
            Swal.fire({ icon: 'success', title: 'Sukses', text: msg, timer: 2000, showConfirmButton: false });
        }
    }
});

/* ---------- Create modal ---------- */
function confirmCreate() {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Anda akan membuat Order baru.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, buat!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) openCreate();
    });
}
function openCreate() {
    const m = document.getElementById('modalCreate');
    if (m) m.classList.remove('hidden');
}
function closeCreate() {
    const m = document.getElementById('modalCreate');
    if (m) m.classList.add('hidden');
}

/* ---------- Edit modal: fetch data from server and open modal ---------- */
function openEditForm(notification_number) {
    if (!notification_number) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Nomor notifikasi tidak valid.' });
        return;
    }

    // build url: /notifikasi/{id}/edit
    const url = `${window.NotificationBase}/${encodeURIComponent(notification_number)}/edit`;

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(response => {
            if (!response.ok) {
                // show different message for 404 vs 500
                if (response.status === 404) {
                    throw new Error('NotFound');
                } else if (response.status === 403) {
                    throw new Error('Forbidden');
                } else {
                    throw new Error('Network');
                }
            }
            return response.json();
        })
        .then(notification => {
            // populate edit fields (make sure element IDs exist)
            const setIf = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.value = val ?? '';
            };

            setIf('editNotifikasiNo', notification.notification_number);
            setIf('editNamaPekerjaan', notification.job_name);
            setIf('editUnitKerja', notification.unit_work);

            // priority: ensure priority_edit exists
            const prEl = document.getElementById('priority_edit');
            if (prEl) prEl.value = notification.priority ?? 'Medium';

            // dates
            setIf('editInputDate', notification.input_date ?? '');
            setIf('editRencanaPemakaian', notification.usage_plan_date ?? '');

            // Set form action to PATCH /notifikasi/{id}
            const editForm = document.getElementById('editForm');
            if (editForm) {
                editForm.action = `${window.NotificationBase}/${encodeURIComponent(notification.notification_number)}`;
            }

            // show modal
            const m = document.getElementById('modalEdit');
            if (m) m.classList.remove('hidden');
        })
        .catch(err => {
            console.error('Fetch edit error', err);
            if (err.message === 'NotFound') {
                Swal.fire({ icon: 'error', title: 'Tidak ditemukan', text: 'Notifikasi tidak ditemukan atau Anda tidak punya akses.' });
            } else if (err.message === 'Forbidden') {
                Swal.fire({ icon: 'error', title: 'Akses ditolak', text: 'Anda tidak memiliki izin untuk melihat data ini.' });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal mengambil data notifikasi. Cek console untuk detail.' });
            }
        });
}

function closeEdit() {
    const m = document.getElementById('modalEdit');
    if (m) m.classList.add('hidden');
}

/* ---------- Helper: jenisKontrak -> namaKontrak (create only) ---------- */
function handleJenisKontrakChange(mode = 'create') {
    const jenisEl = (mode === 'create') ? document.getElementById('jenisKontrak') : null;
    const namaContainer = document.getElementById('namaKontrakContainer');
    const namaSelect = document.getElementById('namaKontrak');

    if (!jenisEl || !namaSelect || !namaContainer) return;

    const jenis = jenisEl.value;
    namaSelect.innerHTML = '';

    if (jenis === 'Bengkel Mesin') {
        namaSelect.innerHTML = `<option value="Fabrikasi_Konstruksi_Pengerjaan_Mesin">Fabrikasi, Konstruksi dan Pengerjaan Mesin</option>`;
    } else if (jenis === 'Bengkel Listrik') {
        namaSelect.innerHTML = `
            <option value="Maintenance">Maintenance</option>
            <option value="Perbaikan">Perbaikan</option>
            <option value="Listrik">Listrik</option>
        `;
    } else if (jenis === 'Field Supporting') {
        namaSelect.innerHTML = `
            <option value="Kontrak Jasa OVH Packer">Kontrak Jasa OVH Packer</option>
            <option value="Kontrak Service">Kontrak Service</option>
            <option value="Kontrak Jasa Area Kiln">Kontrak Jasa Area Kiln</option>
            <option value="Kontrak Jasa Mekanikal">Kontrak Jasa Mekanikal</option>
        `;
    }

    namaContainer.style.display = jenis ? 'block' : 'none';
}
