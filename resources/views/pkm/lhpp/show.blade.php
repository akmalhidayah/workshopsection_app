<x-document>
    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-4 lg:px-6">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4 border border-black">
                <!-- Bagian Header -->
                <div class="flex justify-between items-center mb-3">
                    <img src="{{ asset('images/logo-st.png') }}" alt="Logo Tonasa" class="h-16 w-16 object-contain">
                    <h2 class="font-semibold text-lg text-gray-800 leading-tight text-center flex-1">
                        <strong>Laporan Hasil Penyelesaian Pekerjaan (LHPP)</strong><br>
                        JASA PEKERJAAN FABRIKASI, KONSTRUKSI & MESIN
                    </h2>
                    <img src="{{ asset('images/pkm.png') }}" alt="Logo PKM" class="h-16 w-16 object-contain">
                </div>
                <!-- Bagian Informasi -->
                <div class="mb-4">
                    <table class="min-w-full border-collapse table-auto">
                        <tbody>
                            <tr>
                                <td class="pr-1 py-1 text-left font-semibold w-1/4">ORDER</td>
                                <td class="px-1 py-1 text-left w-3/4">: {{ $lhpp->notification_number }}</td>
                            </tr>
                            <tr>
                                <td class="pr-1 py-1 text-left font-semibold w-1/4">ORDER NUMBER</td>
                                <td class="px-1 py-1 text-left w-3/4">: {{ $lhpp->nomor_order }}</td>
                            </tr>
                            <tr>
                                <td class="pr-1 py-1 text-left font-semibold w-1/4">DESCRIPTION</td>
                                <td class="px-1 py-1 text-left w-3/4">: {{ $lhpp->description_notifikasi }}</td>
                            </tr>
                            <tr>
                                <td class="pr-1 py-1 text-left font-semibold w-1/4">PURCHASING ORDER</td>
                                <td class="px-1 py-1 text-left w-3/4">: {{ $lhpp->purchase_order_number }}</td>
                            </tr>
                            <tr>
                                <td class="pr-1 py-1 text-left font-semibold w-1/4">UNIT KERJA PEMINTA (USER)</td>
                                <td class="px-1 py-1 text-left w-3/4">: {{ $lhpp->unit_kerja }}</td>
                            </tr>
                            <tr>
                                <td class="pr-1 py-1 text-left font-semibold w-1/4">TANGGAL SELESAI PEKERJAAN</td>
                                <td class="px-1 py-1 text-left w-3/4">: {{ $lhpp->tanggal_selesai }}</td>
                            </tr>
                            <tr>
                                <td class="pr-1 py-1 text-left font-semibold w-1/4">WAKTU PENGERJAAN</td>
                                <td class="px-1 py-1 text-left w-3/4">: {{ $lhpp->waktu_pengerjaan }} Hari</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
<!-- Bagian Tabel Material -->
<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-black mb-6">
        <thead class="bg-amber-100">
            <tr>
                <th colspan="5" class="px-4 py-2 text-left border border-black">NO. A. ACTUAL PEMAKAIAN MATERIAL</th>
            </tr>
            <tr>
                <th class="px-4 py-2 text-left border border-black">No</th>
                <th class="px-4 py-2 text-left border border-black">Material Description</th> <!-- Tambahkan kolom baru -->
                <th class="px-4 py-2 text-left border border-black">Volume (Kg)</th>
                <th class="px-4 py-2 text-left border border-black">Harga Satuan (Rp)</th>
                <th class="px-4 py-2 text-left border border-black">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalMaterial = 0; @endphp
            @foreach($lhpp->material_description as $key => $desc)
                <tr>
                    <td class="px-4 py-2 border border-black">{{ $key + 1 }}</td>
                    <td class="px-4 py-2 border border-black">{{ $desc }}</td>
                    <td class="px-4 py-2 border border-black">{{ $lhpp->material_volume[$key] }}</td>
                    <td class="px-4 py-2 border border-black">{{ number_format($lhpp->material_harga_satuan[$key], 2, ',', '.') }}</td>
                    <td class="px-4 py-2 border border-black">{{ number_format($lhpp->material_jumlah[$key], 2, ',', '.') }}</td>
                </tr>
                @php $totalMaterial += $lhpp->material_jumlah[$key]; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="px-4 py-2 text-right font-bold border border-black">SUB TOTAL ( A )</td>
                <td colspan="2" class="px-4 py-2 border border-black">Rp {{ number_format($totalMaterial, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Bagian Tabel Consumable -->
    <table class="min-w-full bg-white border border-black mb-6">
        <thead class="bg-amber-100">
            <tr>
                <th colspan="5" class="px-4 py-2 text-left border border-black">NO. B. ACTUAL PEMAKAIAN CONSUMABLE</th>
            </tr>
            <tr>
                <th class="px-4 py-2 text-left border border-black">No</th>
                <th class="px-4 py-2 text-left border border-black">Consumable Description</th> <!-- Tambahkan kolom baru -->
                <th class="px-4 py-2 text-left border border-black">Volume (Jam/Kg)</th>
                <th class="px-4 py-2 text-left border border-black">Harga Satuan (Rp)</th>
                <th class="px-4 py-2 text-left border border-black">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalConsumable = 0; @endphp
            @foreach($lhpp->consumable_description as $key => $desc)
                <tr>
                    <td class="px-4 py-2 border border-black">{{ $key + 1 }}</td>
                    <td class="px-4 py-2 border border-black">{{ $desc }}</td>
                    <td class="px-4 py-2 border border-black">{{ $lhpp->consumable_volume[$key] }}</td>
                    <td class="px-4 py-2 border border-black">{{ number_format($lhpp->consumable_harga_satuan[$key], 2, ',', '.') }}</td>
                    <td class="px-4 py-2 border border-black">{{ number_format($lhpp->consumable_jumlah[$key], 2, ',', '.') }}</td>
                </tr>
                @php $totalConsumable += $lhpp->consumable_jumlah[$key]; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="px-4 py-2 text-right font-bold border border-black">SUB TOTAL ( B )</td>
                <td colspan="2" class="px-4 py-2 border border-black">Rp {{ number_format($totalConsumable, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Bagian Tabel Biaya Upah Kerja -->
    <table class="min-w-full bg-white border border-black">
        <thead class="bg-amber-100">
            <tr>
                <th colspan="5" class="px-4 py-2 text-left border border-black">NO. C. ACTUAL BIAYA UPAH KERJA</th>
            </tr>
            <tr>
                <th class="px-4 py-2 text-left border border-black">No</th>
                <th class="px-4 py-2 text-left border border-black">Upah Kerja Description</th> <!-- Tambahkan kolom baru -->
                <th class="px-4 py-2 text-left border border-black">Volume (Jam/Kg)</th>
                <th class="px-4 py-2 text-left border border-black">Harga Satuan (Rp)</th>
                <th class="px-4 py-2 text-left border border-black">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalUpah = 0; @endphp
            @foreach($lhpp->upah_description as $key => $desc)
                <tr>
                    <td class="px-4 py-2 border border-black">{{ $key + 1 }}</td>
                    <td class="px-4 py-2 border border-black">{{ $desc }}</td>
                    <td class="px-4 py-2 border border-black">{{ $lhpp->upah_volume[$key] }}</td>
                    <td class="px-4 py-2 border border-black">{{ number_format($lhpp->upah_harga_satuan[$key], 2, ',', '.') }}</td>
                    <td class="px-4 py-2 border border-black">{{ number_format($lhpp->upah_jumlah[$key], 2, ',', '.') }}</td>
                </tr>
                @php $totalUpah += $lhpp->upah_jumlah[$key]; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="px-4 py-2 text-right font-bold border border-black">SUB TOTAL ( C )</td>
                <td colspan="2" class="px-4 py-2 border border-black">Rp {{ number_format($totalUpah, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3" class="px-4 py-2 text-right font-bold border border-black">TOTAL ACTUAL BIAYA ( A + B + C )</td>
                <td colspan="2" class="px-4 py-2 border border-black">Rp {{ number_format($totalMaterial + $totalConsumable + $totalUpah, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</div>
   <!-- Tabel Hasil Quality Control dan Unit -->
<div class="mt-8 border border-black">
    <table class="w-full table-auto text-left border-collapse">
        <thead class="font-bold">
            <tr>
                <th class="border border-black p-2">HASIL QUALITY CONTROL</th>
                <th class="border border-black p-2">UNIT KERJA PEMINTA</th>
                <th class="border border-black p-2">UNIT WORKSHOP</th>
                <th class="border border-black p-2">PT. PKM</th>
            </tr>
        </thead>
        <tbody>
            <tr>
            <td class="border border-black p-2">
            <div class="flex justify-between">
                <div>APPROVE</div>
                <div>
                    <!-- Jika status approve, tampilkan kotak tercentang -->
                    @if($lhpp->status_approve == 'Approved')
                        <input type="checkbox" checked disabled class="form-checkbox h-5 w-5 appearance-none checked:bg-black bg-white border-black">
                    @else
                        <input type="checkbox" disabled class="form-checkbox h-5 w-5 appearance-none bg-white border-black">
                    @endif
                </div>
            </div>
            <div class="border-t border-black mt-2"></div> <!-- Garis antara approve dan reject -->
            <div class="flex justify-between mt-2">
                <div>REJECT</div>
                <div>
                    <!-- Jika status reject, tampilkan kotak tercentang -->
                    @if($lhpp->status_approve == 'Rejected')
                        <input type="checkbox" checked disabled class="form-checkbox h-5 w-5 appearance-none checked:bg-black bg-white border-black">
                    @else
                        <input type="checkbox" disabled class="form-checkbox h-5 w-5 appearance-none bg-white border-black">
                    @endif
                </div>
            </div>
        </td>
        <!-- Kolom Tanda Tangan dan Nama untuk Manager User -->
        <td class="border border-black text-center p-2">
                @if(!is_null($lhpp->manager_signature_requesting))
                    <div class="flex flex-col items-center">
                        <img src="{{ $lhpp->manager_signature_requesting }}" alt="Tanda Tangan Manager Workshop" class="w-32 h-auto mb-2">
                        <div class="text-sm">
                            {{ \App\Models\User::find($lhpp->manager_signature_requesting_user_id)->name ?? 'HERWANTO.S' }} <br>
                        {{ \App\Models\User::find($lhpp->manager_signature_requesting_user_id)->seksi ?? '-' }}
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center">
                        <span>Manager User</span>
                    </div>
                @endif
            </td>

            <!-- Kolom Tanda Tangan dan Nama untuk Manager Workshop -->

            <td class="border border-black text-center p-2">
                @if(!is_null($lhpp->manager_signature))
                    <div class="flex flex-col items-center">
                        <img src="{{ $lhpp->manager_signature }}" alt="Tanda Tangan Manager" class="w-32 h-auto mb-2">
                        <div class="text-sm">
                            {{ \App\Models\User::find($lhpp->manager_signature_user_id)->name ?? '(Manager User)' }} <br>
                        {{ \App\Models\User::find($lhpp->manager_signature_user_id)->seksi ?? '-' }}
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center">
                        <span>Herwanto S</span>
                    </div>
                @endif
            </td>

            <!-- Kolom Tanda Tangan dan Nama untuk Manager PKM -->
            <td class="border border-black text-center p-2">
                @if(!is_null($lhpp->manager_pkm_signature))
                    <div class="flex flex-col items-center">
                        <img src="{{ $lhpp->manager_pkm_signature }}" alt="Tanda Tangan Manager PKM" class="w-32 h-auto mb-2">
                        <div class="text-sm">
                            {{ \App\Models\User::find($lhpp->manager_pkm_signature_user_id)->name ?? 'MANAGER PKM' }} <br>
                            {{ \App\Models\User::find($lhpp->manager_pkm_signature_user_id)->seksi ?? '-' }}
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center">
                        <span>MANAGER PT. Prima Karya Manunggal</span>
                    </div>
                @endif
            </td>
            </tr>
        </tbody>
    </table>
</div>
<!-- Bagian Catatan dan Tindakan -->
<div class="mt-8">
    <div class="grid grid-cols-2 gap-6">
        <!-- Bagian Catatan User -->
        <div class="border border-black px-2 py-4">
            <p class="font-semibold">Catatan User:</p>
            @if(!empty($lhpp->requesting_notes))
                @foreach(json_decode($lhpp->requesting_notes, true) as $note)
                    <p>{{ $loop->iteration }}. {{ $note['note'] }}</p>
                    @php
                        $user = \App\Models\User::find($note['user_id']);
                    @endphp
                    <small>Ditambahkan oleh: {{ $user ? $user->jabatan : 'Pengguna Tidak Dikenal' }}</small>
                @endforeach
            @else
                <p>-</p> <!-- Jika tidak ada catatan -->
            @endif
        </div>

        <!-- Bagian Catatan Unit Workshop -->
        <div class="border border-black px-2 py-4">
            <p class="font-semibold">Catatan Unit Of Workshop:</p>
            @if(!empty($lhpp->controlling_notes))
                @foreach(json_decode($lhpp->controlling_notes, true) as $note)
                    <p>{{ $loop->iteration }}. {{ $note['note'] }}</p>
                    @php
                        $user = \App\Models\User::find($note['user_id']);
                    @endphp
                    <small>Ditambahkan oleh: {{ $user ? $user->jabatan : 'Pengguna Tidak Dikenal' }}</small>
                @endforeach
            @else
                <p>-</p> <!-- Jika tidak ada catatan -->
            @endif
        </div>
    </div>
</div>

<!-- Dokumentasi Pekerjaan -->
<div class="py-4">
        <div class="max-w-full mx-auto sm:px-4 lg:px-6">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4 border border-black">
                <h3 class="font-semibold text-lg text-gray-800 mb-2">Dokumentasi Pekerjaan Selesai</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                    @foreach($lhpp->images as $image)
                        <div class="border border-gray-300 p-2 rounded-md flex flex-col items-center w-full">
                            <img src="{{ asset('storage/' . $image['path']) }}" 
                                 alt="Dokumentasi LHPP" 
                                 class="w-48 h-48 object-cover rounded-md">
                            <p class="text-xs text-gray-600 mt-1 text-center">
                                {{ $image['description'] ?? 'Tanpa Keterangan' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    
</x-document>