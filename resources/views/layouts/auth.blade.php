<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') — Velour</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        velour: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        },
                    },
                    boxShadow: {
                        'auth-card': '0 0 0 1px rgba(255,255,255,0.65) inset, 0 1px 2px rgba(91, 33, 182, 0.06), 0 28px 56px -16px rgba(91, 33, 182, 0.22), 0 16px 32px -12px rgba(15, 23, 42, 0.14)',
                        'auth-input': '0 1px 2px rgba(15, 23, 42, 0.04)',
                        'auth-input-focus': '0 0 0 4px rgba(124, 58, 237, 0.12), 0 4px 12px -2px rgba(124, 58, 237, 0.15)',
                    },
                    animation: {
                        'auth-orb-1': 'authOrbDrift 22s ease-in-out infinite',
                        'auth-orb-2': 'authOrbDrift 28s ease-in-out infinite reverse',
                        'auth-shine': 'authShine 2.5s ease-in-out infinite',
                    },
                    keyframes: {
                        authOrbDrift: {
                            '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                            '33%': { transform: 'translate(2%, 3%) scale(1.03)' },
                            '66%': { transform: 'translate(-2%, 1%) scale(0.98)' },
                        },
                        authShine: {
                            '0%, 100%': { opacity: '0.35' },
                            '50%': { opacity: '0.65' },
                        },
                    },
                },
            },
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        html, body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .auth-bg-mesh {
            background:
                radial-gradient(ellipse 120% 80% at 50% -30%, rgba(167, 139, 250, 0.22), transparent 55%),
                radial-gradient(ellipse 90% 60% at 100% 50%, rgba(196, 181, 253, 0.12), transparent 50%),
                radial-gradient(ellipse 70% 50% at 0% 80%, rgba(221, 214, 254, 0.35), transparent 45%),
                linear-gradient(180deg, #faf8ff 0%, #f4f0ff 45%, #faf5ff 100%);
        }
        .auth-bg-grid {
            background-image:
                linear-gradient(to right, rgba(124, 58, 237, 0.055) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(124, 58, 237, 0.055) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse 85% 75% at 50% 0%, black 15%, transparent 72%);
            -webkit-mask-image: radial-gradient(ellipse 85% 75% at 50% 0%, black 15%, transparent 72%);
        }
        .auth-orb {
            filter: blur(80px);
        }
        .auth-noise {
            opacity: 0.035;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }
        .auth-brand-link:hover .auth-logo-tile {
            transform: translateY(-2px) rotate(-2deg);
            box-shadow: 0 20px 40px -12px rgba(124, 58, 237, 0.45);
        }
        .auth-logo-tile {
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease;
        }
        .auth-link-line {
            padding-bottom: 2px;
            background-image: linear-gradient(currentColor, currentColor);
            background-size: 0% 1px;
            background-repeat: no-repeat;
            background-position: 0 100%;
            transition: background-size 0.35s ease;
        }
        .auth-link-line:hover {
            background-size: 100% 1px;
        }
        @media (prefers-reduced-motion: reduce) {
            .animate-auth-orb-1,
            .animate-auth-orb-2,
            .animate-auth-shine {
                animation: none !important;
            }
            .auth-brand-link:hover .auth-logo-tile {
                transform: none;
                box-shadow: 0 20px 40px -12px rgba(124, 58, 237, 0.45);
            }
        }
        * {
            scrollbar-width: thin;
            scrollbar-color: rgb(221 214 254) rgb(245 243 255);
        }
        *::-webkit-scrollbar { width: 8px; height: 8px; }
        *::-webkit-scrollbar-track { background: rgb(245 243 255); border-radius: 4px; }
        *::-webkit-scrollbar-thumb { background: rgb(196 181 253); border-radius: 4px; }
    </style>
    <script>
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-password-target]');
            if (!btn || btn.type !== 'button') return;
            e.preventDefault();
            var id = btn.getAttribute('data-password-target');
            var input = document.getElementById(id);
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            var eye = btn.querySelector('.auth-password-eye');
            var eyeOff = btn.querySelector('.auth-password-eye-off');
            if (eye && eyeOff) {
                eye.classList.toggle('hidden', show);
                eyeOff.classList.toggle('hidden', !show);
            }
        });
    </script>
</head>
<body class="min-h-screen relative overflow-x-hidden text-slate-800 auth-bg-mesh">
    <div class="pointer-events-none fixed inset-0 auth-bg-grid" aria-hidden="true"></div>
    <div class="pointer-events-none fixed inset-0 auth-noise" aria-hidden="true"></div>
    <div class="pointer-events-none fixed -top-40 left-1/2 h-[480px] w-[780px] -translate-x-1/2 animate-auth-orb-1 rounded-full bg-gradient-to-br from-velour-300 via-violet-300 to-fuchsia-200 opacity-60 auth-orb" aria-hidden="true"></div>
    <div class="pointer-events-none fixed bottom-[-80px] right-[-100px] h-[400px] w-[520px] animate-auth-orb-2 rounded-full bg-gradient-to-tr from-indigo-200 via-velour-100 to-purple-100 opacity-50 auth-orb" aria-hidden="true"></div>
    <div class="pointer-events-none fixed top-1/3 left-[-80px] h-[280px] w-[280px] animate-auth-shine rounded-full bg-violet-200/40 auth-orb blur-3xl" aria-hidden="true"></div>

    <div class="relative z-10 flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:px-6 sm:py-14">
        <div class="w-full mx-auto min-w-0 @yield('auth_container_class', 'max-w-[440px]')">
            <header class="mb-9 text-center sm:mb-11">
                <a href="{{ url('/') }}" class="auth-brand-link group inline-flex flex-col items-center gap-4 rounded-2xl outline-none ring-velour-500/30 focus-visible:ring-2 focus-visible:ring-offset-4 focus-visible:ring-offset-transparent">
                    <span class="auth-logo-tile relative flex h-[3.75rem] w-[3.75rem] items-center justify-center rounded-2xl bg-gradient-to-br from-velour-500 via-violet-600 to-purple-700 text-white shadow-xl shadow-velour-600/30 ring-2 ring-white/60">
                        <span class="absolute inset-0 rounded-2xl bg-gradient-to-t from-black/10 to-transparent" aria-hidden="true"></span>
                        <svg class="relative h-8 w-8 drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.65" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </span>
                    <span class="space-y-1">
                        <span class="block bg-gradient-to-r from-slate-900 via-velour-900 to-violet-900 bg-clip-text text-3xl font-bold tracking-tight text-transparent sm:text-[2rem]">Velour</span>
                        <span class="block text-[13px] font-medium tracking-wide text-slate-500">Salon management, elevated</span>
                    </span>
                </a>
            </header>

            <main class="overflow-hidden rounded-[1.35rem] bg-white/75 shadow-auth-card ring-1 ring-white/80 backdrop-blur-xl sm:rounded-3xl">
                <div class="h-1 w-full bg-gradient-to-r from-indigo-400 via-velour-500 to-fuchsia-500 opacity-95" aria-hidden="true"></div>
                <div class="px-6 py-7 sm:px-9 sm:py-9 md:px-10 md:py-10">
                    @if(session('success'))
                        <div class="mb-6 flex gap-3 rounded-2xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50/95 to-teal-50/50 px-4 py-3 text-sm text-emerald-900 shadow-sm">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="mb-6 space-y-1 rounded-2xl border border-red-200/80 bg-gradient-to-br from-red-50 to-rose-50/80 px-4 py-3 text-sm text-red-900 shadow-sm">
                            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                        </div>
                    @endif
                    @yield('content')
                </div>
            </main>

            <div class="mt-10 flex flex-col items-center gap-3">
                <div class="h-px w-16 bg-gradient-to-r from-transparent via-slate-300 to-transparent" aria-hidden="true"></div>
                <p class="text-center text-[11px] font-medium uppercase tracking-[0.18em] text-slate-400">
                    © {{ date('Y') }} Velour · Encrypted session
                </p>
            </div>
        </div>
    </div>
</body>
</html>
