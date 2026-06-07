# Getting started

## Requirements

- PHP 8.1+ (8.3 recommended)
- Composer 2.x
- Node.js 18+ (for Vite assets and `salon-website`)
- MySQL 8.0+ or MariaDB 10.6+
- Redis (optional; recommended for queues/cache in production)

## Repository layout

```
vellor/
├── app/                 # Application code (models, HTTP, services, jobs)
├── bootstrap/
├── config/
├── database/migrations/
├── docs/                # This handbook
├── public/              # Web root (index.php, built assets, website/)
├── resources/views/     # Blade templates
├── routes/web.php       # Web routes
├── routes/api.php       # API v1 routes
├── salon-website/       # React public storefront (Vite)
├── scripts/             # Maintenance scripts (e.g. code reference generator)
└── storage/
```

## Local setup (XAMPP)

1. Clone into `htdocs/vellor` (or your vhost path).
2. Copy environment file:
   ```bash
   cp .env.example .env
   ```
3. Set database in `.env`:
   ```env
   DB_DATABASE=vellor
   DB_USERNAME=root
   DB_PASSWORD=
   APP_URL=http://localhost/vellor/public
   ```
4. Install PHP dependencies and generate key:
   ```bash
   composer install
   php artisan key:generate
   ```
5. Run migrations:
   ```bash
   php artisan migrate
   ```
6. Seed (if seeders exist for your environment):
   ```bash
   php artisan db:seed
   ```
7. Storage link (avatars, logos, uploads):
   ```bash
   php artisan storage:link
   ```
8. Frontend assets (admin UI):
   ```bash
   npm install
   npm run dev
   ```
9. Public salon website (optional):
   ```bash
   cd salon-website
   npm install
   npm run dev
   ```
   Build for production: `npm run build` (outputs to `public/website/`).

## URLs

| URL | Purpose |
|-----|---------|
| `{APP_URL}/` | Redirects to dashboard when logged in |
| `{APP_URL}/login` | Salon staff login |
| `{APP_URL}/book/{slug}` | Legacy/public booking blade |
| `{APP_URL}/s/{slug}` | React marketing + booking site |
| `{APP_URL}/admin` | Super-admin (requires `super_admin`) |

## First login

- Create a user via register or tinker, attach to a salon as owner.
- Complete onboarding / profile if `profile.complete` middleware blocks access.
- Assign roles via Spatie (`tenant_admin`, `manager`, `receptionist`, `stylist`, etc.).

## Common Artisan commands

| Command | Purpose |
|---------|---------|
| `php artisan migrate` | Apply migrations |
| `php artisan queue:work` | Process jobs (reminders, marketing, SMS) |
| `php artisan schedule:run` | Cron entry (reminders, pruning, invoices) |
| `php artisan tenants:list` | List tenants (custom `TenantsCommand`) |
| `php artisan optimize:clear` | Clear caches after config changes |

## Testing

```bash
./vendor/bin/pest
```

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 500 on `/clients` after partial eager load | Include FK columns in `select()` lists; see `ClientHistory` |
| Avatars 404 on live | `php artisan storage:link`, correct `APP_URL`, sync `storage/app/public` |
| Wrong “today” in reports | Set salon `timezone`; use `SalonTime` |
| Tenant not found | Match host to `salons.domain` or subdomain + `APP_BASE_DOMAIN` |

See [02-architecture.md](02-architecture.md) for middleware and tenancy details.
