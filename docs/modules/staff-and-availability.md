# Staff, leave, and attendance

## Purpose

Manage team members, weekly schedules, **leave requests**, and **daily attendance** (on duty, absent, half day, on leave). Attendance and leave **block booking slots**.

## Key files

| Layer | Path |
|-------|------|
| Staff CRUD | `app/Http/Controllers/Web/StaffController.php` |
| Availability hub | `app/Http/Controllers/Web/AvailabilityResourcesController.php` |
| Attendance service | `app/Services/StaffAttendanceService.php` |
| Availability service | `app/Services/AvailabilityService.php` |
| Models | `Staff`, `StaffLeaveRequest`, `StaffAttendanceRecord` |
| Views | `resources/views/staff/*`, `resources/views/availability/*` |
| Migration | `2026_05_31_100000_create_staff_attendance_records_table.php` |

## Web routes (`availability.*`)

- Resources CRUD (chairs/rooms) — availability module tables from `2026_04_04_120000_create_availability_module_tables.php`
- Leave: approve/reject → syncs attendance to `on_leave`
- Attendance: store, clock-in, clock-out — **JSON responses**, Alpine `attendanceHub` (no full page reload)
- Staff day toggle for weekly availability

## Staff avatars

Always use:

- `Staff::resolvePublicAvatarUrl()`
- `<x-staff-avatar :staff="$staff" />`

`PublicStorage` normalizes paths; live deploy needs `storage:link` and correct `APP_URL`.

## Booking integration

| Method | Role |
|--------|------|
| `StaffAttendanceService::daySchedulingBlockReason()` | Whole day blocked |
| `StaffAttendanceService::attendanceBookingBlockReason()` | Per-staff block |
| `AvailabilityService::pushAttendanceReasons()` | Validation messages |

Called from `BookingService::getAvailableSlots` and `AppointmentController::occupiedSlots`.

## Staff payroll export

`GET staff/payroll/export` — CSV export (permissions required).

## Permissions

- `staff.view`, `staff.create`, `staff.update`, `staff.delete`
- Availability hub: roles `tenant_admin`, `manager`, `receptionist` (`SidebarNav`)

## Plan limits

`plan.limit:staff` on staff create routes and quick-create.

Methods: [CODE_REFERENCE.md](../reference/CODE_REFERENCE.md) → `StaffAttendanceService`, `AvailabilityResourcesController`.
