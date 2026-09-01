@php
    $easygroxBasePath = \App\Support\AppUrl::basePath();
    $easygroxCsrfCookiePath = parse_url(route('sanctum.csrf-cookie'), PHP_URL_PATH) ?: ($easygroxBasePath.'/sanctum/csrf-cookie');
    $easygroxCsrfTokenPath = \App\Support\AppUrl::path('csrf.token');
@endphp
<script>
    window.__EASYGROX__ = {
        basePath: @json($easygroxBasePath),
        csrfCookieUrl: @json($easygroxCsrfCookiePath),
        csrfTokenUrl: @json($easygroxCsrfTokenPath)
    };
</script>
<script src="{{ asset('js/easygrox-http.js') }}?v=1"></script>
