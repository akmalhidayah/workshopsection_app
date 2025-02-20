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