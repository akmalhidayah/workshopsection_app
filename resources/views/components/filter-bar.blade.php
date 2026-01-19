<div class="admin-card p-5 mb-4">
    <form method="GET" action="{{ url()->current() }}" class="admin-filter">
        {{-- Search --}}
        @if($search)
            <div class="flex flex-col">
                <label class="text-xs font-semibold text-slate-600 mb-1">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ $searchPlaceholder ?? 'Cari...' }}"
                    class="admin-input w-44">
            </div>
        @endif

        {{-- Status --}}
        @if(!empty($statusOptions))
            <div class="flex flex-col">
                <label class="text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status"
                    class="admin-select w-44">
                    <option value="">-- Semua --</option>
                    @foreach($statusOptions as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Date Filter --}}
        @if($dateFilter)
            <div class="flex flex-col">
                <label class="text-xs font-semibold text-slate-600 mb-1">Tanggal (Dari)</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="admin-input w-44">
            </div>
            <div class="flex flex-col">
                <label class="text-xs font-semibold text-slate-600 mb-1">Tanggal (Sampai)</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="admin-input w-44">
            </div>
        @endif

        {{-- Slot untuk filter tambahan --}}
        {{ $slot }}

        {{-- Entries --}}
        <div class="flex flex-col">
            <label class="text-xs font-semibold text-slate-600 mb-1">Tampilkan</label>
            <select name="entries"
                class="admin-select w-28">
                @foreach($entriesOptions as $e)
                    <option value="{{ $e }}" {{ request('entries', 10) == $e ? 'selected' : '' }}>
                        {{ $e }} data
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tombol --}}
        <div class="ml-auto flex gap-2">
            <button type="submit"
                class="admin-btn admin-btn-primary">
                <i data-lucide="filter" class="w-4 h-4"></i> Filter
            </button>
            <a href="{{ url()->current() }}"
                class="admin-btn admin-btn-ghost">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
            </a>
        </div>
    </form>
</div>
