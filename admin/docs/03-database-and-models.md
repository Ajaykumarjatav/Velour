# Database and models

## Schema source of truth

- **Initial schema:** `database/migrations/2024_01_01_000001_create_velour_schema.php` — users, salons, staff, services, clients, appointments, POS, marketing, inventory, vouchers, reviews, etc.
- **Incremental migrations:** 56 files under `database/migrations/` (2024 platform + 2026 feature modules).

Run `php artisan migrate` on deploy. Never edit applied migrations in production; add new migrations.

## Core entity relationships

```
users ──owns──> salons (Tenant)
salons ──has many──> staff, services, service_categories, clients
staff ──many-to-many──> services (pivot)
clients ──has many──> appointments, client_notes, client_formulas
appointments ──has many──> appointment_services ──belongs to──> services
appointments ──belongs to──> staff, client, salon
pos_transactions ──has many──> pos_transaction_items
marketing_campaigns ──belongs to──> salon
staff_leave_requests, staff_attendance_records ──belongs to──> staff
```

## Eloquent models (`app/Models/`)

| Model | Table | Notes |
|-------|-------|-------|
| `User` | `users` | Login; roles; may link to `Staff` |
| `Tenant` | `salons` | Multitenancy tenant; same row as `Salon` |
| `Salon` | `salons` | Business profile, hours, timezone, currency |
| `SalonSetting` | `salon_settings` | Key/value settings |
| `Staff` | `staff` | Team member; schedule; avatar |
| `StaffLeaveRequest` | `staff_leave_requests` | Leave approval workflow |
| `StaffAttendanceRecord` | `staff_attendance_records` | Daily attendance (on duty, leave, etc.) |
| `Service` | `services` | Price, duration, variants, dynamic pricing |
| `ServiceCategory` | `service_categories` | Grouping |
| `ServicePackage` | `service_packages` + pivot | Bundled services |
| `Client` | `clients` | CRM record |
| `ClientNote` | `client_notes` | Internal notes |
| `ClientFormula` | `client_formulas` | Colour/formula tracking |
| `Appointment` | `appointments` | Status, payment_status, reminders |
| `AppointmentService` | `appointment_services` | Line items; `service_name` snapshot |
| `PosTransaction` | `pos_transactions` | `completed_at` for revenue recognition |
| `PosTransactionItem` | `pos_transaction_items` | Service/product lines |
| `InventoryItem` | `inventory_items` | Stock |
| `InventoryAdjustment` | `inventory_adjustments` | Stock movements |
| `MarketingCampaign` | `marketing_campaigns` | Email/SMS campaigns |
| `MarketingAutomationTemplate` | `marketing_automation_templates` | Automated flows |
| `Review` | `reviews` | Client reviews |
| `ReviewLink` | `review_links` | Shareable review tokens |
| `Voucher` | `vouchers` | Discount codes |
| `Facility` | `facilities` | Rooms/chairs (multi-resource) |
| `SalonResource` | `salon_resources` | Availability module resources |
| `DynamicPricingRule` | `dynamic_pricing_rules` | Time-based pricing |
| `AuditLog` | `audit_logs` | Security audit |
| `Invoice` | `invoices` | Platform billing |
| `SupportTicket` | `support_tickets` | Super-admin support |

## Tenant scoping

Models that belong to a salon use:

- `salon_id` column
- `App\Traits\BelongsToTenant`
- Global scope `App\Scopes\TenantScope`

Always create records with the current salon’s ID. Super-admin controllers bypass scope explicitly where needed.

## Important columns

| Area | Columns |
|------|---------|
| Appointments | `status`, `starts_at`, `ends_at`, `staff_id`, `client_id`, `payment_status` |
| POS revenue | `status` = completed; `completed_at` (fallback `created_at`) |
| Salon | `timezone`, `currency`, `opening_hours` (JSON), `slug`, `domain`, `subdomain` |
| Staff | `working_days` (JSON), `start_time`, `end_time`, `bookable_online` |

## Soft deletes

`users`, `salons`, `staff`, `clients`, and others use `deleted_at`. Queries default to excluding deleted rows.

## Permissions tables

`2026_03_16_055526_create_permission_tables.php` — Spatie `roles`, `permissions`, pivots.

## Seeding

Check `database/seeders/` for:

- Default services/categories
- Business types
- Demo salon data (if present)

## Model highlights

### `Appointment`

- Relations: `client`, `staff`, `services` (has many `AppointmentService`).
- Do **not** eager-load `service` on `services()` by default; use `services.service` when needed.
- Status transitions via `AppointmentController` / `AppointmentService`.

### `Staff`

- `resolvePublicAvatarUrl()` — use for all avatar display (local + live).
- Linked to `User` when staff can log in.

### `Client`

- Uses `AuditLog` trait (single `$auditExclude` definition).
- Import/export via `ClientController`.

Method-level detail: [reference/CODE_REFERENCE.md](reference/CODE_REFERENCE.md).
