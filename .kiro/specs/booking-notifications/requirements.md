# Requirements Document

## Introduction

This feature covers two related areas of the salon management SaaS:

1. **Appointment Management Dashboard** — The tenant dashboard (`/appointments`) where salon owners and staff view, filter, and manage all appointments (confirm, cancel, reschedule, complete, view details).
2. **Booking Notification System** — Automated email (and optionally SMS) notifications sent to the tenant (salon owner/admin) whenever a customer books, cancels, or reschedules an appointment via the public booking widget (`/book/{slug}`).

The system is a multi-tenant Laravel 11 application using `spatie/laravel-multitenancy`. Notifications are dispatched through the existing `NotificationService` and Laravel Mail infrastructure. In-app notifications are stored via the `SalonNotification` model.

---

## Glossary

- **Tenant**: A salon business registered on the platform, identified by a `Salon` model.
- **Owner**: The `User` associated with `$salon->owner` (the primary account holder).
- **Appointment**: An `Appointment` model record with statuses: `pending`, `confirmed`, `checked_in`, `in_progress`, `completed`, `cancelled`, `no_show`.
- **Booking_Widget**: The public-facing booking page at `/book/{slug}` used by customers to book appointments.
- **Dashboard**: The tenant-facing appointments management page at `/appointments`.
- **NotificationService**: The existing `app/Services/NotificationService.php` class responsible for dispatching notifications.
- **BookingService**: The existing `app/Services/BookingService.php` class, specifically `confirmFromHold()` which creates appointments from the public widget.
- **Tenant_Email**: The email address stored on `$salon->email`.
- **Owner_Email**: The email address of `$salon->owner` (a `User` model).
- **SalonNotification**: The existing in-app notification model used to surface alerts in the dashboard.
- **Notification_Email**: A Laravel Mailable sent to the tenant to inform them of booking events.

---

## Requirements

### Requirement 1: View Appointments in the Dashboard

**User Story:** As a salon owner or staff member, I want to view all upcoming and past appointments in the dashboard, so that I can stay informed about the schedule.

#### Acceptance Criteria

1. THE Dashboard SHALL display a paginated list of appointments including client name, service name, assigned staff member, appointment date/time, status, and total price.
2. WHEN the tenant navigates to `/appointments`, THE Dashboard SHALL load and display appointments belonging only to the authenticated tenant's salon.
3. WHILE an appointment list is loading, THE Dashboard SHALL display a loading indicator to the user.
4. IF no appointments exist for the current filter criteria, THEN THE Dashboard SHALL display an empty-state message indicating no appointments were found.
5. THE Dashboard SHALL display upcoming appointments (future `starts_at`) and past appointments in separate or filterable views.

---

### Requirement 2: Filter and Search Appointments

**User Story:** As a salon owner or staff member, I want to filter and search appointments by date, status, staff, or service, so that I can quickly find specific bookings.

#### Acceptance Criteria

1. THE Dashboard SHALL provide filter controls for: date range, appointment status, assigned staff member, and service.
2. WHEN a filter is applied, THE Dashboard SHALL update the appointment list to show only appointments matching all active filter criteria.
3. WHEN a search term is entered, THE Dashboard SHALL filter appointments by matching client name, email, or phone number.
4. WHEN multiple filters are active simultaneously, THE Dashboard SHALL apply all filters using AND logic.
5. THE Dashboard SHALL allow the tenant to clear all active filters and return to the unfiltered appointment list.

---

### Requirement 3: View Appointment Details

**User Story:** As a salon owner or staff member, I want to view the full details of an appointment, so that I can see all relevant information before taking action.

#### Acceptance Criteria

1. WHEN a tenant selects an appointment from the list, THE Dashboard SHALL display the appointment detail view containing: client full name, client email, client phone, service name, assigned staff member, appointment date and time, duration, total price, deposit status, and current status.
2. THE Dashboard SHALL display the client notes and internal notes fields on the appointment detail view.
3. THE Dashboard SHALL display the appointment reference number on the detail view.

---

### Requirement 4: Confirm Appointments

**User Story:** As a salon owner or staff member, I want to confirm a pending appointment, so that the client knows their booking is accepted.

#### Acceptance Criteria

1. WHEN a tenant confirms an appointment with status `pending`, THE Dashboard SHALL update the appointment status to `confirmed`.
2. WHEN an appointment is confirmed via the Dashboard, THE NotificationService SHALL dispatch an appointment confirmation notification to the client.
3. IF an appointment is not in `pending` status, THEN THE Dashboard SHALL prevent the confirm action and display an appropriate error message.

---

### Requirement 5: Cancel Appointments

**User Story:** As a salon owner or staff member, I want to cancel an appointment, so that the slot is freed and the client is informed.

#### Acceptance Criteria

1. WHEN a tenant cancels an appointment, THE Dashboard SHALL update the appointment status to `cancelled` and record the `cancelled_at` timestamp.
2. WHEN a tenant cancels an appointment, THE Dashboard SHALL allow the tenant to optionally provide a cancellation reason.
3. WHEN an appointment is cancelled via the Dashboard, THE NotificationService SHALL dispatch a cancellation notification to the client.
4. IF an appointment has status `completed`, `cancelled`, or `no_show`, THEN THE Dashboard SHALL prevent the cancel action and display an appropriate error message.

---

### Requirement 6: Reschedule Appointments

**User Story:** As a salon owner or staff member, I want to reschedule an appointment to a new date and time, so that the client's booking is updated without being lost.

#### Acceptance Criteria

1. WHEN a tenant reschedules an appointment, THE Dashboard SHALL update the `starts_at`, `ends_at`, and optionally the `staff_id` fields on the appointment.
2. WHEN an appointment is rescheduled via the Dashboard, THE NotificationService SHALL dispatch a reschedule notification to the client.
3. IF the requested new time slot is unavailable due to a scheduling conflict, THEN THE Dashboard SHALL reject the reschedule and display a conflict error message.
4. IF an appointment has status `completed`, `cancelled`, or `no_show`, THEN THE Dashboard SHALL prevent the reschedule action.

---

### Requirement 7: Mark Appointments as Completed

**User Story:** As a salon owner or staff member, I want to mark an appointment as completed, so that the visit history is accurately recorded.

#### Acceptance Criteria

1. WHEN a tenant marks an appointment as completed, THE Dashboard SHALL update the appointment status to `completed`.
2. WHEN an appointment is marked as completed, THE Dashboard SHALL increment the associated client's `visit_count` and update the client's `last_visit_at` timestamp.
3. IF an appointment is not in `confirmed`, `checked_in`, or `in_progress` status, THEN THE Dashboard SHALL prevent the complete action.

---

### Requirement 8: Tenant Email Notification on New Booking

**User Story:** As a salon owner, I want to receive an email notification when a customer books an appointment via the public booking widget, so that I am immediately aware of new bookings.

#### Acceptance Criteria

1. WHEN a customer successfully completes a booking via the Booking_Widget (i.e., `BookingService::confirmFromHold()` succeeds), THE NotificationService SHALL send a Notification_Email to the Tenant_Email address.
2. WHERE the Tenant_Email is not set on the Salon model, THE NotificationService SHALL fall back to sending the Notification_Email to the Owner_Email address.
3. THE Notification_Email for a new booking SHALL include: customer full name, service name, assigned staff member's full name, appointment date and time, and total price.
4. THE Notification_Email SHALL be dispatched as a queued job to avoid blocking the booking confirmation response.
5. IF the email dispatch fails, THEN THE NotificationService SHALL log the failure and SHALL NOT prevent the booking confirmation from being returned to the customer.

---

### Requirement 9: Tenant Email Notification on Cancellation

**User Story:** As a salon owner, I want to receive an email notification when a customer cancels an appointment, so that I can manage the freed slot.

#### Acceptance Criteria

1. WHEN an appointment is cancelled (via the Booking_Widget public cancel endpoint or the Dashboard), THE NotificationService SHALL send a Notification_Email to the Tenant_Email address.
2. WHERE the Tenant_Email is not set, THE NotificationService SHALL fall back to the Owner_Email address.
3. THE Notification_Email for a cancellation SHALL include: customer full name, service name, original appointment date and time, and the cancellation reason if provided.
4. IF the email dispatch fails, THEN THE NotificationService SHALL log the failure without affecting the cancellation outcome.

---

### Requirement 10: Tenant Email Notification on Reschedule

**User Story:** As a salon owner, I want to receive an email notification when a customer reschedules an appointment, so that I am aware of the updated schedule.

#### Acceptance Criteria

1. WHEN an appointment is rescheduled (via the Booking_Widget or the Dashboard), THE NotificationService SHALL send a Notification_Email to the Tenant_Email address.
2. WHERE the Tenant_Email is not set, THE NotificationService SHALL fall back to the Owner_Email address.
3. THE Notification_Email for a reschedule SHALL include: customer full name, service name, the original appointment date and time, and the new appointment date and time.
4. IF the email dispatch fails, THEN THE NotificationService SHALL log the failure without affecting the reschedule outcome.

---

### Requirement 11: In-App Notification on Booking Events

**User Story:** As a salon owner, I want to see in-app notifications for new bookings, cancellations, and reschedules, so that I am informed even when not checking email.

#### Acceptance Criteria

1. WHEN a new booking is confirmed via the Booking_Widget, THE NotificationService SHALL create a `SalonNotification` record of type `appointment` for the tenant's salon.
2. WHEN an appointment is cancelled, THE NotificationService SHALL create a `SalonNotification` record of type `cancellation` for the tenant's salon.
3. WHEN an appointment is rescheduled, THE NotificationService SHALL create a `SalonNotification` record of type `reschedule` for the tenant's salon.
4. THE SalonNotification record SHALL include the `appointment_id` in its `data` field to allow deep-linking from the notification to the appointment detail view.

---

### Requirement 12: Optional SMS Notification to Tenant

**User Story:** As a salon owner, I want to optionally receive an SMS notification for new bookings, so that I am alerted even when away from email.

#### Acceptance Criteria

1. WHERE SMS notifications are enabled for the tenant (via a salon setting), THE NotificationService SHALL send an SMS to the salon's configured phone number when a new booking is confirmed via the Booking_Widget.
2. THE SMS notification SHALL include: customer full name, service name, and appointment date and time.
3. WHERE SMS notifications are not enabled, THE NotificationService SHALL skip SMS dispatch without error.
4. IF the SMS dispatch fails, THEN THE NotificationService SHALL log the failure without affecting the booking confirmation outcome.
