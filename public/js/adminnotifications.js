// public/js/notifications.js

document.getElementById('search').addEventListener('input', function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

document.querySelectorAll('.priority-select').forEach(select => {
    updatePriorityColor(select); // Set the initial color

    select.addEventListener('change', function () {
        updatePriorityColor(select);
    });
});

function updatePriorityColor(select) {
    select.classList.remove('bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500', 'text-white');
    switch (select.value) {
        case 'Urgently':
            select.classList.add('bg-red-500', 'text-white');
            break;
        case 'Hard':
            select.classList.add('bg-orange-500', 'text-white');
            break;
        case 'Medium':
            select.classList.add('bg-yellow-500', 'text-white');
            break;
        case 'Low':
            select.classList.add('bg-green-500', 'text-white');
            break;
        default:
            select.classList.add('bg-gray-200');
    }
}
