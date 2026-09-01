# Appointments and calendar

## Purpose

Internal scheduling: create/edit appointments, change status, reschedule, and view **calendar** (day/week/month) with **staff sidebar** layout (Skello-style week grid).

## Key files

| Layer | Path |
|-------|------|
| Web controller | `app/Http/Controllers/Web/AppointmentController.php` |
| API controller | `app/Http/Controllers/Api/AppointmentController.php` |
| Calendar controller | `app/Http/Controllers/Web/CalendarController.php` |
| Service | `app/Services/AppointmentService.php` |
| Availability | `app/Services/AvailabilityService.php` |
| Models | `Appointment`, `AppointmentService` |
| Views | `resources/views/appointments/*`, `resources/views/calendar/*` |

## Routes (web)

- `appointments` resource (index, create, store, show, edit, update, destroy)
- `appointments/{id}/status`, `/confirm`, `/cancel`, `/reschedule`, `/complete`
- `GET appointments/occupied-slots` — slot picker JSON (respects leave + attendance)
- `POST appointments/validate-window` — pre-check before save
- `GET calendar` — calendar UI

## Appointment statuses

Typical flow: `scheduled` → `confirmed` → `checked_in` → `completed` (or `cancelled`, `no_show`). Exact enums in migration/model.

## Calendar implementation

`CalendarController::buildStaffSidebarGrid()` builds staff × day columns. Partial: `calendar/partials/staff-sidebar-grid.blade.php` shared by week, day, and month views.

Appointment payloads include services, duration, staff colour for rendering.

## Slot validation

1. `AvailabilityService::validateProposedWindow()` — server-side on save.
2. `BookingService::getAvailableSlots()` — shared with public booking.
3. `StaffAttendanceService::daySchedulingBlockReason()` — blocks entire days.

## Model notes

```php
// Appointment.php
public function services() {
    return $this->hasMany(AppointmentService::class);
}
```

Use `->with('services.service')` when you need linked `Service` model. Partial selects must include `service_id` if nesting `service`.

## Permissions

- `appointments.view`, `appointments.create`, `appointments.update`, `appointments.delete` (Spatie)

## Extending

- New status: migration + policy + controller transition + calendar colour in blade/JS.
- New calendar view: extend `CalendarController`, add partial, wire route.

Methods: `AppointmentController`, `CalendarController` in [CODE_REFERENCE.md](../reference/CODE_REFERENCE.md).
