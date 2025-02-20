<x-approval>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dokumen SPK Menunggu Approval</h2>

                <!-- Tabel untuk menampilkan dokumen SPK yang menunggu approval -->
                <table class="min-w-full bg-white mt-6">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-blue-500 tracking-wider">No</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-blue-500 tracking-wider">Nomor SPK</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-blue-500 tracking-wider">Perihal</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-blue-500 tracking-wider">Unit Work</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-blue-500 tracking-wider">Dokumen</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-blue-500 tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($spks as $spk)
                            <tr>
                                <td class="px-6 py-4 border-b border-gray-300 text-sm">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 border-b border-gray-300 text-sm">{{ $spk->nomor_spk }}</td>
                                <td class="px-6 py-4 border-b border-gray-300 text-sm">{{ $spk->perihal }}</td>
                                <td class="px-6 py-4 border-b border-gray-300 text-sm">{{ $spk->unit_work }}</td>
                                <td class="px-6 py-4 border-b border-gray-300 text-sm">
                                    <a href="{{ route('spk.show', ['notification_number' => $spk->notification_number]) }}" class="bg-amber-900 text-white px-3 py-1 rounded text-xs hover:bg-blue-600 transition-colors duration-150 flex items-center space-x-1" target="_blank">
                                        <i class="fas fa-eye"></i> Lihat Dokumen
                                    </a>
                                </td>
                                <td class="px-6 py-4 border-b border-gray-300 text-sm">
                                    @if (is_null($spk->manager_signature) && auth()->user()->jabatan == 'Manager')
                                        <button type="button" class="bg-blue-500 text-white px-2 py-1 rounded text-xs" onclick="openSignPad('{{ $spk->nomor_spk }}', 'manager')">Tanda Tangan Manager</button>
                                    @elseif (is_null($spk->senior_manager_signature) && auth()->user()->jabatan == 'Senior Manager')
                                        <button type="button" class="bg-green-500 text-white px-2 py-1 rounded text-xs" onclick="openSignPad('{{ $spk->nomor_spk }}', 'senior_manager')">Tanda Tangan Senior Manager</button>
                                    @else
                                        <span class="text-green-500 text-xs">Sudah Ditandatangani</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Modal untuk tanda tangan -->
                <div id="signPadModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tanda Tangani Dokumen SPK</h3>
                                        <div class="mt-2">
                                            <canvas id="signaturePad" class="border rounded w-full" style="height: 300px;"></canvas>
                                            <input type="hidden" id="nomorSpk" name="nomorSpk" value="">
                                            <input type="hidden" id="signType" name="signType" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-500 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm" onclick="saveSignature()">Save</button>
                                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeSignPad()">Cancel</button>
                                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-red-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="clearSignature()">Clear</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let signaturePad;

        function openSignPad(nomorSpk, signType) {
            document.getElementById('signPadModal').classList.remove('hidden');
            const canvas = document.getElementById('signaturePad');
            if (canvas) {
                signaturePad = new SignaturePad(canvas);
                canvas.width = canvas.parentElement.offsetWidth;
                canvas.height = 300;
                signaturePad.clear();
            }
            document.getElementById('nomorSpk').value = nomorSpk;
            document.getElementById('signType').value = signType;
        }

        function saveSignature() {
            const signature = signaturePad.toDataURL();
            const nomorSpk = document.getElementById('nomorSpk').value;
            const signType = document.getElementById('signType').value;

            fetch(`/approval/spk/sign/${signType}/${nomorSpk}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ tanda_tangan: signature })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message === 'Signature saved successfully!') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Tanda tangan berhasil disimpan!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        closeSignPad();
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Gagal menyimpan tanda tangan.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Kesalahan!',
                    text: 'Terjadi kesalahan. Silakan coba lagi.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }

        function clearSignature() {
            signaturePad.clear();
        }

        function closeSignPad() {
            document.getElementById('signPadModal').classList.add('hidden');
        }
    </script>
</x-approval>
