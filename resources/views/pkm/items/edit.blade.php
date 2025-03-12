<x-pkm-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Item Kebutuhan Kerjaan') }}
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
                <form action="{{ route('pkm.items.update', $item->notification_number) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Nomor Notification -->
                    <div class="mb-4">
                        <label for="nomor_order" class="block text-sm font-medium text-gray-700">Nomor Notification</label>
                        <input type="text" name="nomor_order" id="nomor_order" value="{{ $item->notification_number }}" readonly
                               class="mt-1 p-2 w-full border rounded-lg bg-gray-200">
                    </div>

                    <!-- Deskripsi Pekerjaan -->
                    <div class="mb-4">
                        <label for="deskripsi_pekerjaan" class="block text-sm font-medium text-gray-700">Deskripsi Pekerjaan</label>
                        <input type="text" name="deskripsi_pekerjaan" id="deskripsi_pekerjaan" value="{{ $item->deskripsi_pekerjaan }}" readonly
                               class="mt-1 p-2 w-full border rounded-lg bg-gray-200">
                    </div>

                    <!-- Total HPP -->
                    <div class="mb-4">
                        <label for="total_hpp" class="block text-sm font-medium text-gray-700">Total HPP</label>
                        <input type="number" name="total_hpp" id="total_hpp" value="{{ $item->total_hpp }}" readonly
                               class="mt-1 p-2 w-full border rounded-lg bg-gray-200">
                    </div>

                    <!-- Kebutuhan Material & Jasa -->
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Kebutuhan Material & Jasa</h3>
                    <div id="material-container">
                        @foreach ($item->materials as $index => $material)
                            <div class="material-item flex space-x-2 mb-2">
                                <input type="text" name="material[]" value="{{ $material }}"
                                       class="p-2 w-2/3 border rounded-lg">
                                <input type="number" name="harga[]" value="{{ $item->harga[$index] ?? 0 }}" min="0"
                                       class="p-2 w-1/3 border rounded-lg harga-input" oninput="hitungTotal()">
                                <button type="button" class="bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-700 remove-material"
                                        onclick="hapusMaterial(this)">
                                    ✖
                                </button>
                            </div>
                        @endforeach
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
                        <input type="number" name="total" id="total" value="{{ $item->total }}" readonly
                               class="mt-1 p-2 w-full border rounded-lg bg-gray-200">
                    </div>

                    <!-- Total Margin -->
                    <div class="mb-4">
                        <label for="total_margin" class="block text-sm font-medium text-gray-700">Total Margin</label>
                        <input type="number" name="total_margin" id="total_margin" value="{{ $item->total_margin }}" readonly
                               class="mt-1 p-2 w-full border rounded-lg bg-gray-200">
                    </div>

                    <!-- Tombol Update -->
                    <div class="flex justify-end">
                        <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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
            document.getElementById('total_margin').value = totalHpp - total;
        }

        document.addEventListener('DOMContentLoaded', hitungTotal);
    </script>
    <!-- Tambahkan SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Menampilkan notifikasi jika ada session 'success'
    @if(session('success'))
        Swal.fire({
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    @endif

    // Konfirmasi sebelum update
    document.querySelector("form").addEventListener("submit", function(event) {
        event.preventDefault();
        let form = this;

        Swal.fire({
            title: 'Konfirmasi Perubahan',
            text: "Apakah Anda yakin ingin memperbarui item ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Konfirmasi sebelum delete
    function confirmDelete(form) {
        Swal.fire({
            title: 'Hapus Item?',
            text: "Item akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

        return false;
    }
</script>

</x-pkm-layout>
