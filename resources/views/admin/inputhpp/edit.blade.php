<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tombol Kembali -->
            <a href="{{ route('admin.inputhpp.index') }}" class="inline-block mb-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="font-semibold text-3xl text-gray-800 leading-tight">Edit Form HPP</h2>

                <!-- Form Edit -->
                <form action="{{ route('admin.inputhpp.update', $hpp->notification_number) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <!-- Bagian Notifikasi dan Deskripsi -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="notification_number" class="block text-sm font-medium text-gray-700">Nomor Order</label>
                            <input type="text" id="notification_number" name="notification_number" value="{{ $hpp->notification_number }}" readonly
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-100 focus:outline-none sm:text-sm">
                        </div>
                        <div>
                            <label for="cost_centre" class="block text-sm font-medium text-gray-700">Cost Centre</label>
                            <input type="text" id="cost_centre" name="cost_centre" value="{{ $hpp->cost_centre }}"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea id="description" name="description" rows="3"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ $hpp->description }}</textarea>
                        </div>
                    </div>

                    <!-- Bagian Rencana Pemakaian, Target Penyelesaian, Unit Kerja -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="requesting_unit" class="block text-sm font-medium text-gray-700">Unit Kerja Peminta</label>
                            <input type="text" id="requesting_unit" name="requesting_unit" value="{{ $hpp->requesting_unit }}"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="controlling_unit" class="block text-sm font-medium text-gray-700">Unit Kerja Pengendali</label>
                            <input type="text" id="controlling_unit" name="controlling_unit" value="{{ $hpp->controlling_unit }}"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" readonly>
                        </div>
                    </div>

                    <!-- Bagian Outline Agreement -->
                    <div class="mt-6">
                        <label for="outline_agreement" class="block text-sm font-medium text-gray-700">Outline Agreement (OA)</label>
                        <input type="text" id="outline_agreement" name="outline_agreement" value="{{ $hpp->outline_agreement }}"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" readonly>
                    </div>

                    <!-- Bagian Uraian Pekerjaan -->
                    <div id="uraian-pekerjaan-container" class="mt-6">
                        @foreach ($hpp->uraian_pekerjaan as $index => $uraian)
                            <div class="uraian-group">
                                <label class="font-semibold text-lg text-gray-700">Uraian Pekerjaan {{ $index + 1 }}</label>
                                <div class="mt-4">
                                    <input type="text" name="uraian_pekerjaan[]" value="{{ $uraian }}"
                                        class="col-span-4 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <!-- Sub-Uraian Pekerjaan -->
                                <div class="sub-uraian-container mt-6">
                                    <label class="block text-lg font-medium text-gray-700">Sub Uraian Pekerjaan</label>
                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-2 mt-1">
                                        <input type="text" placeholder="Jenis Material" name="jenis_material[]" value="{{ $hpp->jenis_material[$index] ?? '' }}"
                                            class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="text" placeholder="Qty" name="qty[]" value="{{ $hpp->qty[$index] ?? '' }}"
                                            class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="text" placeholder="Satuan" name="satuan[]" value="{{ $hpp->satuan[$index] ?? '' }}"
                                            class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="number" placeholder="Volume Satuan" name="volume_satuan[]" value="{{ $hpp->volume_satuan[$index] ?? '' }}"
                                            class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <input type="number" placeholder="Jumlah Volume" name="jumlah_volume_satuan[]" value="{{ $hpp->jumlah_volume_satuan[$index] ?? '' }}"
                                            class="col-span-1 block w-full border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Field Total Keseluruhan -->
                    <div class="mt-6">
                        <label for="total_amount" class="block text-sm font-medium text-gray-700">Total Keseluruhan</label>
                        <input type="number" id="total_amount" name="total_amount" value="{{ $hpp->total_amount }}"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-500 text-base font-medium text-white hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:w-auto sm:text-sm">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
