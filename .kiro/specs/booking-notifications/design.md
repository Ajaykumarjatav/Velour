# Design Document — Booking Notifications

## Overview

This feature extends the Velour salon management platform with two tightly coupled capabilities:

1. **Appointment Management Dashboard** — dedicated confirm, cancel, reschedule, and complete actions on the `/appointments` dashboard, replacing the generic `updateStatus` catch-all with status-guarded controller methods and updated Blade views.
2. **Tenant Notification System** — queued email (and optional SMS) notifications sent to the salon owner whenever a customer books, cancels, or reschedules via the public booking widget, layered on top of the existing `SalonNotification` in-app records.

The system is a multi-tenant Laravel 11 application using `spatie/laravel-multitenancy`. All new code follows the existing patterns: `Mailable` + `ShouldQueue`, `NotificationService` for dispatch orchestration, Blade templates extending `emails.auth._layout`, and PATCH routes inside the `tenant` middleware group.

---

## Architecture

```mermaid
flowchart TD
    subgraph Public Widget
        BW[BookingController API\n/book/{slug}/confirm\n/book/{slug}/cancel\n/book/{slug}/reschedule]
    end

    subgraph Dashboard
        AC[AppointmentController Web\nconfirm / cancel / reschedule / complete]
    end

    subgraph NotificationService
        NS_NEW[notifyTenantNewBooking]
        NS_CANCEL[notifyTenantCancellation]
        NS_RESCHEDULE[notifyTenantReschedule]
        NS_SMS[notifyTenantSms]
        NS_INAPP[createNotification → SalonNotification]
    end

    subgraph Mail
        M1[TenantNewBookingMail]
        M2[TenantCancellationMail]
        M3[TenantRescheduleMail]
    end

    subgraph Queue
        Q[(Laravel Queue)]
    end

    BW -->|new booking| NS_NEW
    BW -->|cancel| NS_CANCEL
    BW -->|reschedule| NS_RESCHEDULE
    AC -->|confirm| NS_NEW
    AC -->|cancel| NS_CANCEL
    AC -->|reschedule| NS_RESCHEDULE

    NS_NEW --> NS_INAPP
    NS_NEW --> M1 --> Q
    NS_NEW --> NS_SMS --> Q

    NS_CANCEL --> NS_INAPP
    NS_CANCEL --> M2 --> Q

    NS_RESCHEDULE --> NS_INAPP
    NS_RESCHEDULE --> M3 --> Q
```

### Key Design Decisions

- **Queued mail** — all three Mailables implement `ShouldQueue` so email dispatch never blocks the HTTP response (Requirement 8.4).
- **Email fallback** — `NotificationService` resolves the recipient as `$salon->email ?: $salon->owner->email` (Requirements 8.2, 9.2, 10.2).
- **Failure isolation** — each dispatch is wrapped in a try/catch; failures are logged but never bubble up to the caller (Requirements 8.5, 9.4, 10.4, 12.4).
- **SMS gating** — SMS is dispatched only when `$salon->getSetting('sms_new_booking_enabled')` is truthy (Requirement 12.3).
- **Status guards** — dedicated controller actions enforce valid prior-state transitions rather than a single generic `updateStatus`, preventing illegal state changes (Requirements 4.3, 5.4, 6.4, 7.3).
- **Client visit tracking** — the `complete` action increments `visit_count` and sets `last_visit_at` on the `Client` model (Requirement 7.2).

---

## Components and Interfaces

### 1. Mailable Classes (`app/Mail/`)

Three new classes, all implementing `ShouldQueue` and using `Queueable` + `SerializesModels`:

| Class | View | Subject |
|---|---|---|
| `TenantNewBookingMail` | `emails.appointments.tenant-new-booking` | `New booking: {client} — {date}` |
| `TenantCancellationMail` | `emails.appointments.tenant-cancellation` | `Booking cancelled: {client} — {date}` |
| `TenantRescheduleMail` | `emails.appointments.tenant-reschedule` | `Booking rescheduled: {client} — {new date}` |

Each constructor accepts `Appointment $appointment` (with `client`, `staff`, `services` eager-loaded). The view receives `$appointment` and `$salon`.

### 2. NotificationService Extensions (`app/Services/NotificationService.php`)

Three new public methods added to the existing class:

```
notifyTenantNewBooking(Appointment $appointment): void
notifyTenantCancellation(Appointment $appointment): void
notifyTenantReschedule(Appointment $appointment, Carbon $originalStartsAt): void
notifyTenantSms(Appointment $appointment): void  // called internally by notifyTenantNewBooking
```

Each method:
1. Calls `$this->createNotification(...)` for the in-app `SalonNotification` record.
2. Resolves the recipient email via `$salon->email ?: optional($salon->owner)->email`.
3. Dispatches the appropriate Mailable via `Mail::to($recipient)->queue(new ...)` inside a try/catch.
4. `notifyTenantNewBooking` additionally calls `notifyTenantSms` if the salon setting is enabled.

The existing `appointmentConfirmation`, `appointmentCancellation`, and `appointmentRescheduled` methods are **updated** to delegate to the new tenant-facing methods, so all call sites (BookingController API) automatically gain email dispatch.

### 3. AppointmentController Enhancements (`app/Http/Controllers/Web/AppointmentController.php`)

Four new action methods replacing the generic `updateStatus` for dashboard use:

| Method | Route | Guard | Side-effects |
|---|---|---|---|
| `confirm(Appointment)` | `PATCH /appointments/{id}/confirm` | status must be `pending` | calls `notifyTenantNewBooking` |
| `cancel(Request, Appointment)` | `PATCH /appointments/{id}/cancel` | status not in `completed,cancelled,no_show` | sets `cancelled_at`, `cancellation_reason`; calls `notifyTenantCancellation` |
| `reschedule(Request, Appointment)` | `PATCH /appointments/{id}/reschedule` | status not in `completed,cancelled,no_show` | checks conflicts; calls `notifyTenantReschedule` |
| `complete(Appointment)` | `PATCH /appointments/{id}/complete` | status in `confirmed,checked_in,in_progress` | increments `client.visit_count`, sets `client.last_visit_at` |

The existing `updateStatus` method is retained for backward compatibility but the new dedicated routes are preferred.

### 4. Blade Views

**`resources/views/appointments/show.blade.php`** — replace the generic status button grid with context-aware action buttons:
- Confirm button (shown when `pending`)
- Cancel button with optional reason textarea (shown when not `completed/cancelled/no_show`)
- Reschedule button opening a date/time/staff form (shown when not `completed/cancelled/no_show`)
- Complete button (shown when `confirmed/checked_in/in_progress`)

**`resources/views/appointments/index.blade.php`** — add `pending` to the status filter dropdown (currently missing).

**Three new email templates** extending `emails.auth._layout`:
- `resources/views/emails/appointments/tenant-new-booking.blade.php`
- `resources/views/emails/appointments/tenant-cancellation.blade.php`
- `resources/views/emails/appointments/tenant-reschedule.blade.php`

### 5. Routes (`routes/web.php`)

Four new PATCH routes added inside the `tenant` middleware group, alongside the existing `appointments` resource:

```php
Route::patch('appointments/{appointment}/confirm',    [AppointmentController::class, 'confirm'])->name('appointments.confirm');
Route::patch('appointments/{appointment}/cancel',     [AppointmentController::class, 'cancel'])->name('appointments.cancel');
Route::patch('appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
Route::patch('appointments/{appointment}/complete',   [AppointmentController::class, 'complete'])->name('appointments.complete');
```

---

## Data Models

No new database migrations are required. All fields used already exist on the `Appointment` model:

| Field | Used by |
|---|---|
| `status` | All status-guard checks and transitions |
| `confirmed_at` | Set to `now()` on confirm |
| `cancelled_at` | Set to `now()` on cancel |
| `cancellation_reason` | Stored on cancel |
| `starts_at` / `ends_at` | Updated on reschedule |
| `staff_id` | Optionally updated on reschedule |

The `Client` model fields used by the complete action:

| Field | Action |
|---|---|
| `visit_count` | Incremented by 1 |
| `last_visit_at` | Set to `$appointment->starts_at` |

The `SalonNotification` model fields used:

| Field | Value |
|---|---|
| `salon_id` | `$appointment->salon_id` |
| `type` | `appointment` / `cancellation` / `reschedule` |
| `title` | Human-readable summary |
| `body` | Client name + date |
| `data` | `['appointment_id' => $appointment->id]` |

The `Salon` model's `getSetting(string $key)` method is used to read `sms_new_booking_enabled`. No schema change needed — this reads from the existing `salon_settings` table via the polymorphic `settings()` relation.

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Tenant Isolation

*For any* two distinct salons, querying appointments for salon A must never return appointments belonging to salon B.

**Validates: Requirements 1.2**

---

### Property 2: Filter Correctness

*For any* combination of active filters (status, date, staff_id, search term), every appointment returned by the index query must satisfy all active filter criteria simultaneously (AND logic).

**Validates: Requirements 2.2, 2.3, 2.4**

---

### Property 3: Upcoming Filter Invariant

*For any* date, filtering appointments with the "upcoming" scope must return only appointments whose `starts_at` is greater than or equal to the current time.

**Validates: Requirements 1.5**

---

### Property 4: Confirm Status Transition

*For any* appointment in `pending` status, calling the confirm action must result in `status === confirmed` and `confirmed_at` being set to a non-null timestamp.

**Validates: Requirements 4.1**

---

### Property 5: Status Guard — Illegal Transitions Rejected

*For any* appointment in a terminal or non-applicable state, the following must hold:
- Confirm is rejected unless status is `pending`
- Cancel is rejected if status is `completed`, `cancelled`, or `no_show`
- Reschedule is rejected if status is `completed`, `cancelled`, or `no_show`
- Complete is rejected unless status is `confirmed`, `checked_in`, or `in_progress`

Each rejection must return an error response without modifying the appointment.

**Validates: Requirements 4.3, 5.4, 6.4, 7.3**

---

### Property 6: Cancel Records Timestamp and Reason

*For any* cancellable appointment, after the cancel action: `status === cancelled`, `cancelled_at` is a non-null timestamp, and if a reason was provided it is persisted in `cancellation_reason`.

**Validates: Requirements 5.1, 5.2**

---

### Property 7: Reschedule Updates Time Fields

*For any* reschedulable appointment with a valid new `starts_at`, after the reschedule action: `starts_at` equals the requested new time and `ends_at` is recalculated based on the appointment's duration.

**Validates: Requirements 6.1**

---

### Property 8: Reschedule Conflict Rejection

*For any* appointment where the requested new time slot overlaps an existing non-cancelled appointment for the same staff member, the reschedule action must be rejected with a conflict error and the appointment must remain unchanged.

**Validates: Requirements 6.3**

---

### Property 9: Complete Updates Client Visit Stats

*For any* appointment in a completable state, after the complete action: `status === completed`, the associated client's `visit_count` is incremented by exactly 1, and `last_visit_at` is set to the appointment's `starts_at`.

**Validates: Requirements 7.1, 7.2**

---

### Property 10: Tenant Email Dispatched on Booking Events

*For any* successful booking, cancellation, or reschedule event (from either the widget or the dashboard), a queued mail job must be dispatched to the resolved tenant email address.

**Validates: Requirements 8.1, 8.4, 9.1, 10.1**

---

### Property 11: Tenant Email Recipient Fallback

*For any* salon where `email` is null or empty, all three notification types (new booking, cancellation, reschedule) must dispatch to `$salon->owner->email` instead.

**Validates: Requirements 8.2, 9.2, 10.2**

---

### Property 12: Tenant Email Content Completeness

*For any* tenant notification email:
- New booking email contains: customer full name, service name, staff full name, appointment date/time, total price
- Cancellation email contains: customer full name, service name, original appointment date/time, cancellation reason (if provided)
- Reschedule email contains: customer full name, service name, original date/time, new date/time

**Validates: Requirements 8.3, 9.3, 10.3**

---

### Property 13: Notification Failure Isolation

*For any* booking event where the email or SMS dispatch throws an exception, the underlying operation (booking confirmation, cancellation, or reschedule) must still succeed and return a successful response. The failure must be logged.

**Validates: Requirements 8.5, 9.4, 10.4, 12.4**

---

### Property 14: In-App Notification Created for Every Booking Event

*For any* booking, cancellation, or reschedule event, a `SalonNotification` record must be created with the correct `type` (`appointment`, `cancellation`, or `reschedule` respectively) and the `data` field must contain `appointment_id` equal to the appointment's id.

**Validates: Requirements 11.1, 11.2, 11.3, 11.4**

---

### Property 15: SMS Gating

*For any* new booking event:
- If `sms_new_booking_enabled` is truthy for the salon, an SMS job must be dispatched
- If `sms_new_booking_enabled` is falsy or absent, no SMS job must be dispatched

**Validates: Requirements 12.1, 12.3**

---

## Error Handling

### Status Transition Errors

All four new controller actions (`confirm`, `cancel`, `reschedule`, `complete`) validate the current appointment status before proceeding. On an invalid transition:
- Return `back()->withErrors(['status' => '...'])` for web requests
- HTTP 422 with a descriptive message for API requests

### Scheduling Conflict on Reschedule

The reschedule action queries for overlapping appointments (`starts_at < new_ends_at AND ends_at > new_starts_at`) for the same `staff_id` and `salon_id`, excluding the current appointment and cancelled/no_show statuses. On conflict: return `back()->withErrors(['starts_at' => 'That time slot is no longer available.'])`.

### Email Dispatch Failures

All `Mail::to(...)->queue(...)` calls are wrapped in try/catch. On exception:
```php
Log::error('Tenant notification email failed', [
    'appointment_id' => $appointment->id,
    'type'           => $type,
    'error'          => $e->getMessage(),
]);
```
The exception is swallowed — it must not propagate to the caller.

### SMS Dispatch Failures

Same pattern as email: try/catch with `Log::error(...)`, exception swallowed.

### Missing Owner Email

If both `$salon->email` and `$salon->owner?->email` are null, the email dispatch is skipped entirely (no recipient to send to). This is logged at `warning` level.

### Tenant Authorization

All web controller actions call the existing `authorise(Appointment $appointment)` helper which aborts with 403 if `$appointment->salon_id !== $this->salon()->id`.

---

## Testing Strategy

### Dual Testing Approach

Both unit/feature tests and property-based tests are required. They are complementary:
- **Feature tests** cover specific examples, integration points, and edge cases
- **Property tests** verify universal correctness across randomized inputs

### Property-Based Testing

**Library**: [`jqhph/fast-check-php`](https://github.com/dubzzz/fast-check) or [`eris`](https://github.com/giorgiosironi/eris) for PHP. The recommended choice is **Eris** (available via `composer require giorgiosironi/eris`) as it integrates cleanly with PHPUnit.

Each property test must run a **minimum of 100 iterations**.

Each test must include a comment tag in the format:
`// Feature: booking-notifications, Property {N}: {property_text}`

| Property | Test Class | PBT Pattern |
|---|---|---|
| P1: Tenant Isolation | `AppointmentTenantIsolationTest` | Generate two salons with random appointments, assert no cross-contamination |
| P2: Filter Correctness | `AppointmentFilterTest` | Generate random filter combinations + appointment sets, assert all results satisfy all filters |
| P3: Upcoming Filter Invariant | `AppointmentScopeTest` | Generate random `starts_at` values, assert upcoming scope only returns future records |
| P4: Confirm Status Transition | `AppointmentConfirmTest` | Generate pending appointments, assert post-confirm state |
| P5: Status Guard | `AppointmentStatusGuardTest` | Generate appointments in each non-applicable state, assert each illegal action is rejected |
| P6: Cancel Records Timestamp | `AppointmentCancelTest` | Generate cancellable appointments with random reasons, assert post-cancel state |
| P7: Reschedule Updates Time | `AppointmentRescheduleTest` | Generate valid new times, assert starts_at/ends_at updated correctly |
| P8: Reschedule Conflict | `AppointmentRescheduleConflictTest` | Generate overlapping appointments, assert conflict rejection |
| P9: Complete Updates Client | `AppointmentCompleteTest` | Generate completable appointments, assert client stats incremented |
| P10: Email Dispatched | `TenantNotificationEmailTest` | Use `Mail::fake()`, generate appointments, assert mail queued for each event type |
| P11: Email Fallback | `TenantNotificationFallbackTest` | Generate salons with null email, assert mail sent to owner email |
| P12: Email Content | `TenantNotificationContentTest` | Generate appointments with random client/service/staff data, assert rendered email contains all required fields |
| P13: Failure Isolation | `NotificationFailureIsolationTest` | Mock Mail to throw, assert booking/cancel/reschedule still succeeds |
| P14: In-App Notification | `SalonNotificationCreationTest` | Generate booking events, assert SalonNotification record created with correct type and data |
| P15: SMS Gating | `TenantSmsGatingTest` | Generate salons with setting on/off, assert SMS dispatched only when enabled |

### Feature Tests (Specific Examples)

- `GET /appointments` returns 200 with paginated list for authenticated tenant
- `GET /appointments` with no results returns empty-state (no appointments in DB)
- `GET /appointments/{id}` returns all required detail fields
- `PATCH /appointments/{id}/confirm` on a `pending` appointment returns redirect with success flash
- `PATCH /appointments/{id}/confirm` on a `confirmed` appointment returns 422/redirect with error
- `PATCH /appointments/{id}/cancel` stores `cancellation_reason` when provided
- `PATCH /appointments/{id}/reschedule` with a conflicting slot returns error
- `PATCH /appointments/{id}/complete` on a `pending` appointment returns error
- New booking via `POST /api/book/{slug}/confirm` dispatches `TenantNewBookingMail` to queue
- Cancellation via `POST /api/book/{slug}/cancel/{ref}` dispatches `TenantCancellationMail` to queue
- Reschedule via `POST /api/book/{slug}/reschedule/{ref}` dispatches `TenantRescheduleMail` to queue
- When `Mail::to()->queue()` throws, booking API still returns 201

### Test Configuration

```php
// PHPUnit feature test example skeleton
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

Mail::fake();
Queue::fake();

// ... trigger action ...

Mail::assertQueued(TenantNewBookingMail::class, fn($mail) =>
    $mail->hasTo($salon->email)
);
```

```php
// Eris property test example skeleton
// Feature: booking-notifications, Property 1: Tenant Isolation
$this->forAll(
    Generator\choose(1, 100), // salon A appointment count
    Generator\choose(1, 100)  // salon B appointment count
)->then(function(int $countA, int $countB) {
    // create two salons with $countA and $countB appointments
    // query salon A's appointments
    // assert none belong to salon B
});
```
