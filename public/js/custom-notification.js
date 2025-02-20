// public/js/custom-notification.js

function confirmCreate() {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Anda akan membuat Order baru.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, buat!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            openForm(); // Panggil fungsi untuk membuka form
        }
    });
}

function openForm() {
    // Tampilkan modal form create notifikasi
    document.getElementById('dataForm').classList.remove('hidden');
}

function closeForm() {
    // Tutup modal form create notifikasi
    document.getElementById('dataForm').classList.add('hidden');
}

function openEditForm(notification_number) {
    fetch(`/notifikasi/${notification_number}/edit`)
        .then(response => response.json())
        .then(notification => {
            document.getElementById('editNotifikasiNo').value = notification.notification_number;
            document.getElementById('editNamaPekerjaan').value = notification.job_name;
            document.getElementById('editUnitKerja').value = notification.unit_work;
            document.getElementById('priority').value = notification.priority;
            document.getElementById('editInputDate').value = notification.input_date;

            // Set jenis kontrak
            document.getElementById('jenisKontrak').value = notification.jenis_kontrak;

            // Panggil fungsi untuk memuat nama kontrak berdasarkan jenis kontrak
            handleJenisKontrakChange(); 

            // Tunggu sampai dropdown terisi baru tetapkan nilai nama kontrak
            setTimeout(() => {
                document.getElementById('namaKontrak').value = notification.nama_kontrak;
            }, 100); // Timeout 100 ms untuk memastikan nama kontrak terisi

            // Set form action untuk update
            document.getElementById('editNotificationForm').action = `/notifikasi/${notification.notification_number}`;

            // Tampilkan modal edit
            document.getElementById('editForm').classList.remove('hidden');
        })
        .catch(error => console.error('Error:', error));
}

function closeEditForm() {
    document.getElementById('editForm').classList.add('hidden');
}

function confirmDelete(form) {
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
            form.submit(); // Lanjutkan penghapusan data
        }
    });

    return false; // Mencegah form dikirim secara otomatis sebelum konfirmasi
}
function handleJenisKontrakChange() {
    const jenisKontrak = document.getElementById('jenisKontrak').value;
    const namaKontrakContainer = document.getElementById('namaKontrakContainer');
    const namaKontrakSelect = document.getElementById('namaKontrak');

    // Reset options setiap kali jenis kontrak berubah
    namaKontrakSelect.innerHTML = '';

    if (jenisKontrak === 'Bengkel Mesin') {
        namaKontrakSelect.innerHTML = `
            <option value="Fabrikasi_Konstruksi_Pengerjaan_Mesin">Fabrikasi, Konstruksi dan Pengerjaan Mesin</option>
        `;
    } else if (jenisKontrak === 'Bengkel Listrik') {
        namaKontrakSelect.innerHTML = `
            <option value="Maintenance">Maintenance</option>
            <option value="Perbaikan">Perbaikan</option>
            <option value="Listrik">Listrik</option>
        `;
    } else if (jenisKontrak === 'Field Supporting') {
        namaKontrakSelect.innerHTML = `
            <option value="Kontrak Jasa OVH Packer">Kontrak Jasa OVH Packer</option>
            <option value="Kontrak Service">Kontrak Service</option>
            <option value="Kontrak Jasa Area Kiln">Kontrak Jasa Area Kiln</option>
            <option value="Kontrak Jasa Mekanikal">Kontrak Jasa Mekanikal</option>
        `;
    }

    // Tampilkan container Nama Kontrak jika Jenis Kontrak sudah dipilih
    if (jenisKontrak) {
        namaKontrakContainer.style.display = 'block';
    } else {
        namaKontrakContainer.style.display = 'none';
    }
}

