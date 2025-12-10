<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Order Permintaan Kawat Las') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Header Card -->
            <div class="bg-white dark:bg-gray-900 shadow-md sm:rounded-lg mb-6">
                <div class="p-6 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                        Daftar Permintaan Kawat Las
                    </h3>
                    <button onclick="openForm()" 
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow-sm transition">
                        + Tambah Permintaan
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Nomor Order</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Tanggal</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Detail Kawat</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Unit Kerja</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Status & Catatan</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($kawatlas as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <!-- Nomor Order -->
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $item->order_number }}
                                    </td>

                                    <!-- Tanggal -->
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $item->tanggal->format('d-m-Y') }}
                                    </td>

                                    <!-- Detail Kawat -->
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                        <div class="space-y-2">
                                            @php $grandTotal = 0; @endphp
                                            @foreach ($item->details as $d)
                                                @php
                                                    $jenis = $jenisList->firstWhere('kode', $d->jenis_kawat);
                                                    $harga = $jenis->harga ?? 0;
                                                    $subtotal = $harga * $d->jumlah;
                                                    $grandTotal += $subtotal;
                                                @endphp
                                                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-md shadow-sm">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <span class="font-semibold">{{ $d->jenis_kawat }}</span>
                                                            <span class="ml-1">({{ $d->jumlah }})</span>
                                                        </div>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $jenis->deskripsi ?? '-' }}
                                                        </span>
                                                    </div>
                                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        Stok: {{ $jenis->stok ?? 'N/A' }} |
                                                        Harga: Rp {{ number_format($harga, 0, ',', '.') }} |
                                                        Cost: {{ $jenis->cost_element ?? '-' }} |
                                                        <span class="font-semibold">Subtotal: Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="mt-2 text-right font-semibold text-indigo-600 dark:text-indigo-400">
                                                Grand Total: Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </td>

                                  <!-- Unit Kerja (diperbarui: tampilkan seksi di bawah nama unit) -->
<td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
    <div class="flex flex-col">
        <div class="font-medium">{{ $item->unit_work }}</div>

        @if(!empty($item->seksi))
            <div class="mt-2">
                <span
                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold
                           bg-indigo-100 text-indigo-800 ring-1 ring-indigo-200
                           dark:bg-indigo-900/40 dark:text-indigo-300 dark:ring-indigo-700">
                    <i class="fas fa-sitemap text-[10px] opacity-80"></i>
                    {{ $item->seksi }}
                </span>
            </div>
        @endif
    </div>
</td>

                                    <!-- Status & Catatan -->
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex flex-col">
                                            @php
                                                $badgeColor = $item->status === 'Good Issue'
                                                    ? 'bg-green-500 text-white'
                                                    : 'bg-yellow-400 text-gray-800';
                                            @endphp
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold text-center {{ $badgeColor }}">
                                                {{ strtoupper($item->status) }}
                                            </span>

                                            @if($item->catatan)
                                                <p class="mt-2 text-xs italic text-gray-600 dark:text-gray-400">
                                                    “{{ $item->catatan }}”
                                                </p>
                                            @else
                                                <p class="mt-2 text-xs italic text-gray-400">
                                                    Tidak ada catatan
                                                </p>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-4 py-3 text-right flex justify-end gap-2">
                                        <button type="button" onclick="openEditForm({{ $item->id }})"
                                            class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-xs shadow-sm transition">
                                            Edit
                                        </button>
                                        <form action="{{ route('kawatlas.destroy', $item->id) }}" method="POST"
                                            onsubmit="return confirm('Yakin hapus data ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs shadow-sm transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-gray-500 dark:text-gray-400">
                                        Belum ada permintaan kawat las.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $kawatlas->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    @include('kawatlas.partials.modal-create')

    <!-- Modal Edit -->
    @include('kawatlas.partials.modal-edit')

    @include('kawatlas.partials.script')
</x-app-layout>
