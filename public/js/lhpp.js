      // Function to add material row
      document.getElementById('add-material-row').addEventListener('click', function () {
        let materialSection = document.getElementById('material-section');
        let newRow = `
            <div class="grid grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Actual Pemakaian Material</label>
                    <input type="text" name="material_description[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Volume (Kg)</label>
                    <input type="text" name="material_volume[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Harga Satuan (Rp)</label>
                    <input type="text" name="material_harga_satuan[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                    <input type="text" name="material_jumlah[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>
        `;
        materialSection.insertAdjacentHTML('beforeend', newRow);
    });

    // Function to add consumable row
    document.getElementById('add-consumable-row').addEventListener('click', function () {
        let consumableSection = document.getElementById('consumable-section');
        let newRow = `
            <div class="grid grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Actual Pemakaian Consumable</label>
                    <input type="text" name="consumable_description[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Volume (Jam/Kg)</label>
                    <input type="text" name="consumable_volume[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Harga Satuan (Rp)</label>
                    <input type="text" name="consumable_harga_satuan[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                    <input type="text" name="consumable_jumlah[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>
        `;
        consumableSection.insertAdjacentHTML('beforeend', newRow);
    });

    // Function to add upah row
    document.getElementById('add-upah-row').addEventListener('click', function () {
        let upahSection = document.getElementById('upah-section');
        let newRow = `
            <div class="grid grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Actual Biaya Upah Kerja</label>
                    <input type="text" name="upah_description[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Volume (Jam/Kg)</label>
                    <input type="text" name="upah_volume[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Harga Satuan (Rp)</label>
                    <input type="text" name="upah_harga_satuan[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                    <input type="text" name="upah_jumlah[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>
        `;
        upahSection.insertAdjacentHTML('beforeend', newRow);
    });
    // Function to calculate subtotal for materials
function calculateMaterialSubtotal() {
    let total = 0;
    document.querySelectorAll('input[name="material_jumlah[]"]').forEach(function (input) {
        let value = parseFloat(input.value) || 0;
        total += value;
    });
    document.getElementById('material_subtotal').value = total;
    calculateTotal();
}

// Function to calculate subtotal for consumables
function calculateConsumableSubtotal() {
    let total = 0;
    document.querySelectorAll('input[name="consumable_jumlah[]"]').forEach(function (input) {
        let value = parseFloat(input.value) || 0;
        total += value;
    });
    document.getElementById('consumable_subtotal').value = total;
    calculateTotal();
}

// Function to calculate subtotal for upah kerja
function calculateUpahSubtotal() {
    let total = 0;
    document.querySelectorAll('input[name="upah_jumlah[]"]').forEach(function (input) {
        let value = parseFloat(input.value) || 0;
        total += value;
    });
    document.getElementById('upah_subtotal').value = total;
    calculateTotal();
}

// Function to calculate total actual biaya
function calculateTotal() {
    let materialSubtotal = parseFloat(document.getElementById('material_subtotal').value) || 0;
    let consumableSubtotal = parseFloat(document.getElementById('consumable_subtotal').value) || 0;
    let upahSubtotal = parseFloat(document.getElementById('upah_subtotal').value) || 0;
    let total = materialSubtotal + consumableSubtotal + upahSubtotal;
    document.getElementById('total_biaya').value = total;
}

// Adding event listeners to recalculate when values change
document.addEventListener('input', function (e) {
    if (e.target.matches('input[name="material_jumlah[]"]')) {
        calculateMaterialSubtotal();
    } else if (e.target.matches('input[name="consumable_jumlah[]"]')) {
        calculateConsumableSubtotal();
    } else if (e.target.matches('input[name="upah_jumlah[]"]')) {
        calculateUpahSubtotal();
    }
});
document.getElementById('notifikasi').addEventListener('change', function () {
    let notificationNumber = this.value;
    
    if (notificationNumber) {
        // Lakukan permintaan AJAX untuk mendapatkan data Purchase Order
        fetch(`/admin/lhpp/get-purchase-order/${notificationNumber}`)
            .then(response => response.json())
            .then(data => {
                // Isi field Purchasing Order secara otomatis
                document.getElementById('purchase_order_number').value = data.purchase_order_number;
            })
            .catch(error => {
                console.error('Error:', error);
            });
    } else {
        document.getElementById('purchase_order_number').value = '';
    }
});
