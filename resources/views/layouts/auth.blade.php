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
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v=3">
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
    <canvas id="auth-network" aria-hidden="true"></canvas>
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
<script>
(function () {
    var canvas = document.getElementById('auth-network');
    if (!canvas) return;

    var ctx = canvas.getContext('2d');
    if (!ctx) return;

    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var dpr = 1;
    var w = 0;
    var h = 0;
    var particles = [];
    var mouse = { x: null, y: null, active: false };
    var raf = 0;
    var dark = document.documentElement.classList.contains('dark');

    function theme() {
        if (dark) {
            return {
                node: 'rgba(196, 181, 253, 0.85)',
                nodeCore: 'rgba(255, 255, 255, 0.55)',
                line: [167, 139, 250],
                lineMax: 0.38,
                linkDist: 140,
            };
        }
        return {
            node: 'rgba(124, 58, 237, 0.55)',
            nodeCore: 'rgba(124, 58, 237, 0.2)',
            line: [109, 40, 217],
            lineMax: 0.22,
            linkDist: 130,
        };
    }

    function countForSize() {
        var area = (window.innerWidth * window.innerHeight) / 14000;
        return Math.max(28, Math.min(70, Math.floor(area)));
    }

    function spawn() {
        var n = countForSize();
        particles = [];
        for (var i = 0; i < n; i++) {
            particles.push({
                x: Math.random() * w,
                y: Math.random() * h,
                vx: (Math.random() - 0.5) * 0.35,
                vy: (Math.random() - 0.5) * 0.35,
                r: 1.2 + Math.random() * 1.6,
            });
        }
    }

    function resize() {
        dpr = Math.min(window.devicePixelRatio || 1, 1.75);
        w = window.innerWidth;
        h = window.innerHeight;
        canvas.width = Math.max(1, Math.floor(w * dpr));
        canvas.height = Math.max(1, Math.floor(h * dpr));
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        if (!particles.length || Math.abs(particles.length - countForSize()) > 12) {
            spawn();
        }
    }

    function drawStatic() {
        var t = theme();
        ctx.clearRect(0, 0, w, h);
        for (var i = 0; i < particles.length; i++) {
            var a = particles[i];
            for (var j = i + 1; j < particles.length; j++) {
                var b = particles[j];
                var dx = a.x - b.x;
                var dy = a.y - b.y;
                var dist = Math.sqrt(dx * dx + dy * dy);
                if (dist > t.linkDist) continue;
                var alpha = (1 - dist / t.linkDist) * t.lineMax;
                ctx.strokeStyle = 'rgba(' + t.line[0] + ',' + t.line[1] + ',' + t.line[2] + ',' + alpha + ')';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(a.x, a.y);
                ctx.lineTo(b.x, b.y);
                ctx.stroke();
            }
            ctx.beginPath();
            ctx.fillStyle = t.node;
            ctx.arc(a.x, a.y, a.r, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function frame() {
        var t = theme();
        ctx.clearRect(0, 0, w, h);

        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];
            p.x += p.vx;
            p.y += p.vy;

            if (p.x < -20) p.x = w + 20;
            if (p.x > w + 20) p.x = -20;
            if (p.y < -20) p.y = h + 20;
            if (p.y > h + 20) p.y = -20;

            if (mouse.active) {
                var mdx = mouse.x - p.x;
                var mdy = mouse.y - p.y;
                var md = Math.sqrt(mdx * mdx + mdy * mdy);
                if (md < 160 && md > 1) {
                    p.vx += (mdx / md) * 0.008;
                    p.vy += (mdy / md) * 0.008;
                }
            }

            var speed = Math.sqrt(p.vx * p.vx + p.vy * p.vy);
            if (speed > 0.55) {
                p.vx *= 0.96;
                p.vy *= 0.96;
            }
        }

        for (var i = 0; i < particles.length; i++) {
            var a = particles[i];
            for (var j = i + 1; j < particles.length; j++) {
                var b = particles[j];
                var dx = a.x - b.x;
                var dy = a.y - b.y;
                var dist = Math.sqrt(dx * dx + dy * dy);
                if (dist > t.linkDist) continue;
                var alpha = (1 - dist / t.linkDist) * t.lineMax;
                if (mouse.active) {
                    var mx = (a.x + b.x) / 2;
                    var my = (a.y + b.y) / 2;
                    var mdist = Math.sqrt((mouse.x - mx) * (mouse.x - mx) + (mouse.y - my) * (mouse.y - my));
                    if (mdist < 180) alpha = Math.min(t.lineMax + 0.2, alpha + 0.12);
                }
                ctx.strokeStyle = 'rgba(' + t.line[0] + ',' + t.line[1] + ',' + t.line[2] + ',' + alpha + ')';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(a.x, a.y);
                ctx.lineTo(b.x, b.y);
                ctx.stroke();
            }
        }

        for (var k = 0; k < particles.length; k++) {
            var n = particles[k];
            ctx.beginPath();
            ctx.fillStyle = t.node;
            ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
            ctx.fill();
            ctx.beginPath();
            ctx.fillStyle = t.nodeCore;
            ctx.arc(n.x, n.y, Math.max(0.6, n.r * 0.35), 0, Math.PI * 2);
            ctx.fill();
        }

        raf = requestAnimationFrame(frame);
    }

    function setDark(isDark) {
        dark = !!isDark;
        if (reduced) drawStatic();
    }

    window.addEventListener('resize', function () {
        resize();
        if (reduced) drawStatic();
    }, { passive: true });

    window.addEventListener('pointermove', function (e) {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
        mouse.active = true;
    }, { passive: true });

    window.addEventListener('pointerleave', function () {
        mouse.active = false;
    });

    window.addEventListener('velour-theme-change', function (e) {
        setDark(!!(e.detail && e.detail.dark));
    });

    new MutationObserver(function () {
        setDark(document.documentElement.classList.contains('dark'));
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    document.addEventListener('visibilitychange', function () {
        if (reduced) return;
        if (document.hidden) {
            cancelAnimationFrame(raf);
            raf = 0;
        } else if (!raf) {
            raf = requestAnimationFrame(frame);
        }
    });

    resize();
    if (reduced) {
        drawStatic();
    } else {
        raf = requestAnimationFrame(frame);
    }
})();
</script>
@include('partials.form-client-validation')
@include('partials.disable-double-submit')
</body>
</html>
