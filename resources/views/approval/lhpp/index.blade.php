<x-approval>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-4 lg:px-6">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dokumen LHPP Menunggu Approval</h2>
            
            <!-- Wrapper untuk Tabel Responsif -->
            <div class="overflow-x-auto mt-6">
                <table class="min-w-full bg-white text-sm">
                    <thead 
                        <tr>
                            <th class="px-4 py-2 border-b text-left text-xs sm:text-sm text-blue-500">No</th>
                            <th class="px-4 py-2 border-b text-left text-xs sm:text-sm text-blue-500">Nomor Order</th>
                            <th class="px-4 py-2 border-b text-left text-xs sm:text-sm text-blue-500">Deskripsi Pekerjaan</th>
                            <th class="px-4 py-2 border-b text-left text-xs sm:text-sm text-blue-500">Unit Kerja</th>
                            <th class="px-4 py-2 border-b text-left text-xs sm:text-sm text-blue-500">Dokumen</th>
                            <th class="px-4 py-2 border-b text-left text-xs sm:text-sm text-blue-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lhppDocuments as $lhpp)
                            <tr>
                                <td class="px-4 py-2 border-b text-xs sm:text-sm">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2 border-b text-xs sm:text-sm">{{ $lhpp->notification_number }}</td>
                                <td class="px-4 py-2 border-b text-xs sm:text-sm">{{ $lhpp->description_notifikasi }}</td>
                                <td class="px-4 py-2 border-b text-xs sm:text-sm">{{ $lhpp->unit_kerja }}</td>

                                <!-- Tampilkan dokumen untuk dilihat -->
                                <td class="px-6 py-4 border-0 flex justify-center space-x-2">
                                <a href="{{ route('approval.lhpp.download_pdf', ['notification_number' => $lhpp->notification_number]) }}" 
                                class="bg-green-500 text-white px-3 py-1 text-xs rounded-lg hover:bg-green-700 transition-all flex items-center justify-center"
                                target="_blank">
                                    <i class="fas fa-download mr-1"></i> Download PDF
                                </a>
                                </td>
                                <!-- Kolom aksi untuk tanda tangan dan catatan -->
                                <td class="px-6 py-4 border-b border-gray-300 text-sm">
                                <!-- Logika untuk aksi tanda tangan -->
                                @if(auth()->user()->jabatan == 'Manager')
                                    @if(is_null($lhpp->manager_pkm_signature) && auth()->user()->unit_work == $lhpp->kontrak_pkm)
                                        <!-- 1️⃣ Manager PKM tanda tangan pertama -->
                                        <button type="button" class="bg-green-500 text-white px-2 py-1 rounded text-xs" 
                                            onclick="openSignPad('{{ $lhpp->notification_number }}', 'manager_pkm')">
                                            Tanda Tangan Manager PKM
                                        </button>
                                        <button type="button" class="bg-red-500 text-white px-2 py-1 rounded text-xs" 
                                            onclick="rejectDocument('{{ $lhpp->notification_number }}', 'manager_pkm')">
                                            Reject
                                        </button>

                                    @elseif(!is_null($lhpp->manager_pkm_signature) && is_null($lhpp->manager_signature_requesting) 
                                            && auth()->user()->unit_work == $lhpp->unit_kerja)
                                        <!-- 2️⃣ Manager Requesting (Peminta) setelah Admin menyetujui -->
                                        <button type="button" class="bg-blue-500 text-white px-2 py-1 rounded text-xs" 
                                            onclick="openSignPad('{{ $lhpp->notification_number }}', 'manager_requesting')">
                                            Tanda Tangan Manager Peminta
                                        </button>
                                        <button type="button" class="bg-red-500 text-white px-2 py-1 rounded text-xs" 
                                            onclick="rejectDocument('{{ $lhpp->notification_number }}', 'manager_requesting')">
                                            Reject
                                        </button>

                                    @elseif(!is_null($lhpp->manager_signature_requesting) && is_null($lhpp->manager_signature) 
                                            && auth()->user()->unit_work == 'Unit Of Workshop')
                                        <!-- 3️⃣ Manager Workshop tanda tangan terakhir -->
                                        <button type="button" class="bg-blue-500 text-white px-2 py-1 rounded text-xs" 
                                            onclick="openSignPad('{{ $lhpp->notification_number }}', 'manager')">
                                            Tanda Tangan Manager Workshop
                                        </button>
                                        <button type="button" class="bg-red-500 text-white px-2 py-1 rounded text-xs" 
                                            onclick="rejectDocument('{{ $lhpp->notification_number }}', 'manager')">
                                            Reject
                                        </button>

                                    @else
                                        <!-- Jika tidak ada aksi -->
                                        <span class="text-gray-500 text-xs">Tidak Ada Aksi</span>
                                    @endif
                                    @endif

                                    <!-- ✅ Jika semua tanda tangan sudah lengkap -->
                                    @if(!is_null($lhpp->manager_pkm_signature) 
                                    && !is_null($lhpp->manager_signature_requesting) 
                                    && !is_null($lhpp->manager_signature))
                                    <span class="text-green-500 text-xs">Sudah Ditandatangani</span>
                                    @endif
                                    <!-- Form untuk hasil Quality Control -->
                                    @if(auth()->user()->jabatan == 'Manager' && auth()->user()->unit_work == 'Unit Of Workshop')
                                        <form method="POST" action="{{ route('approval.lhpp.updateStatus', ['notification_number' => $lhpp->notification_number]) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="mt-4">
                                                <label class="block text-sm font-semibold">Hasil Quality Control:</label>
                                                <div class="mt-2">
                                                    <button type="submit" name="status_approve" value="Approved" class="bg-green-500 text-white px-4 py-2 rounded">Approve</button>
                                                    <button type="submit" name="status_approve" value="Rejected" class="bg-red-500 text-white px-4 py-2 rounded">Reject</button>
                                                </div>
                                            </div>
                                        </form>

                                        <!-- Tampilkan status approval yang sudah ada -->
                                        <div class="mt-4">
                                            <h3 class="font-semibold">Status Approval:</h3>
                                            @if($lhpp->status_approve == 'Approved')
                                                <p class="text-green-500">Approved</p>
                                            @elseif($lhpp->status_approve == 'Rejected')
                                                <p class="text-red-500">Rejected</p>
                                                <p>Alasan Penolakan: {{ $lhpp->rejection_reason }}</p> <!-- Tampilkan alasan reject -->
                                            @else
                                                <p class="text-yellow-500">Pending</p>
                                            @endif
                                        </div>

                                        <!-- Form untuk menambah catatan controlling -->
                                        <form method="POST" action="{{ route('approval.lhpp.saveNotes', ['notification_number' => $lhpp->notification_number, 'type' => 'controlling']) }}" class="mt-4">
                                            @csrf
                                            <input type="text" name="controlling_notes[]" placeholder="Tambahkan Catatan Pengendali" class="border p-2 mb-2 w-full">
                                            <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded">Simpan Catatan</button>
                                        </form>

                                        <!-- Tampilkan catatan controlling yang sudah ada -->
                                        @if(!empty($lhpp->controlling_notes))
                                            <div class="mt-2">
                                                <h3 class="font-semibold text-xs mb-2">Catatan Pengendali:</h3>
                                                @foreach(json_decode($lhpp->controlling_notes, true) as $note)
                                                    <div class="mb-1">
                                                        <strong>{{ $loop->iteration }}. {{ $note['note'] }}</strong><br>
                                                        @php
                                                            $user = \App\Models\User::find($note['user_id']);
                                                        @endphp
                                                        <small>Ditambahkan oleh: {{ $user ? $user->jabatan : 'Pengguna Tidak Dikenal' }}</small>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif

                                    <!-- Form untuk menambah catatan requesting jika unit kerja sama -->
                                    @if(auth()->user()->unit_work === $lhpp->unit_kerja)
                                        <form method="POST" action="{{ route('approval.lhpp.saveNotes', ['notification_number' => $lhpp->notification_number, 'type' => 'requesting']) }}" class="mt-4">
                                            @csrf
                                            <input type="text" name="requesting_notes[]" placeholder="Tambahkan Catatan Peminta" class="border p-2 mb-2 w-full">
                                            <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded">Simpan Catatan</button>
                                        </form>

                                        <!-- Tampilkan catatan requesting yang sudah ada -->
                                        @if(!empty($lhpp->requesting_notes))
                                            <div class="mt-2">
                                                <h3 class="font-semibold text-xs mb-2">Catatan Peminta:</h3>
                                                @foreach(json_decode($lhpp->requesting_notes, true) as $note)
                                                    <div class="mb-1">
                                                        <strong>{{ $loop->iteration }}. {{ $note['note'] }}</strong><br>
                                                        @php
                                                            $user = \App\Models\User::find($note['user_id']);
                                                        @endphp
                                                        <small>Ditambahkan oleh: {{ $user ? $user->jabatan : 'Pengguna Tidak Dikenal' }}</small>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tanda Tangani Dokumen</h3>
                            <div class="mt-2">
                                <canvas id="signaturePad" class="border rounded w-full" style="height: 300px;"></canvas>
                                <input type="hidden" id="notificationNumber" name="notificationNumber" value="">
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

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let signaturePad;

        function openSignPad(notificationNumber, signType) {
            document.getElementById('signPadModal').classList.remove('hidden');
            const canvas = document.getElementById('signaturePad');
            signaturePad = new SignaturePad(canvas);
            canvas.width = canvas.parentElement.offsetWidth;
            canvas.height = 300;
            signaturePad.clear();
            document.getElementById('notificationNumber').value = notificationNumber;
            document.getElementById('signType').value = signType;
        }

        function saveSignature() {
            const signature = signaturePad.toDataURL();
            const notificationNumber = document.getElementById('notificationNumber').value;
            const signType = document.getElementById('signType').value;

            fetch('/approval/lhpp/sign/' + signType + '/' + notificationNumber, {
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
                    Swal.fire('Berhasil!', 'Tanda tangan berhasil disimpan!', 'success').then(() => {
                        closeSignPad();
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', 'Gagal menyimpan tanda tangan.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Kesalahan!', 'Terjadi kesalahan. Silakan coba lagi.', 'error');
            });
        }

        function clearSignature() {
            signaturePad.clear();
        }

        function closeSignPad() {
            document.getElementById('signPadModal').classList.add('hidden');
        }

        // Fungsi untuk menambah input catatan controlling (Pengendali)
        function addControllingNote() {
            const wrapper = document.getElementById('controllingNotesWrapper');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'controlling_notes[]';
            input.placeholder = 'Tambahkan Catatan Pengendali';
            input.classList.add('border', 'p-2', 'mb-2', 'w-full');
            wrapper.appendChild(input);
        }

        // Fungsi untuk menambah input catatan requesting (Peminta)
        function addRequestingNote() {
            const wrapper = document.getElementById('requestingNotesWrapper');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'requesting_notes[]';
            input.placeholder = 'Tambahkan Catatan Peminta';
            input.classList.add('border', 'p-2', 'mb-2', 'w-full');
            wrapper.appendChild(input);
        }
        function rejectDocument(notificationNumber, signType) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Anda akan menolak dokumen ini!",
        input: 'textarea', // Input alasan penolakan
        inputPlaceholder: 'Masukkan alasan penolakan...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, tolak!',
        cancelButtonText: 'Batal',
        preConfirm: (reason) => {
            if (!reason) {
                Swal.showValidationMessage('Alasan penolakan harus diisi');
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            console.log("Notification Number: ", notificationNumber);
            console.log("Sign Type: ", signType);
            console.log("Reason: ", result.value);

            fetch('/approval/lhpp/reject/' + signType + '/' + notificationNumber, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    reason: result.value // Kirim alasan penolakan
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message === 'Document rejected successfully!') {
                    Swal.fire(
                        'Ditolak!',
                        'Dokumen berhasil ditolak.',
                        'success'
                    ).then(() => {
                        location.reload(); // Refresh halaman setelah penolakan
                    });
                } else {
                    Swal.fire(
                        'Gagal!',
                        'Gagal menolak dokumen.',
                        'error'
                    );
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
    });
}

    </script>
</x-approval>
