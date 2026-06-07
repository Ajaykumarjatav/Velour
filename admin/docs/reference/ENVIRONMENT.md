# Environment variables

From `.env.example` and config files. Copy `.env.example` → `.env` for local setup.

## Application

| Variable | Purpose |
|----------|---------|
| `APP_NAME` | Application name |
| `APP_ENV` | `local`, `production`, etc. |
| `APP_KEY` | Encryption key (`php artisan key:generate`) |
| `APP_DEBUG` | Debug mode (false in production) |
| `APP_URL` | Base URL (e.g. `http://localhost/vellor/public`) |
| `APP_TIMEZONE` | Default app timezone (salon has own `timezone`) |
| `ASSET_URL` | Optional CDN/base for assets |

## Multitenancy / domains

| Variable | Purpose |
|----------|---------|
| `APP_BASE_DOMAIN` | Base domain for subdomain tenants (see `config/multitenancy.php`) |

## Subscriptions

| Variable | Purpose |
|----------|---------|
| `SUBSCRIPTIONS_ENABLED` | Enable Stripe billing UI and feature gates |

## Database

| Variable | Purpose |
|----------|---------|
| `DB_CONNECTION` | `mysql`, `sqlite`, etc. |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | MySQL connection |

## Session & cache

| Variable | Purpose |
|----------|---------|
| `SESSION_DRIVER` | Often `database` |
| `SESSION_LIFETIME` | Minutes |
| `CACHE_STORE` | `database`, `redis`, etc. |
| `QUEUE_CONNECTION` | `database`, `redis`, `sync` |

## Mail

| Variable | Purpose |
|----------|---------|
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, … | Outbound email |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Default sender |

## Stripe (Cashier + POS)

Configure in `config/services.php` / Cashier:

| Variable | Purpose |
|----------|---------|
| `STRIPE_KEY` | Publishable key |
| `STRIPE_SECRET` | Secret key |
| `STRIPE_WEBHOOK_SECRET` | Webhook signature |

## Twilio (SMS)

| Variable | Purpose |
|----------|---------|
| `TWILIO_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_FROM` | SMS marketing/notifications |

## Pusher (optional)

| Variable | Purpose |
|----------|---------|
| `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_CLUSTER` | Broadcasting |

## Salon website (React)

| Variable | Purpose |
|----------|---------|
| `SALON_WEBSITE_DEV_URL` | Dev preview URL for Go Live |
| `STOREFRONT_ASSET_BASE` | Asset base path for built SPA |
| `VITE_API_PROXY_TARGET` | In `salon-website/.env` — Laravel API for Vite dev proxy |

## Redis (optional)

`REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT` — queues/cache/Horizon.

## Production checklist

1. `APP_DEBUG=false`
2. `APP_URL` matches public URL
3. `php artisan storage:link`
4. `php artisan migrate --force`
5. `php artisan config:cache` / `route:cache` (optional)
6. Queue worker + cron for `schedule:run`
7. Sync `storage/app/public` for uploads
