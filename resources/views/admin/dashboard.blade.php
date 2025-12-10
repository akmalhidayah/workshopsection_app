<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            {{ __('Notification Process Overview') }}
        </h2>
    </x-slot>
    @php
    /**
     * cleanNumber: bersihkan satu nilai menjadi integer aman
     * - jika $x adalah string JSON array -> return 0 (diproses di luar)
     * - jika numeric -> cast
     * - jika string biasa dengan pemisah ribuan -> hapus non-digit lalu cast
     */
    $cleanNumber = function ($x) {
        if ($x === null || $x === '') return 0;

        // jika value sudah integer atau numeric simple
        if (is_int($x) || (is_string($x) && ctype_digit($x))) {
            return (int) $x;
        }

        // jika numeric (float-like)
        if (is_numeric($x)) {
            return (int) round((float) $x);
        }

        // jika string JSON array (contoh '["1000","2000"]'), jangan gabung -> return 0
        // (array JSON harus didecode di controller seperti langkah 1)
        $trim = trim($x);
        if (str_starts_with($trim, '[') && str_ends_with($trim, ']')) {
            return 0;
        }

        // hapus semua selain digit dan minus
        $onlyDigits = preg_replace('/[^\d\-]/', '', (string) $x);

        return ($onlyDigits === '') ? 0 : (int) $onlyDigits;
    };

    /**
     * fmt: terima number|string|array|null
     * - jika array -> jumlahkan elemen dengan cleanNumber
     * - jika numeric/string -> bersihkan dan format
     */
    $fmt = function ($v) use ($cleanNumber) {
        if (is_array($v)) {
            $sum = 0;
            foreach ($v as $item) {
                $sum += $cleanNumber($item);
            }
            $v = $sum;
        } else {
            $v = $cleanNumber($v);
        }

        return number_format((int) $v, 0, ',', '.');
    };

    $rp = function ($v) use ($fmt) {
        return 'Rp. ' . $fmt($v);
    };
@endphp

    <div class="bg-gray-100" style="padding-top: 10px;">
    <!-- Notification Process Container -->
    <div class="max-w-6xl mx-auto bg-white rounded-lg p-6">
        <h3 class="text-md font-semibold text-gray-700 mb-4">Order Process</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
        <!-- Outstanding Notification Card -->
<a href="{{ route('notifikasi.index') }}" class="block w-36 h-36 bg-blue-400 rounded-lg shadow-sm hover:shadow-md transition transform hover:scale-105">
    <div class="flex flex-col items-center justify-center h-full text-center px-2">
        <div class="text-blue-600 text-2xl mb-1">
            <i class="fas fa-bell"></i>
        </div>
        <h2 class="text-xs font-medium text-gray-700">Outstanding Order</h2>
        <!-- Menampilkan jumlah outstandingNotifications -->
        <p class="text-lg font-bold text-blue-700">{{ $outstandingNotifications }}</p>
    </div>
</a>

<!-- Pending Process (Jasa) Card -->
<a href="{{ route('notifikasi.index') }}" class="block w-36 h-36 bg-yellow-400 rounded-lg shadow-sm hover:shadow-md transition transform hover:scale-105">
    <div class="flex flex-col items-center justify-center h-full text-center px-2">
        <div class="text-yellow-600 text-2xl mb-1">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <h2 class="text-xs font-medium text-gray-700">Pending Process (Jasa)</h2>
        <p class="text-lg font-bold text-yellow-700">{{ $pendingProcessJasa }}</p>
    </div>
</a>

<!-- Document On Process (HPP) Card -->
<a href="{{ route('notifikasi.index') }}" class="block w-36 h-36 bg-gray-400 rounded-lg shadow-sm hover:shadow-md transition transform hover:scale-105">
    <div class="flex flex-col items-center justify-center h-full text-center px-2">
        <div class="text-gray-700 text-2xl mb-1">
            <i class="fas fa-file-alt"></i>
        </div>
        <h2 class="text-xs font-medium text-gray-700">Document On Process (HPP)</h2>
        <p class="text-lg font-bold text-gray-800">{{ $documentOnProcessHPPCount }}</p>
    </div>
</a>
            <!-- Document Process Approval (HPP) Card -->
            <a href="{{ route('notifikasi.index') }}" class="block w-36 h-36 bg-green-400 rounded-lg shadow-sm hover:shadow-md transition transform hover:scale-105">
                <div class="flex flex-col items-center justify-center h-full text-center px-2">
                    <div class="text-green-600 text-2xl mb-1">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="text-xs font-medium text-gray-700">Approval Process (HPP)</h2>
                    <p class="text-lg font-bold text-green-700"> {{ $approvalProcessHPPCount }}</p>
                </div>
            </a>
           <!-- Document On Process PR/PO (HPP Complete Approval) Card -->
            <a href="{{ route('notifikasi.index') }}" class="block w-36 h-36 bg-red-400 rounded-lg shadow-sm hover:shadow-md transition transform hover:scale-105">
                <div class="flex flex-col items-center justify-center h-full text-center px-2">
                    <div class="text-red-800 text-2xl mb-1">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h2 class="text-xs font-medium text-gray-700">PR/PO Process (HPP Approved)</h2>
                    <p class="text-lg font-bold text-red-800"> {{ $documentOnProcessPOCount }}</p>
                </div>
            </a>
        </div>
    </div>
<!-- ============== Bottom consolidated card ============== -->
<div class="max-w-6xl mx-auto mt-6">
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">

    <!-- 2 kolom responsif: stack di mobile, sejajar di md+ -->
    <div class="flex flex-col md:flex-row gap-6">

      {{-- ================= Potensi Biaya (kiri) ================= --}}
      <section class="flex-1 md:pr-6">
        <div class="flex items-center mb-4">
          <i data-feather="dollar-sign" class="text-green-500 text-2xl mr-2"></i>
          <h3 class="text-md font-semibold text-gray-700">Potensi Biaya (Cost)</h3>
        </div>

        <ul class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-gray-700">
          <!-- Document On Process (HPP) -->
          <li class="rounded border border-gray-200 p-3 shadow-sm">
            <div class="flex items-center">
              <i data-feather="file-text" class="text-gray-700 mr-2"></i>
              <span class="font-medium" title="HPP belum ditandatangani GM. Tidak mengurangi kuota.">
                Document On Process (HPP)
              </span>
            </div>
            <div class="mt-2 text-right">
              <div class="inline-block bg-gray-50 px-2 py-1 rounded font-semibold text-gray-800">
                {{ $rp($documentOnProcessHPPAmount) }}
              </div>
            </div>
          </li>

          <!-- Approval Process (HPP) -->
          <li class="rounded border border-gray-200 p-3 shadow-sm">
            <div class="flex items-center">
              <i data-feather="check-circle" class="text-gray-700 mr-2"></i>
              <span class="font-medium" title="HPP sudah approved, belum LPJ/PPL. Tidak mengurangi kuota.">
                Approval Process (HPP)
              </span>
            </div>
            <div class="mt-2 text-right">
              <div class="inline-block bg-gray-50 px-2 py-1 rounded font-semibold text-gray-800">
                {{ $rp($approvalProcessHPPAmount) }}
              </div>
            </div>
          </li>

          <!-- On Process PR/PO -->
          <li class="rounded border border-gray-200 p-3 shadow-sm">
            <div class="flex items-center">
              <i data-feather="alert-triangle" class="text-gray-700 mr-2"></i>
              <span class="font-medium" title="Sudah PR/PO, belum LPJ+PPL. Tidak mengurangi kuota.">
                On Process PR/PO
              </span>
            </div>
            <div class="mt-2 text-right">
              <div class="inline-block bg-gray-50 px-2 py-1 rounded font-semibold text-gray-800">
                {{ $rp($documentOnProcessPOAmount) }}
              </div>
            </div>
          </li>
        </ul>

        <div class="mt-3 text-right">
          <span class="text-[11px] text-gray-500 mr-2">Subtotal potensi</span>
          <span class="font-bold text-sm text-gray-800">
            {{ $rp($totalAmount1) }}
          </span>
        </div>
      </section>

      {{-- ======= separator: horizontal di mobile, vertikal di md+ ======= --}}
      <div class="block md:hidden h-px bg-gray-200"></div>
      <div class="hidden md:block w-px bg-gray-200 self-stretch"></div>

      {{-- ================= Realisasi / menuju realisasi (kanan) ================= --}}
      <section class="flex-1 md:pl-6">
        <div class="flex items-center mb-4">
          <i data-feather="pie-chart" class="text-blue-500 text-2xl mr-2"></i>
          <h3 class="text-md font-semibold text-gray-700">Realisasi Biaya (LPJ)</h3>
        </div>

        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-700">
          <!-- Document PR/PO (LHPP) -->
          <li class="rounded border border-gray-200 p-3 shadow-sm">
            <div class="flex items-center">
              <i data-feather="folder" class="text-gray-700 mr-2"></i>
              <span class="font-medium" title="Tercatat di LHPP (menuju realisasi). Mengurangi kuota saat LPJ+PPL.">
                Document PR/PO (LHPP)
              </span>
            </div>
            <div class="mt-2 text-right">
              <div class="inline-block bg-gray-50 px-2 py-1 rounded font-semibold text-gray-800">
                {{ $rp($documentPRPOAmount) }}
              </div>
            </div>
          </li>

          <!-- Pekerjaan Urgent -->
          <li class="rounded border border-gray-200 p-3 shadow-sm">
            <div class="flex items-center">
              <i data-feather="zap" class="text-gray-700 mr-2"></i>
              <span class="font-medium" title="Pekerjaan berstatus Urgent (masih potensi sampai LPJ+PPL).">
                Pekerjaan Urgent
              </span>
            </div>
            <div class="mt-2 text-right">
              <div class="inline-block bg-gray-50 px-2 py-1 rounded font-semibold text-gray-800">
                {{ $rp($urgentAmount) }}
              </div>
            </div>
          </li>
        </ul>

        <div class="mt-3 text-right">
          <span class="text-[11px] text-gray-500 mr-2">Subtotal potensi</span>
          <span class="font-bold text-sm text-gray-800">
            {{ $rp($totalAmount2) }}
          </span>
        </div>
      </section>

    </div>
  </div>
</div>

<!-- ============== Exposure & Kuota + Realisasi (combined container) ============== -->
<div class="max-w-6xl mx-auto mt-6">
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">

    <div class="flex flex-col md:flex-row gap-6">

      <!-- ================= LEFT: Exposure & Kuota (compact) ================= -->
      <section class="flex-1 md:pr-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4 space-y-3">

          <!-- Header kecil -->
          <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-600 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20h.01M12 12h.01M4 4h16v16H4z"/>
            </svg>
            <h4 class="text-sm font-semibold text-gray-700 tracking-wide">Ringkasan Kuota Anggaran</h4>
          </div>

          <!-- Exposure -->
          <div class="rounded-md bg-blue-50 border border-blue-200 p-2.5">
            <p class="text-sm font-bold text-blue-900">
              Potensi Biaya + Realisasi Biaya:
              <span class="text-gray-900">Rp. {{ number_format($totalSeluruhAmount, 0, ',', '.') }}</span>
            </p>
          </div>

          {{-- Kuota Kontrak Actual = Total Kuota - (Potensi + Realisasi) --}}
          @php
              $kuotaKontrakActual = ($totalKuotaKontrak ?? 0) - ($totalSeluruhAmount ?? 0);
          @endphp
          <div class="rounded-md bg-sky-50 border border-sky-200 p-2.5">
            <p class="text-[11px] font-semibold uppercase text-sky-700">Kuota Kontrak Actual</p>
            <p class="text-sm font-bold text-sky-900">
              Rp. {{ number_format($kuotaKontrakActual, 0, ',', '.') }}
            </p>
            <p class="text-[11px] text-sky-700 mt-0.5 leading-tight">
              = Total Kuota (Rp. {{ number_format($totalKuotaKontrak, 0, ',', '.') }})
              − (Potensi + Realisasi) (Rp. {{ number_format($totalSeluruhAmount, 0, ',', '.') }})
            </p>
          </div>

          <!-- Total Kuota -->
          <div class="rounded-md border border-gray-200 p-2.5">
            <p class="text-[11px] font-semibold uppercase text-gray-700">Total Kuota Kontrak</p>
            <p class="text-sm font-bold text-gray-900">Rp. {{ number_format($totalKuotaKontrak, 0, ',', '.') }}</p>
            <p class="text-[11px] text-gray-600 mt-1 leading-tight">
              Periode:
              {{ $periodeKontrak['start'] ? \Carbon\Carbon::parse($periodeKontrak['start'])->format('d M Y') : '-' }}
              s/d
              {{ $periodeKontrak['end'] ? \Carbon\Carbon::parse($periodeKontrak['end'])->format('d M Y') : '-' }}
              @if ($periodeKontrak['adendum'])
                <span class="text-gray-500">, adendum s/d {{ \Carbon\Carbon::parse($periodeKontrak['adendum'])->format('d M Y') }}</span>
              @endif
            </p>
          </div>

     <!-- -- Target Biaya Jasa Pemeliharaan (dari OA) -- -->
@if(!is_null($targetPemeliharaan))
  @php
    $isArrayTarget = is_array($targetPemeliharaan);
    if ($isArrayTarget) {
        $totalTargetInt = 0;
        foreach ($targetPemeliharaan as $x) {
            $totalTargetInt += $cleanNumber($x);
        }
    } else {
        $totalTargetInt = $cleanNumber($targetPemeliharaan);
    }
  @endphp

  <div class="rounded-md bg-emerald-50 border border-emerald-200 p-2.5">
    <p class="text-[11px] font-semibold uppercase text-emerald-700">Target Biaya Pemeliharaan</p>
    <p class="text-sm font-bold text-emerald-900">
      {{ $rp($totalTargetInt) }}
    </p>

    @if($isArrayTarget)
      @php $years = $latestKuotaAnggaran->tahun ?? null; @endphp
      @if(is_array($years) && count($years) === count($targetPemeliharaan))
        <div class="mt-2 text-xs text-emerald-800">
          @foreach($targetPemeliharaan as $i => $val)
            <div>{{ $years[$i] }}: {{ $rp($val) }}</div>
          @endforeach
        </div>
      @endif
    @endif
  </div>
@endif


          <!-- Sisa Kuota -->
          <div class="rounded-md bg-yellow-100 border border-yellow-200 p-2.5">
            <p class="text-[11px] font-semibold uppercase text-yellow-800">Sisa Kuota Kontrak</p>
            <p class="text-sm font-bold text-yellow-900">
              Rp. {{ number_format($sisaKuotaKontrak, 0, ',', '.') }}
            </p>
          </div>

        </div>
      </section>

      <!-- Separator: horizontal on mobile, vertical on md+ -->
      <div class="block md:hidden h-px bg-gray-200"></div>
      <div class="hidden md:block w-px bg-gray-200 self-stretch"></div>

      <!-- ================= RIGHT: Realisasi & Filter (logic tetap) ================= -->
      <section class="flex-1 md:pl-4">
        <!-- Total Realisasi Biaya -->
        <div class="w-full text-center mb-3 p-2.5 bg-green-200 rounded-md font-bold text-gray-800 text-xs">
          Total Realisasi Biaya: Rp {{ number_format($totalRealisasiBiaya, 0, ',', '.') }}
        </div>

        <!-- Dropdown Rentang Tahun -->
        <p class="text-[11px] text-gray-600 mb-1.5">Sortir per rentang tahun untuk menampilkan data realisasi biaya.</p>
        <div class="flex flex-wrap items-center gap-2 mb-3 text-[11px]">
          <div>
            <label for="startYear" class="text-gray-600">Dari Tahun:</label>
            <select id="startYear" class="bg-gray-100 border border-gray-300 rounded p-1 text-[11px] w-28">
              <option value="" selected disabled>Pilih Tahun</option>
            </select>
          </div>
          <span class="text-gray-600">sampai</span>
          <div>
            <label for="endYear" class="text-gray-600">Sampai Tahun:</label>
            <select id="endYear" class="bg-gray-100 border border-gray-300 rounded p-1 text-[11px] w-28">
              <option value="" selected disabled>Pilih Tahun</option>
            </select>
          </div>
        </div>

        <!-- Dropdown Rentang Bulan -->
        <p class="text-[11px] text-gray-600 mb-1.5">Sortir per rentang bulan untuk menampilkan data realisasi biaya.</p>
        <div class="flex flex-wrap items-center gap-2 mb-2 text-[11px]">
          <div>
            <label for="startMonth" class="text-gray-600">Dari Bulan:</label>
            <select id="startMonth" class="bg-gray-100 border border-gray-300 rounded p-1 text-[11px] w-28">
              <option value="" selected disabled>Pilih Bulan</option>
            </select>
          </div>
          <span class="text-gray-600">sampai</span>
          <div>
            <label for="endMonth" class="text-gray-600">Sampai Bulan:</label>
            <select id="endMonth" class="bg-gray-100 border border-gray-300 rounded p-1 text-[11px] w-28">
              <option value="" selected disabled>Pilih Bulan</option>
            </select>
          </div>
        </div>

        <div class="flex justify-start mt-2 mb-4">
          <button id="applyFilters" class="bg-blue-500 text-white text-[11px] font-semibold py-1 px-3 rounded">
            Terapkan
          </button>
        </div>

        <!-- Chart Section (JANGAN DIUBAH LOGIC) -->
        <div class="flex items-center">
          <canvas id="realisasiBiayaPieChart" style="max-width: 160px; max-height: 160px; margin-right: 16px;"></canvas>
          <div id="chartLegend" class="ml-6 text-[11px] flex flex-col space-y-1"></div>
        </div>
      </section>
    </div>

  </div>
</div>

<!-- Feather Icons and Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
 document.addEventListener('DOMContentLoaded', function () {
    const startYearSelect = document.getElementById('startYear');
    const endYearSelect = document.getElementById('endYear');
    const startMonthSelect = document.getElementById('startMonth');
    const endMonthSelect = document.getElementById('endMonth');
    const applyFiltersButton = document.getElementById('applyFilters');

    // Fetch data tahun untuk dropdown
    function fetchYears() {
        fetch('/admin/get-years')
            .then(response => response.json())
            .then(data => {
                startYearSelect.innerHTML = '<option value="" selected disabled>Pilih Tahun</option>';
                endYearSelect.innerHTML = '<option value="" selected disabled>Pilih Tahun</option>';
                data.forEach(year => {
                    const option = `<option value="${year}">${year}</option>`;
                    startYearSelect.innerHTML += option;
                    endYearSelect.innerHTML += option;
                });

                // Load previously selected values
                loadSavedFilters();
            })
            .catch(error => console.error('Error fetching years:', error));
    }

    // Load data bulan ke dropdown
    function loadMonths() {
        const months = [
            { number: 1, name: "Januari" }, { number: 2, name: "Februari" }, { number: 3, name: "Maret" },
            { number: 4, name: "April" }, { number: 5, name: "Mei" }, { number: 6, name: "Juni" },
            { number: 7, name: "Juli" }, { number: 8, name: "Agustus" }, { number: 9, name: "September" },
            { number: 10, name: "Oktober" }, { number: 11, name: "November" }, { number: 12, name: "Desember" }
        ];

        [startMonthSelect, endMonthSelect].forEach(select => {
            select.innerHTML = '<option value="" selected disabled>Pilih Bulan</option>';
            months.forEach(month => {
                select.innerHTML += `<option value="${month.number}">${month.name}</option>`;
            });
        });
    }

    // Load saved filters from localStorage
    function loadSavedFilters() {
        const savedStartYear = localStorage.getItem('startYear');
        const savedEndYear = localStorage.getItem('endYear');
        const savedStartMonth = localStorage.getItem('startMonth');
        const savedEndMonth = localStorage.getItem('endMonth');

        if (savedStartYear) startYearSelect.value = savedStartYear;
        if (savedEndYear) endYearSelect.value = savedEndYear;
        if (savedStartMonth) startMonthSelect.value = savedStartMonth;
        if (savedEndMonth) endMonthSelect.value = savedEndMonth;

        if (savedStartYear && savedEndYear) {
            fetchData(savedStartYear, savedEndYear, savedStartMonth, savedEndMonth);
        }
    }

    // Fetch and display data
    function fetchData(startYear, endYear, startMonth = null, endMonth = null) {
        const queryParams = new URLSearchParams({
            startYear,
            endYear,
            ...(startMonth && { startMonth }),
            ...(endMonth && { endMonth })
        }).toString();

        fetch(`/admin/realisasi-biaya?${queryParams}`)
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data)) throw new Error("Format data tidak valid.");

                // Update chart
                const labels = data.map(item => `${item.year}-${item.month || 'N/A'}`);
                const values = data.map(item => item.total);

                const ctx = document.getElementById('realisasiBiayaPieChart');
                if (window.realisasiBiayaChart) window.realisasiBiayaChart.destroy();
                window.realisasiBiayaChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            backgroundColor: ['#4CAF50', '#2196F3', '#FF5722', '#FFC107']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return `${context.label}: Rp ${context.raw.toLocaleString('id-ID')}`;
                                    }
                                }
                            }
                        }
                    }
                });

                // Update legend
                updateLegend(labels, values);
            })
            .catch(error => {
                console.error('Error saat memproses data:', error);
                alert('Terjadi kesalahan saat mengambil data.');
            });
    }

    // Update chart legend
    function updateLegend(labels, values) {
        const legend = document.getElementById('chartLegend');
        legend.innerHTML = '';
        labels.forEach((label, index) => {
            legend.innerHTML += `
                <div class="grid grid-cols-2 gap-x-2">
                    <div><span style="color: ${window.realisasiBiayaChart.data.datasets[0].backgroundColor[index]};">●</span> ${label}:</div>
                    <div>Rp ${values[index].toLocaleString('id-ID')}</div>
                </div>`;
        });
    }

    // Event Listener untuk filter
    applyFiltersButton.addEventListener('click', function () {
        const startYear = startYearSelect.value;
        const endYear = endYearSelect.value;
        const startMonth = startMonthSelect.value;
        const endMonth = endMonthSelect.value;

        if (!startYear || !endYear) {
            alert("Pilih rentang tahun terlebih dahulu!");
            return;
        }

        if (parseInt(startYear) > parseInt(endYear)) {
            alert("Tahun mulai tidak boleh lebih besar dari tahun akhir!");
            return;
        }

        if (startMonth && endMonth && parseInt(startMonth) > parseInt(endMonth)) {
            alert("Bulan mulai tidak boleh lebih besar dari bulan akhir!");
            return;
        }

        // Simpan filter di localStorage
        localStorage.setItem('startYear', startYear);
        localStorage.setItem('endYear', endYear);
        if (startMonth) localStorage.setItem('startMonth', startMonth);
        if (endMonth) localStorage.setItem('endMonth', endMonth);

        // Fetch data berdasarkan filter
        fetchData(startYear, endYear, startMonth, endMonth);
    });

    // Initial Load
    fetchYears();
    loadMonths();
});

</script>
</x-admin-layout>
