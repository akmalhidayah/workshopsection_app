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
                        <div class="col-span-3 text-left border-r border-black p-2 pb-0">
                            <div class="grid grid-cols-3 gap-0">
                                <div class="col-span-1 text-sm font-medium mb-0">ORDER NO</div>
                                <div class="col-span-2">: {{ $hpp->notification_number }}</div>

                                <div class="col-span-1 text-sm font-medium mb-0">DESKRIPSI</div>
                                <div class="col-span-2">: {{ $hpp->description }}</div>

                                <div class="col-span-1 text-sm font-medium mb-0">COST CENTRE</div>
                                <div class="col-span-2">: {{ $hpp->cost_centre }}</div>

                                <div class="col-span-1 text-sm font-medium mb-0">RENCANA PEMAKAIAN</div>
                                <div class="col-span-2">: {{ $hpp->notification->usage_plan_date ?? '-' }}</div>

                                <div class="col-span-1 text-sm font-medium mb-0">UNIT KERJA PEMINTA</div>
                                <div class="col-span-2">: {{ $hpp->requesting_unit }}</div>

                                <div class="col-span-1 text-sm font-medium mb-0">UNIT KERJA PENGENDALI</div>
                                <div class="col-span-2">: {{ $hpp->controlling_unit }}</div>
                            </div>
                        </div>
                        <!-- FUNGSI PEMINTA -->
                        <div class="text-left p-2">
                                    <table class="w-full text-xs border border-black">
                                        <tr>
                                            <td colspan="2" class="px-2 py-2 text-center" style="border-bottom: 1px solid black; font-weight: bold;">FUNGSI PEMINTA</td>
                                        </tr>
                                        <tr>
                                            <td class="px-2 py-4 text-center" style="width: 50%; border-right: 1px solid black;">
                                                GM Of
                                                @if(!is_null($hpp->generalManagerRequestingUser))
                                                    {{ $hpp->generalManagerRequestingUser->departemen }} <!-- Ganti dengan nama kolom yang benar jika bukan 'unit_work' -->
                                                @else
                                                    <span>Departemen Tidak Tersedia</span>
                                                @endif
                                            </td>
                                            <td class="px-2 py-4 text-center" style="width: 50%; border-right: 1px solid black;">
                                                SM Of
                                                @if(!is_null($hpp->seniorManagerRequestingUser))
                                                    {{ $hpp->seniorManagerRequestingUser->unit_work }}
                                                @else
                                                    <span>Unit Kerja Tidak Tersedia</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-2 py-4 text-center" style="width: 50%; border-right: 1px solid black;"><img src="{{ $hpp->general_manager_signature_requesting_unit }}">
                                            </td>
                                            <td class="px-2 py-4 text-center" style="width: 50%; border-right: 1px solid black;"><img src="{{ $hpp->senior_manager_signature_requesting_unit }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="w-1/2 border-r border-b border-black py-2 text-center">
                                                <!-- Tampilkan nama Senior Manager Requesting -->
                                                @if(!is_null($hpp->generalManagerRequestingUser))
                                                    {{ $hpp->generalManagerRequestingUser->name }}
                                                @else
                                                    <span>Tanda Tangan Belum Tersedia</span>
                                                @endif
                                            </td>
                                            <td class="w-1/2 border-b border-black py-2 text-center">
                                            @if(!is_null($hpp->seniorManagerRequestingUser))
                                                    {{ $hpp->seniorManagerRequestingUser->name }}
                                                @else
                                                    <span>Tanda Tangan Belum Tersedia</span>
                                                @endif
                                            
                                            </td>
                                            <tr>
                                            <td class="w-full border-b border-black py-2 text-center" colspan="2">
                                            <div style="display: flex; justify-content: flex-end; align-items: center;">
                                                <!-- Tampilkan tanda tangan di sebelah kanan tanpa margin atau padding tambahan -->
                                                @if(!is_null($hpp->manager_signature_requesting_unit))
                                                    <!-- Cek apakah ada data user yang terkait -->
                                                    {{ $hpp->managerSignatureRequestingUser ? $hpp->managerSignatureRequestingUser->name : 'Nama User Tidak Tersedia' }}
                                                    <img src="{{ $hpp->manager_signature_requesting_unit }}" alt="Tanda Tangan Manager Peminta" style="width: 80px; height: auto; padding: 0; margin: 0;">
                                                @else
                                                    <span>Tanda Tangan Belum Tersedia</span>
                                                @endif
                                            </div>
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
                <td class="border border-black px-2 py-2 text-left" style="vertical-align: top; width: 20%;">
                    <strong>Catatan User Peminta (jika ada):</strong>
                    <br>
                    @if(!empty($hpp->requesting_notes))
                        @foreach(json_decode($hpp->requesting_notes, true) as $index => $noteData)
                            <div class="mb-1">
                                <strong>{{ $index + 1 }}.</strong> {{ $noteData['note'] ?? 'Tidak ada catatan' }} <!-- Pastikan 'note' adalah string -->
                                <br>
                                <small>
                                    @php
                                        $user = \App\Models\User::find($noteData['user_id']);
                                    @endphp
                                    @if($user)
                                        <em>Ditambahkan oleh: {{ $user->jabatan }}</em>
                                    @else
                                        <em>Nama User Tidak Tersedia</em>
                                    @endif
                                </small>
                            </div>
                        @endforeach
                    @else
                        <br><br><br><br> <!-- Jika tidak ada catatan, tambahkan spasi -->
                    @endif
                </td>

                <!-- Kolom Catatan Pengendali -->
                <td class="border border-black px-2 py-2 text-left" style="vertical-align: top; width: 20%;">
                    <strong>Catatan Pengendali (jika ada):</strong>
                    <br>
                    @if(!empty($hpp->controlling_notes))
                        @foreach(json_decode($hpp->controlling_notes, true) as $index => $noteData)
                            <div class="mb-1">
                                <strong>{{ $index + 1 }}.</strong> {{ $noteData['note'] ?? 'Tidak ada catatan' }} <!-- Pastikan 'note' adalah string -->
                                <br>
                                <small>
                                    @php
                                        $user = \App\Models\User::find($noteData['user_id']);
                                    @endphp
                                    @if($user)
                                        <em>Ditambahkan oleh: {{ $user->jabatan }}</em>
                                    @else
                                        <em>Nama User Tidak Tersedia</em>
                                    @endif
                                </small>
                            </div>
                        @endforeach
                    @else
                        <br><br><br><br> <!-- Jika tidak ada catatan, tambahkan spasi -->
                    @endif
                </td>
                        <!-- Kolom Tanda Tangan -->
                    <td class="px-2 py-2" style="width: 30%; border: 1px solid black;">
                        <table class="min-w-full border-collapse text-xs" style="border: 1px solid black;">
                            <tr>
                            
                                <td colspan="2" class="px-2 py-2 text-center" style="width: 67%; border-bottom: 1px solid black; font-weight: bold; font-style: italic;">FUNGSI PENGENDALI</td>
                            </tr>
                            <tr>
                                <td class="px-2 py-4 text-center" style="width: 34%; border-right: 1px solid black;"><strong>GM of {{ $hpp->generalManagerSignatureUser ? $hpp->generalManagerSignatureUser->departemen : 'N/A' }}</strong></td>
                                <td class="px-2 py-4 text-center" style="width: 33%;"><strong>SM Of {{ $hpp->seniorManagerSignatureUser ? $hpp->seniorManagerSignatureUser->unit_work : 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                            <td class="px-2 py-4 text-center" style="width: 34%; border-right: 1px solid black;">
                                @if($hpp->general_manager_signature)
                                    <img src="{{ $hpp->general_manager_signature }}" alt="GM Signature" style="width: 100px; height: 100px;">
                                @else
                                    <strong>TTD</strong>
                                @endif
                            </td>
                                <td class="px-2 py-4 text-center" style="width: 33%;">
                                @if($hpp->senior_manager_signature)
                                    <img src="{{ $hpp->senior_manager_signature }}" alt="Senior Manager Signature" style="width: 100px; height: 100px;">
                                @else
                                    <strong>TTD</strong>
                                @endif
                            </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-4 text-center" style="width: 34%; border-right: 1px solid black; border-bottom: 1px solid black;">{{ $hpp->generalManagerSignatureUser ? $hpp->generalManagerSignatureUser->name : 'N/A' }}</td>
                                <td class="px-2 py-4 text-center" style="width: 33%; border-bottom: 1px solid black;"> {{ $hpp->seniorManagerSignatureUser ? $hpp->seniorManagerSignatureUser->name : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-2 py-2 text-right" style="border-top: 1px solid black; border-bottom: 1px solid black;">
                                {{ $hpp->managerSignatureUser ? $hpp->managerSignatureUser->initials : 'N/A' }}
                                @if($hpp->manager_signature)
                                <img src="{{ $hpp->manager_signature }}" alt="Manager Signature" style="width: 70px; height: auto; display: inline-block; vertical-align: middle; margin-left: 5px;">
                                @endif
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
