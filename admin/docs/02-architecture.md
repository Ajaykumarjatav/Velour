# Architecture

## High-level diagram

```
                    ┌─────────────────────────────────────────┐
                    │           Browser / Mobile app           │
                    └───────────────┬─────────────────────────┘
                                    │
          ┌─────────────────────────┼─────────────────────────┐
          │                         │                         │
    Web (session)              API (Sanctum)            Public (no auth)
    routes/web.php             routes/api.php          book/, s/, reviews/
          │                         │                         │
          ▼                         ▼                         ▼
    Middleware stack          salon.access + tenant      InitializeTenancy
    auth, verified, 2fa       cross.tenant, audit        (domain/subdomain)
          │                         │
          ▼                         ▼
    Controllers (Web/*)       Controllers (Api/*)
          │                         │
          └────────────┬────────────┘
                       ▼
              Services + Models (salon_id scoped)
                       ▼
                   MySQL (single database)
```

## Multitenancy

Configured in `config/multitenancy.php`:

- **Tenant model:** `App\Models\Tenant` (maps to `salons` table).
- **Resolution:** `DomainOrSubdomainTenantFinder` — custom domain first, then `{subdomain}.{APP_BASE_DOMAIN}`.
- **Isolation:** Shared database; every tenant row has `salon_id`. Global scopes on models (`BelongsToTenant`, `TenantScope`).
- **Switch tasks:** Session binding, container binding, cache prefix per tenant.
- **Queues:** Tenant context restored on queued jobs by default.

Middleware `InitializeTenancyFromDomain` runs on tenant-scoped web and API routes.

## Authentication (web)

Order for salon app routes:

1. `guest` — login/register only
2. `auth` — logged in
3. `verified` — email verified
4. `2fa` — two-factor passed (`RequireTwoFactor`)
5. `password.changed` — forced password change completed
6. `InitializeTenancyFromDomain` + `tenant` + `profile.complete`

**2FA:** TOTP or email codes via `TwoFactorController`.

**Stylist-scoped dashboard:** Users linked to a single `staff` record see a reduced nav (`SidebarNav::STYLIST_NAV`) and scoped KPIs.

## Authorization

- **Spatie Laravel Permission** — permissions like `appointments.view`, `reports.view`, `marketing.view`.
- **Policies** — `app/Policies/*` per resource; use `$this->authorize()` in controllers.
- **Subscription middleware** — `subscription:feature:reports`, `subscription:feature:marketing`, etc.
- **Plan limits** — `plan.limit:staff`, `plan.limit:services` on create routes.

## HTTP layer layout

```
app/Http/
├── Controllers/
│   ├── Web/          # Blade UI
│   ├── Api/          # JSON API v1
│   ├── Admin/        # Super-admin panel
│   └── Billing/      # Stripe Cashier checkout
├── Middleware/       # Tenancy, security, throttling
├── Requests/         # Form validation
└── Resources/        # API transformers
```

## Domain layer

```
app/
├── Models/           # Eloquent entities
├── Services/         # Business logic (booking, POS, reports, …)
├── Support/          # Stateless helpers (SalonTime, ClientHistory, …)
├── Jobs/             # Async work
├── Mail/             # Mailable classes
├── Policies/         # Authorization rules
└── Traits/           # AuditLog, BelongsToTenant, HasSupportId
```

## Key support classes

| Class | Role |
|-------|------|
| `SalonTime` | Timezone-aware dates for a salon |
| `SidebarNav` | Which sidebar items appear per user |
| `ReportCatalog` | Report type definitions and permissions |
| `SalonSetupProgress` | Onboarding checklist completion |
| `ClientHistory` | Client appointment/POS history for index/show |
| `PublicStorage` | Avatar/logo URL resolution |
| `StaffServiceEligibility` | Which staff can perform a service |

## Billing

- **Laravel Cashier** on `User` or salon owner account.
- Web routes under `billing.*` (plans, checkout, portal) when `subscriptions.enabled`.
- Stripe webhooks: API `PosController@stripeWebhook` and admin replay tools.

## Audit & activity

- **Spatie Activity Log** — model changes where `AuditLog` trait is used.
- **AuditLog model + middleware** — API request audit trail (`audit.request`).
- Super-admin: `admin.audit.*` routes.

## Caching & performance

- Tenant-prefixed cache keys when tenant is current.
- `LogSlowQueries`, `LogSlowRequests` middleware in development.

## External integrations

| Integration | Usage |
|-------------|--------|
| Stripe | Subscriptions, POS card payments |
| Twilio | SMS marketing / notifications |
| Pusher | Real-time (if configured) |
| DomPDF | POS invoice PDF |
| Intervention Image | Image uploads |

See [04-routes-web-and-api.md](04-routes-web-and-api.md) for endpoint inventory.
