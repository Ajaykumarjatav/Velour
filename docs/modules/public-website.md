# Public website (React)

## Purpose

Marketing storefront per salon: hero, services, staff, testimonials, packages, and embedded **booking flow** (`#book`).

## Architecture

```
Browser → GET /s/{slug} → StorefrontController → public/website/index.html
                ↓
         SalonContext fetches GET /api/v1/salon/{slug}/website
                ↓
         SalonWebsitePayloadService builds JSON
                ↓
         React sections OR BookingFlow (#book)
```

## Key files

| Path | Role |
|------|------|
| `salon-website/src/App.jsx` | Root layout |
| `salon-website/src/context/SalonContext.jsx` | Data loading |
| `salon-website/src/lib/api.js` | Website API |
| `salon-website/src/lib/bookingApi.js` | Booking API |
| `app/Http/Controllers/Api/SalonWebsiteController.php` | API endpoint |
| `app/Services/SalonWebsitePayloadService.php` | Payload builder |
| `app/Http/Controllers/Web/StorefrontController.php` | Serves SPA |
| `app/Http/Controllers/Web/WebsiteSeoController.php` | SEO meta publish |

## Build and deploy

```bash
cd salon-website
npm install
npm run build   # output: ../public/website/
```

Set `APP_URL` correctly so asset URLs resolve. API base in production points to Laravel `/api/v1`.

## SEO

`website-seo.*` routes — publish meta tags / settings stored on salon.

## Customization

`CustomizationController` — branding/forms for public-facing content (tenant admin).

## Staff images on website

Payload uses `Staff::resolvePublicAvatarUrl()` — same rules as admin UI; sync `storage/app/public` on server.

## Local dev

```bash
cd salon-website && npm run dev
```

Vite proxy: `VITE_API_PROXY_TARGET` → Laravel public URL.
