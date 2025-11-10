<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Daftar Unit Kerja</h2>
    </x-slot>

    <div class="p-6 bg-white dark:bg-gray-800 shadow rounded-lg">
        {{-- Flash success (SweetAlert) --}}
        @if(session('success'))
            <script>
                (function(){
                    const run = () => Swal.fire({icon:'success', title:'Berhasil', text:'{{ session('success') }}', timer:2000, showConfirmButton:false});
                    if (window.Swal) return run();
                    const s=document.createElement('script'); s.src="https://cdn.jsdelivr.net/npm/sweetalert2@11"; s.onload=run; document.head.appendChild(s);
                })();
            </script>
        @endif

        {{-- Header + Add --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Data Unit Kerja</h3>
            <a href="{{ route('admin.unit_work.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                <i class="fas fa-plus"></i><span>Tambah</span>
            </a>
        </div>

        {{-- Filter Bar (auto-submit) --}}
        <form id="filterForm" method="GET" action="{{ route('admin.unit_work.index') }}"
              class="mb-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3 flex flex-col sm:flex-row gap-3 sm:items-end">
            <div class="sm:flex-1">
                <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300 mb-1 block">Pencarian</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama unit / seksi…"
                       autocomplete="off"
                       class="w-full px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 text-sm">
            </div>
            <div>
                <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300 mb-1 block">Per halaman</label>
                <select name="entries"
                        class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-800 dark:text-gray-100">
                    @foreach([10,25,50,100] as $n)
                        <option value="{{ $n }}" {{ (int)request('entries', 10) === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-auto">
                {{-- Tombol dibiarkan sebagai fallback jika JS mati --}}
                <button type="submit" class="mt-1 sm:mt-6 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="{{ route('admin.unit_work.index') }}"
                   class="mt-1 sm:mt-6 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm ml-2">
                    Reset
                </a>
            </div>
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider">Unit Kerja & Seksi</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse ($units as $unit)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                            <td class="px-4 py-3">
                                {{-- Unit Name --}}
                                <div class="text-gray-900 dark:text-gray-100 font-semibold">
                                    {{ $unit->name }}
                                </div>

                                {{-- Seksi chips --}}
                                @php
                                    $chips = method_exists($unit,'getSeksiListAttribute')
                                        ? $unit->seksi_list
                                        : (is_array($unit->seksi) ? $unit->seksi : []);
                                @endphp

                                @if(!empty($chips))
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        @foreach($chips as $sx)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                                                         bg-indigo-100 text-indigo-800 ring-1 ring-indigo-200
                                                         dark:bg-indigo-900/40 dark:text-indigo-300 dark:ring-indigo-800">
                                                <i class="fas fa-sitemap text-[10px] mr-1 opacity-80"></i>{{ $sx }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-1 text-xs text-gray-500 italic">Belum ada seksi.</div>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.unit_work.edit', $unit->id) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100
                                              dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.unit_work.destroy', $unit->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 text-red-600 hover:bg-red-100
                                                   dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50 btn-delete"
                                            title="Hapus"
                                            data-name="{{ $unit->name }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-10">
                                <div class="text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-folder-open text-2xl mb-2"></i>
                                    <div class="font-medium">Belum ada data unit kerja.</div>
                                    <div class="text-xs mt-1">Klik tombol <b>Tambah</b> untuk membuat unit kerja baru.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $units->withQueryString()->links() }}
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
        // Auto-search & auto-filter
        (function() {
          const form   = document.getElementById('filterForm');
          if (!form) return;

          const inputQ = form.querySelector('input[name="q"]');
          const selEnt = form.querySelector('select[name="entries"]');

          // prevent submit on Enter (biar nggak reload ketika mengetik)
          inputQ?.addEventListener('keydown', e => { if (e.key === 'Enter') e.preventDefault(); });

          // debounce helper
          function debounce(fn, delay) {
            let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
          }

          let lastQ = inputQ ? inputQ.value : '';
          const submitSearch = debounce(() => {
            if (!inputQ) return;
            const val = inputQ.value;
            if (val === lastQ) return;
            lastQ = val;
            form.requestSubmit ? form.requestSubmit() : form.submit();
          }, 400);

          inputQ?.addEventListener('input', submitSearch);
          selEnt?.addEventListener('change', () => form.requestSubmit ? form.requestSubmit() : form.submit());
        })();

        // Konfirmasi hapus (SweetAlert, fallback confirm)
        document.addEventListener('click', function(e){
            const btn = e.target.closest('.btn-delete');
            if(!btn) return;
            e.preventDefault();
            const form = btn.closest('form');
            const name = btn.dataset.name || 'unit kerja ini';

            const run = () => Swal.fire({
                title: 'Hapus?',
                html: `Yakin ingin menghapus <b>${name}</b>?<br><span class="text-sm text-gray-500">Tindakan ini tidak bisa dibatalkan.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then(r => { if (r.isConfirmed) form.submit(); });

            if (window.Swal) return run();
            if (confirm('Yakin ingin menghapus ' + name + '?')) form.submit();
        }, false);
        </script>
    @endpush
</x-admin-layout>
