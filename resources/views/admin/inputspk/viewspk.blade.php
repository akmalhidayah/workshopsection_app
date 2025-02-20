<x-document>
    <div class="p-6 border rounded-lg shadow-lg">
        <!-- Kop Surat -->
        <div class="flex items-left border-b-2 pb-4 mb-4">
            <div class="flex items-left mr-4"> <!-- Menambahkan margin kanan untuk memberikan jarak antara logo dan teks -->
                <img src="{{ asset('images/logo-st.png') }}" alt="Logo PT Semen Tonasa" class="h-20 w-auto">
            </div>
            <div class="text-left">
                <h1 class="text-lg font-bold">PT. SEMEN TONASA</h1>
                <h1 class="text-lg font-bold">DEPARTEMEN PEMELIHARAAN</h1>
                <h1 class="text-lg font-bold">UNIT WORKSHOP</h1>
            </div>
        </div>

        <h1 class="text-center text-2xl font-bold">SURAT PERINTAH KERJA KONTRAK JASA FABRIKASI, KONSTRUKSI DAN PENGERJAAN MESIN</h1>
        <h1 class="text-center text-2xl font-bold">PT. PRIMA KARYA MANUNGGAL</h1>
        <br><br>
        
        <div class="col-span-3 text-left p-2 pb-0">
            <div class="grid grid-cols-3 gap-0">
                <div class="col-span-1 text-sm font-medium mb-0">KEPADA YTH</div>
                <div class="col-span-2">: {{ $spk->kepada_yth ?? 'PT. PRIMA KARYA MANUNGGAL' }}</div>

                <div class="col-span-1 text-sm font-medium mb-0">PERIHAL</div>
                <div class="col-span-2">: {{ $spk->perihal }}</div>

                <div class="col-span-1 text-sm font-medium mb-0">NOMOR SPK</div>
                <div class="col-span-2">: {{ $spk->nomor_spk }}</div>

                <div class="col-span-1 text-sm font-medium mb-0">TANGGAL SPK</div>
                <div class="col-span-2">: {{ $spk->tanggal_spk }}</div>

                <div class="col-span-1 text-sm font-medium mb-0">NOMOR ORDER</div>
                <div class="col-span-2">: {{ $spk->notification_number }}</div>

                <div class="col-span-1 text-sm font-medium mb-0">KATEGORI PEKERJAAN</div>
                <div class="col-span-2 font-bold">: URGENT</div>

                <div class="col-span-1 text-sm font-medium mb-0">UNIT KERJA PEMINTA</div>
                <div class="col-span-2">: {{ $spk->unit_work }}</div>
            </div>
        </div>

        <!-- Tabel Functional Locations dan Scope Pekerjaan -->
        <div class="mt-6">
            <table class="min-w-full bg-white text-gray-900 border border-gray-300 text-xs rounded-lg shadow-md mt-4">
                <thead class="bg-gray-300 text-black">
                    <tr>
                        <th class="text-center px-2 py-2">No</th>
                        <th class="text-center px-2 py-2">Functional Location</th>
                        <th class="text-center px-2 py-2">Scope Pekerjaan</th>
                        <th class="text-center px-2 py-2">Qty</th>
                        <th class="text-center px-2 py-2">Stn</th>
                        <th class="text-center px-2 py-2">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($spk->functional_location as $index => $location)
                        <tr>
                            <td class="text-center px-2 py-2">{{ $index + 1 }}</td>
                            <td class="text-center px-2 py-2">{{ $location }}</td>
                            <td class="text-center px-2 py-2">{{ is_array($spk->scope_pekerjaan) ? $spk->scope_pekerjaan[$index] ?? '-' : '-' }}</td>
                            <td class="text-center px-2 py-2">{{ is_array($spk->qty) ? $spk->qty[$index] ?? '-' : '-' }}</td>
                            <td class="text-center px-2 py-2">{{ is_array($spk->stn) ? $spk->stn[$index] ?? '-' : '-' }}</td>
                            <td class="text-center px-2 py-2">{{ is_array($spk->keterangan) ? $spk->keterangan[$index] ?? '-' : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <h4><strong>KETERANGAN PENGERJAAN URGENSI :</strong></h4>
            <p>{{ $spk->keterangan_pekerjaan ?? 'Tidak ada keterangan tambahan' }}</p>
        </div>

        <div class="mt-6">
            <h4><strong>Catatan:</strong></h4>
            <ol class="list-decimal ml-6">
                <li>PT. Prima Karya Manunggal mengurus working permit untuk jasa pekerjaan Fabrikasi, Konstruksi dan Mesin yang memerlukan working permit.</li>
                <li>Proses pekerjaan wajib menggunakan APD sesuai standar K3.</li>
                <li>SPK ini merupakan Perintah Kerja untuk pekerjaan yang bersifat urgent bukan untuk penagihan dan dilampirkan untuk ajuan approval HPP.</li>
                <li>User peminta wajib membantu percepatan proses approval HPP ke atasan masing-masing setelah HPP diterbitkan.</li>
                <li>User peminta wajib menyiapkan anggaran sesuai nilai HPP Pekerjaan.</li>
            </ol>
        </div>

<!-- Signature Section -->
<div class="border border-black w-1/2 ml-0">
    <div class="border-b border-black">
        <h1 class="text-center font-bold">PT. SEMEN TONASA</h1>
    </div>
    <div class="border-b border-black">
        <h2 class="text-center">UNIT WORKSHOP</h2>
    </div>
    <div class="grid grid-cols-2 divide-x divide-black">
        <div class="text-center p-4">
            @if (!empty($spk->senior_manager_signature))
                <img src="{{ $spk->senior_manager_signature }}" alt="Tanda Tangan Senior Manager" class="mx-auto w-32 h-auto">
            @else
                <p class="text-gray-500">Belum ditandatangani</p>
            @endif
            <p class="font-bold"></p>
            <p>Senior Manager Unit Of Workshop</p>
        </div>
        <div class="text-center p-4">
            @if (!empty($spk->manager_signature))
                <img src="{{ $spk->manager_signature }}" alt="Tanda Tangan Manager" class="mx-auto w-32 h-auto">
            @else
                <p class="text-gray-500">Belum ditandatangani</p>
            @endif
            <p class="font-bold">HERWANTO S</p>
            <p>Manager Machine Workshop</p>
        </div>
    </div>
</div>

</x-document>
