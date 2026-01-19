<x-admin-layout>
    <div class="py-6">
        <div class="w-full max-w-[98%] mx-auto">

            <div class="admin-card p-5 mb-4">
                <div class="admin-header">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h1 class="admin-title">Garansi</h1>
                            <p class="admin-subtitle">Kelola masa garansi berdasarkan LHPP dan Garansi</p>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.garansi.index') }}" class="admin-filter mt-4">
                    <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-5">
                            <label class="text-xs text-slate-600 block mb-1">Pencarian (Nomor Order)</label>
                            <div class="relative">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Masukkan Nomor Order..."
                                    class="admin-input w-full pl-9">
                            </div>
                        </div>

                        <div class="md:col-span-7 flex items-center gap-2 md:justify-end">
                            <button type="submit" class="admin-btn admin-btn-primary">
                                <i data-lucide="search" class="w-4 h-4"></i> Cari
                            </button>

                            <a href="{{ route('admin.garansi.index') }}" class="admin-btn admin-btn-ghost">
                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

<!-- Desktop TABLE -->
            <div class="hidden md:block admin-card p-0 overflow-x-auto">
                <table class="min-w-full text-[11px] text-slate-700">
                    <thead class="bg-slate-50 text-slate-600 uppercase tracking-wide border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Nomor Order</th>
                            <th class="px-4 py-3 text-left font-semibold">Mulai Garansi</th>
                            <th class="px-4 py-3 text-left font-semibold">Berakhir Garansi</th>
                            <th class="px-4 py-3 text-left font-semibold">Garansi</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Gambar</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-slate-100">
                        @forelse($garansiList as $g)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $g['order_number'] }}</div>
                                    <div class="text-[10px] text-slate-500 mt-1">
                                        @if(! ($g['lpj_present'] ?? false))
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-[10px]">
                                                <i class="fas fa-exclamation-triangle text-[9px]"></i>
                                                LPJ belum terisi
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-slate-700">
                                    @if(isset($g['ttd_date']) && $g['ttd_date'] !== '-' && $g['ttd_date'] !== null)
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fas fa-calendar-check text-[10px] text-emerald-600"></i>
                                            {{ $g['ttd_date'] }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-slate-700">
                                    @if(array_key_exists('garansi_months', $g) && $g['garansi_months'] !== null)
                                        @if(isset($g['end_date']) && $g['end_date'] !== '-' && $g['end_date'] !== null)
                                            {{ $g['end_date'] }}
                                            <span class="text-[10px] text-slate-500">({{ $g['garansi_months'] }} {{ \Illuminate\Support\Str::plural('Bln', (int)$g['garansi_months']) }})</span>
                                        @else
                                            <span class="text-[10px] text-slate-500">
                                                {{ $g['garansi_months'] }} {{ \Illuminate\Support\Str::plural('Bulan', (int)$g['garansi_months']) }}
                                                <small class="text-slate-400"> (Belum ada tanggal akhir)</small>
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-slate-700">
                                    @if(array_key_exists('garansi_months', $g) && $g['garansi_months'] !== null)
                                        @if($g['garansi_months'] === 0)
                                            <span class="text-[10px] inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                                <i class="fas fa-ban text-[9px]"></i> 0 Bulan (Tanpa Garansi)
                                            </span>
                                        @else
                                            <span class="text-[10px] inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">
                                                <i class="fas fa-clock text-[9px]"></i> {{ $g['garansi_months'] }} Bulan
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 text-[10px]">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @php
                                        $status = $g['status'] ?? null;
                                        $has3Ttd = $g['has_3_ttd'] ?? false;
                                    @endphp

                                    @if($status === 'Masih Berlaku')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] rounded-full">
                                            <i class="fas fa-check-circle text-[9px]"></i> Masih Berlaku
                                        </span>
                                    @elseif($status === 'Habis')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-100 text-rose-800 text-[10px] rounded-full">
                                            <i class="fas fa-times-circle text-[9px]"></i> Habis
                                        </span>
                                    @else
                                        @if(! $has3Ttd)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-800 text-[10px] rounded-full">
                                                <i class="fas fa-pen-nib text-[9px]"></i> LHPP Belum ada TTD
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-[10px]">-</span>
                                        @endif
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if(!empty($g['gambar']) && is_array($g['gambar']) && count($g['gambar']) > 0)
                                        <button
                                            onclick="openModal({{ json_encode($g['gambar']) }})"
                                            class="inline-flex items-center gap-2 text-sky-600 hover:text-sky-700 text-[11px] font-medium"
                                            title="Lihat gambar pekerjaan">
                                            <i class="fas fa-images text-[12px]"></i>
                                            <span>Lihat</span>
                                            <span class="text-[10px] text-slate-500">({{ count($g['gambar']) }})</span>
                                        </button>
                                    @else
                                        <span class="text-slate-400 text-[10px]">Belum ada</span>
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
                    <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-slate-800 text-sm">{{ $g['order_number'] }}</div>
                                <div class="text-[10px] text-slate-500 mt-1">
                                    @if(! ($g['lpj_present'] ?? false))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-[10px]">
                                            <i class="fas fa-exclamation-triangle text-[9px]"></i>
                                            LPJ belum terisi
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right">
                                @php $status = $g['status'] ?? null; $has3Ttd = $g['has_3_ttd'] ?? false; @endphp
                                @if($status === 'Masih Berlaku')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] rounded-full">
                                        <i class="fas fa-check-circle text-[9px]"></i> Masih
                                    </span>
                                @elseif($status === 'Habis')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-100 text-rose-800 text-[10px] rounded-full">
                                        <i class="fas fa-times-circle text-[9px]"></i> Habis
                                    </span>
                                @else
                                    @if(! $has3Ttd)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-800 text-[10px] rounded-full">
                                            <i class="fas fa-pen-nib text-[9px]"></i> LHPP Belum ada TTD
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[10px]">-</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2 text-[11px] text-slate-700">
                            <div>
                                <div class="text-slate-500 text-[10px]">Mulai</div>
                                <div class="mt-1">{{ $g['ttd_date'] ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-slate-500 text-[10px]">Berakhir</div>
                                @if(array_key_exists('garansi_months', $g) && $g['garansi_months'] !== null)
                                    <div class="mt-1">
                                        {{ $g['end_date'] ?? '-' }}
                                        <div class="text-[10px] text-slate-500">({{ $g['garansi_months'] }} bln)</div>
                                    </div>
                                @else
                                    <div class="mt-1 text-slate-400">-</div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <div>
                                @if(!empty($g['gambar']) && is_array($g['gambar']) && count($g['gambar']) > 0)
                                    <button onclick="openModal({{ json_encode($g['gambar']) }})" class="inline-flex items-center gap-2 text-sky-600 text-[11px] hover:text-sky-700">
                                        <i class="fas fa-images text-[12px]"></i> Lihat ({{ count($g['gambar']) }})
                                    </button>
                                @else
                                    <span class="text-slate-400 text-[10px]">Tidak ada gambar</span>
                                @endif
                            </div>

                            <div class="text-[10px] text-slate-500">
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
