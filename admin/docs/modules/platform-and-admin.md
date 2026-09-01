# Platform, billing, and admin

## Purpose

SaaS platform layer: multitenancy, subscriptions, super-admin, tenant admin, onboarding, audit, support tickets.

## Multitenancy

- Config: `config/multitenancy.php`
- Finder: `DomainOrSubdomainTenantFinder`
- Trait: `BelongsToTenant` on models
- Middleware: `InitializeTenancyFromDomain`, `tenant`, `PreventCrossTenantAccess`

## Billing (salon subscription)

| File | Role |
|------|------|
| `Billing\BillingController` | Plans, checkout, portal, cancel |
| `Billing\WebhookController` | Cashier webhooks |
| `app/Billing/Plan.php` | Plan definitions |
| Laravel Cashier | `User` billable |

Routes: `billing.*` when `subscriptions.enabled` middleware.

## Tenant admin (`salon-admin.*`)

`TenantAdminController` — team invite, roles, subscription view, ownership transfer.

## Super admin (`admin.*`)

| Controller | Function |
|------------|----------|
| `SuperAdminController` | Dashboard, users, impersonation |
| `AdminTenantController` | Tenants, suspend, domain, plan override |
| `AdminRevenueController` | Platform revenue |
| `AdminPlanController` | Plan migrations |
| `AdminSupportController` | Support tickets |
| `AdminAnalyticsController` | Platform analytics |
| `AdminBillingController` | Webhook replay |
| `AuditLogController` | Cross-tenant audit |

Middleware: `super_admin`.

## Onboarding

`OnboardingController` — steps until salon profile complete (`profile.complete` middleware).

## Go live / setup

- `GoLiveController` — logo, settings, photos
- `SetupProgressController` — checklist API
- `SalonSetupProgress` support class

## Security

- `SecuritySupportController` — security settings UI
- `AccountController` — sessions, tokens, delete account
- `TwoFactorController` — 2FA setup/challenge
- Middleware: `AccountLockout`, `SecurityHeaders`, `SanitizeInput`

## Activity log

Tenant: `Tenant\ActivityLogController` — `activity.index` (permission `view-activity-log`).

## Multi-location

`MultiLocationController` — feature `subscription:feature:multi_location`.

## Chatbot

`ChatbotService` + `ChatbotController` — salon and admin variants.
