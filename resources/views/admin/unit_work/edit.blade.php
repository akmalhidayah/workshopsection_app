<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                    Edit Unit Kerja
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Sesuaikan departemen, nama unit, dan daftar seksi untuk struktur organisasi.
                </p>
            </div>

            <a href="{{ route('admin.unit_work.index') }}"
               class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md
                      bg-gray-100 hover:bg-gray-200 text-gray-700
                      dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100">
                <span class="mr-1">⟵</span> Kembali ke daftar
            </a>
        </div>
    </x-slot>

    <div class="p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg max-w-3xl mx-auto">
        {{-- Alert error global --}}
        @if ($errors->has('error'))
            <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 px-4 py-2 text-sm text-red-700 dark:text-red-200">
                {{ $errors->first('error') }}
            </div>
        @endif

        {{-- Preview kecil struktur --}}
        <div class="mb-6 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 p-4 bg-gray-50/60 dark:bg-gray-900/60">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">
                Departemen
            </p>
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                {{ optional($unitWork->department)->name ?? 'Belum di-set' }}
            </h3>

            <div class="mt-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-600 dark:text-sky-300">
                    Unit Kerja
                </p>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                    {{ $unitWork->name }}
                </p>
            </div>
        </div>

        <form id="unitEditForm"
              action="{{ route('admin.unit_work.update', $unitWork->id) }}"
              method="POST"
              autocomplete="off"
              novalidate>
            @csrf
            @method('PUT')

            {{-- BARIS 1: Departemen + Unit --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Departemen --}}
                <div class="space-y-1">
                    <label for="department_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                        Departemen <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="department_id"
                        name="department_id"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 mt-0.5
                               bg-white dark:bg-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none text-sm"
                        required
                    >
                        <option value="" disabled {{ old('department_id', $unitWork->department_id) ? '' : 'selected' }}>
                            — Pilih Departemen —
                        </option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ (int) old('department_id', $unitWork->department_id) === $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Departemen induk dari unit kerja ini.
                    </p>
                </div>

                {{-- Nama Unit Kerja --}}
                <div class="space-y-1">
                    <label for="name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                        Nama Unit Kerja <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        maxlength="255"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 mt-0.5
                               bg-white dark:bg-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none text-sm"
                        value="{{ old('name', $unitWork->name) }}"
                        required
                    >
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Gunakan nama unit yang konsisten dengan dokumen resmi.
                    </p>
                </div>
            </div>

            {{-- BARIS 2: GM & Senior Manager --}}
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- General Manager departemen --}}
                <div class="space-y-1">
                    <label for="general_manager_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                        General Manager (Departemen)
                    </label>
                    <select
                        id="general_manager_id"
                        name="general_manager_id"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 mt-0.5
                               bg-white dark:bg-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none text-sm"
                    >
                        <option value="">— Belum ditetapkan —</option>
                        @foreach($generalManagers as $gm)
                            <option value="{{ $gm->id }}"
                                {{ (int) old('general_manager_id', optional($unitWork->department)->general_manager_id) === $gm->id ? 'selected' : '' }}>
                                {{ $gm->display_title ?? ($gm->name . ($gm->jabatan ? ' — '.$gm->jabatan : '')) }}
                            </option>
                        @endforeach
                    </select>
                    @error('general_manager_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Hanya user dengan <code>usertype = approval</code> yang tampil di sini.
                    </p>
                </div>

                {{-- Senior Manager unit --}}
                <div class="space-y-1">
                    <label for="senior_manager_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                        Senior Manager (Unit)
                    </label>
                    <select
                        id="senior_manager_id"
                        name="senior_manager_id"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 mt-0.5
                               bg-white dark:bg-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none text-sm"
                    >
                        <option value="">— Belum ditetapkan —</option>
                        @foreach($seniorManagers as $sm)
                            <option value="{{ $sm->id }}"
                                {{ (int) old('senior_manager_id', $unitWork->senior_manager_id) === $sm->id ? 'selected' : '' }}>
                                {{ $sm->display_title ?? ($sm->name . ($sm->jabatan ? ' — '.$sm->jabatan : '')) }}
                            </option>
                        @endforeach
                    </select>
                    @error('senior_manager_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Hanya user dengan <code>usertype = approval</code> yang tampil di sini.
                    </p>
                </div>
            </div>

            {{-- Input Multi Seksi --}}
            <div class="mt-6">
                <label for="seksiInput" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                    Daftar Seksi
                    <span class="text-xs text-gray-500">(opsional, bisa lebih dari satu)</span>
                </label>

                <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <input
                            type="text"
                            id="seksiInput"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5
                                   bg-white dark:bg-gray-900 dark:text-gray-100
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none text-sm"
                            placeholder="Ketik nama seksi lalu Enter / klik Tambah"
                            aria-describedby="seksiHelp"
                        >
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="addSeksiBtn"
                            class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-lg
                                   bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">
                            + Tambah
                        </button>
                        <button type="button" id="clearAllBtn"
                            class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-lg
                                   bg-gray-100 hover:bg-gray-200 text-gray-700
                                   dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100">
                            Kosongkan
                        </button>
                    </div>
                </div>

                <p id="seksiHelp" class="text-[11px] text-gray-500 mt-1">
                    Tekan <b>Enter</b> untuk menambah cepat. Manager per seksi bisa diatur di tabel di bawah.
                </p>

                {{-- Daftar Seksi sebagai chip/tag --}}
                <div id="seksiList"
                     class="flex flex-wrap gap-2 mt-3 min-h-[2rem] rounded-md border border-dashed
                            border-gray-200 dark:border-gray-700 px-3 py-2 bg-gray-50/60 dark:bg-gray-900/40">
                </div>

                {{-- Hidden field JSON --}}
                <input
                    type="hidden"
                    name="seksi"
                    id="seksiHidden"
                    value='@json(old("seksi", $unitWork->seksi ?? []))'
                >
                @error('seksi')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- MANAGER PER SEKSI --}}
            <div class="mt-8">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">
                    Manager per Seksi
                </h3>
                <p class="text-[11px] text-gray-500 mb-3">
                    Mapping antara nama seksi dan user yang bertugas sebagai Manager.
                    Jika kamu mengubah daftar seksi di atas, simpan dulu lalu kembali ke halaman ini
                    untuk memastikan mapping Manager tetap sesuai.
                </p>

                @php
                    // daftar seksi rapi dari accessor UnitWork::getSeksiListAttribute()
                    $seksiList = $unitWork->seksi_list;
                    $oldSections = old('sections', []);
                @endphp

                @if (empty($seksiList))
                    <div class="text-xs text-gray-500 italic">
                        Belum ada seksi pada unit ini. Tambahkan dulu di bagian "Daftar Seksi".
                    </div>
                @else
                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/60">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        Nama Seksi
                                    </th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        Manager
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($seksiList as $i => $seksiName)
                                    @php
                                        // cari record di relasi sections
                                        $section = $unitWork->sections->firstWhere('name', $seksiName);
                                        $selectedManagerId = $oldSections[$i]['manager_id'] ?? $section->manager_id ?? null;
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-100 align-top">
                                            {{ $seksiName }}
                                            <input type="hidden"
                                                   name="sections[{{ $i }}][name]"
                                                   value="{{ $seksiName }}">
                                        </td>
                                        <td class="px-3 py-2">
                                            <select
                                                name="sections[{{ $i }}][manager_id]"
                                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1.5
                                                       bg-white dark:bg-gray-900 dark:text-gray-100
                                                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none text-xs"
                                            >
                                                <option value="">— Belum ditetapkan —</option>
                                                @foreach($sectionManagers as $mgr)
                                                    <option value="{{ $mgr->id }}"
                                                        {{ (int) $selectedManagerId === $mgr->id ? 'selected' : '' }}>
                                                        {{ $mgr->display_title ?? ($mgr->name . ($mgr->jabatan ? ' — '.$mgr->jabatan : '')) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @error('sections')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            {{-- Tombol --}}
            <div class="flex flex-col sm:flex-row justify-between items-center mt-8 gap-3">
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center px-4 py-2 text-xs font-medium rounded-md
                          bg-gray-100 hover:bg-gray-200 text-gray-700
                          dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100">
                    ← Kembali
                </a>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.unit_work.index') }}"
                       class="inline-flex items-center px-4 py-2 text-xs font-medium rounded-md
                              bg-gray-200 hover:bg-gray-300 text-gray-800
                              dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-xs font-medium rounded-md
                                   bg-blue-600 hover:bg-blue-700 text-white shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- === SCRIPT INLINE === --}}
    <script>
    (function () {
        const form        = document.getElementById('unitEditForm');
        const seksiInput  = document.getElementById('seksiInput');
        const seksiList   = document.getElementById('seksiList');
        const seksiHidden = document.getElementById('seksiHidden');
        const addBtn      = document.getElementById('addSeksiBtn');
        const clearBtn    = document.getElementById('clearAllBtn');

        if (!form || !seksiInput || !seksiList || !seksiHidden || !addBtn) return;

        let seksiArr = [];

        const norm = (s) => (s ?? '').toString().trim();
        const exists = (val) => {
            const v = norm(val).toLowerCase();
            return seksiArr.some(x => x.toLowerCase() === v);
        };

        function renderTags() {
            seksiList.innerHTML = '';
            if (!seksiArr.length) {
                const empty = document.createElement('div');
                empty.className = 'text-xs text-gray-500 italic';
                empty.textContent = 'Belum ada seksi. Tambahkan minimal satu jika diperlukan.';
                seksiList.appendChild(empty);
                return;
            }
            seksiArr.forEach((s, idx) => {
                const wrap = document.createElement('span');
                wrap.className =
                    'inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px] ' +
                    'bg-indigo-100 text-indigo-800 ring-1 ring-indigo-200 ' +
                    'dark:bg-indigo-900/40 dark:text-indigo-200 dark:ring-indigo-800';

                const bullet = document.createElement('span');
                bullet.className = 'w-1.5 h-1.5 rounded-full bg-indigo-500 dark:bg-indigo-300';
                wrap.appendChild(bullet);

                const text = document.createElement('span');
                text.textContent = s;
                wrap.appendChild(text);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.dataset.index = String(idx);
                btn.className =
                    'ml-1 w-4 h-4 flex items-center justify-center rounded-full ' +
                    'hover:bg-indigo-200/70 dark:hover:bg-indigo-800/70 text-[11px]';
                btn.innerHTML = '&times;';
                wrap.appendChild(btn);

                seksiList.appendChild(wrap);
            });
        }

        function syncHidden() {
            seksiHidden.value = JSON.stringify(seksiArr);
        }

        function addItem() {
            const val = norm(seksiInput.value);
            if (!val) return;
            if (exists(val)) {
                seksiInput.classList.add('ring-2','ring-yellow-400');
                setTimeout(() => seksiInput.classList.remove('ring-2','ring-yellow-400'), 250);
                return;
            }
            seksiArr.push(val);
            renderTags();
            syncHidden();
            seksiInput.value = '';
            seksiInput.focus();
        }

        function removeIndex(i) {
            i = Number(i);
            if (Number.isNaN(i) || i < 0 || i >= seksiArr.length) return;
            seksiArr.splice(i, 1);
            renderTags();
            syncHidden();
        }

        function clearAll() {
            if (!seksiArr.length) return;
            if (!confirm('Kosongkan semua seksi?')) return;
            seksiArr = [];
            renderTags();
            syncHidden();
            seksiInput.focus();
        }

        // Events
        addBtn.addEventListener('click', addItem);
        seksiInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addItem();
            }
        });
        seksiList.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-index]');
            if (!btn) return;
            removeIndex(btn.dataset.index);
        });
        clearBtn?.addEventListener('click', clearAll);

        // Bootstrap dari old()/model
        (function bootstrap() {
            try {
                const raw = norm(seksiHidden.value);
                let parsed = raw ? JSON.parse(raw) : [];
                if (!Array.isArray(parsed)) parsed = [];
                const seen = new Set();
                seksiArr = parsed
                    .filter(x => typeof x === 'string')
                    .map(x => norm(x))
                    .filter(x => x !== '')
                    .filter(x => {
                        const k = x.toLowerCase();
                        if (seen.has(k)) return false;
                        seen.add(k);
                        return true;
                    });
            } catch (_) {
                seksiArr = [];
            } finally {
                renderTags();
                syncHidden();
            }
        })();
    })();
    </script>
</x-admin-layout>
