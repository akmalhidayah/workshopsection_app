<x-document>
    <div class="py-6">
        <div class="max-w-5xl mx-auto bg-white rounded-lg p-8">
            
            <!-- Bagian Header -->
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                <img src="{{ asset('images/logo-st.png') }}" alt="Logo Tonasa" class="h-16 w-16 object-contain"> <!-- Ukuran dikecilkan -->
                <h2 class="font-bold text-xl text-gray-800 text-center flex-1 leading-tight">
                    Detail Item Kebutuhan Kerjaan <br>
                    <span class="text-sm font-medium text-gray-600">Jasa Pekerjaan Fabrikasi, Konstruksi & Mesin</span>
                </h2>
                <img src="{{ asset('images/pkm.png') }}" alt="Logo PKM" class="h-16 w-16 object-contain"> <!-- Ukuran dikecilkan -->
            </div>

            <!-- Informasi Order -->
            <div class="mb-6 pb-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Informasi Order</h3>
                <div class="grid grid-cols-2 gap-4 text-gray-900">
                    <p><strong class="text-orange-600">Nomor Order:</strong> {{ $item->nomor_order }}</p>
                    <p><strong class="text-blue-600">Deskripsi Pekerjaan:</strong> {{ $item->deskripsi_pekerjaan }}</p>
                    <p><strong class="text-green-600">Total HPP:</strong> Rp {{ number_format($item->total_hpp, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Tabel Kebutuhan Material & Jasa -->
            <div class="mb-6 pb-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Kebutuhan Material & Jasa</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 shadow-sm rounded-lg">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-2 border border-gray-300">No</th>
                                <th class="px-4 py-2 border border-gray-300">Material/Jasa</th>
                                <th class="px-4 py-2 border border-gray-300">Harga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($item->materials as $key => $material)
                                <tr>
                                    <td class="px-4 py-3 border border-gray-300 text-center">{{ $key + 1 }}</td>
                                    <td class="px-4 py-3 border border-gray-300">{{ $material->nama_material }}</td>
                                    <td class="px-4 py-3 border border-gray-300 text-right">Rp {{ number_format($material->harga, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Total Harga, Margin, dan Persentase Margin -->
            <div class="grid grid-cols-3 gap-4 text-lg font-semibold text-center">
                <div class="px-6 py-3 rounded-lg">
                    <p class="text-gray-600">Total Harga</p>
                    <p class="text-2xl text-blue-600">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</p>
                </div>
                <div class="px-6 py-3 rounded-lg">
                    <p class="text-gray-600">Total Margin</p>
                    <p class="text-2xl text-green-600">Rp {{ number_format($item->total_margin, 0, ',', '.') }}</p>
                </div>
                <div class="px-6 py-3 rounded-lg">
                    <p class="text-gray-600">Persentase Margin</p>
                    <p class="text-2xl text-purple-600">{{ number_format(($item->total_margin / $item->total_harga) * 100, 2) }}%</p>
                </div>
            </div>

            <!-- Tombol Kembali -->
            <div class="mt-6 text-center">
                <a href="{{ route('pkm.items.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-all">
                    Kembali
                </a>
            </div>

        </div>
    </div>
</x-document>
