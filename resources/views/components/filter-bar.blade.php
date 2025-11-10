<div class="bg-white shadow-sm rounded-lg p-3 mb-4 border border-gray-200">
    <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-end gap-3">
        {{-- Search --}}
        @if($search)
            <div class="flex flex-col">
                <label class="text-[11px] font-semibold text-gray-600 mb-1">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ $searchPlaceholder ?? 'Cari...' }}"
                    class="w-40 border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        @endif

        {{-- Status --}}
        @if(!empty($statusOptions))
            <div class="flex flex-col">
                <label class="text-[11px] font-semibold text-gray-600 mb-1">Status</label>
                <select name="status"
                    class="w-40 border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
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
                <label class="text-[11px] font-semibold text-gray-600 mb-1">Tanggal (Dari)</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="w-40 border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex flex-col">
                <label class="text-[11px] font-semibold text-gray-600 mb-1">Tanggal (Sampai)</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="w-40 border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        @endif

        {{-- Slot untuk filter tambahan --}}
        {{ $slot }}

        {{-- Entries --}}
        <div class="flex flex-col">
            <label class="text-[11px] font-semibold text-gray-600 mb-1">Tampilkan</label>
            <select name="entries"
                class="w-28 border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
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
                class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                Filter
            </button>
            <a href="{{ url()->current() }}"
                class="bg-gray-400 text-white px-4 py-2 rounded text-sm hover:bg-gray-500">
                Reset
            </a>
        </div>
    </form>
</div>
