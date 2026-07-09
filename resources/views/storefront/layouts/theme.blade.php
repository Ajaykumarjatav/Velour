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
    </style>
    @stack('head')
</head>
<body class="antialiased bg-white text-black">
    @yield('content')

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        (function () {
            function syncBookingHash() {
                var open = window.location.hash === '#book';
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
