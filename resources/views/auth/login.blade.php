<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BMS Workshop') }} — Login</title>

    <!-- Font CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root{
            --bg-cream:#f5efe7;
            --bg-sky:#e6f0ff;
            --ink:#0f172a;
            --muted:#667085;
            --accent:#f28b35;
            --accent-2:#2f6fed;
            --card: rgba(255,255,255,.92);
            --border: rgba(15,23,42,.12);
            --shadow: 0 28px 70px rgba(15,23,42,.12);

            --ring: 0 0 0 4px rgba(47,111,237,.18);
        }

        *{ box-sizing:border-box; }
        html,body{ height:100%; }
        body{
            margin:0;
            font-family:"Plus Jakarta Sans",system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
            color:var(--ink);
            background:
                linear-gradient(180deg, rgba(15,23,42,.30), rgba(15,23,42,.10)),
                url("{{ asset('images/bg-login.jpg') }}") center/cover no-repeat;
            background-color: var(--bg-sky);
            overflow-x:hidden;
        }

        .grain{
            position:fixed; inset:0;
            opacity:.05;
            background-image: radial-gradient(rgba(15,23,42,.35) 1px, transparent 1px);
            background-size:18px 18px;
            pointer-events:none;
            mix-blend-mode:multiply;
            z-index:0;
        }

        .orb{
            position:fixed;
            width:320px; height:320px;
            border-radius:999px;
            filter:blur(34px);
            opacity:.55;
            animation: floaty 14s ease-in-out infinite;
            z-index:0;
            display:none;
        }
        .orb.a{ background:#ffd0a6; left:-120px; top:14%; }
        .orb.b{ background:#b3d7ff; right:-140px; bottom:8%; animation-delay:-7s; }

        @keyframes floaty{
            0%,100%{ transform:translate(0,0); }
            50%{ transform:translate(14px,-18px); }
        }

        /* ====== LAYOUT ====== */
        .wrap{
            position:relative;
            z-index:1;
            min-height:100dvh;
            display:grid;
            place-items:center;
            padding:28px 16px;
        }

        .stage{
            width: min(1600px, 100%);
            border-radius:32px;
            background: linear-gradient(160deg, rgba(255,255,255,.92), rgba(255,255,255,.72));
            border:1px solid var(--border);
            box-shadow: var(--shadow);
            backdrop-filter: blur(12px);
            overflow:hidden;
        }

        .stage-inner{ padding: 22px; }

        .grid{
            display:grid;
            grid-template-columns: 1fr;
            gap:18px;
            align-items:center;
        }

        @media (min-width: 1024px){
            .stage-inner{ padding: 40px; }
            .grid{
                grid-template-columns: 1.25fr 520px;
                gap: 44px;
                align-items: center;
            }
            .scene{ display:grid; }
            .mobile-logo{ display:none; }
        }
        @media (max-width: 640px){
            body{ overflow:hidden; }
            .wrap{ padding:12px; min-height:100dvh; }
            .stage{ border-radius:18px; }
            .stage-inner{ padding:16px; }
            .hero{ gap:8px; }
            .card{ padding:18px; border-radius:18px; }
            .row{ flex-direction:column; align-items:stretch; }
            .btn{ width:100%; }
        }

        /* ====== LEFT / HERO ====== */
        .hero{
            display:grid;
            align-content:center;
            gap:14px;
            text-align:center;
            padding: 6px 6px;
        }

        .mobile-logo{
            display:grid;
            place-items:center;
            margin-top: 6px;
        }
        .mobile-logo img{
            width: 220px;
            max-width: 72vw;
            height: auto;
            filter: drop-shadow(0 10px 22px rgba(15,23,42,.12));
        }


        /* (logo dipindah ke jidat robot, jadi brand di atas bisa kosong/optional) */
        .brand{
            display:flex;
            justify-content:center;
            gap:10px;
        }
        .badge{
            display:inline-flex;
            align-items:center;
            gap:10px;
            border-radius:999px;
            padding:6px 14px;
            border:1px solid rgba(47,111,237,.18);
            background: rgba(47,111,237,.08);
            font-size:12px;
            letter-spacing:.14em;
            text-transform:uppercase;
            color:#2c5bd1;
        }

        .hero-title{
            font-size: clamp(16px, 1.7vw, 24px);
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-top: 4px;
        }
        .hero-sub{
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
            max-width: 60ch;
            margin: 0 auto;
        }
        .scene{
            display:none;
            place-items:center;
            margin-top: 6px;
        }
        .mascot-card{
            width: min(300px, 92%);
            aspect-ratio: 1 / 1;
            border-radius: 28px;
            background: rgba(255,255,255,.62);
            border: 1px solid rgba(15,23,42,.08);
            box-shadow: 0 20px 50px rgba(15,23,42,.12);
            backdrop-filter: blur(10px);
            display:grid;
            place-items:center;
            overflow:hidden;
        }
        .mascot-card svg{
            width: 92%;
            height:auto;
            filter: drop-shadow(0 18px 34px rgba(15,23,42,.16));
        }

        /* ====== RIGHT / FORM ====== */
        .card{
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 26px;
            padding: 26px;
            box-shadow: 0 20px 50px rgba(15,23,42,.12);
            backdrop-filter: blur(10px);
        }

        .card h2{
            margin:0 0 6px 0;
            font-size: 22px;
            font-weight: 800;
        }
        .card p{
            margin:0 0 18px 0;
            font-size: 13px;
            color: var(--muted);
        }

        .field{ margin-bottom: 14px; }
        label{
            display:block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .input{
            width: 100%;
            height: 44px;
            border-radius: 12px;
            border: 1px solid rgba(15,23,42,.14);
            background: rgba(255,255,255,.95);
            padding: 0 12px;
            outline: none;
            font-size: 14px;
        }
        .input:focus{
            border-color: rgba(47,111,237,.55);
            box-shadow: var(--ring);
        }

        .pw-wrap{ position: relative; }
        .pw-wrap .input{ padding-right: 46px; }

        .pw-btn{
            position:absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border-radius: 12px;
            border: 1px solid rgba(15,23,42,.12);
            background: rgba(255,255,255,.9);
            display:grid;
            place-items:center;
            cursor:pointer;
            user-select:none;
        }
        .pw-btn:hover{ background: rgba(255,255,255,1); }
        .pw-btn svg{ width:18px; height:18px; opacity:.75; }

        .row{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            flex-wrap:wrap;
            margin-top: 14px;
        }

        .remember{
            display:flex;
            align-items:center;
            gap:8px;
            font-size: 13px;
            color: var(--muted);
            margin: 8px 0 0;
        }

        .link{
            color: var(--muted);
            font-size: 13px;
            text-decoration: none;
        }
        .link:hover{ color: var(--ink); }

        .btn{
            border:none;
            border-radius: 999px;
            height: 44px;
            padding: 0 22px;
            font-weight: 800;
            letter-spacing:.02em;
            cursor:pointer;
            background: linear-gradient(120deg, var(--accent), #f7b56f);
            box-shadow: 0 12px 26px rgba(242,139,53,.28);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn:hover{
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(242,139,53,.32);
        }

        .error{
            margin-top: 8px;
            font-size: 12px;
            color: #b42318;
        }

        .status{
            border: 1px solid rgba(47,111,237,.22);
            background: rgba(47,111,237,.08);
            color: #1f4fd0;
            padding: 10px 12px;
            border-radius: 14px;
            font-size: 13px;
            margin-bottom: 14px;
        }

        /* ====== MASCOT EYES ANIMATION ====== */
        .eye-pupil{
            transition: transform .08s ease, opacity .18s ease;
            transform-origin: center;
        }
        .eye-lid{
            opacity: 0;
            transition: opacity .16s ease;
        }
        .eyes-closed .eye-pupil{ opacity: 0; }
        .eyes-closed .eye-lid{ opacity: 1; }

        /* logo di dalam svg biar halus */
        .m-logo{ image-rendering:auto; }
    </style>
</head>

<body>
<div class="grain"></div>
<div class="orb a"></div>
<div class="orb b"></div>

<div class="wrap" id="loginShell">
    <div class="stage">
        <div class="stage-inner">
            <div class="grid">

                <!-- LEFT -->
                <section class="hero">

                    <div class="mobile-logo">
                        <img src="{{ asset('images/logo-bms.png') }}" alt="BMS Workshop">
                    </div>

                    <div class="scene">
                        <div class="mascot-card">
                            <!-- âœ… Mascot + logo di jidat + mata ngikut -->
                            <svg id="mascotSvg" viewBox="0 0 420 420" role="img" aria-label="Mascot">
                                <defs>
                                    <linearGradient id="mBody" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#FFE7CF"/>
                                        <stop offset="52%" stop-color="#F5F7FF"/>
                                        <stop offset="100%" stop-color="#DDEBFF"/>
                                    </linearGradient>

                                    <linearGradient id="mGlow" x1="0" y1="0" x2="1" y2="0">
                                        <stop offset="0%" stop-color="#f9c28b"/>
                                        <stop offset="100%" stop-color="#8dbbff"/>
                                    </linearGradient>

                                    <filter id="mShadow" x="-30%" y="-30%" width="160%" height="160%">
                                        <feDropShadow dx="0" dy="18" stdDeviation="16" flood-color="#0f172a" flood-opacity="0.18"/>
                                    </filter>

                                    <!-- âœ… clip jidat biar logo ikut rounded -->
                                    <clipPath id="foreheadClip">
                                        <rect x="110" y="124" width="200" height="72" rx="22"/>
                                    </clipPath>
                                </defs>

                                <g filter="url(#mShadow)">
                                    <!-- head -->
                                    <rect x="70" y="92" width="280" height="250" rx="64" fill="url(#mBody)" stroke="rgba(15,23,42,0.08)"/>

                                    <!-- top panel -->
                                    <rect x="110" y="124" width="200" height="72" rx="22"
                                          fill="rgba(255,255,255,0.80)" stroke="rgba(15,23,42,0.06)"/>

                                    <!-- âœ… logo di jidat -->
                                    <g clip-path="url(#foreheadClip)">
                                        <rect x="110" y="124" width="200" height="72" rx="22" fill="rgba(255,255,255,0.35)"/>
                                        <image class="m-logo"
                                               href="{{ asset('images/logo-bms.png') }}"
                                               x="132" y="136"
                                               width="156" height="48"
                                               preserveAspectRatio="xMidYMid meet"
                                               opacity="0.98"/>
                                    </g>

                                    <!-- antennas -->
                                    <g>
                                        <path d="M150 120 C150 80, 175 70, 195 56" stroke="rgba(47,111,237,0.65)" stroke-width="7" stroke-linecap="round" fill="none"/>
                                        <circle cx="200" cy="54" r="10" fill="#2f6fed"/>
                                        <path d="M270 120 C270 82, 245 70, 230 58" stroke="rgba(242,139,53,0.65)" stroke-width="7" stroke-linecap="round" fill="none"/>
                                        <circle cx="226" cy="56" r="10" fill="#f28b35"/>
                                    </g>

                                    <!-- cheeks -->
                                    <circle cx="130" cy="250" r="11" fill="rgba(47,111,237,0.18)"/>
                                    <circle cx="290" cy="250" r="11" fill="rgba(242,139,53,0.18)"/>

                                    <!-- eyes -->
                                    <g>
                                        <circle cx="160" cy="250" r="46" fill="rgba(255,255,255,0.92)"/>
                                        <circle cx="260" cy="250" r="46" fill="rgba(255,255,255,0.92)"/>

                                        <circle class="eye-pupil" cx="160" cy="250" r="14" fill="#0f172a"/>
                                        <circle class="eye-pupil" cx="260" cy="250" r="14" fill="#0f172a"/>

                                        <circle cx="152" cy="242" r="6" fill="#fff" opacity="0.85"/>
                                        <circle cx="252" cy="242" r="6" fill="#fff" opacity="0.85"/>

                                        <rect class="eye-lid" x="126" y="246" width="68" height="12" rx="6" fill="#0f172a"/>
                                        <rect class="eye-lid" x="226" y="246" width="68" height="12" rx="6" fill="#0f172a"/>
                                    </g>

                                    <!-- mouth -->
                                    <g>
                                        <rect x="150" y="308" width="120" height="30" rx="15" fill="url(#mGlow)" opacity="0.85"/>
                                        <rect x="182" y="314" width="56" height="18" rx="9" fill="#0f172a" opacity="0.84"/>
                                    </g>
                                </g>
                            </svg>
                        </div>
                    </div>

                    <div class="hero-title">Silahkan Log In Terlebih dahulu</div>
                </section>

                <!-- RIGHT -->
                <section class="card">
                    @if (session('status'))
                        <div class="status">{{ session('status') }}</div>
                    @endif

                    <h2>Log in</h2>
                    <p>Gunakan email & password yang terdaftar.</p>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="field">
                            <label for="email">Email</label>
                            <input
                                id="email"
                                class="input"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="name@company.com"
                            >
                            @error('email')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="password">Password</label>
                            <div class="pw-wrap">
                                <input
                                    id="password"
                                    class="input"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="***********"
                                >

                                <button type="button" class="pw-btn" onclick="togglePasswordVisibility()" aria-label="Lihat/Sembunyikan password">
                                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 3C5.455 3 1.743 6.423.5 10c1.243 3.577 4.955 7 9.5 7s8.257-3.423 9.5-7c-1.243-3.577-4.955-7-9.5-7zm0 12a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 000 6z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <label class="remember">
                            <input id="remember_me" type="checkbox" name="remember">
                            Remember me
                        </label>

                        <div class="row">
                            @if (Route::has('password.request'))
                                <a class="link" href="{{ route('password.request') }}">Forgot your password?</a>
                            @else
                                <span></span>
                            @endif

                            <button class="btn" type="submit">Log in</button>
                        </div>
                    </form>
                </section>

            </div>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(){
        const input = document.getElementById("password");
        const eyeIcon = document.getElementById("eyeIcon");
        if(!input || !eyeIcon) return;

        if(input.type === "password"){
            input.type = "text";
            eyeIcon.innerHTML = `<path d="M2.1 5a10.92 10.92 0 0115.8 0l-1.5 1.5A8.93 8.93 0 0010 4c-2.2 0-4.2.8-5.7 2.2L2.1 5zm1.4 1.4L4.9 7.7c-1.4 1.4-2.3 3.3-2.3 5.3s.8 4 2.3 5.3l1.5-1.5A8.93 8.93 0 0010 16a8.93 8.93 0 006.3-2.2l1.5 1.5c1.4-1.4 2.3-3.3 2.3-5.3s-.8-4-2.3-5.3l-1.5-1.5A8.93 8.93 0 0010 8a8.93 8.93 0 00-6.3 2.2L3.5 6.4z" />`;
        } else {
            input.type = "password";
            eyeIcon.innerHTML = `<path d="M10 3C5.455 3 1.743 6.423.5 10c1.243 3.577 4.955 7 9.5 7s8.257-3.423 9.5-7c-1.243-3.577-4.955-7-9.5-7zm0 12a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 000 6z" />`;
        }
    }

    // âœ… Eye tracking + blink saat fokus password
    (function(){
        const shell = document.getElementById("loginShell");
        const mascot = document.getElementById("mascotSvg");
        const pupils = document.querySelectorAll(".eye-pupil");
        const password = document.getElementById("password");

        if(!shell || !mascot || pupils.length === 0) return;

        function moveEyes(xRatio, yRatio){
            const max = 10;
            const x = (xRatio - 0.5) * 2 * max;
            const y = (yRatio - 0.5) * 2 * max;

            pupils.forEach(p => {
                p.style.transform = `translate(${x.toFixed(2)}px, ${y.toFixed(2)}px)`;
            });
        }

        shell.addEventListener("mousemove", (e) => {
            const r = shell.getBoundingClientRect();
            const xRatio = (e.clientX - r.left) / r.width;
            const yRatio = (e.clientY - r.top) / r.height;
            moveEyes(xRatio, yRatio);
        });

        shell.addEventListener("mouseleave", () => moveEyes(0.5, 0.5));

        if(password){
            password.addEventListener("focus", () => mascot.classList.add("eyes-closed"));
            password.addEventListener("blur", () => mascot.classList.remove("eyes-closed"));
        }
    })();
</script>

</body>
</html>
