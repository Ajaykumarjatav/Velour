# Marketing

## Purpose

Email/SMS campaigns, loyalty tiers, referral settings, automation templates, attributed revenue tracking.

## Key files

| Layer | Path |
|-------|------|
| Web | `Web\MarketingController` |
| API | `Api\MarketingController` |
| Services | `MarketingService`, `MarketingNotificationBridge`, `MarketingGrowthDefaults` |
| Catalog | `MarketingAutomationCatalog` |
| Models | `MarketingCampaign`, `MarketingAutomationTemplate`, `MarketingSmsThread`, `LoyaltyTier`, `SalonReferralSetting` |
| Jobs | `SendMarketingCampaign`, `SendMarketingEmail`, `SendSmsNotification`, `SendWhatsAppNotification` |
| Views | `resources/views/marketing/*` |

## Routes (web)

- Campaign resource (index, create, store, show, edit, update, destroy)
- Duplicate, send, schedule, pause
- Loyalty tiers, referral settings, automation templates CRUD
- SMS reply endpoint

Middleware: `subscription:feature:marketing`

## Channels

Templates support channels (migration `2026_05_30_100000_add_channels_to_marketing_automation_templates.php`). Bridge dispatches email vs WhatsApp per configuration.

## Report integration

Marketing report: campaigns in period, open/click rates, attributed bookings/revenue.

## Permissions

`marketing.view`, `marketing.create`, etc.

## Notifications config

Salon-level settings in `SettingsController` notifications tab + `NotificationConfigService`.
