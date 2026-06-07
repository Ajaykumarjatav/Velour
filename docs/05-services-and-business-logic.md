# Services and business logic

Business rules live in `app/Services/` and `app/Support/`. Controllers should stay thin: validate input, authorize, delegate, return view/JSON.

## Service classes

| Service | File | Responsibility |
|---------|------|----------------|
| `BookingService` | `BookingService.php` | Public slot generation, hold, confirm, reschedule |
| `AvailabilityService` | `AvailabilityService.php` | Validate proposed windows; merge leave + attendance + hours |
| `StaffAttendanceService` | `StaffAttendanceService.php` | Attendance CRUD; `daySchedulingBlockReason()` for booking |
| `AppointmentService` | `AppointmentService.php` | Internal appointment create/update helpers |
| `PosService` | `PosService.php` | Checkout, line items, completion, refunds |
| `ReportService` | `ReportService.php` | Aggregations for API reports |
| `MarketingService` | `MarketingService.php` | Campaign send, segments, stats |
| `MarketingNotificationBridge` | `MarketingNotificationBridge.php` | Email/WhatsApp channel dispatch |
| `NotificationService` | `NotificationService.php` | In-app salon notifications |
| `NotificationConfigService` | `NotificationConfigService.php` | Per-salon notification settings |
| `ChatbotService` | `ChatbotService.php` | Rule-based assistant replies |
| `AuditLogService` | `AuditLogService.php` | Audit persistence helpers |
| `SalonWebsitePayloadService` | `SalonWebsitePayloadService.php` | JSON for public website API |

## BookingService (critical paths)

### `getAvailableSlots($salon, $date, $serviceIds, $staffId, …)`

1. Reject day if `StaffAttendanceService::daySchedulingBlockReason()` (leave, day off, attendance).
2. Load staff working hours + existing appointments.
3. Apply buffer rules from `SalonBufferRule`.
4. Return slot strings for UI/API.

### `holdSlot($salonId, $data)`

Creates temporary hold (cache/session key) before confirm.

### `confirmFromHold($salon, $data)`

Creates `Appointment` + `AppointmentService` rows; sends notifications.

### `reschedule($appointment, $data)`

Moves appointment; re-validates availability.

**Used by:** `Web\BookingController`, `Api\BookingController`, `Web\AppointmentController` (reschedule).

## AvailabilityService

### `validateProposedWindow(...)`

Checks:

- Salon opening hours
- Staff weekly schedule
- Approved leave (`StaffLeaveRequest`)
- Attendance records (`StaffAttendanceService::pushAttendanceReasons`)
- Overlapping appointments
- Facility/resource constraints (if applicable)

Throws `AvailabilityRejectedException` with user-facing reason.

## StaffAttendanceService

- Records: present, absent, half_day, on_leave, etc.
- **Web UI:** `AvailabilityResourcesController` — tab `attendance`; JSON responses (no full page reload).
- **Booking:** `attendanceBookingBlockReason()` / `daySchedulingBlockReason()` integrated into `BookingService` and `AppointmentController::occupiedSlots`.

## PosService

- Creates `PosTransaction` with items (services, products).
- Sets `completed_at` on completion (drives **revenue reports**).
- Stripe charge via `PaymentGateway` when configured.
- Invoice PDF/email via `PosInvoiceFormatting`, `PosTransactionInvoiceMail`.

## ReportService / Web ReportController

- `ReportController@show($type)` dispatches to private methods: `revenueReport`, `staffReport`, etc.
- Revenue uses **completed POS** dated by `completed_at` ?? `created_at` in salon timezone (`SalonTime`).
- Catalog: `App\Support\ReportCatalog::definitions()`.

## MarketingService

- Campaign creation, preview, send, schedule.
- Jobs: `SendMarketingCampaign`, `SendMarketingEmail`, `SendSmsNotification`, `SendWhatsAppNotification`.
- Automation templates: `MarketingAutomationCatalog`.

## Scheduling support classes

| Class | Role |
|-------|------|
| `AppointmentSlotGrid` | Grid math for calendar slots |
| `ScheduleValidationResult` | DTO for validation outcome |
| `AvailabilityRejectedException` | Failed validation |

## Support utilities (frequently used)

| Class | When to use |
|-------|-------------|
| `SalonTime` | `todayDateString()`, `monthStartDateString()`, timezone abbr |
| `ClientHistory` | Eager-load pattern for client list/show history |
| `PublicStorage` | `storage/` URL for uploads |
| `DisplayFormatter` | Money, dates in UI |
| `SalonBusinessStatus` | Sidebar “business open/closed” badge |
| `ProfileCompletion` | Setup % for dashboard |

Full method lists: [reference/CODE_REFERENCE.md](reference/CODE_REFERENCE.md).
