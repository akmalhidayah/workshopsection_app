<x-admin-layout>
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Tombol Kembali -->
            <a href="{{ route('admin.inputhpp.index') }}"
               class="inline-flex items-center mb-4 bg-blue-500 text-white px-4 py-2 rounded-md shadow hover:bg-blue-600 transition">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>

            <div class="bg-white shadow-lg rounded-lg p-6">
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight mb-6 border-b pb-3">
                    Form Input HPP di atas 250 Juta
                </h2>

                <!-- Form Input -->
                <form action="{{ route('admin.inputhpp.store_hpp2') }}" method="POST">
                    @csrf
                    <input type="hidden" name="source_form" value="{{ $source_form ?? 'createhpp2' }}">
                    <input type="hidden" name="status" value="draft">

                    <!-- Bagian 1: Informasi Dasar -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nomor Order</label>
                            <select name="notification_number" id="notifikasi"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" required>
                                <option value="" disabled selected>Pilih Nomor Order</option>
                                @foreach($notifications as $notification)
                                    <option 
                                        value="{{ $notification->notification_number }}"
                                        data-job="{{ $notification->job_name }}"
                                        data-unit="{{ $notification->seksi }}">
                                        {{ $notification->notification_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cost Centre</label>
                            <input type="text" name="cost_centre"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>

                        <!-- Deskripsi -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="description" id="deskripsi" rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm bg-gray-50" required></textarea>
                        </div>
                    </div>

                    <!-- Bagian 2: Unit Kerja -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit Kerja Peminta</label>
                            <input type="text" id="unit_kerja_peminta" name="requesting_unit"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm bg-gray-50">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit Kerja Pengendali</label>
                            <input type="text" name="controlling_unit" value="Section of Workshop Machine" readonly
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                    </div>

                    <!-- Outline Agreement -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Outline Agreement (OA)</label>
                        <input type="text" id="outline_agreement" name="outline_agreement"
                               value="{{ $currentOA->outline_agreement ?? '' }}" readonly
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 text-sm">
                        <p class="text-xs text-gray-600 mt-2">
                            Periode:
                            {{ $currentOA ? \Carbon\Carbon::parse($currentOA->periode_kontrak_start)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($currentOA->periode_kontrak_end)->format('d/m/Y') : '-' }}
                        </p>
                    </div>

                    <!-- Tambah Uraian Pekerjaan -->
                    <div class="mt-8 flex gap-3 items-center">
                        <button type="button" id="tambah-pekerjaan-btn"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm shadow flex items-center gap-2">
                            <i class="fas fa-plus"></i> Tambah Uraian Pekerjaan
                        </button>
                    </div>

                    <!--
                      Catatan:
                      - Script partial akan membangkitkan field:
                        uraian_pekerjaan[g]
                        jenis_item[g][i]    -> material|consumable|upah
                        nama_item[g][i]     -> NAMA BARANG/JASA
                        qty[g][i], satuan[g][i], harga_satuan[g][i], harga_total[g][i]
                        keterangan[g][i]    -> catatan item (opsional)
                    -->
                    <div id="pekerjaan-container" class="mt-6 space-y-6"></div>

                    <!-- Total Keseluruhan -->
                    <div class="mt-6 border-t pt-4">
                        <label class="block text-sm font-medium text-gray-700">Total Keseluruhan (Rp)</label>
                        <input type="number" name="total_amount" id="total_keseluruhan"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 text-sm" readonly>
                    </div>

                    <!-- Keterangan Umum (masuk ke requesting_notes) -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Keterangan Umum</label>
                        <textarea name="requesting_notes" id="requesting_notes" rows="3"
                                  placeholder="Tambahkan catatan/keterangan umum terkait pekerjaan..."
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm bg-gray-50"></textarea>
                    </div>

                    <!-- Submit -->
                    <div class="mt-6">
                                             <button type="submit" name="action" value="draft"
    class="bg-gray-500 text-white px-3 py-1 rounded">
    Simpan Draft
</button>

<button type="submit" name="action" value="submit"
    class="bg-indigo-600 text-white px-3 py-1 rounded">
    Submit
</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Pastikan partial JS yang dipanggil SUDAH versi terbaru (pakai nama_item[g][]) --}}
    @include('admin.inputhpp.partials._hpp_form_script')

    @if(session('error'))
        <script>
            Swal.fire({ icon: 'error', title: 'Oops...', text: @json(session('error')) });
        </script>
    @endif

    @if(session('success'))
        <script>
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: @json(session('success')) });
        </script>
    @endif
</x-admin-layout>
