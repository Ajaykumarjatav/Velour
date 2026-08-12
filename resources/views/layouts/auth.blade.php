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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v=9">
    {{-- Tailwind optional: extras on register / other auth pages; core login works without it --}}
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
                            50: '#f5f3ff', 100: '#ede9fe', 200: '#ddd6fe', 300: '#c4b5fd',
                            400: '#a78bfa', 500: '#8b5cf6', 600: '#7c3aed', 700: '#6d28d9',
                            800: '#5b21b6', 900: '#4c1d95',
                        },
                    },
                },
            },
        };
    </script>
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
<body class="auth-body auth-bg-base" x-data>
    <button type="button" class="auth-theme-btn" @click="$store.theme.toggle()" title="Toggle theme" aria-label="Toggle theme">
        <svg x-show="$store.theme.dark" x-cloak fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        <svg x-show="!$store.theme.dark" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
    </button>

    <div class="auth-glow" aria-hidden="true"></div>
    <div class="auth-noise" aria-hidden="true"></div>

    <div class="auth-shell">
        <div class="auth-container auth-card-enter @yield('auth_container_class', '')">
            <header class="auth-brand">
                <a href="{{ url('/') }}" class="auth-brand-link">
                    <img src="{{ asset('images/easygrox-logo-light.png') }}" alt="EasyGrox" class="auth-logo-img">
                    <span class="auth-tagline">Your business, one platform</span>
                </a>
            </header>

            <main class="auth-panel">
                <div class="auth-panel-accent" aria-hidden="true"></div>
                <div class="auth-panel-body">
                    @if(session('success'))
                        <div class="auth-alert auth-alert--ok">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="auth-alert auth-alert--warn">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="auth-alert auth-alert--warn">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            <div>{{ session('warning') }}</div>
                        </div>
                    @endif
                    @if(session('info'))
                        <div class="auth-alert auth-alert--info">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
                            <div>{{ session('info') }}</div>
                        </div>
                    @endif
                    @php
                        $authInlineErrorFields = ['email', 'password', 'name', 'password_confirmation', 'cf-turnstile-response', 'code', 'recovery_code'];
                        $authBannerErrors = collect($errors->getMessages())->except($authInlineErrorFields)->flatten();
                    @endphp
                    @if($authBannerErrors->isNotEmpty())
                        <div class="auth-alert auth-alert--err">
                            @foreach($authBannerErrors as $error)<p>{{ $error }}</p>@endforeach
                        </div>
                    @endif
                    @yield('content')
                </div>
            </main>

            <div class="auth-meta">
                <div class="auth-meta-rule" aria-hidden="true"></div>
                <p class="auth-meta-copy">© {{ date('Y') }} EasyGrox · Encrypted session</p>
                <p class="auth-meta-credit">
                    Developed and managed by
                    <a href="https://samarthinfoservices.com/" target="_blank" rel="noopener noreferrer" class="auth-meta-badge">SamarthInfo Services</a>
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
                window.dispatchEvent(new CustomEvent('velour-theme-change', { detail: { dark: this.dark } }));
            }
        });
    });
</script>
@include('partials.form-client-validation')
@include('partials.disable-double-submit')
</body>
</html>
