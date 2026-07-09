# Public website (Blade)

## Purpose

Marketing storefront per salon: hero, services, staff, testimonials, packages, and embedded **booking flow** (`#book`).

## Architecture

```
Browser → GET /s/{slug} → StorefrontController
                ↓
         SalonWebsitePayloadService builds payload
                ↓
         Blade theme (storefront.themes.{slug}.show)
                ↓
         Alpine.js booking overlay (#book) → /api/v1/book/{slug}/*
```

Legacy React SPA remains available with `STOREFRONT_ENGINE=react` in `.env`.

## Key files

| Path | Role |
|------|------|
| `resources/views/storefront/layouts/theme.blade.php` | HTML shell, theme CSS, Alpine |
| `resources/views/storefront/themes/{slug}/show.blade.php` | Theme composer |
| `resources/views/storefront/partials/booking-flow.blade.php` | Alpine booking (same APIs as React) |
| `resources/views/storefront/partials/dynamic/*.blade.php` | Shared data-driven sections |
| `app/Support/StorefrontAssets.php` | CSS URL, asset URLs, theme tokens |
| `config/storefront-themes.php` | Per-theme tokens and asset filenames |
| `app/Services/SalonWebsitePayloadService.php` | Payload builder |
| `app/Http/Controllers/Web/StorefrontController.php` | Serves Blade (or legacy React) |
| `scripts/sync-storefront-assets.php` | Copy theme CSS/images to `public/storefront/` |

## Build and deploy

```bash
# Sync theme CSS + static images (run after theme CSS changes)
php scripts/sync-storefront-assets.php

# Verify all 7 themes
php deploy/verify-storefront.php
```

Set `STOREFRONT_ENGINE=blade` (default). Set `APP_URL` correctly so asset URLs resolve.

To refresh theme CSS from Vite (only when salon-website styles change):

```bash
cd salon-website && npm run build:all
php scripts/sync-storefront-assets.php
```

## SEO

`website-seo.*` routes — publish meta tags / settings stored on salon.

## Customization

`CustomizationController` — branding/forms for public-facing content (tenant admin).

## Staff images on website

Payload uses `Staff::resolvePublicAvatarUrl()` — same rules as admin UI; sync `storage/app/public` on server.
