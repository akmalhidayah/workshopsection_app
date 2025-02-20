<x-document>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <!-- Header dengan Logo di Kiri, Judul di Tengah, dan Logo di Kanan -->
                <div class="px-4 py-5 sm:px-6 flex items-center justify-between">
                    <!-- Logo Kiri -->
                    <img src="{{ asset('images/logo-sig.png') }}" alt="Logo SIG" class="h-20 w-auto"> <!-- Ganti 'logo-sig.png' dengan path logo SIG Anda -->

                    <!-- Judul Tengah -->
                    <h1 class="text-3xl font-bold text-gray-900 text-center flex-grow">
                        SCOPE OF WORK
                    </h1>

                    <!-- Logo Kanan -->
                    <img src="{{ asset('images/logo-st.png') }}" alt="Logo Semen Tonasa" class="h-24 w-24"> <!-- Ganti 'logo-st.png' dengan path logo Semen Tonasa Anda -->
                </div>

                <!-- Tabel Informasi Dasar -->
                <div class="border-t border-gray-200 p-6">
                    <table class="table-auto w-full">
                        <tbody>
                            <tr>
                                <th class="text-left pr-2 py-2 w-1/3">Order No</th>
                                <td class="py-2 w-2/3 ">: {{ $scopeOfWork->notification_number }}</td>
                            </tr>
                            <tr>
                                <th class="text-left pr-2 py-2 w-1/3">Nama Pekerjaan</th>
                                <td class="py-2 w-2/3 ">: {{ $scopeOfWork->nama_pekerjaan }}</td>
                            </tr>
                            <tr>
                                <th class="text-left pr-2 py-2 w-1/3 ">Unit Kerja</th>
                                <td class="py-2 w-2/3">: {{ $scopeOfWork->unit_kerja }}</td>
                            </tr>
                            <tr>
                                <th class="text-left pr-2 py-2 w-1/3 ">Tanggal Pemakaian</th>
                                <td class="py-2 w-2/3 ">: {{ $scopeOfWork->tanggal_pemakaian }}</td>
                            </tr>
                            <tr>
                                <th class="text-left pr-2 py-2 w-1/3 ">Tanggal Dokumen</th>
                                <td class="py-2 w-2/3 ">: {{ $scopeOfWork->tanggal_dokumen }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tabel Scope of Work -->
                <div class="p-6">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="border-2 border-black px-4 py-2 w-1/12">No</th>
                                <th class="border-2 border-black px-4 py-2 w-4/12">Scope Pekerjaan</th>
                                <th class="border-2 border-black px-4 py-2 w-2/12">Qty</th>
                                <th class="border-2 border-black px-4 py-2 w-2/12">Satuan</th>
                                <th class="border-2 border-black px-4 py-2 w-3/12">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scopeOfWork->scope_pekerjaan as $index => $pekerjaan)
                                <tr>
                                    <td class="border-2 border-black px-4 py-2">{{ $index + 1 }}</td>
                                    <td class="border-2 border-black px-4 py-2">{{ $pekerjaan }}</td>
                                    <td class="border-2 border-black px-4 py-2">{{ $scopeOfWork->qty[$index] ?? '-' }}</td>
                                    <td class="border-2 border-black px-4 py-2">{{ $scopeOfWork->satuan[$index] ?? '-' }}</td>
                                    <td class="border-2 border-black px-4 py-2">{{ $scopeOfWork->keterangan[$index] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Catatan dan Tanda Tangan -->
                <div class=" p-6 grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <strong>Catatan:</strong> {{ $scopeOfWork->catatan }}
                    </div>
                    <div class="text-center">
                        <p>Yang Membuat,</p>
                        <div style="height: 50px;"></div> <!-- Menambahkan spasi kosong -->
                        @if($scopeOfWork->tanda_tangan)
                        <img src="{{ $scopeOfWork->tanda_tangan }}" alt="Tanda Tangan" class="mt-2 mx-auto" style="max-height: 100px; margin-top: 20px;">
                        @endif
                        <p style="margin-top: 30px;">{{ $scopeOfWork->nama_penginput }}</p>
                        <p>{{ $scopeOfWork->unit_kerja }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-document>
