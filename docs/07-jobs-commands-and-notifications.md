# Jobs, commands, and notifications

## Queued jobs (`app/Jobs/`)

| Job | Trigger | Purpose |
|-----|---------|---------|
| `SendAppointmentReminder` | Scheduler / command | Single appointment reminder |
| `SendAppointmentReminders` | Batch wrapper | Multiple reminders |
| `SendMarketingCampaign` | User action / schedule | Dispatch campaign |
| `SendMarketingEmail` | Campaign | One email |
| `SendSmsNotification` | Various | Twilio SMS |
| `SendWhatsAppNotification` | Various | WhatsApp messages |
| `ProcessBirthdayCampaigns` | Scheduled | Birthday automations |
| `ProcessLapsedClientCampaigns` | Scheduled | Win-back automations |
| `ProcessCsvImport` | Client import | Async CSV processing |
| `CheckLowStock` | Scheduled | Low stock alerts |
| `OnboardNewTenant` | Registration | Tenant setup tasks |

Queues are **tenant-aware** by default — ensure `Tenant::current()` is set when dispatching from web requests.

Run worker: `php artisan queue:work`

## Artisan commands (`app/Console/Commands/`)

| Command | Purpose |
|---------|---------|
| `SendAppointmentReminders` | Dispatch reminder jobs |
| `SendBirthdayCampaigns` | Marketing birthdays |
| `SendTrialReminders` | Subscription trials |
| `GenerateMonthlyInvoices` | Platform invoicing |
| `AlertLowStock` | Inventory alerts |
| `PruneAuditLogs` | Retention cleanup |
| `PruneStaleData` | Old data cleanup |
| `PurgeExpiredData` | GDPR / TTL purge |
| `HealthCheck` | Ops health |
| `TenantsCommand` | Tenant maintenance |

Register schedules in `routes/console.php` or `bootstrap/app.php` (Laravel 11).

## Mail (`app/Mail/`)

| Mailable | When |
|----------|------|
| `ClientBookingConfirmationMail` | Client books online |
| `TenantNewBookingMail` | Salon notified of booking |
| `TenantRescheduleMail` | Reschedule notice |
| `PosTransactionInvoiceMail` | POS invoice email |
| `ClientReviewRequestMail` | Review link sent |
| `ClientExportCsvMail` | GDPR/export |
| `WelcomeEmail` | Onboarding |

## Notifications (`app/Notifications/`)

- Auth: `VerifyEmailNotification`, `ResetPasswordNotification`, `TwoFactorCodeNotification`
- Billing: `SubscriptionCreatedNotification`, `TrialEndingNotification`, `PaymentFailedNotification`
- Staff: `StaffInviteCredentialsNotification`
- Admin: `TenantSuspendedNotification`, `PlanOverrideNotification`, support ticket replies

## Events / broadcasting

Pusher configured in `config/broadcasting.php` if real-time features are enabled. Check controllers for `broadcast()` usage.

## Idempotency

API middleware `IdempotencyKey` prevents duplicate POST processing for selected endpoints.
