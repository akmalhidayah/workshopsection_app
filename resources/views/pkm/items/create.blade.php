<x-pkm-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Item Kebutuhan Kerjaan') }}
        </h2>
    </x-slot>

    <!-- Tombol Kembali -->
    <div class="mt-6">
        <a href="{{ route('pkm.items.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            Kembali
        </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6">
                <form action="{{ route('pkm.items.store') }}" method="POST">
                    @csrf

                    <!-- Pilih Nomor Order -->
                    <div class="mb-4">
                        <label for="nomor_order" class="block text-sm font-medium text-gray-700">Nomor Order</label>
                        <select name="notification_number" id="nomor_order" required
                                class="mt-1 p-2 w-full border rounded-lg">
                            <option value="">Pilih Nomor Order</option>
                            @foreach($notifications as $notification)
                                <option value="{{ $notification->notification_number }}">
                                    {{ $notification->notification_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Deskripsi Pekerjaan -->
                    <div class="mb-4">
                        <label for="deskripsi_pekerjaan" class="block text-sm font-medium text-gray-700">Deskripsi Pekerjaan</label>
                        <input type="text" name="deskripsi_pekerjaan" id="deskripsi_pekerjaan" readonly
                               class="mt-1 p-2 w-full border rounded-lg bg-gray-200">
                    </div>

                    <!-- Total HPP -->
                    <div class="mb-4">
                        <label for="total_hpp" class="block text-sm font-medium text-gray-700">Total HPP</label>
                        <input type="number" name="total_hpp" id="total_hpp" readonly
                               class="mt-1 p-2 w-full border rounded-lg bg-gray-200">
                    </div>

                    <!-- Kebutuhan Material & Jasa -->
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Kebutuhan Material & Jasa</h3>
                    <div id="material-container">
                        <div class="material-item flex space-x-2 mb-2">
                            <input type="text" name="material[]" placeholder="Masukkan nama material/jasa"
                                   class="p-2 w-2/3 border rounded-lg">
                            <input type="number" name="harga[]" placeholder="Masukkan harga" min="0"
                                   class="p-2 w-1/3 border rounded-lg harga-input" oninput="hitungTotal()">
                            <button type="button" class="bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-700 remove-material hidden">
                                ✖
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Tambah Material -->
                    <div class="mb-4">
                        <button type="button" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-700"
                                onclick="tambahMaterial()">
                            + Tambah Material/Jasa
                        </button>
                    </div>

                    <!-- Total -->
                    <div class="mb-4">
                        <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
                        <input type="number" name="total" id="total" readonly
                               class="mt-1 p-2 w-full border rounded-lg bg-gray-200">
                    </div>

                    <!-- Total Margin -->
                    <div class="mb-4">
                        <label for="total_margin" class="block text-sm font-medium text-gray-700">Total Margin</label>
                        <input type="number" name="total_margin" id="total_margin" readonly
                               class="mt-1 p-2 w-full border rounded-lg bg-gray-200">
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="flex justify-end">
                        <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Event listener untuk mengisi otomatis deskripsi pekerjaan dan total HPP
        document.getElementById('nomor_order').addEventListener('change', function() {
            let notificationNumber = this.value;
            if (notificationNumber) {
                fetch(`{{ route('getItemData', '') }}/${notificationNumber}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Data tidak ditemukan');
                        }
                        return response.json();
                    })
                    .then(data => {
                        document.getElementById('deskripsi_pekerjaan').value = data.deskripsi_pekerjaan || 'Tidak tersedia';
                        document.getElementById('total_hpp').value = data.total_hpp || 0;
                    })
                    .catch(error => console.error('Error fetching data:', error));
            }
        });

        function tambahMaterial() {
            let container = document.getElementById('material-container');
            let div = document.createElement('div');
            div.classList.add('material-item', 'flex', 'space-x-2', 'mb-2');

            div.innerHTML = `
                <input type="text" name="material[]" placeholder="Masukkan nama material/jasa"
                       class="p-2 w-2/3 border rounded-lg">
                <input type="number" name="harga[]" placeholder="Masukkan harga" min="0"
                       class="p-2 w-1/3 border rounded-lg harga-input" oninput="hitungTotal()">
                <button type="button" class="bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-700 remove-material"
                        onclick="hapusMaterial(this)">
                    ✖
                </button>
            `;

            container.appendChild(div);
        }

        function hapusMaterial(button) {
            button.parentElement.remove();
            hitungTotal();
        }

        function hitungTotal() {
    let hargaInputs = document.querySelectorAll('.harga-input');
    let total = 0;

    hargaInputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    document.getElementById('total').value = total;

    let totalHpp = parseFloat(document.getElementById('total_hpp').value) || 0;

    // Perbaiki rumus total_margin
    let totalMargin = totalHpp - total;

    document.getElementById('total_margin').value = totalMargin;

    // Debugging log untuk memastikan nilai yang dihitung benar
    console.log("Total:", total, "Total HPP:", totalHpp, "Total Margin:", totalMargin);
}
document.querySelector("form").addEventListener("submit", function(event) {
    // Daftar field yang wajib diisi sesuai validasi di controller
    let requiredFields = [
        { id: "notifikasi", message: "Nomor Order harus dipilih!" },
        { id: "purchase_order_number", message: "Purchasing Order harus diisi!" },
        { id: "unit_kerja", message: "Unit Kerja Peminta harus diisi!" },
        { id: "tanggal_selesai", message: "Tanggal Selesai Pekerjaan harus diisi!" },
        { id: "waktu_pengerjaan", message: "Waktu Pengerjaan harus diisi!" },
        { id: "kontrak_pkm", message: "Kontrak PKM harus dipilih!" }
    ];

    let isValid = true;
    let firstErrorField = null;

    requiredFields.forEach(field => {
        let element = document.getElementById(field.id);
        if (!element || element.value.trim() === "") {
            isValid = false;
            if (!firstErrorField) firstErrorField = element;
            
            // Tampilkan pesan error menggunakan SweetAlert2
            Swal.fire({
                title: "Gagal!",
                text: field.message,
                icon: "error",
                confirmButtonText: "OK"
            });

            return;
        }
    });

    // Jika ada field wajib yang kosong, hentikan form submission
    if (!isValid) {
        event.preventDefault();
        if (firstErrorField) firstErrorField.focus();
    }
});
    </script>
</x-pkm-layout>
