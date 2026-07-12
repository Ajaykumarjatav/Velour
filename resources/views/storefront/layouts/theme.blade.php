<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $data['salon']['name'] ?? $salon->name }} — Book Online</title>
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
        html { scroll-behavior: smooth; }
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
        (function () {
            if (window.location.hash === '#book') {
                document.body.classList.add('storefront-booking-active');
            }
            function syncBookingHash() {
                var open = window.location.hash === '#book';
                document.body.classList.toggle('storefront-booking-active', open);
                window.dispatchEvent(new CustomEvent('storefront-booking-toggle', { detail: { open: open } }));
            }
            window.addEventListener('hashchange', syncBookingHash);
            document.addEventListener('DOMContentLoaded', syncBookingHash);
            syncBookingHash();
        })();
    </script>
    @stack('scripts')
</body>
</html>
