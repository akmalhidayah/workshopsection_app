{{-- resources/views/dokumen_orders/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dokumen Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-900 shadow-lg overflow-hidden sm:rounded-lg">
                {{-- Header dan Filter --}}
                <div class="p-6 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
                    <h3 class="text-lg font-semibold">List Dokumen & Permintaan User</h3>
                    <form id="filterForm" action="{{ route('dokumen_orders.index') }}" method="GET"
                          class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0 w-full sm:w-auto">
                        <input type="text" name="search" placeholder="Search..."
                               value="{{ request('search') }}"
                               class="px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-white rounded-md text-sm placeholder-gray-400 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50 w-full sm:w-auto" />
                        <select name="sortOrder" class="px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-white rounded-md text-sm">
                            <option value="latest" {{ request('sortOrder') == 'latest' ? 'selected' : '' }}>Urutkan Terbaru</option>
                            <option value="oldest" {{ request('sortOrder') == 'oldest' ? 'selected' : '' }}>Urutkan Terlama</option>
                        </select>
                        <select name="entries" class="px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-white rounded-md text-sm">
                            <option value="10" {{ request('entries') == 10 ? 'selected' : '' }}>Show 10</option>
                            <option value="25" {{ request('entries') == 25 ? 'selected' : '' }}>Show 25</option>
                            <option value="50" {{ request('entries') == 50 ? 'selected' : '' }}>Show 50</option>
                            <option value="100" {{ request('entries') == 100 ? 'selected' : '' }}>Show 100</option>
                        </select>
                        <button type="submit" class="px-3 py-2 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600 focus:outline-none focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                            Search
                        </button>
                    </form>
                </div>
            </div>

            {{-- Konten List Dokumen --}}
            <div class="p-6 bg-white dark:bg-gray-900 space-y-4">
                @foreach($notifications as $notif)
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                        <div class="mb-4">
                            <p class="text-gray-800 dark:text-white font-semibold">Nomor Order: {{ $notif->notification_number }}</p>
                            <p class="text-gray-800 dark:text-white">Nama Pekerjaan: {{ $notif->job_name }}</p>
                            <p class="text-gray-800 dark:text-white">Input Date: {{ $notif->input_date }}</p>
                        </div>

                        <p class="text-gray-800 dark:text-white font-semibold">Action Sections</p>

                        {{-- === Abnormalitas === --}}
                        <div class="border-t border-gray-700 pt-2 mt-2">
                            <span class="text-sm text-gray-800 dark:text-white">Abnormalitas:</span>
                            @php $abnormal = $notif->dokumenOrders->where('jenis_dokumen','abnormalitas')->first(); @endphp

                            <form method="POST" action="{{ route('dokumen_orders.upload') }}" enctype="multipart/form-data" class="inline upload-form">
                                @csrf
                                <input type="hidden" name="notification_number" value="{{ $notif->notification_number }}">
                                <input type="hidden" name="jenis_dokumen" value="abnormalitas">
                                <input type="file" name="dokumen_file" class="hidden upload-input" id="abnormal_file_{{ $notif->notification_number }}">
                                <a href="#" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded-md text-xs"
                                   onclick="document.getElementById('abnormal_file_{{ $notif->notification_number }}').click(); return false;">
                                    <i class="fas fa-upload mr-1"></i> Upload Abnormalitas
                                </a>
                            </form>

                            @if($abnormal)
                                <a href="{{ route('dokumen_orders.view', [$notif->notification_number, 'abnormalitas']) }}"
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-xs ml-2">
                                    <i class="fas fa-folder-open mr-1"></i> Lihat File
                                </a>
                            @else
                                <span class="text-xs text-red-500 ml-2">Belum ada file</span>
                            @endif
                        </div>

                        {{-- === Scope of Work === --}}
                        <div class="border-t border-gray-700 pt-2 mt-2">
                            <span class="text-sm text-gray-800 dark:text-white">Scope of Work:</span>
                            @if(!$notif->scopeOfWork)
                                <button type="button" onclick="openSowCreate('{{ $notif->notification_number }}')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-xs">
                                    <i class="fas fa-plus-circle mr-1"></i> Buat Scope Of Work
                                </button>
                            @else
                                <button onclick="openSignPad('{{ $notif->notification_number }}')" class="bg-gray-500 text-white px-3 py-1 rounded-md text-xs hover:bg-gray-600">
                                    <i class="fas fa-signature mr-1"></i> Tanda Tangani
                                </button>
                                <button type="button" onclick="openSowEdit('{{ $notif->notification_number }}')" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-xs">
                                    <i class="fas fa-edit mr-1"></i> Edit Scope Of Work
                                </button>
                                <a href="{{ route('dokumen_orders.scope.download_pdf', $notif->notification_number) }}"
                                   class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs" target="_blank">
                                   <i class="fas fa-file-pdf mr-1"></i> Lihat Scope Of Work
                                </a>
                            @endif
                        </div>

                        {{-- === Gambar Teknik === --}}
                        <div class="border-t border-gray-700 pt-2 mt-2">
                            <span class="text-sm text-gray-800 dark:text-white">Gambar Teknik:</span>
                            @php $teknik = $notif->dokumenOrders->where('jenis_dokumen','gambar_teknik')->first(); @endphp
                            <form method="POST" action="{{ route('dokumen_orders.upload') }}" enctype="multipart/form-data" class="inline upload-form">
                                @csrf
                                <input type="hidden" name="notification_number" value="{{ $notif->notification_number }}">
                                <input type="hidden" name="jenis_dokumen" value="gambar_teknik">
                                <input type="file" name="dokumen_file" class="hidden upload-input" id="teknik_file_{{ $notif->notification_number }}">
                                <a href="#" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded-md text-xs"
                                   onclick="document.getElementById('teknik_file_{{ $notif->notification_number }}').click(); return false;">
                                    <i class="fas fa-upload mr-1"></i> Upload Gambar
                                </a>
                            </form>

                            @if($teknik)
                                <a href="{{ route('dokumen_orders.view', [$notif->notification_number, 'gambar_teknik']) }}"
                                   class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-xs ml-2">
                                    <i class="fas fa-folder-open mr-1"></i> Lihat Gambar
                                </a>
                            @else
                                <span class="text-xs text-red-500 ml-2">Belum ada gambar</span>
                            @endif
                        </div>
                    </div>
                @endforeach
                <div>{{ $notifications->links() }}</div>
            </div>
        </div>
    </div>

    {{-- === Modal Scope of Work (AJAX) === --}}
    <div id="sowModal" class="fixed inset-0 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeSowModal()"></div>
            <div id="sowModalBox" class="bg-white rounded-lg shadow-lg w-full max-w-3xl z-10 overflow-auto p-4"></div>
        </div>
    </div>

    {{-- === Modal Tanda Tangan (TTD) === --}}
    <div id="signPadModal" class="fixed inset-0 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeSignPad()"></div>
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg z-10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Tanda Tangani Scope Of Work</h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeSignPad()">✕</button>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-2">Silakan tanda tangan pada area di bawah ini:</p>
                    <canvas id="signaturePad" class="border rounded w-full" style="height:250px;"></canvas>
                    <input type="hidden" id="scopeOfWorkId" name="scopeOfWorkId" value="">
                </div>
                <div class="px-6 py-4 bg-gray-100 flex justify-end space-x-2">
                    <button type="button" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" onclick="clearSignature()">Clear</button>
                    <button type="button" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400" onclick="closeSignPad()">Cancel</button>
                    <button type="button" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600" onclick="saveSignature()">Save</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        
        /* === SIGNATURE PAD === */
        let signaturePad;
        function openSignPad(notificationNumber) {
            document.getElementById('signPadModal').classList.remove('hidden');
            document.getElementById('scopeOfWorkId').value = notificationNumber;

            const canvas = document.getElementById('signaturePad');
            canvas.width = canvas.offsetWidth;
            canvas.height = 250;
            const ctx = canvas.getContext("2d");
            ctx.strokeStyle = "#000";
            ctx.lineWidth = 2;

            let drawing = false;
            canvas.onmousedown = e => { drawing = true; ctx.beginPath(); ctx.moveTo(e.offsetX, e.offsetY); };
            canvas.onmousemove = e => { if (drawing) { ctx.lineTo(e.offsetX, e.offsetY); ctx.stroke(); } };
            canvas.onmouseup = () => drawing = false;
            canvas.onmouseleave = () => drawing = false;
            signaturePad = canvas;
        }

        function closeSignPad() { document.getElementById('signPadModal').classList.add('hidden'); }
        function clearSignature() { const ctx = signaturePad.getContext("2d"); ctx.clearRect(0, 0, signaturePad.width, signaturePad.height); }

        async function saveSignature() {
            const dataUrl = signaturePad.toDataURL("image/png");
            const notifId = document.getElementById('scopeOfWorkId').value;
            try {
                Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                const res = await fetch("{{ route('dokumen_orders.scope.saveSignature') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    body: JSON.stringify({ scope_of_work_id: notifId, tanda_tangan: dataUrl })
                });
                const json = await res.json().catch(() => null);
                if (!res.ok) throw new Error(json?.error || 'Gagal menyimpan tanda tangan.');
                Swal.fire("Sukses!", "Tanda tangan berhasil disimpan!", "success");
                closeSignPad();
                setTimeout(() => location.reload(), 800);
            } catch (err) {
                console.error(err);
                Swal.fire("Error", err.message || "Gagal menyimpan tanda tangan!", "error");
            }
        }

        /* === SOW AJAX (create/edit) === */
        const modalCreateBase = "{{ url('dokumen-orders/scopeofwork/modal-create') }}";
        const modalEditBase = "{{ url('dokumen-orders/scopeofwork/modal-edit') }}";

        async function openSowCreate(notificationNumber) {
            try {
                const res = await fetch(`${modalCreateBase}/${notificationNumber}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const html = await res.text();
                document.getElementById('sowModalBox').innerHTML = html;
                document.getElementById('sowModal').classList.remove('hidden');
            } catch {
                Swal.fire('Error', 'Gagal memuat form.', 'error');
            }
        }

        async function openSowEdit(notificationNumber) {
            try {
                const res = await fetch(`${modalEditBase}/${notificationNumber}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const html = await res.text();
                document.getElementById('sowModalBox').innerHTML = html;
                document.getElementById('sowModal').classList.remove('hidden');
            } catch {
                Swal.fire('Error', 'Gagal memuat form edit.', 'error');
            }
        }

        function closeSowModal() {
            document.getElementById('sowModal').classList.add('hidden');
            document.getElementById('sowModalBox').innerHTML = '';
        }

        /* === AJAX Upload === */
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.upload-input').forEach(input => {
                input.addEventListener('change', function () {
                    const form = this.closest('form');
                    if (form) form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                });
            });

            document.querySelectorAll('.upload-form').forEach(form => {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const fd = new FormData(form);
                    Swal.fire({ title: 'Mengupload...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    try {
                        const res = await fetch(form.action, { method: form.method, body: fd });
                        const json = await res.json().catch(() => null);
                        Swal.close();
                        if (!res.ok) {
                            Swal.fire('Gagal', json?.error || 'Upload gagal.', 'error');
                            return;
                        }
                        Swal.fire({ title: 'Sukses', text: json?.message || 'Dokumen berhasil diupload.', icon: 'success', timer: 1500, showConfirmButton: false });
                        setTimeout(() => location.reload(), 900);
                    } catch {
                        Swal.close();
                        Swal.fire('Error', 'Gagal upload (network).', 'error');
                    }
                });
            });
        });

        /* === Flash Messages === */
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({ title: 'Sukses!', text: {!! json_encode(session('success')) !!}, icon: 'success' });
            @endif
            @if(session('error'))
                Swal.fire({ title: 'Oops!', text: {!! json_encode(session('error')) !!}, icon: 'error' });
            @endif
            @if($errors->any())
                Swal.fire({ title: 'Gagal!', html: `{!! implode('<br>', $errors->all()) !!}`, icon: 'error' });
            @endif
        });
        // === Tambah baris SOW secara dinamis (berlaku untuk modal AJAX) ===
document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'modal-add-row') {
        const container = document.getElementById('modal-sow-rows');
        if (!container) return;

        const newRow = document.createElement('div');
        newRow.className = 'grid grid-cols-1 md:grid-cols-4 gap-4 mt-2';
        newRow.innerHTML = `
            <div>
                <label class="text-sm">Scope Pekerjaan</label>
                <input name="scope_pekerjaan[]" type="text" class="form-input w-full" required>
            </div>
            <div>
                <label class="text-sm">Qty</label>
                <input name="qty[]" type="text" class="form-input w-full" required>
            </div>
            <div>
                <label class="text-sm">Satuan</label>
                <input name="satuan[]" type="text" class="form-input w-full" required>
            </div>
            <div>
                <label class="text-sm">Keterangan</label>
                <input name="keterangan[]" type="text" class="form-input w-full">
            </div>
        `;
        container.appendChild(newRow);
    }
});

    </script>
    @endpush
</x-app-layout>
