<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $data['salon']['name'] ?? $salon->name }} — Book Online</title>
    @include('partials.easygrox-http')
    @if(!empty($data['salon']['description']))
    <meta name="description" content="{{ $data['salon']['description'] }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&family=Pacifico&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ \App\Support\StorefrontAssets::cssUrl($theme) }}">
    <style>
        :root {
            {!! \App\Support\StorefrontAssets::cssVariables($theme) !!}
        }
        [x-cloak] { display: none !important; }
        /* scroll-padding keeps section anchors clear of the nav once it goes fixed. */
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 5rem;
        }
        body {
            overflow-x: hidden;
        }
        .storefront-booking-overlay {
            position: fixed !important;
            inset: 0 !important;
            z-index: 99999 !important;
            background: #000 !important;
            color: #fff;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        body.storefront-booking-active {
            overflow: hidden !important;
        }
        body.storefront-booking-active .storefront-site-content {
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* Theme stylesheets are pre-compiled, so responsive corrections live here. */
        @supports (height: 100dvh) {
            .storefront-booking-overlay .min-h-screen { min-height: 100dvh; }
        }

        .sf-logo { max-width: 100%; }

        .sf-hero-title { line-height: 1.1; }

        @media (max-width: 1023px) {
            .sf-hero-title { font-size: clamp(2.25rem, 7.5vw, 3.5rem); }
        }

        @media (max-width: 639px) {
            .sf-hero-pill { padding: 0.625rem 1rem; }
            .sf-logo { min-width: 0; }
        }

        /* Opening-hours strings only fit on one line once the top bar is wide. */
        @media (max-width: 1023px) {
            .sf-hours { justify-content: center; }
            .sf-hours span { white-space: normal; text-align: center; }
        }

        /* Landscape phones: the hero must not reserve more height than the screen has. */
        @media (max-height: 520px) and (max-width: 1023px) {
            .sf-hero { min-height: 0; }
        }

        @media (min-width: 640px) and (max-width: 767px) {
            .sf-hero-perks { gap: 1.5rem; }
        }

        @media (max-width: 419px) {
            .sf-slot-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        /* Keeps the mobile section menu reachable when the nav is pinned on short screens. */
        .sf-sticky-nav {
            max-height: 100dvh;
            overflow-y: auto;
            overscroll-behavior: contain;
        }
    </style>
    @stack('head')
</head>
<body class="antialiased bg-white text-black">
    <div class="storefront-site-content">
        @yield('content')
    </div>
    @isset($salon)
        @include('storefront.partials.booking-flow')
    @endisset

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        window.__STOREFRONT_BOOKING_ENABLED__ = @json((bool) ($data['salon']['online_booking_enabled'] ?? $salon->online_booking_enabled ?? false));
        (function () {
            function syncBookingHash() {
                var open = window.location.hash === '#book';
                document.body.classList.toggle('storefront-booking-active', open);
                window.dispatchEvent(new CustomEvent('storefront-booking-toggle', { detail: { open: open } }));
            }
            if (window.location.hash === '#book') {
                document.body.classList.add('storefront-booking-active');
            }
            window.addEventListener('hashchange', syncBookingHash);
            document.addEventListener('DOMContentLoaded', syncBookingHash);
            syncBookingHash();
        })();

        // The section nav switches to fixed positioning on scroll; a spacer keeps the
        // page from jumping by the nav height when that happens.
        (function () {
            function initNavSpacer() {
                var nav = document.querySelector('.sf-sticky-nav');
                if (!nav || !nav.parentNode) return;

                var spacer = document.createElement('div');
                spacer.setAttribute('aria-hidden', 'true');
                spacer.style.display = 'none';
                nav.parentNode.insertBefore(spacer, nav.nextSibling);

                var flowHeight = nav.offsetHeight;

                function sync() {
                    if (window.getComputedStyle(nav).position === 'fixed') {
                        spacer.style.height = flowHeight + 'px';
                        spacer.style.display = 'block';
                    } else {
                        flowHeight = nav.offsetHeight;
                        spacer.style.display = 'none';
                    }
                }

                window.addEventListener('scroll', sync, { passive: true });
                window.addEventListener('resize', sync);
                sync();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initNavSpacer);
            } else {
                initNavSpacer();
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
