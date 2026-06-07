# Velour (Vellor) — Developer Documentation

Complete technical documentation for the **Velour salon SaaS** codebase (`c:\xampp\htdocs\vellor`). Use this handbook to onboard, debug, and extend the product **without relying on AI**.

---

## What this project is

| Layer | Technology |
|-------|------------|
| Backend | Laravel 11, PHP 8.1+ |
| Database | MySQL 8 / MariaDB (single DB, `salon_id` tenant isolation) |
| Auth | Session (web), Sanctum (API), Spatie Permission, optional 2FA |
| Billing | Laravel Cashier + Stripe |
| Multitenancy | Spatie Laravel Multitenancy (`Tenant` = salon) |
| Frontend (app) | Blade + Tailwind + Alpine.js |
| Public website | React 19 + Vite (`salon-website/`) → `public/website/` |
| Queues | Laravel queues (tenant-aware) |

---

## Documentation map

| Document | Contents |
|----------|----------|
| [01-getting-started.md](01-getting-started.md) | Install, `.env`, XAMPP, migrations, dev servers |
| [02-architecture.md](02-architecture.md) | Request flow, tenancy, auth, plans, folder layout |
| [03-database-and-models.md](03-database-and-models.md) | Schema overview, Eloquent models, relationships |
| [04-routes-web-and-api.md](04-routes-web-and-api.md) | All route groups (web + API v1) |
| [05-services-and-business-logic.md](05-services-and-business-logic.md) | Service classes, scheduling, POS, marketing |
| [06-frontend-and-views.md](06-frontend-and-views.md) | Blade views, components, assets, salon-website |
| [07-jobs-commands-and-notifications.md](07-jobs-commands-and-notifications.md) | Background jobs, Artisan commands, mail |
| [modules/README.md](modules/README.md) | **Per-module guides** (appointments, POS, reports, …) |
| [reference/CODE_REFERENCE.md](reference/CODE_REFERENCE.md) | **Auto-generated** method index (258 classes, 1100+ methods) |
| [reference/ENVIRONMENT.md](reference/ENVIRONMENT.md) | Environment variables |
| [reference/PERMISSIONS.md](reference/PERMISSIONS.md) | Roles and permissions |

---

## Regenerating the code reference

When you add or rename methods under `app/`:

```bash
php scripts/generate-code-reference.php
```

This updates [reference/CODE_REFERENCE.md](reference/CODE_REFERENCE.md).

---

## Legacy / feature-specific docs (root)

These predate the full handbook and focus on booking UI fixes:

| File | Topic |
|------|--------|
| [../BOOKING_QUICK_START.md](../BOOKING_QUICK_START.md) | Booking setup quick steps |
| [../BOOKING_IMPROVEMENTS.md](../BOOKING_IMPROVEMENTS.md) | BookingService / seeding fixes |
| [../INDEX.md](../INDEX.md) | Old booking documentation index |

Prefer **`docs/`** for system-wide truth; use root `BOOKING_*.md` only for booking-specific history.

---

## Quick navigation by feature

| Feature | Start here |
|---------|------------|
| Appointments & calendar | [modules/appointments-and-calendar.md](modules/appointments-and-calendar.md) |
| Online booking | [modules/booking.md](modules/booking.md) |
| Staff, leave, attendance | [modules/staff-and-availability.md](modules/staff-and-availability.md) |
| Clients & CRM | [modules/clients.md](modules/clients.md) |
| POS & payments | [modules/pos-and-payments.md](modules/pos-and-payments.md) |
| Reports | [modules/reports.md](modules/reports.md) |
| Marketing | [modules/marketing.md](modules/marketing.md) |
| Inventory | [modules/inventory.md](modules/inventory.md) |
| Multitenancy & admin | [modules/platform-and-admin.md](modules/platform-and-admin.md) |
| Public React site | [modules/public-website.md](modules/public-website.md) |

---

## Conventions for contributors

1. **Tenant scope** — Every salon-owned model uses `salon_id` and `BelongsToTenant` / global scopes. Never query across salons without super-admin middleware.
2. **Time** — Use `App\Support\SalonTime` for “today”, ranges, and timezone display; do not use `now()` alone for business dates.
3. **Money** — Use `@money()` / `DisplayFormatter` / salon `currency` column; amounts stored as decimals in DB.
4. **Partial eager loads** — When using `with(['relation:col1,col2'])`, include foreign keys needed by nested relations (e.g. `service_id` on `appointment_services`).
5. **Plans** — Feature gates: `subscription:feature:*` middleware; limits: `plan.limit:*`.

---

*Last handbook update: 2026-05-30. Regenerate code reference after significant `app/` changes.*
