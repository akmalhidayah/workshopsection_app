<x-document>
    <div class="py-12">
    <div class="w-full max-w-none">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Logo dan Header -->
                <div class="flex justify-between items-center mb-4">
                    <img src="{{ asset('images/logo-sig.png') }}" alt="Logo SIG" class="h-28">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center"><strong>HARGA PERKIRAAN PERANCANG (HPP)</strong><br>JASA PEKERJAAN FABRIKASI, KONSTRUKSI & MESIN</h2>
                    <img src="{{ asset('images/logo-st.png') }}" alt="Logo Tonasa" class="h-20">
                </div>
          <!-- Informasi Notifikasi dan Detail Lainnya -->
            <div class="border border-black mb-0">
                <div class="grid grid-cols-4 gap-0">
                    <!-- Bagian Detail -->
                    <div class="col-span-3 text-left border-r border-black p-2">
                        <div class="grid grid-cols-3 gap-y-1">
                            <div class="col-span-1 text-sm font-medium">ORDER NO</div>
                            <div class="col-span-2 text-sm">: {{ $hpp->notification_number }}</div>

                            <div class="col-span-1 text-sm font-medium">DESKRIPSI</div>
                            <div class="col-span-2 text-sm">: {{ $hpp->description }}</div>

                            <div class="col-span-1 text-sm font-medium">COST CENTRE</div>
                            <div class="col-span-2 text-sm">: {{ $hpp->cost_centre }}</div>

                            <div class="col-span-1 text-sm font-medium">RENCANA PEMAKAIAN</div>
                            <div class="col-span-2 text-sm">: {{ $hpp->notification->usage_plan_date ?? '-' }}</div>

                            <div class="col-span-1 text-sm font-medium">UNIT KERJA PEMINTA</div>
                            <div class="col-span-2 text-sm">: {{ $hpp->requesting_unit }}</div>

                            <div class="col-span-1 text-sm font-medium">UNIT KERJA PENGENDALI</div>
                            <div class="col-span-2 text-sm">: {{ $hpp->controlling_unit }}</div>
                        </div>
                    </div>
<!-- Fungsi Peminta -->
<div class="p-2">
    <table class="w-full text-xs border border-black table-fixed">
        <tr>
            <td colspan="2" class="py-1 text-center font-bold border-b border-black">
                FUNGSI PEMINTA
            </td>
        </tr>
        <tr>
            <td class="py-1 text-center border-r border-black w-1/2">
                <strong>SM Of {{ $hpp->seniorManagerSignatureUser ? $hpp->seniorManagerSignatureUser->unit_work : 'N/A' }}</strong>
            </td>
            <td class="py-1 text-center border-black w-1/2">
                <strong>Mgr Of {{ $hpp->managerSignatureUser ? $hpp->managerSignatureUser->seksi : 'N/A' }}</strong>
            </td>
        </tr>
        <tr>
            <td class="py-2 text-center border-r border-black align-middle" style="height: 80px;">
                @if($hpp->senior_manager_signature)
                    <div class="h-full w-full flex items-center justify-center">
                        <img src="{{ $hpp->senior_manager_signature }}" alt="Senior Manager Signature" class="object-contain max-h-[150px] max-w-[150px]">
                    </div>
                @else
                    <strong>TTD</strong>
                @endif
            </td>
            <td class="py-2 text-center border-black align-middle" style="height: 80px;">
                @if($hpp->manager_signature)
                    <div class="h-full w-full flex items-center justify-center">
                        <img src="{{ $hpp->manager_signature }}" alt="Manager Signature" class="object-contain max-h-[150px] max-w-[150px]">
                    </div>
                @else
                    <strong>TTD</strong>
                @endif
            </td>
        </tr>
        <tr>
            <td class="py-1 text-center border-r border-black">
                {{ $hpp->seniorManagerSignatureUser ? $hpp->seniorManagerSignatureUser->name : 'N/A' }}
            </td>
            <td class="py-1 text-center border-black">
                {{ $hpp->managerSignatureUser ? $hpp->managerSignatureUser->name : 'N/A' }}
            </td>
        </tr>
    </table>
</div>

                </div>
            </div>

                <!-- Tabel Informasi HPP -->
                <div class="overflow-x-auto">
    <table class="min-w-full border border-gray-400 text-[9px]">
        <thead class="bg-blue-200 text-gray-800">
            <tr>
                <th rowspan="3" class="border border-gray-400 px-2 py-1 text-center bg-blue-200">OUTLINE AGREEMENT (OA)</th>
                <th rowspan="3" class="border border-gray-400 px-2 py-1 text-center bg-blue-200">URAIAN PEKERJAAN</th>
                <th rowspan="3" class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Qty</th>
                <th rowspan="3" class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Satuan</th>
                <th rowspan="3" class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Volume Satuan (Kg/Ea/Lot)</th>
                <th rowspan="2" class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Jumlah Volume Satuan</th>
                <th colspan="3" class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Harga Satuan</th>
                <th colspan="3" class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Jumlah Harga Satuan</th>
                <th rowspan="2" class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Harga Total</th>
                <th rowspan="3" class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Keterangan</th>
            </tr>
            <tr>
                <th class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Material</th>
                <th class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Consumable</th>
                <th class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Upah Kerja</th>
                <th class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Material</th>
                <th class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Consumable</th>
                <th class="border border-gray-400 px-2 py-1 text-center bg-blue-200">Upah Kerja</th>
            </tr>
            <tr>
                <th class="border border-gray-400 px-2 py-1 text-center bg-yellow-500">(1)</th>
                <th class="border border-gray-400 px-2 py-1 text-center bg-yellow-500">(2)</th>
                <th class="border border-gray-400 px-2 py-1 text-center bg-yellow-500">(3)</th>
                <th class="border border-gray-400 px-2 py-1 text-center bg-yellow-500">(4)</th>
                <th class="border border-gray-400 px-2 py-1 text-center bg-yellow-500">A=(1)*(2)</th>
                <th class="border border-gray-400 px-2 py-1 text-center bg-yellow-500">B=(1)*(3)</th>
                <th class="border border-gray-400 px-2 py-1 text-center bg-yellow-500">C=(1)*(4)</th>
                <th class="border border-gray-400 px-2 py-1 text-center bg-yellow-500">A+B+C</th>
            </tr>
        </thead>
        <tbody>
    @php
        $uraian_pekerjaan = is_string($hpp->uraian_pekerjaan) ? json_decode($hpp->uraian_pekerjaan, true) : $hpp->uraian_pekerjaan;
        $jenis_material = is_string($hpp->jenis_material) ? json_decode($hpp->jenis_material, true) : $hpp->jenis_material;
        $qty = is_string($hpp->qty) ? json_decode($hpp->qty, true) : $hpp->qty;
        $satuan = is_string($hpp->satuan) ? json_decode($hpp->satuan, true) : $hpp->satuan;
        $volume_satuan = is_string($hpp->volume_satuan) ? json_decode($hpp->volume_satuan, true) : $hpp->volume_satuan;
        $jumlah_volume_satuan = is_string($hpp->jumlah_volume_satuan) ? json_decode($hpp->jumlah_volume_satuan, true) : $hpp->jumlah_volume_satuan;
        $harga_material = is_string($hpp->harga_material) ? json_decode($hpp->harga_material, true) : $hpp->harga_material;
        $harga_consumable = is_string($hpp->harga_consumable) ? json_decode($hpp->harga_consumable, true) : $hpp->harga_consumable;
        $harga_upah = is_string($hpp->harga_upah) ? json_decode($hpp->harga_upah, true) : $hpp->harga_upah;
        $jumlah_harga_material = is_string($hpp->jumlah_harga_material) ? json_decode($hpp->jumlah_harga_material, true) : $hpp->jumlah_harga_material;
        $jumlah_harga_consumable = is_string($hpp->jumlah_harga_consumable) ? json_decode($hpp->jumlah_harga_consumable, true) : $hpp->jumlah_harga_consumable;
        $jumlah_harga_upah = is_string($hpp->jumlah_harga_upah) ? json_decode($hpp->jumlah_harga_upah, true) : $hpp->jumlah_harga_upah;
        $harga_total = is_string($hpp->harga_total) ? json_decode($hpp->harga_total, true) : $hpp->harga_total;
        $keterangan = is_string($hpp->keterangan) ? json_decode($hpp->keterangan, true) : $hpp->keterangan;
        $previousUraian = null;
    @endphp

    @foreach($uraian_pekerjaan as $index => $uraian)
        <tr>
            @if($index === 0)
                <td rowspan="{{ count($uraian_pekerjaan) }}" class="border border-gray-400 px-2 py-1 text-center">{{ $hpp->outline_agreement ?: '-' }}</td>
            @endif
            <td class="border border-gray-400 px-2 py-1"><strong>{{ $uraian ?: '-' }}</strong><br> {{ $jenis_material[$index] ?? '-' }}</td>
            <td class="border border-gray-400 px-2 py-1 text-center">{{ $qty[$index] ?? '-' }}</td>
            <td class="border border-gray-400 px-2 py-1 text-center">{{ $satuan[$index] ?? '-' }}</td>
            <td class="border border-gray-400 px-2 py-1 text-center">{{ $volume_satuan[$index] ?? '-' }}</td>
            <td class="border border-gray-400 px-2 py-1 text-center">{{ $jumlah_volume_satuan[$index] ?? '-' }}</td>
            <td class="border border-gray-400 px-2 py-1 text-center">
                {{ isset($harga_material[$index]) && is_numeric($harga_material[$index]) ? number_format($harga_material[$index], 0, ',', '.') : '-' }}
            </td>
            <td class="border border-gray-400 px-2 py-1 text-center">
                {{ isset($harga_consumable[$index]) && is_numeric($harga_consumable[$index]) ? number_format($harga_consumable[$index], 0, ',', '.') : '-' }}
            </td>
            <td class="border border-gray-400 px-2 py-1 text-center">
                {{ isset($harga_upah[$index]) && is_numeric($harga_upah[$index]) ? number_format($harga_upah[$index], 0, ',', '.') : '-' }}
            </td>
            <td class="border border-gray-400 px-2 py-1 text-center">
                {{ isset($jumlah_harga_material[$index]) && is_numeric($jumlah_harga_material[$index]) ? number_format($jumlah_harga_material[$index], 0, ',', '.') : '-' }}
            </td>
            <td class="border border-gray-400 px-2 py-1 text-center">
                {{ isset($jumlah_harga_consumable[$index]) && is_numeric($jumlah_harga_consumable[$index]) ? number_format($jumlah_harga_consumable[$index], 0, ',', '.') : '-' }}
            </td>
            <td class="border border-gray-400 px-2 py-1 text-center">
                {{ isset($jumlah_harga_upah[$index]) && is_numeric($jumlah_harga_upah[$index]) ? number_format($jumlah_harga_upah[$index], 0, ',', '.') : '-' }}
            </td>
            <td class="border border-gray-400 px-2 py-1 text-center">
                {{ isset($harga_total[$index]) ? number_format($harga_total[$index], 0, ',', '.') : '-' }}
            </td>
            <td class="border border-gray-400 px-2 py-1">{{ $keterangan[$index] ?? '-' }}</td>
        </tr>
    @endforeach
    <tr>
        <strong>
        <td colspan="12" class="border border-gray-400 px-2 py-1 text-center font-bold bg-gray-200">TOTAL</td>
        <td colspan="1" class="border border-gray-400 px-2 py-1 text-center bg-gray-200"><strong>{{ number_format($hpp->total_amount, 0, ',', '.') }}</strong></td>
        <td colspan="1" class="border border-gray-400 px-2 py-1 text-center bg-gray-200"></td>
        </strong>
    </tr>
</tbody>
</table>
</div>
<!-- Informasi Catatan dan Tanda Tangan -->
<div class="mt-6">
    <table class="min-w-full border border-black text-xs">
        <tr>
            <!-- Kolom Catatan User Peminta -->
            <td class="border border-black px-2 py-2 text-left align-top w-1/3">
                <strong>Catatan User Peminta (jika ada):</strong>
                <br><br><br><br>
            </td>
            <!-- Kolom Catatan Pengendali -->
            <td class="border border-black px-2 py-2 text-left align-top w-1/3">
                <strong>Catatan Pengendali (jika ada):</strong>
                <br><br><br><br>
            </td>
            <!-- Kolom Tanda Tangan -->
            <td class="px-2 py-2 w-1/3 border border-black">
                <table class="min-w-full text-xs">
                    <tr>
                        <td colspan="1" class="px-2 py-2 text-center border-b border-black font-bold italic">
                            FUNGSI PENGENDALI
                        </td>
                    </tr>
                    <tr>
                        <td class="px-2 py-2 text-center font-bold">
                            GM OF {{ $hpp->generalManagerSignatureUser ? $hpp->generalManagerSignatureUser->departemen : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="px-2 py-2 text-center">
                            @if($hpp->general_manager_signature)
                                <div class="flex justify-center items-center">
                                    <img src="{{ $hpp->general_manager_signature }}" alt="GM Signature" class="object-contain h-[100px] w-auto">
                                </div>
                            @else
                                <strong>TTD</strong>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="px-2 py-2 text-center">
                            {{ $hpp->generalManagerSignatureUser ? $hpp->generalManagerSignatureUser->name : 'N/A' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

                <!-- Footer 
                <div class="mt-6 border-t border-gray-400 pt-4">
                    <p class="text-xs text-gray-600">Keterangan:</p>
                    <p class="text-xs text-gray-600">- Kontrak Payung Jasa Fabrikasi, Konstruksi & Mesin Tahun 2022-2024</p>
                    <p class="text-xs text-gray-600">- Nilai yang dibayarkan berdasarkan realisasi Pekerjaan (Kurang dari Nilai HPP atau maksimal sama dengan HPP)</p>
                </div>
            </div>
        </div>
        <p class="text-xs text-gray-600 text-center">Form : No 26.3.0/09/R/01</p>
    </div>
-->
</x-document>
