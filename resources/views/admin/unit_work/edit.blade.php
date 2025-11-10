<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
            Edit Unit Kerja
        </h2>
        <p class="text-xs text-gray-500 mt-1">Ubah nama unit & daftar seksi lalu simpan.</p>
    </x-slot>

    <div class="p-6 bg-white dark:bg-gray-800 shadow rounded-lg max-w-lg mx-auto">
        <form id="unitEditForm" action="{{ route('admin.unit_work.update', $unitWork->id) }}" method="POST" autocomplete="off" novalidate>
            @csrf
            @method('PUT')

            {{-- Nama Unit Kerja --}}
            <div class="mb-4">
                <label for="name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                    Nama Unit Kerja
                </label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    maxlength="255"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 mt-1 bg-white dark:bg-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    value="{{ old('name', $unitWork->name) }}"
                    required
                >
                @error('name')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Input Multi Seksi --}}
            <div class="mb-4">
                <label for="seksiInput" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                    Daftar Seksi <span class="text-xs text-gray-500">(bisa lebih dari satu)</span>
                </label>

                <div class="flex gap-2 mt-1">
                    <input
                        type="text"
                        id="seksiInput"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        placeholder="Ketik nama seksi lalu Enter / klik Tambah"
                        aria-describedby="seksiHelp"
                    >
                    <button type="button" id="addSeksiBtn"
                        class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-sm">
                        Tambah
                    </button>
                </div>
                <p id="seksiHelp" class="text-xs text-gray-500 mt-1">
                    Tekan <b>Enter</b> untuk menambah cepat. Hindari duplikat.
                </p>

                {{-- Daftar Seksi sebagai chip/tag --}}
                <div id="seksiList" class="flex flex-wrap gap-2 mt-3"></div>

                {{-- Hidden field JSON (bootstrap dari model/old) --}}
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

            {{-- Tombol --}}
            <div class="flex justify-between items-center mt-6">
                <a href="{{ url()->previous() }}"
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 rounded text-sm">
                    ← Kembali
                </a>

                <div class="flex gap-2">
                    <button type="button" id="clearAllBtn"
                        class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-sm rounded">
                        Kosongkan Seksi
                    </button>
                    <a href="{{ route('admin.unit_work.index') }}"
                       class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded text-sm">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm">
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
                empty.textContent = 'Belum ada seksi.';
                seksiList.appendChild(empty);
                return;
            }
            seksiArr.forEach((s, idx) => {
                const wrap = document.createElement('span');
                wrap.className =
                    'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800 ring-1 ring-indigo-200 ' +
                    'dark:bg-indigo-900/40 dark:text-indigo-200 dark:ring-indigo-800';

                const icon = document.createElement('i');
                icon.className = 'fas fa-sitemap text-[10px] opacity-70';
                wrap.appendChild(icon);

                const text = document.createElement('span');
                text.textContent = ' ' + s;
                wrap.appendChild(text);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.dataset.index = String(idx);
                btn.className = 'ml-1 w-5 h-5 flex items-center justify-center rounded-full hover:bg-indigo-200/70 dark:hover:bg-indigo-800/70';
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
