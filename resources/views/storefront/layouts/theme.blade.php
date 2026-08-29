<!DOCTYPE html>
<html lang="en" class="css-pending">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ($data['salon']['name'] ?? $salon->name).' — Book Online')</title>
    @include('partials.favicon')
    @include('partials.prevent-fouc-start')
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
        /* Theme CSS does not always zero the browser default body margin — that
           shows up as a thin white strip around the brown top bar. */
        html, body {
            margin: 0;
            padding: 0;
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

        .sf-booking-shell { min-height: 100dvh; display: flex; flex-direction: column; }
        .sf-booking-container { max-width: 1280px; margin: 0 auto; width: 100%; padding-left: 1rem; padding-right: 1rem; }
        .sf-booking-header { padding: 0.75rem 0 0.5rem; }
        .sf-booking-brand-name { font-family: Manrope, sans-serif; font-weight: 700; font-size: 1.125rem; line-height: 1.2; color: #fff; }
        .sf-booking-brand-tag { font-size: 0.625rem; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.45); margin-top: 0.125rem; }
        .sf-booking-back-btn {
            display: inline-flex; align-items: center; gap: 0.25rem;
            font-size: 0.8125rem; font-weight: 500; color: rgba(255,255,255,0.8);
            padding: 0.375rem 0.5rem; margin-left: -0.5rem; border-radius: 0.5rem;
            transition: color 0.15s, background 0.15s;
        }
        .sf-booking-back-btn:hover { color: #fff; background: rgba(255,255,255,0.06); }
        .sf-booking-close-btn {
            font-size: 0.6875rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;
            color: rgba(255,255,255,0.45); padding: 0.375rem 0.5rem; border-radius: 0.5rem;
        }
        .sf-booking-close-btn:hover { color: #fff; background: rgba(255,255,255,0.06); }

        /* Progress stepper */
        .sf-stepper {
            display: flex; align-items: center; gap: 0; overflow-x: auto;
            padding: 0.75rem 0 0.5rem; scrollbar-width: none;
        }
        .sf-stepper::-webkit-scrollbar { display: none; }
        .sf-stepper-item {
            display: flex; align-items: center; gap: 0.375rem; flex-shrink: 0;
            color: rgba(255,255,255,0.35);
        }
        .sf-stepper-item.is-active { color: #fff; }
        .sf-stepper-item.is-done { color: rgba(255,255,255,0.75); }
        .sf-stepper-dot {
            width: 1.375rem; height: 1.375rem; border-radius: 9999px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.625rem; font-weight: 700; border: 1.5px solid rgba(255,255,255,0.25);
            background: transparent; flex-shrink: 0;
        }
        .sf-stepper-item.is-active .sf-stepper-dot {
            border-color: var(--color-primary, #e11d48); background: var(--color-primary, #e11d48); color: #fff;
        }
        .sf-stepper-item.is-done .sf-stepper-dot {
            border-color: rgba(255,255,255,0.35); background: rgba(255,255,255,0.12); color: #fff;
        }
        .sf-stepper-label { font-size: 0.6875rem; font-weight: 600; white-space: nowrap; }
        @media (max-width: 639px) { .sf-stepper-label { display: none; } .sf-stepper-item.is-active .sf-stepper-label { display: inline; } }
        .sf-stepper-line {
            flex: 1; min-width: 1rem; max-width: 2.5rem; height: 1.5px;
            background: rgba(255,255,255,0.15); margin: 0 0.375rem; flex-shrink: 0;
        }
        .sf-stepper-line.is-done { background: rgba(255,255,255,0.35); }

        .sf-booking-main { flex: 1; width: 100%; padding: 1.25rem 0 5rem; }
        @media (min-width: 640px) { .sf-booking-main { padding-top: 1.5rem; } }
        @media (min-width: 1024px) { .sf-booking-main { padding-bottom: 2rem; } }
        .sf-booking-layout {
            display: grid; grid-template-columns: 1fr; gap: 1.25rem; align-items: start;
        }
        @media (min-width: 1024px) {
            .sf-booking-layout { grid-template-columns: minmax(0, 1fr) 340px; gap: 1.5rem; }
        }
        @media (min-width: 1280px) {
            .sf-booking-layout { grid-template-columns: minmax(0, 1fr) 380px; }
        }
        .sf-booking-primary { min-width: 0; }

        .sf-booking-card {
            border-radius: 1rem; border: 1px solid rgba(255,255,255,0.1);
            background: #1a1f2e; overflow: hidden; width: 100%;
        }
        .sf-booking-card-body { padding: 1rem 1.25rem; }
        @media (min-width: 640px) { .sf-booking-card-body { padding: 1.25rem 1.5rem; } }
        .sf-booking-section-title {
            font-family: Manrope, sans-serif; font-weight: 700; font-size: 1.125rem; color: #fff; margin-bottom: 0.875rem;
        }

        .sf-search-wrap {
            display: flex; align-items: center; gap: 0.625rem;
            border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.15);
            background: rgba(0,0,0,0.25); padding: 0.625rem 0.875rem;
        }
        .sf-search-wrap:focus-within { border-color: rgba(var(--color-primary-rgb, 225,29,72), 0.5); box-shadow: 0 0 0 2px rgba(225,29,72,0.15); }
        .sf-search-input {
            min-width: 0; flex: 1; background: transparent; border: 0; padding: 0;
            font-size: 0.875rem; color: #fff; outline: none;
        }
        .sf-search-input::placeholder { color: rgba(255,255,255,0.4); }
        .sf-popular-row { display: flex; flex-wrap: wrap; align-items: center; gap: 0.375rem; margin-top: 0.625rem; }
        .sf-popular-label { font-size: 0.6875rem; color: rgba(255,255,255,0.4); margin-right: 0.25rem; }
        .sf-popular-chip {
            font-size: 0.6875rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 9999px;
            border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.04);
            color: rgba(255,255,255,0.75); transition: border-color 0.15s, background 0.15s;
        }
        .sf-popular-chip:hover { border-color: rgba(255,255,255,0.25); background: rgba(255,255,255,0.08); color: #fff; }

        .sf-services-scroll {
            overflow-y: auto; -webkit-overflow-scrolling: touch;
            max-height: min(56dvh, 28rem); margin-top: 0.875rem;
        }
        @media (min-width: 1024px) { .sf-services-scroll { max-height: min(62dvh, 32rem); } }

        .sf-cat-block { border-radius: 0.625rem; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02); overflow: hidden; }
        .sf-cat-toggle {
            width: 100%; display: flex; align-items: center; gap: 0.625rem;
            padding: 0.625rem 0.75rem; text-align: left;
            transition: background 0.15s;
        }
        .sf-cat-toggle:hover { background: rgba(255,255,255,0.04); }
        .sf-cat-icon { font-size: 0.875rem; opacity: 0.7; flex-shrink: 0; width: 1.25rem; text-align: center; }
        .sf-cat-title { font-size: 0.875rem; font-weight: 600; color: #fff; line-height: 1.2; }
        .sf-cat-sub { font-size: 0.6875rem; color: rgba(255,255,255,0.42); margin-top: 0.0625rem; }
        .sf-cat-count { font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.45); font-variant-numeric: tabular-nums; flex-shrink: 0; }
        .sf-cat-chevron { width: 1rem; height: 1rem; color: rgba(255,255,255,0.45); flex-shrink: 0; transition: transform 0.2s; }
        .sf-cat-chevron.is-open { transform: rotate(180deg); }

        .sf-svc-row {
            display: flex; align-items: center; gap: 0.625rem;
            padding: 0.625rem 0.75rem; cursor: pointer; border-top: 1px solid rgba(255,255,255,0.06);
            transition: background 0.15s;
        }
        .sf-svc-row:hover { background: rgba(255,255,255,0.04); }
        .sf-svc-row.is-selected {
            background: rgba(225,29,72,0.1);
            box-shadow: inset 3px 0 0 var(--color-primary, #e11d48);
            outline: 1px solid rgba(225,29,72,0.22);
            outline-offset: -1px;
        }
        .sf-svc-check {
            width: 1.125rem; height: 1.125rem; border-radius: 0.25rem; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border: 1.5px solid rgba(255,255,255,0.25); font-size: 0.625rem; color: transparent;
        }
        .sf-svc-row.is-selected .sf-svc-check {
            border-color: var(--color-primary, #e11d48); background: var(--color-primary, #e11d48); color: #fff;
        }
        .sf-svc-name { font-size: 0.8125rem; font-weight: 600; color: #fff; line-height: 1.25; }
        .sf-svc-meta { font-size: 0.6875rem; color: rgba(255,255,255,0.45); margin-top: 0.0625rem; }
        .sf-svc-price { font-size: 0.8125rem; font-weight: 600; color: #fff; flex-shrink: 0; margin-left: auto; }

        /* Summary sidebar — desktop only */
        .sf-booking-summary { display: none; }
        @media (min-width: 1024px) {
            .sf-booking-summary { display: block; position: sticky; top: 5.5rem; }
        }
        .sf-booking-summary-inner {
            border-radius: 1rem; border: 1px solid rgba(255,255,255,0.1);
            background: #141824; padding: 1.25rem;
        }
        .sf-booking-summary-title {
            font-family: Manrope, sans-serif; font-size: 0.6875rem; font-weight: 700;
            letter-spacing: 0.14em; text-transform: uppercase; color: rgba(255,255,255,0.45); margin-bottom: 1rem;
        }
        .sf-booking-summary-empty { font-size: 0.8125rem; color: rgba(255,255,255,0.4); line-height: 1.5; }
        .sf-booking-summary-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.625rem; }
        .sf-booking-summary-item { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
        .sf-booking-summary-item-main { display: flex; gap: 0.5rem; min-width: 0; }
        .sf-booking-summary-check {
            width: 1rem; height: 1rem; border-radius: 9999px; flex-shrink: 0; margin-top: 0.125rem;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.5rem; font-weight: 700; background: rgba(225,29,72,0.2); color: var(--color-primary, #e11d48);
        }
        .sf-booking-summary-item-name { font-size: 0.8125rem; font-weight: 600; color: #fff; line-height: 1.3; }
        .sf-booking-summary-item-meta { font-size: 0.6875rem; color: rgba(255,255,255,0.45); margin-top: 0.0625rem; }
        .sf-booking-summary-item-price { font-size: 0.8125rem; font-weight: 600; color: #fff; flex-shrink: 0; }
        .sf-booking-summary-extra { margin-top: 0.875rem; padding-top: 0.875rem; border-top: 1px solid rgba(255,255,255,0.08); }
        .sf-booking-summary-extra-label { font-size: 0.625rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.38); }
        .sf-booking-summary-extra-value { font-size: 0.8125rem; font-weight: 600; color: #fff; margin-top: 0.125rem; }
        .sf-booking-summary-total {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.12);
            font-size: 0.875rem; color: rgba(255,255,255,0.55);
        }
        .sf-booking-summary-total span:last-child { font-size: 1.125rem; color: #fff; }
        .sf-booking-summary-cta {
            width: 100%; margin-top: 1rem; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.75rem 1.25rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 700;
            background: var(--color-primary, #e11d48); color: #fff;
            box-shadow: 0 4px 14px rgba(225,29,72,0.35); transition: filter 0.15s;
        }
        .sf-booking-summary-cta:hover { filter: brightness(1.08); }
        .sf-booking-summary-cta:disabled { opacity: 0.55; cursor: not-allowed; }
        .sf-booking-summary-hint { font-size: 0.75rem; color: rgba(255,255,255,0.4); margin-top: 0.75rem; text-align: center; }

        /* Mobile sticky bar */
        .sf-booking-mobile-bar {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 60;
            background: rgba(0,0,0,0.92); backdrop-filter: blur(12px);
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 0.75rem 1rem calc(0.75rem + env(safe-area-inset-bottom));
        }
        .sf-booking-mobile-bar-inner { max-width: 1280px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; }
        .sf-booking-mobile-cta {
            display: inline-flex; align-items: center; gap: 0.375rem; flex-shrink: 0;
            padding: 0.625rem 1.125rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 700;
            background: var(--color-primary, #e11d48); color: #fff;
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
                var open = window.location.hash === '#book' || window.location.hash.indexOf('#book') === 0;
                document.body.classList.toggle('storefront-booking-active', open);
                window.dispatchEvent(new CustomEvent('storefront-booking-toggle', { detail: { open: open } }));
            }
            if (window.location.hash === '#book' || window.location.hash.indexOf('#book') === 0) {
                document.body.classList.add('storefront-booking-active');
            }
            window.addEventListener('hashchange', syncBookingHash);
            document.addEventListener('DOMContentLoaded', syncBookingHash);
            syncBookingHash();
            window.storefrontOpenBooking = function (opts) {
                opts = opts || {};
                window.dispatchEvent(new CustomEvent('storefront-book-preselect', { detail: opts }));
                if (window.location.hash !== '#book') {
                    window.location.hash = 'book';
                } else {
                    window.dispatchEvent(new CustomEvent('storefront-booking-toggle', { detail: { open: true } }));
                }
            };
            window.storefrontHomeCart = (function () {
                var serviceIds = {};
                var packageIds = {};
                function snapshot() {
                    return {
                        serviceIds: Object.keys(serviceIds).map(Number),
                        packageIds: Object.keys(packageIds).map(Number),
                    };
                }
                function emit() {
                    var snap = snapshot();
                    window.dispatchEvent(new CustomEvent('storefront-home-cart-change', { detail: snap }));
                    document.querySelectorAll('[data-home-package]').forEach(function (el) {
                        var on = !!packageIds[String(el.getAttribute('data-home-package'))];
                        el.classList.toggle('ring-2', on);
                        el.classList.toggle('ring-primary', on);
                        el.classList.toggle('border-primary', on);
                        el.classList.toggle('border-gray-100', !on);
                        var label = el.querySelector('[data-home-package-label]');
                        if (label) {
                            label.textContent = on ? 'Selected' : 'Select';
                            label.classList.toggle('bg-primary', on);
                            label.classList.toggle('text-white', on);
                            label.classList.toggle('bg-[#FFEFEF]', !on);
                            label.classList.toggle('text-primary', !on);
                        }
                    });
                }
                return {
                    toggleService: function (id) {
                        id = String(id);
                        if (serviceIds[id]) delete serviceIds[id]; else serviceIds[id] = true;
                        emit();
                    },
                    togglePackage: function (id) {
                        id = String(id);
                        if (packageIds[id]) delete packageIds[id]; else packageIds[id] = true;
                        emit();
                    },
                    hasService: function (id) { return !!serviceIds[String(id)]; },
                    snapshot: snapshot,
                    bookSelected: function () {
                        var s = snapshot();
                        if (s.serviceIds.length || s.packageIds.length) {
                            window.storefrontOpenBooking(s);
                            return;
                        }
                        window.location.hash = 'book';
                    },
                };
            })();
        })();

        // The section nav switches to fixed positioning on scroll; a spacer keeps the
        // page from jumping by the nav height when that happens.
        (function () {
            function initNavSpacer() {
                var nav = document.querySelector('.sf-sticky-nav');
                if (!nav || !nav.parentNode || nav.hasAttribute('data-overlay-nav')) return;

                var spacer = document.createElement('div');
                spacer.setAttribute('aria-hidden', 'true');
                spacer.className = 'sf-sticky-nav-spacer';
                spacer.style.display = 'none';
                nav.parentNode.insertBefore(spacer, nav.nextSibling);

                var flowHeight = nav.offsetHeight;

                function spacerFill() {
                    var bg = window.getComputedStyle(nav).backgroundColor;
                    var m = bg && bg.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
                    if (m && (+m[1] + +m[2] + +m[3]) < 90) {
                        spacer.style.backgroundColor = 'rgb(' + m[1] + ',' + m[2] + ',' + m[3] + ')';
                    } else {
                        spacer.style.backgroundColor = bg || 'transparent';
                    }
                }

                function sync() {
                    if (window.getComputedStyle(nav).position === 'fixed') {
                        spacer.style.height = flowHeight + 'px';
                        spacer.style.display = 'block';
                        spacerFill();
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
    @include('partials.prevent-fouc-end')
</body>
</html>
