@php
    $legalSlug = $salon->slug ?? ($data['salon']['slug'] ?? null);
    $privacyHref = $legalSlug
        ? \App\Support\AppUrl::path('storefront.privacy', ['slug' => $legalSlug])
        : '#';
    $termsHref = $legalSlug
        ? \App\Support\AppUrl::path('storefront.terms', ['slug' => $legalSlug])
        : '#';
@endphp
<a href="{{ $privacyHref }}" class="hover:text-white transition-colors duration-200">Privacy Policy</a>
<a href="{{ $termsHref }}" class="hover:text-white transition-colors duration-200">Terms & Conditions</a>
