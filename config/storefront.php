<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storefront rendering — Blade only (React SPA removed from production)
    |--------------------------------------------------------------------------
    */
    'engine' => 'blade',

    /*
    |--------------------------------------------------------------------------
    | Local Vite preview (optional)
    |--------------------------------------------------------------------------
    | Only used when STOREFRONT_USE_VITE_PREVIEW=true and Vite is running.
    | Otherwise Go Live / Website preview use Laravel: /vellor/s/{slug}.
    */
    'dev_url' => env('SALON_WEBSITE_DEV_URL'),
    'use_vite_preview' => (bool) env('STOREFRONT_USE_VITE_PREVIEW', false),

    /*
    |--------------------------------------------------------------------------
    | Theme static asset route base (public/storefront/{theme}/assets/)
    |--------------------------------------------------------------------------
    */
    'asset_base' => env('STOREFRONT_ASSET_BASE', '/website/'),
];
