<x-approval>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dokumen Menunggu Approval</h2>
                <!-- Tabel untuk menampilkan dokumen yang menunggu approval -->
                <div class="overflow-x-auto">
                <table class="min-w-full bg-white mt-6">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-blue-500 tracking-wider">No</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-blue-500 tracking-wider">Nomor Order</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-blue-500 tracking-wider">Job Name</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-blue-500 tracking-wider">Unit Work</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-blue-500 tracking-wider">Dokumen</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-blue-500 tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($hppDocuments as $hpp)
<tr>
    <td class="px-6 py-4 border-b border-gray-300 text-sm">{{ $loop->iteration }}</td>
    <td class="px-6 py-4 border-b border-gray-300 text-sm">{{ $hpp->notification_number }}</td>
    <td class="px-6 py-4 border-b border-gray-300 text-sm">{{ $hpp->description }}</td>
    <td class="px-6 py-4 border-b border-gray-300 text-sm">{{ $hpp->requesting_unit }}</td>
    <td class="px-6 py-4 border-b border-gray-300 text-sm">
<!-- Tombol untuk melihat dokumen -->
@if($hpp->source_form === 'createhpp1')
    <a href="{{ route('approval.hpp.download_hpp1', ['notification_number' => $hpp->notification_number]) }}" 
       target="_blank" 
       class="bg-blue-500 text-white px-3 py-1 text-xs rounded-lg hover:bg-blue-600 transition-all flex items-center justify-center">
        <i class="fas fa-download mr-1"></i> Download HPP
    </a>
@elseif($hpp->source_form === 'createhpp2')
    <a href="{{ route('approval.hpp.download_hpp2', ['notification_number' => $hpp->notification_number]) }}" 
       target="_blank" 
       class="bg-green-500 text-white px-3 py-1 text-xs rounded-lg hover:bg-green-600 transition-all flex items-center justify-center">
        <i class="fas fa-download mr-1"></i> Download HPP
    </a>
@elseif($hpp->source_form === 'createhpp3')
    <a href="{{ route('approval.hpp.download_hpp3', ['notification_number' => $hpp->notification_number]) }}" 
       target="_blank" 
       class="bg-purple-500 text-white px-3 py-1 text-xs rounded-lg hover:bg-purple-600 transition-all flex items-center justify-center">
        <i class="fas fa-download mr-1"></i> Download HPP
    </a>
@endif


    </td>
    <td class="px-6 py-4 border-b border-gray-300 text-sm">
        <!-- Jika dokumen ditolak -->
        @if ($hpp->manager_signature === 'rejected')
            <span class="text-red-500 text-xs">Ditolak oleh Manager Controlling</span>
            <p class="text-gray-500 text-xs">{{ $hpp->rejection_reason ?? 'Alasan tidak tersedia' }}</p>
        @endif

        <!-- Include partial berdasarkan source_form -->
        @if ($hpp->source_form === 'createhpp1')
            @include('approval.hpp.partial.createhpp1', ['hpp' => $hpp])
        @elseif ($hpp->source_form === 'createhpp2')
            @include('approval.hpp.partial.createhpp2', ['hpp' => $hpp])
        @elseif ($hpp->source_form === 'createhpp3')
            @include('approval.hpp.partial.createhpp3', ['hpp' => $hpp])
        @endif

        <!-- Catatan untuk Unit Controlling -->
        @if (auth()->user()->unit_work === $hpp->controlling_unit || in_array($hpp->controlling_unit, auth()->user()->related_units ?? []))
            <div class="border rounded p-4 bg-gray-100">
                <h3 class="font-semibold text-lg mb-4">Catatan Pengendali</h3>
                <form method="POST" action="{{ route('approval.hpp.saveNotes', ['notification_number' => $hpp->notification_number, 'type' => 'controlling']) }}">
                    @csrf
                    <div id="controllingNotesWrapper">
                        @if (!empty($hpp->controlling_notes))
                            @foreach (json_decode($hpp->controlling_notes, true) as $note)
                                <div class="mb-2 w-full">
                                    <strong>{{ $loop->iteration }}. {{ $note['note'] }}</strong><br>
                                    @php
                                        $user = \App\Models\User::find($note['user_id']);
                                    @endphp
                                    <small>Ditambahkan oleh: {{ $user ? $user->jabatan : 'Pengguna Tidak Dikenal' }}</small>
                                </div>
                            @endforeach
                        @endif
                        <input type="text" name="controlling_notes[]" placeholder="Tambahkan Catatan Pengendali" class="border p-2 mb-2 w-full">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded">Simpan Catatan</button>
                </form>
            </div>
        @endif

        <!-- Catatan untuk Unit Requesting -->
        @if (auth()->user()->unit_work === $hpp->requesting_unit || in_array($hpp->requesting_unit, auth()->user()->related_units ?? []))
            <div class="border rounded p-4 bg-gray-100">
                <h3 class="font-semibold text-lg mb-4">Catatan Peminta</h3>
                <form method="POST" action="{{ route('approval.hpp.saveNotes', ['notification_number' => $hpp->notification_number, 'type' => 'requesting']) }}">
                    @csrf
                    <div id="requestingNotesWrapper">
                        @if (!empty($hpp->requesting_notes))
                            @foreach (json_decode($hpp->requesting_notes, true) as $note)
                                <div class="mb-2 w-full">
                                    <strong>{{ $loop->iteration }}. {{ $note['note'] }}</strong><br>
                                    @php
                                        $user = \App\Models\User::find($note['user_id']);
                                    @endphp
                                    <small>Ditambahkan oleh: {{ $user ? $user->jabatan : 'Pengguna Tidak Dikenal' }}</small>
                                </div>
                            @endforeach
                        @endif
                        <input type="text" name="requesting_notes[]" placeholder="Tambahkan Catatan Peminta" class="border p-2 mb-2 w-full">
                    </div>
                    <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded">Simpan Catatan</button>
                </form>
            </div>
        @endif
    </td>
</tr>
@endforeach
<!-- Tambahkan di bagian akhir file sebelum tag penutup -->
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
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-gray-500 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm" onclick="useOldSignature()">Gunakan Tanda Tangan Lama</button>
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
            document.getElementById('signPadModal').classList.remove('hidden'); // Menampilkan modal

            const canvas = document.getElementById('signaturePad');
            if (canvas) {
                signaturePad = new SignaturePad(canvas);
                canvas.width = canvas.parentElement.offsetWidth;
                canvas.height = 300;
                signaturePad.clear();
            }

            document.getElementById('notificationNumber').value = notificationNumber;
            document.getElementById('signType').value = signType;
        }
        function saveSignature() {
    const signature = signaturePad.toDataURL();
    const notificationNumber = document.getElementById('notificationNumber').value;
    const signType = document.getElementById('signType').value;

    fetch('/approval/hpp/sign/' + signType + '/' + notificationNumber, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            tanda_tangan: signature
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data); // Log the response for debugging
        if (data.message && data.message.includes('saved')) {
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

function rejectDocument(notificationNumber, signType) {
   Swal.fire({
       title: 'Apakah Anda yakin?',
       text: "Anda akan menolak dokumen ini!",
       input: 'textarea', // Tambahkan input untuk alasan penolakan
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
           console.log("Reason: ", result.value); // Tambahkan log di sini
           
           fetch('/approval/hpp/reject/' + signType + '/' + notificationNumber, {
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
                       location.reload();
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


        function clearSignature() {
            signaturePad.clear();
        }

        function closeSignPad() {
            document.getElementById('signPadModal').classList.add('hidden');
        }
        function addControllingNote() {
    const wrapper = document.getElementById('controllingNotesWrapper');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'controlling_notes[]';
    input.placeholder = 'Tambahkan Catatan Pengendali';
    input.classList.add('border', 'rounded', 'p-2', 'mb-2', 'w-full');
    wrapper.appendChild(input);
}

function addRequestingNote() {
    const wrapper = document.getElementById('requestingNotesWrapper');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'requesting_notes[]';
    input.placeholder = 'Tambahkan Catatan Peminta';
    input.classList.add('border', 'rounded', 'p-2', 'mb-2', 'w-full');
    wrapper.appendChild(input);
}
function useOldSignature() {
    const notificationNumber = document.getElementById('notificationNumber').value;
    const signType = document.getElementById('signType').value;

    console.log(`/approval/hpp/get-old-signature/${signType}/${notificationNumber}`);

    fetch(`/approval/hpp/get-old-signature/${signType}/${notificationNumber}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.signature) {
            signaturePad.fromDataURL(data.signature);
            Swal.fire({
                title: 'Tanda Tangan Lama Ditemukan!',
                text: `Tanda tangan oleh ${data.user.name} (${data.user.jabatan}).`,
                icon: 'info',
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                title: 'Tidak Ada Tanda Tangan Lama',
                text: data.message,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Kesalahan!',
            text: 'Terjadi kesalahan saat mengambil tanda tangan lama.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}

    </script>
</x-approval>
