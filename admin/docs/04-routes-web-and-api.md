# Routes — Web and API

Route files: `routes/web.php`, `routes/api.php`.

## Web middleware chains

**Salon app (typical):**  
`auth` → `verified` → `2fa` → `password.changed` → `InitializeTenancyFromDomain` → `tenant` → `profile.complete`

**Tenant admin:** + `tenant_admin`  
**Super admin:** `auth` → `verified` → `2fa` → `password.changed` → `super_admin` (no tenant middleware)

## Web route groups (by name prefix)

| Prefix | Controller | Notes |
|--------|------------|-------|
| `login`, `register`, `password.*` | `Web\AuthController` | Guest only |
| `dashboard` | `Web\DashboardController` | KPIs, widgets |
| `calendar` | `Web\CalendarController` | Week/day/month + staff sidebar |
| `appointments.*` | `Web\AppointmentController` | CRUD, status, reschedule, `occupied-slots` |
| `clients.*` | `Web\ClientController` | CRUD, import/export, review requests |
| `staff.*` | `Web\StaffController` | CRUD, payroll export, weekly schedule |
| `services.*` | `Web\ServiceController` | CRUD, variants, pricing rules |
| `service-packages.*` | `Web\ServicePackageController` | Bundles |
| `availability.*` | `Web\AvailabilityResourcesController` | Resources, leave, **attendance** (AJAX) |
| `inventory.*` | `Web\InventoryController` | Stock, adjust, export |
| `pos.*` | `Web\PosController` | Checkout, invoice PDF |
| `marketing.*` | `Web\MarketingController` | Campaigns, loyalty, SMS |
| `reports.*`, `revenue.index` | `Web\ReportController` | Analytics + typed reports |
| `reviews.*` | `Web\ReviewController` | In-app reviews |
| `settings.*` | `Web\SettingsController` | Salon, hours, team, notifications |
| `billing.*` | `Billing\BillingController` | Stripe subscription |
| `salon-admin.*` | `Admin\TenantAdminController` | Team, subscription |
| `admin.*` | `Admin\*` | Platform super-admin |
| `onboarding.*` | `Web\OnboardingController` | New salon wizard |
| `book/{slug}` | `Web\BookingController` | Blade booking page |
| `storefront.show` | `Web\StorefrontController` | Serves React `public/website` |
| `reviews.public*` | `Web\ReviewController` | Token-based public review form |
| `lookup.*` | `Web\TenantLookupController` | AJAX client/staff search |
| `quick-create.*` | `Web\RelationQuickCreateController` | Inline create modals |
| `chatbot.message` | `Web\ChatbotController` | In-app assistant |
| `go-live.*`, `setup-progress` | Go live / setup checklist |
| `website-seo.*` | `Web\WebsiteSeoController` | SEO publish |
| `multi-location.*` | `Web\MultiLocationController` | Feature-gated |
| `account.*` | `Web\AccountController` | Sessions, API tokens, delete account |

## API v1 (`routes/api.php`)

Base: `/api/v1` with `sanitize` middleware.

### Public

| Path | Controller |
|------|------------|
| `POST v1/auth/register`, `login`, … | `Api\AuthController` |
| `GET v1/salon/{slug}/website` | `Api\SalonWebsiteController` |
| `v1/book/{slug}/*` | `Api\BookingController` |
| `v1/reviews/*` | `Api\ReviewController` |
| `POST v1/webhooks/stripe` | `Api\PosController@stripeWebhook` |
| `GET v1/health` | `Api\HealthController` |

### Authenticated + tenant (`auth:sanctum`, `salon.access`, `tenant`)

Prefix: `v1/salon/…`

| Domain | Controller |
|--------|------------|
| Salon profile | `Api\SalonController` |
| Dashboard | `Api\DashboardController` |
| Calendar | `Api\CalendarController` |
| Staff | `Api\StaffController` |
| Services / categories | `Api\ServiceController` |
| Clients | `Api\ClientController` |
| Appointments | `Api\AppointmentController` |
| Inventory + purchase orders | `Api\InventoryController` |
| POS | `Api\PosController` |
| Marketing | `Api\MarketingController` |
| Reviews | `Api\ReviewController` |
| Reports | `Api\ReportController` |
| Notifications | `Api\NotificationController` |
| GDPR | `Api\GdprController` |
| Share / Go Live | `Api\ShareController` |

Rate limits: auth 10/min, booking 30/min, authenticated 120/min.

## Named routes in Blade

Use `route('appointments.index')` etc. Report types use `route('reports.show', $type)`.

## Adding a new web feature

1. Register route inside correct middleware group in `web.php`.
2. Create `Web\*Controller` method.
3. Add policy + permission if needed.
4. Add `SidebarNav::show()` case if it needs a nav link.
5. Create Blade under `resources/views/{feature}/`.

See module guides under [modules/](modules/).
