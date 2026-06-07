# Online booking

## Purpose

Clients book appointments via:

1. **Blade** — `/book/{slug}` (`Web\BookingController`)
2. **API** — `v1/book/{slug}/*` (`Api\BookingController`) for widgets/React
3. **React site** — `salon-website` `BookingFlow.jsx` → booking API

## Key files

| Layer | Path |
|-------|------|
| Service | `app/Services/BookingService.php` |
| Web | `app/Http/Controllers/Web/BookingController.php` |
| API | `app/Http/Controllers/Api/BookingController.php` |
| Views | `resources/views/booking/*` |
| Requests | `app/Http/Requests/Booking/ConfirmBookingRequest.php` |

## API flow

| Step | Endpoint | Method |
|------|----------|--------|
| Salon info | `GET .../info` | Salon + settings |
| Services | `GET .../services` | Bookable services |
| Staff | `GET .../staff` | Staff for service |
| Slots | `GET .../availability` | `BookingService::getAvailableSlots` |
| Hold | `POST .../hold` | Temporary hold token |
| Confirm | `POST .../confirm` | Create appointment |
| Lookup | `GET .../by-ref` | By reference code |
| Cancel/reschedule | `POST .../cancel`, `.../reschedule` | Client self-service |

Throttle: 30 requests/minute on booking routes.

## Hold → confirm

1. `holdSlot()` stores selection in cache with TTL.
2. `confirmFromHold()` validates hold, creates `Appointment` + lines, sends `ClientBookingConfirmationMail` / `TenantNewBookingMail`.

## Salon settings affecting booking

- `online_booking_enabled`, `new_client_booking_enabled`
- `booking_advance_days`, `cancellation_hours`
- `deposit_required`, `deposit_percentage`
- Buffer rules: `SalonBufferRule`
- Staff: `bookable_online`, working hours, leave, attendance

## Troubleshooting

| Symptom | Check |
|---------|-------|
| No slots | Staff hours, leave, attendance, existing appointments |
| Hold expired | Hold TTL; client waited too long |
| Service not listed | Service active, staff eligibility, `allowed_roles` |

See also [staff-and-availability.md](staff-and-availability.md), root `BOOKING_QUICK_START.md`.
