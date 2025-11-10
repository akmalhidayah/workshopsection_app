<x-admin-layout>
    <div class="py-6">
        <div class="w-full max-w-[98%] mx-auto">

            <!-- Header / Title -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 mb-4 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Garansi</h3>
                        <p class="text-sm text-gray-500">Kelola masa garansi berdasarkan LHPP & LPJ</p>
                    </div>

                    <!-- Filter: hanya search by notification_number sesuai controller -->
                    <form method="GET" action="{{ route('admin.garansi.index') }}" class="flex flex-col sm:flex-row items-end gap-3">
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Pencarian (Nomor Order)</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Masukkan Nomor Order..."
                                class="px-3 py-2 border border-gray-300 rounded-md w-56 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit"
                                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm shadow-sm transition">
                                <i class="fas fa-search"></i> Cari
                            </button>

                            <a href="{{ route('admin.garansi.index') }}"
                                class="inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-2 rounded-md text-sm transition">
                                <i class="fas fa-undo-alt"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table container -->
            <div class="bg-white shadow rounded-lg border border-gray-200 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left font-medium text-gray-700">Nomor Order</th>
                            <th class="px-5 py-3 text-left font-medium text-gray-700">Mulai Garansi</th>
                            <th class="px-5 py-3 text-left font-medium text-gray-700">Berakhir Garansi</th>
                            <th class="px-5 py-3 text-left font-medium text-gray-700">Status</th>
                            <th class="px-5 py-3 text-left font-medium text-gray-700">Gambar</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($garansiList as $g)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-800">{{ $g['order_number'] }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        @if(! $g['lpj_present'])
                                            <span class="px-2 py-1 bg-yellow-50 text-yellow-700 rounded text-xs">LPJ belum terisi</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    @if($g['ttd_date'] !== '-')
                                        {{ $g['ttd_date'] }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    @if($g['garansi_months'])
                                        @if($g['end_date'] !== '-')
                                            {{ $g['end_date'] }} <span class="text-xs text-gray-500">({{ $g['garansi_months'] }} bln)</span>
                                        @else
                                            <span class="text-xs text-gray-500">Belum ada</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    @if($g['status'] === 'Masih Berlaku')
                                        <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Masih Berlaku</span>
                                    @elseif($g['status'] === 'Habis')
                                        <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 text-xs rounded">Habis</span>
                                    @else
                                        {{-- kemungkinan: belum ada ttd lengkap atau belum ada garansi --}}
                                        @if(! $g['has_3_ttd'])
                                            <span class="inline-flex items-center px-2 py-1 bg-yellow-50 text-yellow-800 text-xs rounded">Belum ada</span>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    @if(!empty($g['gambar']))
                                        <button onclick="openModal({{ json_encode($g['gambar']) }})" class="text-indigo-600 hover:underline text-sm">
                                            Lihat ({{ count($g['gambar']) }})
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-xs">Belum ada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    Tidak ada data garansi
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Jika ingin pagination nanti, controller harus mengembalikan paginate dan tampilkan links di sini --}}
            {{-- <div class="mt-4">{{ $garansiList->links() }}</div> --}}
        </div>
    </div>

    <!-- Modal Preview Gambar -->
    <div id="imageModal" class="fixed inset-0 z-50 bg-black bg-opacity-60 hidden items-center justify-center">
        <div id="modalContent" class="relative w-full max-w-4xl bg-white rounded-lg shadow-lg p-4">
            <button onclick="closeModal()"
                class="absolute top-3 right-3 text-gray-600 hover:text-red-600 text-2xl leading-none">&times;</button>

            <div class="relative">
                <div id="carouselContainer" class="flex transition-transform duration-300 ease-in-out"></div>

                <button id="prevBtn" class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-gray-800 bg-opacity-60 text-white p-2 rounded-full hover:bg-opacity-90">&lt;</button>
                <button id="nextBtn" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-800 bg-opacity-60 text-white p-2 rounded-full hover:bg-opacity-90">&gt;</button>
            </div>
        </div>
    </div>

    <script>
        let currentIndex = 0;
        let images = [];

        function openModal(imgList) {
            const modal = document.getElementById('imageModal');
            const container = document.getElementById('carouselContainer');

            images = Array.isArray(imgList) ? imgList : [imgList];
            container.innerHTML = '';
            currentIndex = 0;

            images.forEach((img, i) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'w-full flex-shrink-0 flex items-center justify-center';
                wrapper.style.minWidth = '100%';

                const imgEl = document.createElement('img');

                // safe: kalau path sudah mengandung '/storage/' atau 'http' jangan tambahkan lagi
                if (typeof img === 'string') {
                    const src = img.startsWith('/storage/') || img.startsWith('http') ? img : ('/storage/' + img);
                    imgEl.src = src;
                } else if (img.path) {
                    const src = img.path.startsWith('/storage/') || img.path.startsWith('http') ? img.path : ('/storage/' + img.path);
                    imgEl.src = src;
                } else {
                    imgEl.src = '/assets/img/no-image.png'; // fallback (pastikan ada asset ini) 
                }

                imgEl.alt = 'Gambar pekerjaan';
                imgEl.className = 'max-h-96 object-contain';

                wrapper.appendChild(imgEl);
                container.appendChild(wrapper);
            });

            updateCarousel();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function updateCarousel() {
            const container = document.getElementById('carouselContainer');
            container.style.transform = `translateX(-${currentIndex * 100}%)`;
        }

        document.getElementById('prevBtn').addEventListener('click', () => {
            if (!images.length) return;
            currentIndex = (currentIndex === 0) ? images.length - 1 : currentIndex - 1;
            updateCarousel();
        });

        document.getElementById('nextBtn').addEventListener('click', () => {
            if (!images.length) return;
            currentIndex = (currentIndex === images.length - 1) ? 0 : currentIndex + 1;
            updateCarousel();
        });

        // close if click outside modalContent
        document.getElementById('imageModal').addEventListener('click', (e) => {
            const modalContent = document.getElementById('modalContent');
            if (!modalContent.contains(e.target)) closeModal();
        });

        // esc to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
            if (e.key === 'ArrowLeft') document.getElementById('prevBtn').click();
            if (e.key === 'ArrowRight') document.getElementById('nextBtn').click();
        });
    </script>
</x-admin-layout>
