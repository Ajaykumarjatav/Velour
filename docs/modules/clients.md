# Clients (CRM)

## Purpose

Client database: contact details, history, notes, formulas (colour recipes), import/export, review request batching.

## Key files

| Layer | Path |
|-------|------|
| Controller | `app/Http/Controllers/Web/ClientController.php` |
| API | `app/Http/Controllers/Api/ClientController.php` |
| Notes | `ClientNoteController` |
| Formulas | `ClientFormulaController` |
| Support | `app/Support/ClientHistory.php`, `ClientEngagement.php` |
| Model | `Client` (uses `AuditLog` trait) |
| Views | `resources/views/clients/*` |
| Policy | `app/Policies/ClientPolicy.php` |

## Routes

- `clients` resource
- `GET clients/export`, `POST clients/import`
- `POST clients/review-requests` — send review links

## Client history loading

`ClientHistory::forClient()` / `forClientIds()` eager-load appointments with partial columns.

**Important:** include `service_id` when loading `services` if any code accesses `service` relation:

```php
'services:id,appointment_id,service_id,service_name'
```

`Appointment::services()` does not auto-load `service` relation.

## GDPR (API)

`GdprController` — erase, export, consent per client under `v1/salon/gdpr/...`.

## Permissions

`clients.view`, `clients.create`, `clients.update`, `clients.delete`

## Stylist scope

Users with `dashboardScopedStaffId()` see only clients they have booked (dashboard + limited nav).
