<x-admin-layout>
    <div class="admin-card p-5">

        {{-- Header Action --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="admin-title">Daftar Struktur Organisasi</h1>
                <p class="admin-subtitle">PT. Semen Tonasa</p>
            </div>

            <a href="{{ route('admin.unit_work.create') }}"
               class="admin-btn admin-btn-primary">
                <i class="fas fa-plus text-xs"></i>
                Tambah Unit
            </a>
        </div>

        {{-- Info / meta --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                @if($units->total())
                    Menampilkan
                    <span class="font-semibold text-gray-700 dark:text-gray-200">
                        {{ $units->firstItem() }}–{{ $units->lastItem() }}
                    </span>
                    dari
                    <span class="font-semibold text-gray-700 dark:text-gray-200">
                        {{ $units->total() }}
                    </span>
                    unit kerja.
                @else
                    Tidak ada data unit kerja yang cocok dengan filter saat ini.
                @endif
            </div>
            <div class="text-xs text-gray-400 italic">
                Filter aktif:
                @php
                    $activeFilter = [];
                    if (request('q')) $activeFilter[] = 'Pencarian: "'.request('q').'"';
                    if (request('department')) {
                        $deptName = optional(
                            $departments->firstWhere('id', (int) request('department'))
                        )->name;
                        if ($deptName) $activeFilter[] = "Departemen: {$deptName}";
                    }
                @endphp

                @if($activeFilter)
                    {{ implode(' · ', $activeFilter) }}
                @else
                    Tidak ada filter
                @endif
            </div>
        </div>

        {{-- Filter --}}
        <form id="filterForm" method="GET"
              action="{{ route('admin.unit_work.index') }}"
              class="mb-6 grid grid-cols-1 md:grid-cols-12 gap-4
                     p-4 rounded-xl border border-gray-200 dark:border-gray-700
                     bg-gray-50 dark:bg-gray-900">

            {{-- Pencarian --}}
            <div class="md:col-span-5">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1 block">
                    Pencarian
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="q"
                           value="{{ request('q') }}"
                           placeholder="Cari berdasarkan nama unit atau seksi..."
                           class="admin-input w-full pl-9">
                </div>
            </div>

            {{-- Departemen --}}
            <div class="md:col-span-4">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1 block">
                    Departemen
                </label>
                <select name="department"
                        data-autosubmit="1"
                        class="admin-select w-full">
                    <option value="">Semua departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}"
                            {{ (int) request('department') === $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Per halaman & tombol --}}
            <div class="md:col-span-3 flex flex-col gap-2">
                <div>
                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1 block">
                        Per Halaman
                    </label>
                    <select name="entries"
                            data-autosubmit="1"
                            class="admin-select w-full">
                        @foreach([10,25,50,100] as $n)
                            <option value="{{ $n }}" {{ request('entries',10)==$n?'selected':'' }}>
                                {{ $n }} data
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 mt-1">
                    <button type="submit"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium
                                   bg-blue-600 hover:bg-blue-700 text-white">
                        Terapkan
                    </button>
                    <a href="{{ route('admin.unit_work.index') }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium
                              bg-gray-100 hover:bg-gray-200 text-gray-700
                              dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-100">
                        Reset
                    </a>
                </div>
            </div>
        </form>

      {{-- CARD GRID --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    @forelse($units as $unit)

        @php
            $seksis = method_exists($unit,'getSeksiListAttribute')
                ? $unit->seksi_list
                : (is_array($unit->seksi) ? $unit->seksi : []);

            $sectionsByName = $unit->relationLoaded('sections')
                ? $unit->sections->keyBy('name')
                : collect();
        @endphp

        <div class="relative p-5 rounded-2xl border
                    border-gray-200 dark:border-gray-700
                    bg-white dark:bg-gray-900
                    hover:shadow-md hover:border-blue-200 dark:hover:border-blue-500/60
                    transition duration-150 ease-out">

            {{-- Action --}}
            <div class="absolute top-4 right-4 flex gap-1.5">
                <a href="{{ route('admin.unit_work.edit',$unit->id) }}"
                   class="w-9 h-9 flex items-center justify-center
                          rounded-lg bg-blue-50 text-blue-600
                          hover:bg-blue-100 text-xs
                          dark:bg-blue-900/30 dark:text-blue-300">
                    <i class="fas fa-edit"></i>
                </a>

                <form action="{{ route('admin.unit_work.destroy',$unit->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-9 h-9 flex items-center justify-center
                                   rounded-lg bg-red-50 text-red-600
                                   hover:bg-red-100 text-xs
                                   dark:bg-red-900/30 dark:text-red-300
                                   btn-delete"
                            data-name="{{ $unit->name }}">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>

            {{-- DEPARTEMEN (TITLE BESAR) + GENERAL MANAGER --}}
            <div class="mb-4 flex items-start justify-between gap-3">
                <div class="space-y-1">
                    @if($unit->department)
                        <p class="text-[11px] font-semibold uppercase tracking-wide
                                  text-emerald-600 dark:text-emerald-300">
                            Departemen
                        </p>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            {{ $unit->department->name }}
                        </h3>

                        @if($unit->department->generalManager)
                            <div class="mt-2 flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                                <span class="inline-flex items-center justify-center
                                             w-7 h-7 rounded-full bg-amber-100 text-amber-700
                                             dark:bg-amber-900/40 dark:text-amber-200 text-[11px] font-semibold">
                                    GM
                                </span>
                                <div>
                                    <div class="font-semibold">
                                        {{ $unit->department->generalManager->name }}
                                    </div>
                                    <div class="text-[11px] text-gray-400 dark:text-gray-500">
                                        General Manager {{ $unit->department->name }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200">
                            Tanpa Departemen
                        </h3>
                    @endif
                </div>

                <div class="flex flex-col items-end gap-1 mt-6">
                    <span class="inline-flex items-center px-2 py-1 rounded-full
                                 text-[11px] font-medium
                                 bg-gray-100 text-gray-600
                                 dark:bg-gray-800 dark:text-gray-300">
                        <i class="fas fa-layer-group mr-1 text-[10px]"></i>
                        {{ $seksis ? count($seksis).' seksi' : '0 seksi' }}
                    </span>
                </div>
            </div>

            {{-- UNIT (LEBIH KECIL) + SENIOR MANAGER --}}
            <div class="mb-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide
                          text-sky-600 dark:text-sky-300">
                    Unit Kerja
                </p>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    {{ $unit->name }}
                </h4>

                @if($unit->seniorManager)
                    <div class="mt-2 flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                        <span class="inline-flex items-center justify-center
                                     w-7 h-7 rounded-full bg-sky-100 text-sky-700
                                     dark:bg-sky-900/40 dark:text-sky-200 text-[11px] font-semibold">
                            SM
                        </span>
                        <div>
                            <div class="font-semibold">
                                {{ $unit->seniorManager->name }}
                            </div>
                            <div class="text-[11px] text-gray-400 dark:text-gray-500">
                                Senior Manager {{ $unit->name }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Garis pemisah --}}
            <div class="border-t border-dashed border-gray-200 dark:border-gray-700 my-3"></div>

            {{-- SEKSIs & MANAGER (LEVEL: Manager) --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">
                        Seksi & Manager
                    </span>
                    <span class="text-[11px] text-gray-400">
                        Level Manager
                    </span>
                </div>

                @if($seksis)
                    <div class="space-y-1.5">
                        @foreach($seksis as $sx)
                            @php
                                /** @var \App\Models\UnitWorkSection|null $section */
                                $section = $sectionsByName->get($sx);
                                $manager = $section?->manager;
                            @endphp

                            <div class="flex gap-3 items-start">
                                {{-- garis & bullet kiri --}}
                                <div class="flex flex-col items-center">
                                    <span class="w-1 h-1 rounded-full bg-indigo-500 dark:bg-indigo-300 mt-1"></span>
                                    <span class="flex-1 w-px bg-gray-200 dark:bg-gray-700 mt-1"></span>
                                </div>

                                <div class="flex-1">
                                    <div class="inline-flex items-center px-2 py-0.5 rounded-full
                                                text-[11px] font-medium
                                                bg-indigo-100 text-indigo-800
                                                dark:bg-indigo-900/40 dark:text-indigo-300">
                                        <i class="fas fa-sitemap mr-1 text-[10px]"></i>
                                        {{ $sx }}
                                    </div>

                                    <div class="mt-1 text-[11px] text-gray-600 dark:text-gray-300">
                                        @if($manager)
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fas fa-user text-[10px]"></i>
                                                <span>
                                                    Manager:
                                                    <span class="font-semibold">{{ $manager->name }}</span>
                                                </span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-gray-400">
                                                <i class="fas fa-user-slash text-[10px]"></i>
                                                <span>Manager belum ditetapkan</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-500 italic mt-1">
                        Tidak memiliki seksi terdaftar.
                    </p>
                @endif
            </div>
        </div>

    @empty
        <div class="col-span-full text-center py-16 text-gray-500 dark:text-gray-400">
            <i class="fas fa-folder-open text-4xl mb-4 opacity-60"></i>
            <p class="font-semibold text-gray-700 dark:text-gray-200 mb-1">
                Belum ada unit kerja
            </p>
            <p class="text-sm mb-4">
                Tambahkan unit kerja pertama untuk mulai menyusun struktur organisasi.
            </p>
            <a href="{{ route('admin.unit_work.create') }}"
               class="admin-btn admin-btn-primary">
                <i class="fas fa-plus text-xs"></i>
                Tambah Unit
            </a>
        </div>
    @endforelse
</div>


        {{-- Pagination --}}
        <div class="mt-6">
            {{ $units->withQueryString()->links() }}
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Flash success
                @if(session('success'))
                    const successMessage = @json(session('success'));
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: successMessage,
                        timer: 2200,
                        showConfirmButton: false
                    });
                @endif

                // Konfirmasi hapus
                document.addEventListener('click', e => {
                    const btn = e.target.closest('.btn-delete');
                    if (!btn) return;

                    e.preventDefault();
                    const form = btn.closest('form');
                    const name = btn.dataset.name || 'unit ini';

                    Swal.fire({
                        title: 'Hapus unit?',
                        html: `Yakin ingin menghapus <b>${name}</b>?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });

                // Auto submit untuk select filter tertentu
                const filterForm = document.getElementById('filterForm');
                if (filterForm) {
                    filterForm.querySelectorAll('[data-autosubmit="1"]').forEach(el => {
                        el.addEventListener('change', () => filterForm.submit());
                    });
                }
            });
        </script>
    @endpush
</x-admin-layout>
