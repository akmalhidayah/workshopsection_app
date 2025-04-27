function openForm() {
    document.getElementById('dataForm').classList.remove('hidden');
}

function closeForm() {
    document.getElementById('dataForm').classList.add('hidden');
}

function openEditForm(notification) {
    document.getElementById('editForm').classList.remove('hidden');

    // Set form action dynamically
    const form = document.getElementById('editNotificationForm');
    form.action = `/notifikasi/${notification.id}`;

    // Populate form with existing data
    document.getElementById('editNotifikasiNo').value = notification.notification_number;
    document.getElementById('editNamaPekerjaan').value = notification.job_name;
    document.getElementById('editUnitKerja').value = notification.unit_work;
    document.getElementById('editPriority').value = notification.unit_work;
    document.getElementById('editInputDate').value = notification.input_date;
}

function closeEditForm() {
    document.getElementById('editForm').classList.add('hidden');
}

function confirmDelete() {
    return confirm('Apakah Anda yakin ingin menghapus notifikasi ini beserta semua data terkait?');
}


document.getElementById('search').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

document.getElementById('entries').addEventListener('change', function() {
    let entriesToShow = parseInt(this.value);
    let rows = Array.from(document.querySelectorAll('#tableBody tr'));
    rows.forEach((row, index) => {
        row.style.display = index < entriesToShow ? '' : 'none';
    });
});

// Initialize to show the default number of entries
document.getElementById('entries').dispatchEvent(new Event('change'));

function sortTableBy(sortType) {
    let rows = Array.from(document.querySelectorAll('#tableBody tr'));
    
    rows.sort((a, b) => {
        let dateA = new Date(a.querySelector('td:nth-child(5)').textContent);
        let dateB = new Date(b.querySelector('td:nth-child(5)').textContent);
        let priorityA = a.querySelector('td:nth-child(4)').textContent;
        let priorityB = b.querySelector('td:nth-child(4)').textContent;

        switch (sortType) {
            case 'latest':
                return dateB - dateA;
            case 'oldest':
                return dateA - dateB;
            case 'priority-highest':
                return priorityA.localeCompare(priorityB);
            case 'priority-lowest':
                return priorityB.localeCompare(priorityA);
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

    document.addEventListener('DOMContentLoaded', function () {
        $('#unitKerja').select2({
            placeholder: 'Cari Unit Kerja...',
            allowClear: true,
            width: '100%',
            dropdownCssClass: "bg-gray-900 text-white"
        });
    });