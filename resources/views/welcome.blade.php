<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Section of Workshop · PT. Semen Tonasa</title>

    {{-- Vite compiled assets (Tailwind + app.js) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine (defer) --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        /* subtle float animation for hero headline */
        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0); }
        }
        .floating {
            animation: float 4s ease-in-out infinite;
        }

        /* hide default scrollbar for a cleaner look (optional) */
        body::-webkit-scrollbar { display: none; }
        body { -ms-overflow-style: none; scrollbar-width: none; }
                .footer-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            color: #cbd5e1; /* slate-300 */
            border-radius: 8px;
            transition: transform .18s ease, color .18s ease, box-shadow .18s ease;
            background: linear-gradient(180deg, rgba(255,255,255,0.01), transparent);
        }

        .footer-icon:hover {
            transform: translateY(-2px) scale(1.03);
            color: #fff;
            box-shadow: 0 6px 18px rgba(99,102,241,0.12); /* indigo-400 glow */
        }

        /* subtle, slow pulse animation for social icons (non intrusive) */
        @keyframes tinyPulse {
            0% { box-shadow: 0 0 0 0 rgba(99,102,241,0.00); }
            70% { box-shadow: 0 6px 14px 0 rgba(99,102,241,0.06); }
            100% { box-shadow: 0 0 0 0 rgba(99,102,241,0.00); }
        }
        .footer-icon:nth-child(odd) { animation: tinyPulse 6s infinite ease-in-out; }
        .footer-icon:nth-child(even) { animation: tinyPulse 8s infinite ease-in-out; }
    </style>
</head>
<body class="antialiased bg-gray-50 text-gray-900">

<a class="sr-only focus:not-sr-only" href="#main">Skip to content</a>

{{-- FLOATING STICKY NAVBAR --}}
<nav x-data="{ open:false }"
     class="fixed top-4 inset-x-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-white/80 backdrop-blur-xl shadow-lg rounded-2xl border border-gray-200">
            <div class="flex items-center justify-between h-16 px-5">

                {{-- LEFT : LOGO --}}
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-sig.png') }}" class="h-8">
                    <img src="{{ asset('images/logo-st2.png') }}" class="h-8">
                    <div class="leading-tight">
                        <div class="font-extrabold text-sm">WORKSHOP</div>
                        <div class="text-xs text-gray-500">Dept. Project Management & Main Support</div>
                    </div>
                </div>

                {{-- RIGHT : MENU DESKTOP --}}
                <div class="hidden md:flex items-center gap-5">

                    {{-- USER BOOK DROPDOWN --}}
                    <div x-data="{ d:false }" class="relative">
                        <button @mouseenter="d=true" @mouseleave="d=false"
                                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-emerald-600 hover:bg-emerald-50 transition">
                            <span>User Book App</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- DROPDOWN --}}
                        <div x-show="d" x-transition x-cloak
                             @mouseenter="d=true" @mouseleave="d=false"
                             class="absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">

                            {{-- PNS --}}
                            @if(!empty($caraKerjaPns[0]['url']))
                            <a href="{{ $caraKerjaPns[0]['url'] }}" download
                               class="flex items-center gap-3 px-5 py-4 hover:bg-emerald-50 transition">
                                <div class="w-9 h-9 flex items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">📘</div>
                                <div>
                                    <div class="font-semibold text-sm">Role PNS</div>
                                    <div class="text-xs text-gray-500">Panduan pengguna</div>
                                </div>
                            </a>
                            @endif

                            {{-- PKM --}}
                            @if(!empty($caraKerjaPkm[0]['url']))
                            <a href="{{ $caraKerjaPkm[0]['url'] }}" download
                               class="flex items-center gap-3 px-5 py-4 hover:bg-indigo-50 transition">
                                <div class="w-9 h-9 flex items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">📗</div>
                                <div>
                                    <div class="font-semibold text-sm">Role PKM</div>
                                    <div class="text-xs text-gray-500">Panduan PKM</div>
                                </div>
                            </a>
                            @endif

                            {{-- APPROVAL --}}
                            @if(!empty($caraKerjaApproval[0]['url']))
                            <a href="{{ $caraKerjaApproval[0]['url'] }}" download
                               class="flex items-center gap-3 px-5 py-4 hover:bg-amber-50 transition">
                                <div class="w-9 h-9 flex items-center justify-center rounded-xl bg-amber-100 text-amber-600">📙</div>
                                <div>
                                    <div class="font-semibold text-sm">Role Approval</div>
                                    <div class="text-xs text-gray-500">Panduan approval</div>
                                </div>
                            </a>
                            @endif
                        </div>
                    </div>

                    {{-- FLOWCHART --}}
                    @if(!empty($flowchartFiles[0]['url']))
                    <a href="{{ $flowchartFiles[0]['url'] }}" download
                       class="px-4 py-2 rounded-xl text-sm font-semibold text-blue-600 hover:bg-blue-50 transition">
                        Flowchart Aplikasi
                    </a>
                    @endif

                    {{-- E-REPORT --}}
                    <a href="https://www.appsheet.com/start/..."
                       class="px-5 py-2 rounded-xl bg-orange-500 text-white text-sm font-semibold shadow hover:bg-orange-600 transition">
                        E-Report
                    </a>

                    {{-- LOGIN --}}
                    <a href="{{ url('/login') }}"
                       class="px-5 py-2 rounded-xl bg-gray-900 text-white text-sm font-semibold shadow hover:bg-gray-800 transition">
                        Login
                    </a>
                </div>

                {{-- MOBILE BUTTON --}}
                <div class="md:hidden">
                    <button @click="open=!open"
                            class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>

            </div>

            {{-- MOBILE MENU --}}
            <div x-show="open" x-transition x-cloak class="md:hidden px-5 pb-5 space-y-2">
                <div class="text-xs font-semibold text-gray-400 uppercase mt-2">User Book App</div>

                @if(!empty($caraKerjaPns[0]['url']))
                <a href="{{ $caraKerjaPns[0]['url'] }}" download class="block px-3 py-2 rounded-lg hover:bg-emerald-50">📘 Role PNS</a>
                @endif
                @if(!empty($caraKerjaPkm[0]['url']))
                <a href="{{ $caraKerjaPkm[0]['url'] }}" download class="block px-3 py-2 rounded-lg hover:bg-indigo-50">📗 Role PKM</a>
                @endif
                @if(!empty($caraKerjaApproval[0]['url']))
                <a href="{{ $caraKerjaApproval[0]['url'] }}" download class="block px-3 py-2 rounded-lg hover:bg-amber-50">📙 Role Approval</a>
                @endif

                @if(!empty($flowchartFiles[0]['url']))
                <a href="{{ $flowchartFiles[0]['url'] }}" download class="block px-3 py-2 text-blue-600">Flowchart</a>
                @endif

                <a href="https://www.appsheet.com/start/..." class="block bg-orange-500 text-white px-3 py-2 rounded-lg">E-Report</a>
                <a href="{{ url('/login') }}" class="block bg-gray-900 text-white px-3 py-2 rounded-lg">Login</a>
            </div>
        </div>
    </div>
</nav>

<main id="main" class="pt-16">

    {{-- HERO: background video + modern headline --}}
    <header class="relative h-[88vh] md:h-screen overflow-hidden">
        {{-- video background --}}
        <div class="absolute inset-0">
            <video autoplay loop muted playsinline preload="metadata" class="w-full h-full object-cover">
                <source src="{{ asset('images/bg.mp4') }}" type="video/mp4">
                {{-- fallback poster --}}
            </video>
            {{-- dark overlay for readable text --}}
            <div class="absolute inset-0 bg-gradient-to-r from-black/55 via-black/35 to-transparent"></div>
        </div>

        {{-- content --}}
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center">
            <div class="w-full md:w-2/3 lg:w-1/2 py-12">
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight text-white drop-shadow-2xl floating">
                    <span class="block">Welcome to the</span>
                    <span class="block bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-200">Workshop Section</span>
                </h1>

                <p class="mt-4 text-lg sm:text-xl text-red-300 font-semibold">PT. Semen Tonasa</p>

                <p class="mt-6 text-gray-200/90 max-w-xl">
                    Dept. of Project Management & Main Support — pusat inovasi dan layanan bengkel mesin. Temukan SOP,
                    flowchart aplikasi, dan laporan E-Report untuk mendukung operasional Anda.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ url('/login') }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg shadow hover:bg-gray-800">Masuk</a>
                    <a href="#news" class="inline-flex items-center px-4 py-2 border border-white/30 text-white rounded-lg hover:bg-white/5">Berita Terbaru</a>
                </div>
            </div>
        </div>
    </header>

    {{-- SLIDESHOW / GALLERY --}}
    <section aria-labelledby="gallery-heading" class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="gallery-heading" class="text-2xl font-semibold text-gray-800 mb-6">Gallery</h2>

            <div x-data="slideshow()" x-init="init()" x-on:keydown.window.arrow-left="prev()" x-on:keydown.window.arrow-right="next()" class="relative">
                <div class="overflow-hidden rounded-lg shadow-lg">
                    <template x-for="(img, idx) in images" :key="idx">
                        <div x-show="current === idx" x-transition:enter="transition transform duration-700" x-transition:enter-start="opacity-0 -translate-x-6" x-transition:enter-end="opacity-100 translate-x-0" class="w-full h-72 md:h-96">
                            <img :src="img" alt="Gallery image" class="object-cover w-full h-full">
                        </div>
                    </template>
                </div>

                {{-- indicators & controls --}}
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <button @click="prev()" class="px-3 py-2 rounded-md bg-gray-100 hover:bg-gray-200">Prev</button>
                        <button @click="next()" class="px-3 py-2 rounded-md bg-gray-100 hover:bg-gray-200">Next</button>
                    </div>

                    <div class="flex items-center gap-2">
                        <template x-for="(img, idx) in images" :key="idx">
                            <button @click="goTo(idx)" :class="{'bg-gray-800': current === idx, 'bg-gray-300': current !== idx }" class="w-2 h-2 rounded-full"></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- NEWS / CARDS --}}
    <section id="news" class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Berita Terbaru</h2>
                <a href="#" class="text-sm text-indigo-600 hover:underline">Lihat semua</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Card 1 --}}
                <article class="bg-white rounded-lg shadow-sm hover:shadow-lg overflow-hidden">
                    <img src="{{ asset('images/4.jpg') }}" alt="Semen Tonasa Bergabung" class="w-full h-44 object-cover">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg">Semen Tonasa Bergabung dengan SIG</h3>
                        <p class="mt-2 text-sm text-gray-600">Semen Tonasa kini resmi bergabung dengan SIG untuk meningkatkan kualitas layanan...</p>
                        <a href="https://www.sig.id/semen-tonasa" target="_blank" rel="noopener noreferrer" class="mt-3 inline-block text-sm text-red-500 hover:underline">Baca Selengkapnya</a>
                    </div>
                </article>

                {{-- Card 2 --}}
                <article class="bg-white rounded-lg shadow-sm hover:shadow-lg overflow-hidden">
                    <img src="{{ asset('images/1.jpg') }}" alt="Tim Inovasi" class="w-full h-44 object-cover">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg">TIM INOVASI BENGKEL MESIN 2024</h3>
                        <p class="mt-2 text-sm text-gray-600">Tim inovasi berhasil meraih penghargaan Platinum pada kategori Breakthrough Innovation...</p>
                    </div>
                </article>

                {{-- Card 3 --}}
                <article class="bg-white rounded-lg shadow-sm hover:shadow-lg overflow-hidden">
                    <img src="{{ asset('images/2.jpg') }}" alt="E-Report" class="w-full h-44 object-cover">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg">E-REPORT BY BENGKEL MESIN</h3>
                        <p class="mt-2 text-sm text-gray-600">Aplikasi E-Report kini tersedia di Playstore. Download aplikasi untuk mempermudah laporan harian...</p>
                        <a href="https://www.appsheet.com/start/5d8aa0c0-02e4-40da-864d-eacdb78cfd92" target="_blank" rel="noopener noreferrer" class="mt-3 inline-block text-sm text-red-500 hover:underline">Download Sekarang</a>
                    </div>
                </article>
            </div>
        </div>
    </section>

</main>

{{-- PREMIUM COMPACT FOOTER — neon border + gradient + animated icons + thin wave --}}
<footer class="relative bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 text-gray-200">

    {{-- very thin decorative wave (SVG) --}}
    <div class="absolute inset-x-0 -top-6 pointer-events-none">
        <svg class="w-full h-6" viewBox="0 0 1440 40" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 30 C 200 0, 400 0, 720 30 C 1040 60, 1240 60, 1440 30 L1440 40 L0 40 Z"
                  fill="rgba(255,255,255,0.02)"/>
        </svg>
    </div>

    {{-- thin neon top border --}}
    <div class="h-0.5 bg-gradient-to-r from-indigo-400 via-purple-400 to-rose-400 opacity-90"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-5">

        <div class="flex items-center justify-between gap-4">

            {{-- left: branding + tiny links --}}
            <div class="flex items-start gap-3">
                <img src="{{ asset('images/logo-st2.png') }}" alt="Logo" class="h-8 mt-0.5">

                <div>
                    <div class="text-sm font-semibold text-white leading-tight">Workshop Section</div>
                    <div class="flex gap-4 text-[12px] text-gray-300 mt-1">
                        @if(!empty($caraKerjaFiles) && isset($caraKerjaFiles[0]['url']))
                            <a href="{{ $caraKerjaFiles[0]['url'] }}" download class="hover:text-indigo-300">Cara Kerja</a>
                        @else
                            <span class="opacity-50">Cara Kerja</span>
                        @endif

                        @if(!empty($flowchartFiles) && isset($flowchartFiles[0]['url']))
                            <a href="{{ $flowchartFiles[0]['url'] }}" download class="hover:text-indigo-300">Flowchart</a>
                        @else
                            <span class="opacity-50">Flowchart</span>
                        @endif

                        <a href="https://www.appsheet.com/start/..." target="_blank" class="hover:text-indigo-300">E-Report</a>
                    </div>
                </div>
            </div>

            {{-- center: animated icons compact --}}
            <div class="hidden sm:flex items-center gap-4">
                {{-- wrapper for icon hover animations --}}
                <a href="mailto:workshop@semen-tonasa.co.id" class="footer-icon" aria-label="Email Workshop">
                    {{-- envelope svg --}}
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" >
                        <path d="M3 8.5v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M22 8.5L12 15 2 8.5" />
                        <path d="M22 8.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2.5" />
                    </svg>
                </a>

                <a href="https://wa.me/628xxxx" target="_blank" rel="noopener noreferrer" class="footer-icon" aria-label="WhatsApp Workshop">
                    {{-- whatsapp svg --}}
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a9 9 0 1 0-2.6 6.1L21 22l-3.9-1.1A9 9 0 0 0 21 11.5z"/>
                        <path d="M16.2 14.1c-.3-.1-1.7-.8-1.9-.9-.2-.1-.3-.2-.5.2-.2.4-.7.9-.9 1.2-.2.3-.4.4-.7.2-.3-.2-1.1-.4-2.1-1.3-.8-.7-1.3-1.6-1.5-1.9-.2-.3 0-.5.2-.7.2-.2.4-.5.6-.7.2-.2.3-.3.5-.5.2-.1.2-.3.3-.5.1-.2 0-.4-.1-.6-.1-.2-1.9-4.1-2.6-4.9-.7-.8-1.3-.9-1.9-.9-.4 0-.9.1-1.3.6-.4.5-.9 1.6-.9 3.4 0 1.8.9 3.5 1 3.8.1.3 1.8 3 4.5 4.6 2.6 1.6 3.3 1.3 3.9 1.2.6-.1 1.8-.7 2.1-1.4.3-.7.3-1.3.2-1.4-.1-.1-.3-.2-.6-.3z"/>
                    </svg>
                </a>

                <a href="https://t.me/username" target="_blank" rel="noopener noreferrer" class="footer-icon" aria-label="Telegram Workshop">
                    {{-- telegram svg --}}
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 2L11 13" />
                        <path d="M22 2L15 22l-4-9-9-4 20-7z"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- thin divider --}}
        <div class="border-t border-slate-800 mt-4"></div>

        {{-- copyright --}}
        <div class="mt-3 text-center text-[11px] text-slate-400">
            &copy; {{ date('Y') }} Workshop Section · PT. Semen Tonasa
        </div>
    </div>
</footer>

<script>
    function slideshow() {
        return {
            images: [
                '{{ asset('images/1.jpg') }}',
                '{{ asset('images/2.jpg') }}',
                '{{ asset('images/3.jpg') }}',
                '{{ asset('images/4.jpg') }}',
                '{{ asset('images/5.jpg') }}',
                '{{ asset('images/6.jpg') }}'
            ],
            current: 0,
            intervalId: null,
            init() {
                // auto rotate
                this.intervalId = setInterval(() => this.next(), 5000);
            },
            next() {
                this.current = (this.current + 1) % this.images.length;
            },
            prev() {
                this.current = (this.current - 1 + this.images.length) % this.images.length;
            },
            goTo(i) {
                this.current = i;
            },
            destroy() {
                clearInterval(this.intervalId);
            }
        }
    }
</script>

</body>
</html>
