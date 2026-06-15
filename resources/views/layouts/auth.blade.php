<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') — EasyGrox</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <script>
        (function () {
            var saved = localStorage.getItem('velour-theme');
            var themeVersion = localStorage.getItem('velour-theme-v');
            if (themeVersion !== '2') {
                localStorage.setItem('velour-theme', 'light');
                localStorage.setItem('velour-theme-v', '2');
                saved = 'light';
            }
            if (saved === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
        .dark .auth-bg-mesh {
            background:
                radial-gradient(ellipse 100% 70% at 50% -20%, rgba(124, 58, 237, 0.18), transparent 55%),
                radial-gradient(ellipse 80% 50% at 100% 60%, rgba(91, 33, 182, 0.12), transparent 50%),
                linear-gradient(180deg, #0c0c12 0%, #101018 50%, #0a0a10 100%);
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
        .auth-logo-img {
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .auth-brand-link:hover .auth-logo-img {
            transform: translateY(-2px) scale(1.02);
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
            .auth-brand-link:hover .auth-logo-img {
                transform: none;
            }
        }
        * {
            scrollbar-width: thin;
            scrollbar-color: rgb(221 214 254) rgb(245 243 255);
        }
        *::-webkit-scrollbar { width: 8px; height: 8px; }
        *::-webkit-scrollbar-track { background: rgb(245 243 255); border-radius: 4px; }
        *::-webkit-scrollbar-thumb { background: rgb(196 181 253); border-radius: 4px; }
        .dark * {
            scrollbar-color: rgb(91 33 182) rgb(24 24 32);
        }
        .dark *::-webkit-scrollbar-track { background: rgb(24 24 32); }
        .dark *::-webkit-scrollbar-thumb { background: rgb(91 33 182); }
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
<body class="min-h-screen relative overflow-x-hidden text-slate-800 dark:text-slate-200 auth-bg-mesh" x-data>
    <button type="button"
            @click="$store.theme.toggle()"
            class="fixed top-4 right-4 z-20 rounded-xl border border-slate-200/80 bg-white/80 p-2.5 text-slate-500 shadow-sm backdrop-blur-sm transition-colors hover:text-slate-800 dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-400 dark:hover:text-white"
            title="Toggle theme"
            aria-label="Toggle theme">
        <svg x-show="$store.theme.dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        <svg x-show="!$store.theme.dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
    </button>
    <div class="pointer-events-none fixed inset-0 auth-bg-grid" aria-hidden="true"></div>
    <div class="pointer-events-none fixed inset-0 auth-noise" aria-hidden="true"></div>
    <div class="pointer-events-none fixed -top-40 left-1/2 h-[480px] w-[780px] -translate-x-1/2 animate-auth-orb-1 rounded-full bg-gradient-to-br from-velour-300 via-violet-300 to-fuchsia-200 opacity-60 auth-orb" aria-hidden="true"></div>
    <div class="pointer-events-none fixed bottom-[-80px] right-[-100px] h-[400px] w-[520px] animate-auth-orb-2 rounded-full bg-gradient-to-tr from-indigo-200 via-velour-100 to-purple-100 opacity-50 auth-orb" aria-hidden="true"></div>
    <div class="pointer-events-none fixed top-1/3 left-[-80px] h-[280px] w-[280px] animate-auth-shine rounded-full bg-violet-200/40 auth-orb blur-3xl" aria-hidden="true"></div>

    <div class="relative z-10 flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:px-6 sm:py-14">
        <div class="w-full mx-auto min-w-0 @yield('auth_container_class', 'max-w-[440px]')">
            <header class="mb-9 text-center sm:mb-11">
                <a href="{{ url('/') }}" class="auth-brand-link group inline-flex flex-col items-center gap-3 rounded-2xl outline-none ring-velour-500/30 focus-visible:ring-2 focus-visible:ring-offset-4 focus-visible:ring-offset-transparent">
                    <img src="{{ asset('images/easygrox-logo-light.png') }}" alt="EasyGrox" class="auth-logo-img h-16 w-auto sm:h-[4.5rem]">
                    <span class="block text-[13px] font-medium tracking-wide text-slate-500 dark:text-slate-400">Your business, one platform</span>
                </a>
            </header>

            <main class="overflow-hidden rounded-[1.35rem] bg-white/75 shadow-auth-card ring-1 ring-white/80 backdrop-blur-xl dark:bg-gray-900/90 dark:ring-gray-800/80 dark:shadow-none sm:rounded-3xl">
                <div class="h-1 w-full bg-gradient-to-r from-indigo-400 via-velour-500 to-fuchsia-500 opacity-95" aria-hidden="true"></div>
                <div class="px-6 py-7 sm:px-9 sm:py-9 md:px-10 md:py-10">
                    @if(session('success'))
                        <div class="mb-6 flex gap-3 rounded-2xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50/95 to-teal-50/50 px-4 py-3 text-sm text-emerald-900 shadow-sm dark:border-emerald-800/50 dark:from-emerald-950/40 dark:to-teal-950/30 dark:text-emerald-200">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif
                    @php
                        $authInlineErrorFields = ['email', 'password', 'name', 'password_confirmation', 'cf-turnstile-response', 'code', 'recovery_code'];
                        $authBannerErrors = collect($errors->getMessages())->except($authInlineErrorFields)->flatten();
                    @endphp
                    @if($authBannerErrors->isNotEmpty())
                        <div class="mb-6 space-y-1 rounded-2xl border border-red-200/80 bg-gradient-to-br from-red-50 to-rose-50/80 px-4 py-3 text-sm text-red-900 shadow-sm dark:border-red-900/50 dark:from-red-950/40 dark:to-rose-950/30 dark:text-red-200">
                            @foreach($authBannerErrors as $error)<p>{{ $error }}</p>@endforeach
                        </div>
                    @endif
                    @yield('content')
                </div>
            </main>

            <div class="mt-10 flex flex-col items-center gap-3">
                <div class="h-px w-16 bg-gradient-to-r from-transparent via-slate-300 to-transparent" aria-hidden="true"></div>
                <p class="text-center text-[11px] font-medium uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">
                    © {{ date('Y') }} EasyGrox · Encrypted session
                </p>
            </div>
        </div>
    </div>
@stack('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('theme', {
            dark: document.documentElement.classList.contains('dark'),
            toggle() {
                this.dark = !this.dark;
                if (this.dark) {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('velour-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('velour-theme', 'light');
                }
                document.querySelectorAll('.cf-turnstile').forEach((el) => {
                    el.setAttribute('data-theme', this.dark ? 'dark' : 'light');
                });
            }
        });
    });
</script>
@include('partials.form-client-validation')
@include('partials.disable-double-submit')
</body>
</html>
