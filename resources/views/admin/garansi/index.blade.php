<x-admin-layout>
    <div class="py-6">
        <div class="w-full max-w-[98%] mx-auto">

            <!-- Header -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Garansi</h3>
                        <p class="text-sm text-gray-500">Kelola masa garansi berdasarkan LHPP & Garansi</p>
                    </div>

                    <form method="GET" action="{{ route('admin.garansi.index') }}" class="flex gap-3 items-end">
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
                                class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-2 rounded-md text-sm transition">
                                <i class="fas fa-undo-alt"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Desktop TABLE -->
            <div class="hidden md:block bg-white shadow rounded-lg border border-gray-200 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left font-medium text-gray-700">Nomor Order</th>
                            <th class="px-5 py-3 text-left font-medium text-gray-700">Mulai Garansi</th>
                            <th class="px-5 py-3 text-left font-medium text-gray-700">Berakhir Garansi</th>
                            <th class="px-5 py-3 text-left font-medium text-gray-700">Garansi</th>
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
                                        @if(! ($g['lpj_present'] ?? false))
                                            <span class="px-2 py-1 bg-yellow-50 text-yellow-700 rounded text-xs">LPJ belum terisi</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    @if(isset($g['ttd_date']) && $g['ttd_date'] !== '-' && $g['ttd_date'] !== null)
                                        {{ $g['ttd_date'] }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    @if(array_key_exists('garansi_months', $g) && $g['garansi_months'] !== null)
                                        @if(isset($g['end_date']) && $g['end_date'] !== '-' && $g['end_date'] !== null)
                                            {{ $g['end_date'] }}
                                            <span class="text-xs text-gray-500">({{ $g['garansi_months'] }} {{ \Illuminate\Support\Str::plural('Bln', (int)$g['garansi_months']) }})</span>
                                        @else
                                            <span class="text-xs text-gray-500">
                                                {{ $g['garansi_months'] }} {{ \Illuminate\Support\Str::plural('Bulan', (int)$g['garansi_months']) }}
                                                <small class="text-gray-400"> (Belum ada tanggal akhir)</small>
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-gray-700">
                                    @if(array_key_exists('garansi_months', $g) && $g['garansi_months'] !== null)
                                        @if($g['garansi_months'] === 0)
                                            <span class="text-xs inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-700">0 Bulan (Tanpa Garansi)</span>
                                        @else
                                            <span class="text-xs inline-flex items-center px-2 py-1 rounded bg-indigo-50 text-indigo-700">{{ $g['garansi_months'] }} Bulan</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    @php
                                        $status = $g['status'] ?? null;
                                        $has3Ttd = $g['has_3_ttd'] ?? false;
                                    @endphp

                                    @if($status === 'Masih Berlaku')
                                        <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full">Masih Berlaku</span>
                                    @elseif($status === 'Habis')
                                        <span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-800 text-xs rounded-full">Habis</span>
                                    @else
                                        @if(! $has3Ttd)
                                            <span class="inline-flex items-center px-3 py-1 bg-yellow-50 text-yellow-800 text-xs rounded-full">Belum ada TTD</span>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    @if(!empty($g['gambar']) && is_array($g['gambar']) && count($g['gambar']) > 0)
                                        <button
                                            onclick="openModal({{ json_encode($g['gambar']) }})"
                                            class="text-indigo-600 hover:underline text-sm inline-flex items-center gap-2"
                                            title="Lihat gambar pekerjaan">
                                            <i class="fas fa-images"></i> Lihat ({{ count($g['gambar']) }})
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-xs">Belum ada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    Tidak ada data garansi
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile LIST (cards) -->
            <div class="md:hidden space-y-3">
                @forelse($garansiList as $g)
                    <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-medium text-gray-800 text-sm">{{ $g['order_number'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    @if(! ($g['lpj_present'] ?? false))
                                        <span class="px-2 py-0.5 bg-yellow-50 text-yellow-700 rounded text-xs">LPJ belum terisi</span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right">
                                @php $status = $g['status'] ?? null; $has3Ttd = $g['has_3_ttd'] ?? false; @endphp
                                @if($status === 'Masih Berlaku')
                                    <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Masih</span>
                                @elseif($status === 'Habis')
                                    <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Habis</span>
                                @else
                                    @if(! $has3Ttd)
                                        <span class="inline-flex items-center px-2 py-1 bg-yellow-50 text-yellow-800 text-xs rounded-full">Belum TTD</span>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-gray-700">
                            <div>
                                <div class="text-gray-500 text-[11px]">Mulai</div>
                                <div class="mt-1">{{ $g['ttd_date'] ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500 text-[11px]">Berakhir</div>
                                @if(array_key_exists('garansi_months', $g) && $g['garansi_months'] !== null)
                                    <div class="mt-1">
                                        {{ $g['end_date'] ?? '-' }}
                                        <div class="text-[11px] text-gray-500">({{ $g['garansi_months'] }} bln)</div>
                                    </div>
                                @else
                                    <div class="mt-1 text-gray-400">-</div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <div>
                                @if(!empty($g['gambar']) && is_array($g['gambar']) && count($g['gambar']) > 0)
                                    <button onclick="openModal({{ json_encode($g['gambar']) }})" class="text-indigo-600 text-xs hover:underline inline-flex items-center gap-2">
                                        <i class="fas fa-images"></i> Lihat ({{ count($g['gambar']) }})
                                    </button>
                                @else
                                    <span class="text-gray-400 text-xs">Tidak ada gambar</span>
                                @endif
                            </div>

                            <div class="text-xs text-gray-500">
                                {{ $g['garansi_label'] ?? '' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-8">Tidak ada data garansi</div>
                @endforelse
            </div>

            {{-- pagination placeholder (jika controller return paginate) --}}
            @if(method_exists($garansiList, 'links'))
                <div class="mt-4">{{ $garansiList->links() }}</div>
            @endif

        </div>
    </div>

    <!-- Modal Preview Gambar -->
    <div id="imageModal" class="fixed inset-0 z-50 bg-black bg-opacity-60 hidden items-center justify-center p-4">
        <div id="modalContent" class="relative w-full max-w-4xl bg-white rounded-lg shadow-lg p-4">
            <button id="closeModalBtn"
                class="absolute top-3 right-3 text-gray-600 hover:text-red-600 text-2xl leading-none" aria-label="Close">&times;</button>

            <div class="relative">
                <div id="carouselContainer" class="flex transition-transform duration-300 ease-in-out"></div>

                <button id="prevBtn" class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-gray-800 bg-opacity-60 text-white p-2 rounded-full hover:bg-opacity-90" aria-label="Previous">&lt;</button>
                <button id="nextBtn" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-800 bg-opacity-60 text-white p-2 rounded-full hover:bg-opacity-90" aria-label="Next">&gt;</button>
            </div>
        </div>
    </div>

    <script>
        (function(){
            let currentIndex = 0;
            let images = [];

            function el(id){ return document.getElementById(id); }

            window.openModal = function(imgList) {
                const modal = el('imageModal');
                const container = el('carouselContainer');

                images = Array.isArray(imgList) ? imgList : [imgList];
                container.innerHTML = '';
                currentIndex = 0;

                images.forEach((img, i) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'w-full flex-shrink-0 flex items-center justify-center';
                    wrapper.style.minWidth = '100%';

                    const imgEl = document.createElement('img');
                    imgEl.alt = 'Gambar pekerjaan';
                    imgEl.className = 'max-h-96 object-contain';

                    // resolve src
                    if (typeof img === 'string') {
                        const src = img.startsWith('/storage/') || img.startsWith('http') ? img : ('/storage/' + img);
                        imgEl.src = src;
                    } else if (img && (img.path || img.url)) {
                        const p = img.path ?? img.url;
                        const src = p.startsWith('/storage/') || p.startsWith('http') ? p : ('/storage/' + p);
                        imgEl.src = src;
                    } else {
                        imgEl.src = '/assets/img/no-image.png';
                    }

                    wrapper.appendChild(imgEl);
                    container.appendChild(wrapper);
                });

                updateCarousel();
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            }

            window.closeModal = function() {
                const modal = el('imageModal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            }

            function updateCarousel() {
                const container = el('carouselContainer');
                if (!container) return;
                container.style.transform = `translateX(-${currentIndex * 100}%)`;
            }

            // Safe attach handlers
            const prevBtn = el('prevBtn');
            const nextBtn = el('nextBtn');
            const closeBtn = el('closeModalBtn');
            const modalRoot = el('imageModal');

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    if (!images.length) return;
                    currentIndex = (currentIndex === 0) ? images.length - 1 : currentIndex - 1;
                    updateCarousel();
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    if (!images.length) return;
                    currentIndex = (currentIndex === images.length - 1) ? 0 : currentIndex + 1;
                    updateCarousel();
                });
            }
            if (closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }

            if (modalRoot) {
                modalRoot.addEventListener('click', (e) => {
                    const modalContent = el('modalContent');
                    if (modalContent && !modalContent.contains(e.target)) closeModal();
                });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeModal();
                if (e.key === 'ArrowLeft') { if (prevBtn) prevBtn.click(); }
                if (e.key === 'ArrowRight') { if (nextBtn) nextBtn.click(); }
            });
        })();
    </script>
</x-admin-layout>
