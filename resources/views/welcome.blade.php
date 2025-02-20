<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section of Workshop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0); }
        }

        .floating {
            animation: float 4s ease-in-out infinite;
        }

        body::-webkit-scrollbar {
            display: none;
        }

        body {
            -ms-overflow-style: none;  /* Internet Explorer 10+ */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans">

<nav style="background: linear-gradient(90deg, #ffffff, #f5f5f5);" class="shadow-md fixed top-0 left-0 w-full z-50">
    <div class="px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <!-- Logo Section -->
        <div class="flex items-center space-x-4">
            <img src="{{ asset('images/logo-sig.png') }}" alt="SIG Logo" class="h-8 w-auto">
            <img src="{{ asset('images/logo-st2.png') }}" alt="Semen Tonasa Logo" class="h-8 w-auto">
            <div class="text-gray-900">
                <span class="font-bold text-base block tracking-wider">WORKSHOP</span>
                <span class="text-xs">Dept. Of Project Management & Main Support</span>
            </div>
        </div>

        <!-- Mobile Menu Button -->
        <button @click="isOpen = !isOpen" class="sm:hidden focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
        </button>

        <!-- Desktop Menu Items -->
        <div class="hidden sm:flex items-center space-x-6">
            @if (!empty($caraKerjaFiles))
                <a href="{{ Storage::url($caraKerjaFiles[0]) }}" download class="text-xs text-green-600 hover:underline">
                    Cara Kerja Dokumen
                </a>
            @endif

            @if (!empty($flowchartFiles))
                <a href="{{ Storage::url($flowchartFiles[0]) }}" download class="text-xs text-blue-600 hover:underline">
                    Flowchart Aplikasi
                </a>
            @endif
            <a href="https://www.appsheet.com/start/..." class="bg-orange-500 text-white px-3 py-1 rounded hover:bg-orange-600 text-sm">
                E-Report
            </a>
            <a href="/login" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700 text-sm">
                Login Workshop Section
            </a>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="isOpen" x-transition class="sm:hidden bg-white shadow-lg px-4 py-2 space-y-2">
        <a href="{{ Storage::url($caraKerjaFiles[0]) }}" download class="block text-green-500 hover:underline py-2 text-sm">
            Cara Kerja Aplikasi
        </a>
        <a href="{{ Storage::url($flowchartFiles[0]) }}" download class="block text-blue-500 hover:underline py-2 text-sm">
            Flowchart Aplikasi
        </a>
        <a href="https://www.appsheet.com/start/..." class="block bg-orange-500 text-white px-3 py-2 rounded-lg hover:bg-orange-600 text-sm">
            E-Report
        </a>
        <a href="/login" class="block bg-gray-800 text-white px-3 py-2 rounded-lg hover:bg-gray-700 text-sm">
            Login Workshop Section
        </a>
    </div>
</nav>

<!-- Header -->
<header class="relative h-screen overflow-hidden bg-white">
    <!-- Video Section -->
    <div class="absolute inset-0">
        <video autoplay loop muted playsinline class="w-full h-full object-cover">
            <source src="{{ asset('images/bg.mp4') }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    <!-- Text Section -->
    <div class="relative z-10 h-full flex items-end justify-end pb-20 pr-10"> <!-- Naikkan posisi teks -->
        <div>
            <h1 class="text-4xl font-extrabold mb-2 floating text-gray-100 text-right" style="text-shadow: 3px 3px 8px rgba(0, 0, 0, 0.9);">
                Welcome to the Workshop Section
            </h1>
            <p class="text-lg text-red-500 text-right">
                PT. Semen Tonasa
            </p>
        </div>
    </div>
</header>

<!-- Slideshow Gallery -->
<section class="relative py-10 bg-white">
    <div x-data="{ currentSlide: 0, images: ['{{ asset('images/1.jpg') }}', '{{ asset('images/2.jpg') }}', '{{ asset('images/3.jpg') }}','{{ asset('images/4.jpg') }}','{{ asset('images/5.jpg') }}','{{ asset('images/6.jpg') }}'] }" 
         x-init="setInterval(() => currentSlide = (currentSlide + 1) % images.length, 5000)" 
         class="max-w-6xl mx-auto overflow-hidden rounded-lg shadow-lg">
        <div class="relative h-80">
            <template x-for="(image, index) in images" :key="index">
                <div class="absolute inset-0 transition-transform duration-1000" 
                     :class="{'translate-x-full opacity-0': currentSlide !== index, 'translate-x-0 opacity-100': currentSlide === index}" 
                     :style="{ transform: `translateX(${(index - currentSlide) * 100}%)` }">
                    <img :src="image" alt="Slideshow Image" class="object-cover w-full h-full">
                </div>
            </template>
        </div>
    </div>
</section>


<!-- News Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto">
        <h2 class="text-2xl font-semibold text-center mb-8">Berita Terbaru</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Berita Pertama -->
            <div class="bg-gray-100 p-4 rounded-lg shadow-md hover:shadow-xl transition">
                <img src="{{ asset('images/4.jpg') }}" alt="Berita 1" class="rounded-lg mb-4">
                <h3 class="text-lg font-bold">Semen Tonasa Bergabung dengan SIG</h3>
                <p class="text-sm text-gray-600">Semen Tonasa kini resmi bergabung dengan SIG untuk meningkatkan kualitas layanan...</p>
                <a href="https://www.sig.id/semen-tonasa" class="text-red-500 underline">Baca Selengkapnya</a>
            </div>

            <!-- Berita Kedua -->
            <div class="bg-gray-100 p-4 rounded-lg shadow-md hover:shadow-xl transition">
                <img src="{{ asset('images/1.jpg') }}" alt="Berita 2" class="rounded-lg mb-4">
                <h3 class="text-lg font-bold">TIM INOVASI BENGKEL MESIN 2024</h3>
                <p class="text-sm text-gray-600">Tim inovasi berhasil meraih penghargaan Platinum pada kategori Breakthrough Innovation...</p>
            </div>

            <!-- Berita Ketiga -->
            <div class="bg-gray-100 p-4 rounded-lg shadow-md hover:shadow-xl transition">
                <img src="{{ asset('images/2.jpg') }}" alt="Berita 3" class="rounded-lg mb-4">
                <h3 class="text-lg font-bold">E-REPORT BY BENGKEL MESIN</h3>
                <p class="text-sm text-gray-600">Aplikasi E-Report kini tersedia di Playstore. Download aplikasi untuk mempermudah laporan harian...</p>
                <a href="https://www.appsheet.com/start/5d8aa0c0-02e4-40da-864d-eacdb78cfd92?platform=desktop#vss=H4sIAAAAAAAAA52OvQ7CMBCD38VzniArYkCoLCAWwhCaqxTRJlWTAFWUd-fCj1iB8Xz-bGdcLF23UbdnyEP-XGuaIZEVdvNIClJh4V2cfK8gFDZ6eIoNuaRQUI7izUYKkPl7VP7fKmANuWg7S1PNqRTzL4bflWCh-lEEhhT1qafHSPaXwlrn2xTI7HnCj9Vh5Za3UTvTeMNxne4DlTudAZXRUQEAAA==&view=Menu&appName=E-REPORT-919375348" class="text-red-500 underline">Download Sekarang</a>
            </div>
        </div>
    </div>
</section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-6">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Section of Workshop. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
