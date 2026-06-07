<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Salon website (React) — local dev preview
    |--------------------------------------------------------------------------
    | When set (e.g. http://localhost:5173), Go Live "Preview" opens the Vite
    | dev server. Run: cd salon-website && npm run dev
    */
    'dev_url' => env('SALON_WEBSITE_DEV_URL'),

    /*
    |--------------------------------------------------------------------------
    | Built asset base path (Vite `base` — must match salon-website build)
    |--------------------------------------------------------------------------
    */
    'asset_base' => env('STOREFRONT_ASSET_BASE', '/website/'),
];
