<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 leading-tight">
            {{ __('Notification Process Overview') }}
        </h2>
    </x-slot>
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
<!-- Additional Information and Future Content Containers -->
<div class="max-w-6xl mx-auto mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Left Container for Additional Information (Potensi Biaya) -->
    <div class="bg-white rounded-lg p-6">
        <div class="flex items-center mb-4">
            <i data-feather="dollar-sign" class="text-green-500 text-2xl mr-2"></i>
            <h3 class="text-md font-semibold text-gray-700">Potensi Biaya (Cost)</h3>
        </div>
        
        <!-- Menyusun item Potensi Biaya dalam tiga kolom untuk kompak -->
        <ul class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-gray-600">
    <!-- Document On Process (HPP) -->
    <li class="shadow-sm flex flex-col justify-between">
        <div class="flex items-center">
            <i data-feather="file-text" class="text-gray-700 mr-2"></i> 
            <span>Document On Process (HPP)</span>
        </div>
        <div class="text-right">
            <div class="text-gray-800 font-semibold p-1 rounded-md">
                Rp. {{ number_format($documentOnProcessHPPAmount, 0, ',', '.') }}
            </div>
        </div>
    </li>

    <!-- Document Process Approval (HPP) -->
    <li class="shadow-sm flex flex-col justify-between">
        <div class="flex items-center">
            <i data-feather="check-circle" class="text-gray-700 mr-2"></i> 
            <span>Document Process Approval (HPP)</span>
        </div>
        <div class="text-right">
            <div class="text-gray-800 font-semibold p-1 rounded-md">
                Rp. {{ number_format($approvalProcessHPPAmount, 0, ',', '.') }}
            </div>
        </div>
    </li>

    <!-- Document On Process PR/PO -->
    <li class="shadow-sm flex flex-col justify-between">
        <div class="flex items-center">
            <i data-feather="alert-triangle" class="text-gray-700 mr-2"></i> 
            <span>Document On Process PR/PO</span>
        </div>
        <div class="text-right">
            <div class="text-gray-800 font-semibold p-1 rounded-md">
                Rp. {{ number_format($documentOnProcessPOAmount, 0, ',', '.') }}
            </div>
        </div>
    </li>
</ul>
        <!-- Total Pertama -->
        <div class="mt-2 mb-4 text-right font-bold text-sm text-gray-800">
            Total: Rp. {{ number_format($totalAmount1, 0, ',', '.') }}
        </div>

        <!-- Section 2: Dua Kartu di Bawah -->
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-600">
            <li class="shadow-sm flex flex-col justify-between">
                <div class="flex items-center">
                    <i data-feather="folder" class="text-gray-700 mr-2"></i> 
                    <span>Document PR/PO</span>
                </div>
                <div class="text-right">
                    <div class="text-gray-800 font-semibold p-1 rounded-md">{{ number_format($documentPRPOAmount, 0, ',', '.') }}</div>
                </div>
            </li>

            <li class="shadow-sm flex flex-col justify-between">
                <div class="flex items-center">
                    <i data-feather="zap" class="text-gray-700 mr-2"></i> 
                    <span>Pekerjaan Urgent</span>
                </div>
                <div class="text-right">
                    <div class="text-gray-800 font-semibold p-1 rounded-md">  Rp. {{ number_format($urgentAmount, 0, ',', '.') }}</div>
                </div>
            </li>
        </ul>

        <!-- Total Kedua -->
        <div class="mt-2 mb-4 text-right font-bold text-sm text-gray-800">
            Total : {{ number_format($totalAmount2, 0, ',', '.') }}
        </div>

        <!-- Total Keseluruhan -->
        <div class="mt-4 p-4 bg-green-400 rounded-lg text-center font-bold text-lg text-gray-800 shadow-sm">
            Total Keseluruhan: Rp. {{ number_format($totalSeluruhAmount, 0, ',', '.') }}
        </div>
        <!-- Total Kuota Kontrak -->
        <div class="mt-4 p-4 rounded-lg text-center font-medium text-base text-gray-800 shadow-sm">
            <p>Total Kuota Kontrak: Rp.{{ number_format($totalKuotaKontrak, 0, ',', '.') }}</p>
            <p>
                Periode Kontrak:
                {{ $periodeKontrak['start'] ? \Carbon\Carbon::parse($periodeKontrak['start'])->format('d M Y') : '-' }}
                sampai
                {{ $periodeKontrak['end'] ? \Carbon\Carbon::parse($periodeKontrak['end'])->format('d M Y') : '-' }}
                @if ($periodeKontrak['adendum'])
                    , diperpanjang sampai
                    {{ \Carbon\Carbon::parse($periodeKontrak['adendum'])->format('d M Y') }}
                @endif
            </p>
        </div>
        <!-- Total Sisa Kuota Kontrak -->
        <div class="mt-4 p-4 bg-yellow-200 rounded-lg text-center font-bold text-lg text-gray-800 shadow-sm">
            Sisa Kuota Kontrak: Rp. {{ number_format($sisaKuotaKontrak, 0, ',', '.') }}
        </div>

    </div>

    <!-- Right Container for Additional Information (Realisasi Biaya) -->
    <div class="bg-white rounded-lg p-4 flex flex-col items-start">
    <!-- Header Section -->
    <div class="flex items-center mb-4">
        <i data-feather="pie-chart" class="text-blue-500 text-2xl mr-2"></i>
        <h3 class="text-lg font-semibold text-gray-700">Realisasi Biaya (LPJ)</h3>
    </div>

<!-- Total Realisasi Biaya Keseluruhan -->
<div class="w-full text-center mb-4 p-3 bg-green-200 rounded-lg font-bold text-gray-800 text-xs">
    Total Realisasi Biaya: Rp {{ number_format($totalRealisasiBiaya, 0, ',', '.') }}
</div>

<!-- Dropdown Rentang Tahun -->
<p class="text-xs text-gray-600 mb-4">Sortir per rentang tahun untuk menampilkan data realisasi biaya.</p>
<div class="flex flex-wrap items-center space-x-2 mb-4 text-xs">
    <div>
        <label for="startYear" class="text-gray-600">Dari Tahun:</label>
        <select id="startYear" class="bg-gray-100 border border-gray-300 rounded p-1 text-xs w-28">
            <option value="" selected disabled>Pilih Tahun</option>
        </select>
    </div>
    <span class="text-gray-600">sampai</span>
    <div>
        <label for="endYear" class="text-gray-600">Sampai Tahun:</label>
        <select id="endYear" class="bg-gray-100 border border-gray-300 rounded p-1 text-xs w-28">
            <option value="" selected disabled>Pilih Tahun</option>
        </select>
    </div>
</div>

<!-- Dropdown Rentang Bulan -->
<p class="text-xs text-gray-600 mb-4">Sortir per rentang bulan untuk menampilkan data realisasi biaya.</p>
<div class="flex flex-wrap items-center space-x-2 mb-4 text-xs">
    <div>
        <label for="startMonth" class="text-gray-600">Dari Bulan:</label>
        <select id="startMonth" class="bg-gray-100 border border-gray-300 rounded p-1 text-xs w-28">
            <option value="" selected disabled>Pilih Bulan</option>
        </select>
    </div>
    <span class="text-gray-600">sampai</span>
    <div>
        <label for="endMonth" class="text-gray-600">Sampai Bulan:</label>
        <select id="endMonth" class="bg-gray-100 border border-gray-300 rounded p-1 text-xs w-28">
            <option value="" selected disabled>Pilih Bulan</option>
        </select>
    </div>
</div>
<div class="flex justify-start mt-2">
    <button id="applyFilters" class="bg-blue-500 text-white text-xs font-semibold py-1 px-3 rounded">
        Terapkan
    </button>
</div>


<!-- Chart Section -->
<div class="flex items-center">
    <canvas id="realisasiBiayaPieChart" style="max-width: 160px; max-height: 160px; margin-right: 16px;"></canvas>
    <div id="chartLegend" class="ml-6 text-xs flex flex-col space-y-1"></div>
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
