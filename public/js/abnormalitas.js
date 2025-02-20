let signaturePad; // Variabel global untuk Signature Pad

function openSignPad(notificationNumber) {
    console.log('openSignPad called with notification number:', notificationNumber);
    document.getElementById('signPadModal').classList.remove('hidden'); // Menampilkan modal

    const canvas = document.getElementById('signaturePad');
    if (canvas) {
        signaturePad = new SignaturePad(canvas);
        canvas.width = canvas.parentElement.offsetWidth;
        canvas.height = 300;
        signaturePad.clear();
    }

    const scopeOfWorkInput = document.getElementById('scopeOfWorkId');
    if (scopeOfWorkInput) {
        scopeOfWorkInput.value = notificationNumber;
    }
}

// Fungsi untuk menyimpan tanda tangan
function saveSignature() {
    const signature = signaturePad.toDataURL(); // Ambil data tanda tangan sebagai Base64
    const scopeOfWorkId = document.getElementById('scopeOfWorkId').value;

    // Kirim data ke server menggunakan AJAX
    fetch('/save-signature', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            scope_of_work_id: scopeOfWorkId,
            tanda_tangan: signature
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === 'Signature saved successfully!') {
            Swal.fire('Sukses!', 'Tanda tangan berhasil disimpan!', 'success');
            closeSignPad(); // Tutup modal tanda tangan
        } else {
            Swal.fire('Gagal!', 'Gagal menyimpan tanda tangan.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error!', 'Terjadi kesalahan. Silakan coba lagi.', 'error');
    });
}

// Fungsi untuk membersihkan tanda tangan
function clearSignature() {
    signaturePad.clear();
}

// Fungsi untuk menutup modal tanda tangan
function closeSignPad() {
    document.getElementById('signPadModal').classList.add('hidden');
}

// Fungsi untuk membuka modal form
function openFormModal() {
    document.getElementById('formModal').classList.remove('hidden');
}

// Fungsi untuk menutup modal form
function closeFormModal() {
    document.getElementById('formModal').classList.add('hidden');
}

// Fungsi untuk menyimpan data dari form
function saveData() {
    const form = document.getElementById('addDataForm');
    const formData = new FormData(form);
    const newRow = `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">${formData.get('nomorNotifikasi')}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formData.get('namaPekerjaan')}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formData.get('inputDate')}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <a href="#" class="inline-flex items-center px-2 py-1 bg-blue-500 text-white text-xs font-medium rounded hover:bg-blue-600 transition duration-150 ease-in-out">Create</a>
                <a href="#" class="inline-flex items-center px-2 py-1 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition duration-150 ease-in-out">Edit</a>
                <a href="#" class="inline-flex items-center px-2 py-1 bg-red-500 text-white text-xs font-medium rounded hover:bg-red-600 transition duration-150 ease-in-out ml-2">Delete</a>
            </td>
        </tr>
    `;
    document.getElementById('tableBody').insertAdjacentHTML('beforeend', newRow);
    closeFormModal();
}

// Event listener untuk pencarian
document.getElementById('search').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// Event listener untuk pengaturan jumlah entri
document.getElementById('entries').addEventListener('change', function() {
    let entriesToShow = parseInt(this.value);
    let rows = Array.from(document.querySelectorAll('#tableBody tr'));
    rows.forEach((row, index) => {
        row.style.display = index < entriesToShow ? '' : 'none';
    });
});

// Initialize to show the default number of entries
document.getElementById('entries').dispatchEvent(new Event('change'));

function uploadDokumen(input, notificationNumber) {
    if (!notificationNumber) {
        console.error('Notification number tidak valid');
        return;
    }

    const form = document.getElementById('uploadForm_' + notificationNumber);
    if (!form) {
        console.error('Form dengan ID uploadForm_' + notificationNumber + ' tidak ditemukan.');
        return;
    }

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Komentar SweetAlert untuk menampilkan notifikasi di sini
             Swal.fire({
                 title: 'Sukses!',
                 text: 'Dokumen berhasil diunggah.',
                 icon: 'success',
                 confirmButtonText: 'OK'
             });
            console.log('Dokumen berhasil diunggah.');
        } else {
            // Komentar SweetAlert untuk menampilkan notifikasi di sini
             Swal.fire({
                 title: 'Gagal!',
                 text: data.error || 'Gagal mengunggah dokumen.',
                 icon: 'error',
                 confirmButtonText: 'OK'
             });
            console.error('Gagal mengunggah dokumen:', data.error);
        }
    })
    .catch(error => {
        // Komentar SweetAlert untuk menampilkan notifikasi di sini
         Swal.fire({
             title: 'Gagal!',
             text: 'Terjadi kesalahan saat mengunggah dokumen.',
             icon: 'error',
             confirmButtonText: 'OK'
         });
        console.error('Error:', error);
    });
}


// Event listener untuk tombol upload dokumen
document.querySelectorAll('input[type="file"]').forEach(function(input) {
    input.addEventListener('change', function() {
        const notificationNumber = input.closest('div[data-notification-id]').getAttribute('data-notification-id');
        
        if (notificationNumber) {
            uploadDokumen(this, notificationNumber);
        } else {
            console.error('Notification number tidak ditemukan');
        }
    });
});


// Event listener untuk link "Lihat Dokumen"
document.querySelectorAll('.lihat-dokumen-link').forEach(function(link) {
    link.addEventListener('click', function(event) {
        event.preventDefault();  // Mencegah tindakan default dari link
        const abnormalityId = event.target.getAttribute('data-id');
        if (abnormalityId) {
            window.open(`/gambarteknik/${abnormalityId}/view`, '_blank');
        } else {
            console.error('ID not found');
        }
    });
});

// Fungsi untuk mengurutkan tabel berdasarkan tanggal input
function sortTableBy(sortType) {
    let rows = Array.from(document.querySelectorAll('#tableBody tr'));
    
    rows.sort((a, b) => {
        let dateA = new Date(a.querySelector('td:nth-child(3)').textContent); // Kolom tanggal input
        let dateB = new Date(b.querySelector('td:nth-child(3)').textContent);

        switch (sortType) {
            case 'latest':
                return dateB - dateA;
            case 'oldest':
                return dateA - dateB;
            default:
                return 0;
        }
    });

    rows.forEach(row => document.querySelector('#tableBody').appendChild(row));
}

document.getElementById('sortOrder').addEventListener('change', function() {
    sortTableBy(this.value);
});

// Inisialisasi untuk default sort berdasarkan "Terbaru"
document.getElementById('sortOrder').value = 'latest';
sortTableBy('latest');


document.getElementById('filterForm').addEventListener('submit', function(event) {
    // Cegah auto-submit pada perubahan dropdown, hanya kirim ketika form disubmit
    event.preventDefault();
    this.submit();
});