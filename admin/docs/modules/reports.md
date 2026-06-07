# Reports and analytics

## Purpose

Business reporting: revenue, appointments, staff, clients, services, inventory, marketing. Dashboard analytics slider uses overlapping queries.

## Key files

| Layer | Path |
|-------|------|
| Web | `Web\ReportController` |
| API | `Api\ReportController` |
| Service | `ReportService` |
| Catalog | `App\Support\ReportCatalog` |
| Views | `resources/views/reports/*` |

## Report types (`ReportCatalog`)

| Key | View | Date filter |
|-----|------|-------------|
| `revenue` | `reports/revenue.blade.php` | Yes |
| `appointments` | `reports/appointments.blade.php` | Yes |
| `staff` | `reports/staff.blade.php` | Yes |
| `clients` | `reports/clients.blade.php` | Yes |
| `services` | `reports/services.blade.php` | Yes |
| `inventory` | `reports/inventory.blade.php` | Yes (needs `inventory.view`) |
| `marketing` | `reports/marketing.blade.php` | Yes (needs `marketing.view`) |

## Routes

- `GET reports` — index grid
- `GET reports/analytics` — dashboard widgets data
- `GET reports/{type}` — `ReportController@show`
- `GET reports/revenue/export` — CSV
- `GET revenue` — alias redirect to revenue report

Middleware: `subscription:feature:reports` on report routes.

## Revenue report rules

Documented in view alert:

- Completed POS sales only
- Dated by `completed_at` ?? `created_at`
- Timezone: `SalonTime` / salon `timezone`
- Filters: staff, payment method, compare previous period

## Sidebar

`SidebarNav`: `revenue`, `analytics`, `reports_menu` require `reports.view`.

## API

`GET v1/salon/reports/revenue`, `.../appointments`, etc. + `reports/export/{type}`.

Private methods in `ReportController` (e.g. `revenueReport`) — see [CODE_REFERENCE.md](../reference/CODE_REFERENCE.md).
