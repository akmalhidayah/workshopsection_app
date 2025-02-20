<x-pkm-layout>
<div class="container mx-auto p-6 space-y-8">
    <!-- Header -->
    <div class="text-center">
        <h1 class="text-3xl font-bold text-orange-600">Dashboard PKM</h1>
        <p class="text-gray-700">Selamat datang, {{ Auth::user()->name }}!</p>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-r from-orange-100 to-orange-300 shadow-md rounded-lg p-6 text-center">
            <h2 class="text-lg font-semibold text-gray-700">Total Pekerjaan</h2>
            <p class="text-4xl font-bold text-orange-700">{{ $totalPekerjaan }}</p>
            <span class="text-gray-600 text-sm">Pekerjaan yang sedang dikelola</span>
        </div>
        <div class="bg-gradient-to-r from-red-100 to-red-300 shadow-md rounded-lg p-6 text-center">
            <h2 class="text-lg font-semibold text-gray-700">Pekerjaan Menunggu</h2>
            <p class="text-4xl font-bold text-red-700">{{ $pekerjaanMenunggu }}</p>
            <span class="text-gray-600 text-sm">Belum selesai dikerjakan</span>
        </div>
        <div class="bg-gradient-to-r from-green-100 to-green-300 shadow-md rounded-lg p-6 text-center">
            <h2 class="text-lg font-semibold text-gray-700">Total Progress</h2>
            <p class="text-4xl font-bold text-green-700">{{ round($totalProgress, 2) }}%</p>
            <span class="text-gray-600 text-sm">Dari seluruh pekerjaan</span>
        </div>
        <div class="bg-gradient-to-r from-blue-100 to-blue-300 shadow-md rounded-lg p-6 text-center">
            <h2 class="text-lg font-semibold text-gray-700">Pekerjaan Selesai</h2>
            <p class="text-4xl font-bold text-blue-700">{{ $pekerjaanSelesai }}</p>
            <span class="text-gray-600 text-sm">Pekerjaan selesai dikelola</span>
        </div>
    </div>

    <!-- Daftar Pekerjaan dan Kalender -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daftar Pekerjaan -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Daftar Pekerjaan</h2>
            <ul>
                @foreach($targetDates as $task)
                    @php
                        $isOverdue = \Carbon\Carbon::parse($task['date'])->isPast();
                    @endphp
                    <li class="mb-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <strong>Pekerjaan:</strong> 
                                <span class="{{ $isOverdue ? 'text-red-600 font-bold' : 'text-gray-700' }}">
                                    {{ $task['description'] }}
                                </span>
                                <br>
                                <strong>Target:</strong> 
                                <span class="{{ $isOverdue ? 'text-red-600 font-bold' : 'text-gray-700' }}">
                                    {{ \Carbon\Carbon::parse($task['date'])->format('d M Y') }}
                                </span>
                                @if($isOverdue)
                                    <p class="text-xs text-red-500 italic">Melewati batas target!</p>
                                @endif
                            </div>
                            <a href="{{ route('pkm.jobwaiting') }}" class="text-blue-500 hover:underline">Lihat Detail</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Kalender -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="text-center mb-4">
                <h2 class="text-lg font-semibold text-gray-700">
                    Kalender - {{ \Carbon\Carbon::now()->format('F Y') }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table-auto min-w-max w-full text-center border-collapse border border-gray-300">
                    <thead class="bg-gray-100 text-sm">
                        <tr>
                            @foreach(['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                                <th class="border border-gray-300 px-3 py-2 whitespace-nowrap">{{ $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
                            $endOfMonth = \Carbon\Carbon::now()->endOfMonth();
                            $currentDate = $startOfMonth->copy()->startOfWeek();
                        @endphp
                        @while($currentDate <= $endOfMonth->endOfWeek())
                            <tr>
                                @for($i = 0; $i < 7; $i++)
                                    @php
                                        $isTargetDate = collect($targetDates)->contains('date', $currentDate->format('Y-m-d'));
                                        $isToday = $currentDate->isToday();
                                    @endphp
                                    <td class="border border-gray-300 px-3 py-2 text-center text-sm 
                                        {{ $isTargetDate ? 'bg-red-100 text-red-600 font-bold' : '' }} 
                                        {{ $isToday ? 'bg-yellow-100 text-yellow-600 font-bold' : '' }}">
                                        {{ $currentDate->month == $startOfMonth->month ? $currentDate->day : '' }}
                                    </td>
                                    @php $currentDate->addDay(); @endphp
                                @endfor
                            </tr>
                        @endwhile
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    <!-- Day.js & Custom Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.8/dayjs.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarWrapper = document.getElementById('calendar');
            const currentMonth = dayjs().month(); // Bulan saat ini
            const currentYear = dayjs().year();

            const renderCalendar = (month, year) => {
                const startDay = dayjs(`${year}-${month + 1}-01`).startOf('month');
                const endDay = dayjs(`${year}-${month + 1}-01`).endOf('month');
                const daysInMonth = endDay.date();

                let html = '<table class="table-auto w-full border-collapse border border-gray-300">';
                html += '<thead><tr class="bg-gray-100">';
                ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'].forEach(day => {
                    html += `<th class="border border-gray-300 p-2 text-center">${day}</th>`;
                });
                html += '</tr></thead><tbody><tr>';

                // Tambahkan hari kosong sebelum tanggal 1
                for (let i = 0; i < startDay.day(); i++) {
                    html += '<td class="border border-gray-300 p-2"></td>';
                }

                for (let day = 1; day <= daysInMonth; day++) {
                    const currentDate = dayjs(`${year}-${month + 1}-${day}`).format('YYYY-MM-DD');
                    html += `<td class="border border-gray-300 p-2 text-center">${day}</td>`;

                    // Pindah ke baris baru jika hari ke-7
                    if ((startDay.day() + day) % 7 === 0) {
                        html += '</tr><tr>';
                    }
                }

                html += '</tr></tbody></table>';
                calendarWrapper.innerHTML = html;
            };

            renderCalendar(currentMonth, currentYear);
        });
    </script>
</x-pkm-layout>
