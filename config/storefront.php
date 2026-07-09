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
    | Theme static asset route base (public/storefront/{theme}/assets/)
    |--------------------------------------------------------------------------
    */
    'asset_base' => env('STOREFRONT_ASSET_BASE', '/website/'),
];
