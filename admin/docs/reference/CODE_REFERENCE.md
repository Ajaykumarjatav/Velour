# Code reference (generated)

Auto-generated listing of **public** and **protected** methods under `app/`.

**Regenerate:** `php scripts/generate-code-reference.php`

**Generated:** 2026-05-30 11:43:34 UTC

---

## `app/Billing/`

### `App\Billing\Plan`

File: [`app/Billing/Plan.php`](../../app/Billing/Plan.php)

| Method |
|--------|
| `stripePriceId()` |
| `allows()` |
| `limit()` |
| `isUnlimited()` |
| `monthlyEquivalentYearly()` |
| `yearlySaving()` |
| `isUpgradeFrom()` |
| `isFree()` |
| `isPaid()` |

## `app/Console/Commands/`

### `App\Console\Commands\AlertLowStock`

File: [`app/Console/Commands/AlertLowStock.php`](../../app/Console/Commands/AlertLowStock.php)

| Method |
|--------|
| `handle()` |

### `App\Console\Commands\GenerateMonthlyInvoices`

File: [`app/Console/Commands/GenerateMonthlyInvoices.php`](../../app/Console/Commands/GenerateMonthlyInvoices.php)

| Method |
|--------|
| `handle()` |

### `App\Console\Commands\HealthCheck`

File: [`app/Console/Commands/HealthCheck.php`](../../app/Console/Commands/HealthCheck.php)

| Method |
|--------|
| `handle()` |

### `App\Console\Commands\PruneAuditLogs`

File: [`app/Console/Commands/PruneAuditLogs.php`](../../app/Console/Commands/PruneAuditLogs.php)

| Method |
|--------|
| `handle()` |

### `App\Console\Commands\PruneStaleData`

File: [`app/Console/Commands/PruneStaleData.php`](../../app/Console/Commands/PruneStaleData.php)

| Method |
|--------|
| `handle()` |

### `App\Console\Commands\PurgeExpiredData`

File: [`app/Console/Commands/PurgeExpiredData.php`](../../app/Console/Commands/PurgeExpiredData.php)

| Method |
|--------|
| `handle()` |

### `App\Console\Commands\SendAppointmentReminders`

File: [`app/Console/Commands/SendAppointmentReminders.php`](../../app/Console/Commands/SendAppointmentReminders.php)

| Method |
|--------|
| `__construct()` |
| `handle()` |

### `App\Console\Commands\SendBirthdayCampaigns`

File: [`app/Console/Commands/SendBirthdayCampaigns.php`](../../app/Console/Commands/SendBirthdayCampaigns.php)

| Method |
|--------|
| `handle()` |

### `App\Console\Commands\SendTrialReminders`

File: [`app/Console/Commands/SendTrialReminders.php`](../../app/Console/Commands/SendTrialReminders.php)

| Method |
|--------|
| `handle()` |

### `App\Console\Commands\TenantsCommand`

File: [`app/Console/Commands/TenantsCommand.php`](../../app/Console/Commands/TenantsCommand.php)

| Method |
|--------|
| `handle()` |

## `app/Exceptions/`

### `App\Exceptions\Handler`

File: [`app/Exceptions/Handler.php`](../../app/Exceptions/Handler.php)

| Method |
|--------|
| `register()` |

## `app/Http/Controllers/Admin/`

### `App\Http\Controllers\Admin\AdminAnalyticsController`

File: [`app/Http/Controllers/Admin/AdminAnalyticsController.php`](../../app/Http/Controllers/Admin/AdminAnalyticsController.php)

| Method |
|--------|
| `index()` |

### `App\Http\Controllers\Admin\AdminBillingController`

File: [`app/Http/Controllers/Admin/AdminBillingController.php`](../../app/Http/Controllers/Admin/AdminBillingController.php)

| Method |
|--------|
| `index()` |
| `webhooks()` |
| `replayWebhook()` |

### `App\Http\Controllers\Admin\AdminFacilityController`

File: [`app/Http/Controllers/Admin/AdminFacilityController.php`](../../app/Http/Controllers/Admin/AdminFacilityController.php)

| Method |
|--------|
| `index()` |

### `App\Http\Controllers\Admin\AdminPlanController`

File: [`app/Http/Controllers/Admin/AdminPlanController.php`](../../app/Http/Controllers/Admin/AdminPlanController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `migratePlan()` |
| `expireOverride()` |
| `bulkMigrate()` |

### `App\Http\Controllers\Admin\AdminRevenueController`

File: [`app/Http/Controllers/Admin/AdminRevenueController.php`](../../app/Http/Controllers/Admin/AdminRevenueController.php)

| Method |
|--------|
| `index()` |
| `export()` |

### `App\Http\Controllers\Admin\AdminSupportController`

File: [`app/Http/Controllers/Admin/AdminSupportController.php`](../../app/Http/Controllers/Admin/AdminSupportController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `show()` |
| `reply()` |
| `assign()` |
| `updateStatus()` |
| `store()` |

### `App\Http\Controllers\Admin\AdminTenantController`

File: [`app/Http/Controllers/Admin/AdminTenantController.php`](../../app/Http/Controllers/Admin/AdminTenantController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `show()` |
| `suspend()` |
| `unsuspend()` |
| `bulkSuspend()` |
| `applyPlanOverride()` |
| `revokeOverride()` |
| `updateDomain()` |
| `export()` |

### `App\Http\Controllers\Admin\AuditLogController`

File: [`app/Http/Controllers/Admin/AuditLogController.php`](../../app/Http/Controllers/Admin/AuditLogController.php)

| Method |
|--------|
| `index()` |
| `show()` |
| `stats()` |
| `export()` |

### `App\Http\Controllers\Admin\SuperAdminController`

File: [`app/Http/Controllers/Admin/SuperAdminController.php`](../../app/Http/Controllers/Admin/SuperAdminController.php)

| Method |
|--------|
| `dashboard()` |
| `tenants()` |
| `showTenant()` |
| `toggleTenantStatus()` |
| `updateTenantDomain()` |
| `users()` |
| `showUser()` |
| `toggleUserStatus()` |
| `impersonate()` |
| `stopImpersonating()` |
| `promoteToSuperAdmin()` |
| `demoteFromSuperAdmin()` |
| `revokeAllTokens()` |

### `App\Http\Controllers\Admin\TenantAdminController`

File: [`app/Http/Controllers/Admin/TenantAdminController.php`](../../app/Http/Controllers/Admin/TenantAdminController.php)

| Method |
|--------|
| `team()` |
| `invite()` |
| `updateMemberRole()` |
| `removeMember()` |
| `subscription()` |
| `transferOwnership()` |

## `app/Http/Controllers/Api/`

### `App\Http\Controllers\Api\AppointmentController`

File: [`app/Http/Controllers/Api/AppointmentController.php`](../../app/Http/Controllers/Api/AppointmentController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `store()` |
| `show()` |
| `update()` |
| `destroy()` |
| `updateStatus()` |
| `checkIn()` |
| `complete()` |
| `cancelAppointment()` |
| `markNoShow()` |
| `reschedule()` |
| `sendReminder()` |
| `export()` |

### `App\Http\Controllers\Api\AuthController`

File: [`app/Http/Controllers/Api/AuthController.php`](../../app/Http/Controllers/Api/AuthController.php)

| Method |
|--------|
| `register()` |
| `login()` |
| `logout()` |
| `me()` |
| `update()` |
| `updateAvatar()` |
| `forgotPassword()` |
| `resetPassword()` |
| `verifyEmail()` |
| `refresh()` |

### `App\Http\Controllers\Api\BookingController`

File: [`app/Http/Controllers/Api/BookingController.php`](../../app/Http/Controllers/Api/BookingController.php)

| Method |
|--------|
| `__construct()` |
| `info()` |
| `services()` |
| `staff()` |
| `availability()` |
| `hold()` |
| `confirm()` |
| `getByRef()` |
| `cancel()` |
| `reschedule()` |

### `App\Http\Controllers\Api\CalendarController`

File: [`app/Http/Controllers/Api/CalendarController.php`](../../app/Http/Controllers/Api/CalendarController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `day()` |
| `week()` |
| `month()` |
| `availableSlots()` |
| `blockTime()` |
| `removeBlock()` |

### `App\Http\Controllers\Api\ClientController`

File: [`app/Http/Controllers/Api/ClientController.php`](../../app/Http/Controllers/Api/ClientController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `store()` |
| `show()` |
| `update()` |
| `destroy()` |
| `appointments()` |
| `transactions()` |
| `notes()` |
| `addNote()` |
| `updateNote()` |
| `deleteNote()` |
| `formula()` |
| `saveFormula()` |
| `sendMessage()` |
| `export()` |
| `import()` |

### `App\Http\Controllers\Api\DashboardController`

File: [`app/Http/Controllers/Api/DashboardController.php`](../../app/Http/Controllers/Api/DashboardController.php)

| Method |
|--------|
| `index()` |
| `kpis()` |
| `today()` |
| `upcoming()` |
| `recentSales()` |
| `alerts()` |

### `App\Http\Controllers\Api\GdprController`

File: [`app/Http/Controllers/Api/GdprController.php`](../../app/Http/Controllers/Api/GdprController.php)

| Method |
|--------|
| `erase()` |
| `export()` |
| `updateConsent()` |
| `auditLog()` |

### `App\Http\Controllers\Api\HealthController`

File: [`app/Http/Controllers/Api/HealthController.php`](../../app/Http/Controllers/Api/HealthController.php)

| Method |
|--------|
| `check()` |

### `App\Http\Controllers\Api\InventoryController`

File: [`app/Http/Controllers/Api/InventoryController.php`](../../app/Http/Controllers/Api/InventoryController.php)

| Method |
|--------|
| `index()` |
| `store()` |
| `show()` |
| `update()` |
| `destroy()` |
| `adjust()` |
| `history()` |
| `lowStock()` |
| `export()` |
| `import()` |
| `generatePO()` |
| `receivePO()` |

### `App\Http\Controllers\Api\MarketingController`

File: [`app/Http/Controllers/Api/MarketingController.php`](../../app/Http/Controllers/Api/MarketingController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `store()` |
| `show()` |
| `update()` |
| `destroy()` |
| `send()` |
| `schedule()` |
| `pause()` |
| `duplicate()` |
| `stats()` |
| `preview()` |
| `segments()` |
| `templates()` |

### `App\Http\Controllers\Api\NotificationController`

File: [`app/Http/Controllers/Api/NotificationController.php`](../../app/Http/Controllers/Api/NotificationController.php)

| Method |
|--------|
| `index()` |
| `markRead()` |
| `markAllRead()` |
| `destroy()` |
| `unreadCount()` |

### `App\Http\Controllers\Api\PosController`

File: [`app/Http/Controllers/Api/PosController.php`](../../app/Http/Controllers/Api/PosController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `store()` |
| `show()` |
| `update()` |
| `destroy()` |
| `refund()` |
| `void()` |
| `receipt()` |
| `createPaymentIntent()` |
| `stripeWebhook()` |
| `todaySummary()` |
| `validateVoucher()` |
| `export()` |

### `App\Http\Controllers\Api\ReportController`

File: [`app/Http/Controllers/Api/ReportController.php`](../../app/Http/Controllers/Api/ReportController.php)

| Method |
|--------|
| `__construct()` |
| `revenue()` |
| `appointments()` |
| `staff()` |
| `clients()` |
| `services()` |
| `inventory()` |
| `marketing()` |
| `payroll()` |
| `export()` |

### `App\Http\Controllers\Api\ReviewController`

File: [`app/Http/Controllers/Api/ReviewController.php`](../../app/Http/Controllers/Api/ReviewController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `show()` |
| `reply()` |
| `destroy()` |
| `requestReview()` |
| `public()` |
| `submit()` |

### `App\Http\Controllers\Api\SalonController`

File: [`app/Http/Controllers/Api/SalonController.php`](../../app/Http/Controllers/Api/SalonController.php)

| Method |
|--------|
| `show()` |
| `update()` |
| `uploadLogo()` |
| `uploadCover()` |
| `settings()` |
| `updateSettings()` |
| `openingHours()` |
| `updateHours()` |

### `App\Http\Controllers\Api\SalonWebsiteController`

File: [`app/Http/Controllers/Api/SalonWebsiteController.php`](../../app/Http/Controllers/Api/SalonWebsiteController.php)

| Method |
|--------|
| `show()` |

### `App\Http\Controllers\Api\ServiceController`

File: [`app/Http/Controllers/Api/ServiceController.php`](../../app/Http/Controllers/Api/ServiceController.php)

| Method |
|--------|
| `index()` |
| `store()` |
| `show()` |
| `update()` |
| `destroy()` |
| `duplicate()` |
| `reorder()` |
| `toggleStatus()` |
| `assignStaff()` |
| `reorderCategories()` |

### `App\Http\Controllers\Api\ShareController`

File: [`app/Http/Controllers/Api/ShareController.php`](../../app/Http/Controllers/Api/ShareController.php)

| Method |
|--------|
| `stats()` |
| `sources()` |
| `trend()` |
| `devices()` |
| `generateQr()` |
| `embedCode()` |
| `checklist()` |
| `customise()` |
| `trackClick()` |
| `trackVisit()` |

### `App\Http\Controllers\Api\StaffController`

File: [`app/Http/Controllers/Api/StaffController.php`](../../app/Http/Controllers/Api/StaffController.php)

| Method |
|--------|
| `index()` |
| `store()` |
| `show()` |
| `update()` |
| `destroy()` |
| `uploadAvatar()` |
| `schedule()` |
| `updateSchedule()` |
| `performance()` |
| `commission()` |
| `sendInvite()` |

## `app/Http/Controllers/Billing/`

### `App\Http\Controllers\Billing\BillingController`

File: [`app/Http/Controllers/Billing/BillingController.php`](../../app/Http/Controllers/Billing/BillingController.php)

| Method |
|--------|
| `plans()` |
| `checkout()` |
| `success()` |
| `showChangePlan()` |
| `changePlan()` |
| `showCancel()` |
| `cancel()` |
| `resume()` |
| `portal()` |
| `dashboard()` |
| `downloadInvoice()` |
| `applyPromo()` |

### `App\Http\Controllers\Billing\WebhookController`

File: [`app/Http/Controllers/Billing/WebhookController.php`](../../app/Http/Controllers/Billing/WebhookController.php)

| Method |
|--------|
| `handle()` |

## `app/Http/Controllers/Tenant/`

### `App\Http\Controllers\Tenant\ActivityLogController`

File: [`app/Http/Controllers/Tenant/ActivityLogController.php`](../../app/Http/Controllers/Tenant/ActivityLogController.php)

| Method |
|--------|
| `index()` |

## `app/Http/Controllers/Web/`

### `App\Http\Controllers\Web\AccountController`

File: [`app/Http/Controllers/Web/AccountController.php`](../../app/Http/Controllers/Web/AccountController.php)

| Method |
|--------|
| `sessions()` |
| `revokeSession()` |
| `revokeAllOtherSessions()` |
| `revokeToken()` |
| `showDelete()` |
| `destroy()` |

### `App\Http\Controllers\Web\AppointmentController`

File: [`app/Http/Controllers/Web/AppointmentController.php`](../../app/Http/Controllers/Web/AppointmentController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `create()` |
| `validateWindow()` |
| `occupiedSlots()` |
| `store()` |
| `show()` |
| `edit()` |
| `update()` |
| `updateStatus()` |
| `confirm()` |
| `cancel()` |
| `reschedule()` |
| `complete()` |
| `destroy()` |

### `App\Http\Controllers\Web\AuthController`

File: [`app/Http/Controllers/Web/AuthController.php`](../../app/Http/Controllers/Web/AuthController.php)

| Method |
|--------|
| `showLogin()` |
| `login()` |
| `showForcePassword()` |
| `forcePasswordUpdate()` |
| `showRegister()` |
| `register()` |
| `logout()` |
| `verificationNotice()` |
| `verifyEmail()` |
| `resendVerification()` |
| `showForgotPassword()` |
| `sendResetLink()` |
| `showResetPassword()` |
| `resetPassword()` |

### `App\Http\Controllers\Web\AvailabilityResourcesController`

File: [`app/Http/Controllers/Web/AvailabilityResourcesController.php`](../../app/Http/Controllers/Web/AvailabilityResourcesController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `toggleStaffDay()` |
| `storeResource()` |
| `updateResource()` |
| `destroyResource()` |
| `storeLeave()` |
| `approveLeave()` |
| `storeAttendance()` |
| `clockInAttendance()` |
| `clockOutAttendance()` |
| `rejectLeave()` |

### `App\Http\Controllers\Web\BookingController`

File: [`app/Http/Controllers/Web/BookingController.php`](../../app/Http/Controllers/Web/BookingController.php)

| Method |
|--------|
| `show()` |

### `App\Http\Controllers\Web\CalendarController`

File: [`app/Http/Controllers/Web/CalendarController.php`](../../app/Http/Controllers/Web/CalendarController.php)

| Method |
|--------|
| `index()` |

### `App\Http\Controllers\Web\ChatbotController`

File: [`app/Http/Controllers/Web/ChatbotController.php`](../../app/Http/Controllers/Web/ChatbotController.php)

| Method |
|--------|
| `message()` |

### `App\Http\Controllers\Web\CheckoutController`

File: [`app/Http/Controllers/Web/CheckoutController.php`](../../app/Http/Controllers/Web/CheckoutController.php)

| Method |
|--------|
| `create()` |
| `success()` |
| `cancel()` |

### `App\Http\Controllers\Web\ClientController`

File: [`app/Http/Controllers/Web/ClientController.php`](../../app/Http/Controllers/Web/ClientController.php)

| Method |
|--------|
| `index()` |
| `sendReviewRequests()` |
| `export()` |
| `import()` |
| `create()` |
| `store()` |
| `show()` |
| `edit()` |
| `update()` |
| `destroy()` |

### `App\Http\Controllers\Web\ClientFormulaController`

File: [`app/Http/Controllers/Web/ClientFormulaController.php`](../../app/Http/Controllers/Web/ClientFormulaController.php)

| Method |
|--------|
| `create()` |
| `store()` |
| `show()` |
| `destroy()` |

### `App\Http\Controllers\Web\ClientNoteController`

File: [`app/Http/Controllers/Web/ClientNoteController.php`](../../app/Http/Controllers/Web/ClientNoteController.php)

| Method |
|--------|
| `store()` |
| `pin()` |
| `destroy()` |

### `App\Http\Controllers\Web\CustomizationController`

File: [`app/Http/Controllers/Web/CustomizationController.php`](../../app/Http/Controllers/Web/CustomizationController.php)

| Method |
|--------|
| `index()` |
| `updateBrand()` |
| `updateOptions()` |
| `updateForms()` |
| `requestFeature()` |

### `App\Http\Controllers\Web\DashboardController`

File: [`app/Http/Controllers/Web/DashboardController.php`](../../app/Http/Controllers/Web/DashboardController.php)

| Method |
|--------|
| `index()` |

### `App\Http\Controllers\Web\FacilityController`

File: [`app/Http/Controllers/Web/FacilityController.php`](../../app/Http/Controllers/Web/FacilityController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `create()` |
| `store()` |
| `show()` |
| `edit()` |
| `update()` |
| `destroy()` |

### `App\Http\Controllers\Web\GoLiveController`

File: [`app/Http/Controllers/Web/GoLiveController.php`](../../app/Http/Controllers/Web/GoLiveController.php)

| Method |
|--------|
| `index()` |
| `uploadPhoto()` |
| `uploadLogo()` |
| `updateSettings()` |
| `deletePhoto()` |

### `App\Http\Controllers\Web\GuideController`

File: [`app/Http/Controllers/Web/GuideController.php`](../../app/Http/Controllers/Web/GuideController.php)

| Method |
|--------|
| `index()` |

### `App\Http\Controllers\Web\HelpController`

File: [`app/Http/Controllers/Web/HelpController.php`](../../app/Http/Controllers/Web/HelpController.php)

| Method |
|--------|
| `index()` |
| `article()` |
| `feedback()` |

### `App\Http\Controllers\Web\InventoryController`

File: [`app/Http/Controllers/Web/InventoryController.php`](../../app/Http/Controllers/Web/InventoryController.php)

| Method |
|--------|
| `index()` |
| `export()` |
| `barcodeLookup()` |
| `reorder()` |
| `create()` |
| `store()` |
| `edit()` |
| `update()` |
| `adjust()` |
| `adjustHub()` |
| `destroy()` |

### `App\Http\Controllers\Web\LegalController`

File: [`app/Http/Controllers/Web/LegalController.php`](../../app/Http/Controllers/Web/LegalController.php)

| Method |
|--------|
| `privacy()` |
| `terms()` |
| `cookies()` |
| `recordConsent()` |

### `App\Http\Controllers\Web\MarketingController`

File: [`app/Http/Controllers/Web/MarketingController.php`](../../app/Http/Controllers/Web/MarketingController.php)

| Method |
|--------|
| `index()` |
| `growth()` |
| `create()` |
| `store()` |
| `show()` |
| `edit()` |
| `update()` |
| `send()` |
| `destroy()` |
| `duplicate()` |
| `storeLoyaltyTier()` |
| `updateLoyaltyTier()` |
| `destroyLoyaltyTier()` |
| `loyaltyTierMembers()` |
| `updateReferralSettings()` |
| `toggleAutomationTemplate()` |
| `updateAutomationTemplate()` |
| `storeSmsReply()` |

### `App\Http\Controllers\Web\MultiLocationController`

File: [`app/Http/Controllers/Web/MultiLocationController.php`](../../app/Http/Controllers/Web/MultiLocationController.php)

| Method |
|--------|
| `index()` |
| `store()` |
| `update()` |
| `destroy()` |
| `switch()` |

### `App\Http\Controllers\Web\NotificationController`

File: [`app/Http/Controllers/Web/NotificationController.php`](../../app/Http/Controllers/Web/NotificationController.php)

| Method |
|--------|
| `index()` |
| `markRead()` |
| `markAllRead()` |
| `dropdown()` |

### `App\Http\Controllers\Web\OnboardingController`

File: [`app/Http/Controllers/Web/OnboardingController.php`](../../app/Http/Controllers/Web/OnboardingController.php)

| Method |
|--------|
| `index()` |
| `step()` |
| `completeStep()` |
| `complete()` |
| `skip()` |

### `App\Http\Controllers\Web\PaymentGatewayController`

File: [`app/Http/Controllers/Web/PaymentGatewayController.php`](../../app/Http/Controllers/Web/PaymentGatewayController.php)

| Method |
|--------|
| `edit()` |
| `update()` |
| `showCharge()` |
| `charge()` |

### `App\Http\Controllers\Web\PosController`

File: [`app/Http/Controllers/Web/PosController.php`](../../app/Http/Controllers/Web/PosController.php)

| Method |
|--------|
| `index()` |
| `create()` |
| `store()` |
| `sendInvoiceEmail()` |
| `show()` |
| `invoicePdf()` |
| `invoicePdfSigned()` |

### `App\Http\Controllers\Web\RelationQuickCreateController`

File: [`app/Http/Controllers/Web/RelationQuickCreateController.php`](../../app/Http/Controllers/Web/RelationQuickCreateController.php)

| Method |
|--------|
| `storeClient()` |
| `storeStaff()` |
| `storeInventoryCategory()` |
| `storeService()` |

### `App\Http\Controllers\Web\ReportController`

File: [`app/Http/Controllers/Web/ReportController.php`](../../app/Http/Controllers/Web/ReportController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `analytics()` |
| `show()` |
| `exportRevenue()` |

### `App\Http\Controllers\Web\ReviewController`

File: [`app/Http/Controllers/Web/ReviewController.php`](../../app/Http/Controllers/Web/ReviewController.php)

| Method |
|--------|
| `index()` |
| `reply()` |
| `publicForm()` |
| `submitPublicForm()` |

### `App\Http\Controllers\Web\SalonActionItemController`

File: [`app/Http/Controllers/Web/SalonActionItemController.php`](../../app/Http/Controllers/Web/SalonActionItemController.php)

| Method |
|--------|
| `store()` |
| `update()` |

### `App\Http\Controllers\Web\SecuritySupportController`

File: [`app/Http/Controllers/Web/SecuritySupportController.php`](../../app/Http/Controllers/Web/SecuritySupportController.php)

| Method |
|--------|
| `index()` |
| `updateSecurity()` |

### `App\Http\Controllers\Web\ServiceCategoryController`

File: [`app/Http/Controllers/Web/ServiceCategoryController.php`](../../app/Http/Controllers/Web/ServiceCategoryController.php)

| Method |
|--------|
| `index()` |
| `store()` |
| `update()` |
| `destroy()` |

### `App\Http\Controllers\Web\ServiceController`

File: [`app/Http/Controllers/Web/ServiceController.php`](../../app/Http/Controllers/Web/ServiceController.php)

| Method |
|--------|
| `index()` |
| `create()` |
| `store()` |
| `edit()` |
| `update()` |
| `destroy()` |
| `updateVariants()` |
| `updatePricingRules()` |

### `App\Http\Controllers\Web\ServicePackageController`

File: [`app/Http/Controllers/Web/ServicePackageController.php`](../../app/Http/Controllers/Web/ServicePackageController.php)

| Method |
|--------|
| `index()` |
| `create()` |
| `store()` |
| `edit()` |
| `update()` |
| `destroy()` |

### `App\Http\Controllers\Web\SettingsController`

File: [`app/Http/Controllers/Web/SettingsController.php`](../../app/Http/Controllers/Web/SettingsController.php)

| Method |
|--------|
| `index()` |
| `updateSalon()` |
| `updateBooking()` |
| `updateBufferRules()` |
| `updateServices()` |
| `updateHours()` |
| `updateNotifications()` |
| `updateProfile()` |
| `updateTeamMembers()` |
| `updatePassword()` |
| `updateSocialLinks()` |

### `App\Http\Controllers\Web\SetupProgressController`

File: [`app/Http/Controllers/Web/SetupProgressController.php`](../../app/Http/Controllers/Web/SetupProgressController.php)

| Method |
|--------|
| `index()` |

### `App\Http\Controllers\Web\StaffController`

File: [`app/Http/Controllers/Web/StaffController.php`](../../app/Http/Controllers/Web/StaffController.php)

| Method |
|--------|
| `__construct()` |
| `index()` |
| `updateWeeklySchedule()` |
| `updateBaseSalary()` |
| `exportPayroll()` |
| `create()` |
| `store()` |
| `show()` |
| `edit()` |
| `update()` |
| `destroy()` |

### `App\Http\Controllers\Web\StorefrontController`

File: [`app/Http/Controllers/Web/StorefrontController.php`](../../app/Http/Controllers/Web/StorefrontController.php)

| Method |
|--------|
| `show()` |

### `App\Http\Controllers\Web\TaskController`

File: [`app/Http/Controllers/Web/TaskController.php`](../../app/Http/Controllers/Web/TaskController.php)

| Method |
|--------|
| `index()` |
| `update()` |
| `destroy()` |

### `App\Http\Controllers\Web\TenantLookupController`

File: [`app/Http/Controllers/Web/TenantLookupController.php`](../../app/Http/Controllers/Web/TenantLookupController.php)

| Method |
|--------|
| `clients()` |
| `staff()` |

### `App\Http\Controllers\Web\TwoFactorController`

File: [`app/Http/Controllers/Web/TwoFactorController.php`](../../app/Http/Controllers/Web/TwoFactorController.php)

| Method |
|--------|
| `showSetup()` |
| `setupTotp()` |
| `confirmTotp()` |
| `setupEmail()` |
| `disable()` |
| `showRecovery()` |
| `regenerateCodes()` |
| `showChallenge()` |
| `challenge()` |
| `resendCode()` |
| `recovery()` |

### `App\Http\Controllers\Web\VoucherController`

File: [`app/Http/Controllers/Web/VoucherController.php`](../../app/Http/Controllers/Web/VoucherController.php)

| Method |
|--------|
| `index()` |
| `create()` |
| `store()` |
| `show()` |
| `edit()` |
| `update()` |
| `toggle()` |
| `destroy()` |

### `App\Http\Controllers\Web\WebsiteSeoController`

File: [`app/Http/Controllers/Web/WebsiteSeoController.php`](../../app/Http/Controllers/Web/WebsiteSeoController.php)

| Method |
|--------|
| `index()` |
| `publish()` |

## `app/Http/Controllers/Web/Concerns/`

### `App\Http\Controllers\Web\Concerns\ResolvesActiveSalon`

File: [`app/Http/Controllers/Web/Concerns/ResolvesActiveSalon.php`](../../app/Http/Controllers/Web/Concerns/ResolvesActiveSalon.php)

| Method |
|--------|
| `salonScoped()` |
| `activeSalon()` |

## `app/Http/Middleware/`

### `App\Http\Middleware\AccountLockout`

File: [`app/Http/Middleware/AccountLockout.php`](../../app/Http/Middleware/AccountLockout.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\AuditRequestMiddleware`

File: [`app/Http/Middleware/AuditRequestMiddleware.php`](../../app/Http/Middleware/AuditRequestMiddleware.php)

| Method |
|--------|
| `__construct()` |
| `handle()` |
| `shouldLog()` |
| `logRequest()` |
| `categorise()` |
| `matchesPattern()` |
| `sanitiseBody()` |

### `App\Http\Middleware\CheckPlanLimits`

File: [`app/Http/Middleware/CheckPlanLimits.php`](../../app/Http/Middleware/CheckPlanLimits.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\CheckSubscription`

File: [`app/Http/Middleware/CheckSubscription.php`](../../app/Http/Middleware/CheckSubscription.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\EnsureEmailIsVerified`

File: [`app/Http/Middleware/EnsureEmailIsVerified.php`](../../app/Http/Middleware/EnsureEmailIsVerified.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\EnsurePasswordChange`

File: [`app/Http/Middleware/EnsurePasswordChange.php`](../../app/Http/Middleware/EnsurePasswordChange.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\EnsureSalonAccess`

File: [`app/Http/Middleware/EnsureSalonAccess.php`](../../app/Http/Middleware/EnsureSalonAccess.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\EnsureSalonProfileComplete`

File: [`app/Http/Middleware/EnsureSalonProfileComplete.php`](../../app/Http/Middleware/EnsureSalonProfileComplete.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\ForceJsonResponse`

File: [`app/Http/Middleware/ForceJsonResponse.php`](../../app/Http/Middleware/ForceJsonResponse.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\IdempotencyKey`

File: [`app/Http/Middleware/IdempotencyKey.php`](../../app/Http/Middleware/IdempotencyKey.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\InitializeTenancyFromDomain`

File: [`app/Http/Middleware/InitializeTenancyFromDomain.php`](../../app/Http/Middleware/InitializeTenancyFromDomain.php)

| Method |
|--------|
| `__construct()` |
| `handle()` |

### `App\Http\Middleware\LogSlowQueries`

File: [`app/Http/Middleware/LogSlowQueries.php`](../../app/Http/Middleware/LogSlowQueries.php)

| Method |
|--------|
| `__construct()` |
| `handle()` |

### `App\Http\Middleware\LogSlowRequests`

File: [`app/Http/Middleware/LogSlowRequests.php`](../../app/Http/Middleware/LogSlowRequests.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\PreventCrossTenantAccess`

File: [`app/Http/Middleware/PreventCrossTenantAccess.php`](../../app/Http/Middleware/PreventCrossTenantAccess.php)

| Method |
|--------|
| `__construct()` |
| `handle()` |

### `App\Http\Middleware\RedirectUnlessSubscriptionsEnabled`

File: [`app/Http/Middleware/RedirectUnlessSubscriptionsEnabled.php`](../../app/Http/Middleware/RedirectUnlessSubscriptionsEnabled.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\RequireTwoFactor`

File: [`app/Http/Middleware/RequireTwoFactor.php`](../../app/Http/Middleware/RequireTwoFactor.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\SanitizeInput`

File: [`app/Http/Middleware/SanitizeInput.php`](../../app/Http/Middleware/SanitizeInput.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\SecurityHeaders`

File: [`app/Http/Middleware/SecurityHeaders.php`](../../app/Http/Middleware/SecurityHeaders.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\SuperAdminMiddleware`

File: [`app/Http/Middleware/SuperAdminMiddleware.php`](../../app/Http/Middleware/SuperAdminMiddleware.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\TenantAdminMiddleware`

File: [`app/Http/Middleware/TenantAdminMiddleware.php`](../../app/Http/Middleware/TenantAdminMiddleware.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\TenantAwareThrottle`

File: [`app/Http/Middleware/TenantAwareThrottle.php`](../../app/Http/Middleware/TenantAwareThrottle.php)

| Method |
|--------|
| `__construct()` |
| `handle()` |
| `addHeaders()` |
| `tooManyAttempts()` |

### `App\Http\Middleware\TenantMiddleware`

File: [`app/Http/Middleware/TenantMiddleware.php`](../../app/Http/Middleware/TenantMiddleware.php)

| Method |
|--------|
| `handle()` |

### `App\Http\Middleware\ValidateApiVersion`

File: [`app/Http/Middleware/ValidateApiVersion.php`](../../app/Http/Middleware/ValidateApiVersion.php)

| Method |
|--------|
| `handle()` |

## `app/Http/Requests/Appointment/`

### `App\Http\Requests\Appointment\StoreAppointmentRequest`

File: [`app/Http/Requests/Appointment/StoreAppointmentRequest.php`](../../app/Http/Requests/Appointment/StoreAppointmentRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

### `App\Http\Requests\Appointment\UpdateAppointmentRequest`

File: [`app/Http/Requests/Appointment/UpdateAppointmentRequest.php`](../../app/Http/Requests/Appointment/UpdateAppointmentRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

## `app/Http/Requests/Auth/`

### `App\Http\Requests\Auth\LoginRequest`

File: [`app/Http/Requests/Auth/LoginRequest.php`](../../app/Http/Requests/Auth/LoginRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

### `App\Http\Requests\Auth\RegisterRequest`

File: [`app/Http/Requests/Auth/RegisterRequest.php`](../../app/Http/Requests/Auth/RegisterRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

## `app/Http/Requests/Booking/`

### `App\Http\Requests\Booking\ConfirmBookingRequest`

File: [`app/Http/Requests/Booking/ConfirmBookingRequest.php`](../../app/Http/Requests/Booking/ConfirmBookingRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

## `app/Http/Requests/Client/`

### `App\Http\Requests\Client\StoreClientRequest`

File: [`app/Http/Requests/Client/StoreClientRequest.php`](../../app/Http/Requests/Client/StoreClientRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

### `App\Http\Requests\Client\UpdateClientRequest`

File: [`app/Http/Requests/Client/UpdateClientRequest.php`](../../app/Http/Requests/Client/UpdateClientRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

## `app/Http/Requests/Inventory/`

### `App\Http\Requests\Inventory\StoreInventoryRequest`

File: [`app/Http/Requests/Inventory/StoreInventoryRequest.php`](../../app/Http/Requests/Inventory/StoreInventoryRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

### `App\Http\Requests\Inventory\UpdateInventoryRequest`

File: [`app/Http/Requests/Inventory/UpdateInventoryRequest.php`](../../app/Http/Requests/Inventory/UpdateInventoryRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

## `app/Http/Requests/Marketing/`

### `App\Http\Requests\Marketing\StoreCampaignRequest`

File: [`app/Http/Requests/Marketing/StoreCampaignRequest.php`](../../app/Http/Requests/Marketing/StoreCampaignRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

### `App\Http\Requests\Marketing\UpdateCampaignRequest`

File: [`app/Http/Requests/Marketing/UpdateCampaignRequest.php`](../../app/Http/Requests/Marketing/UpdateCampaignRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

## `app/Http/Requests/POS/`

### `App\Http\Requests\POS\StorePosRequest`

File: [`app/Http/Requests/POS/StorePosRequest.php`](../../app/Http/Requests/POS/StorePosRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

## `app/Http/Requests/Service/`

### `App\Http\Requests\Service\StoreServiceRequest`

File: [`app/Http/Requests/Service/StoreServiceRequest.php`](../../app/Http/Requests/Service/StoreServiceRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

### `App\Http\Requests\Service\UpdateServiceRequest`

File: [`app/Http/Requests/Service/UpdateServiceRequest.php`](../../app/Http/Requests/Service/UpdateServiceRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

## `app/Http/Requests/Settings/`

### `App\Http\Requests\Settings\UpdateSalonSettingsRequest`

File: [`app/Http/Requests/Settings/UpdateSalonSettingsRequest.php`](../../app/Http/Requests/Settings/UpdateSalonSettingsRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |
| `messages()` |

## `app/Http/Requests/Staff/`

### `App\Http\Requests\Staff\StoreStaffRequest`

File: [`app/Http/Requests/Staff/StoreStaffRequest.php`](../../app/Http/Requests/Staff/StoreStaffRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

### `App\Http\Requests\Staff\UpdateStaffRequest`

File: [`app/Http/Requests/Staff/UpdateStaffRequest.php`](../../app/Http/Requests/Staff/UpdateStaffRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |

## `app/Http/Requests/Team/`

### `App\Http\Requests\Team\InviteTeamMemberRequest`

File: [`app/Http/Requests/Team/InviteTeamMemberRequest.php`](../../app/Http/Requests/Team/InviteTeamMemberRequest.php)

| Method |
|--------|
| `authorize()` |
| `rules()` |
| `withValidator()` |
| `messages()` |

## `app/Http/Resources/`

### `App\Http\Resources\AppointmentResource`

File: [`app/Http/Resources/AppointmentResource.php`](../../app/Http/Resources/AppointmentResource.php)

| Method |
|--------|
| `toArray()` |

### `App\Http\Resources\AppointmentServiceResource`

File: [`app/Http/Resources/AppointmentServiceResource.php`](../../app/Http/Resources/AppointmentServiceResource.php)

| Method |
|--------|
| `toArray()` |

### `App\Http\Resources\ClientNoteResource`

File: [`app/Http/Resources/ClientNoteResource.php`](../../app/Http/Resources/ClientNoteResource.php)

| Method |
|--------|
| `toArray()` |

### `App\Http\Resources\ClientResource`

File: [`app/Http/Resources/ClientResource.php`](../../app/Http/Resources/ClientResource.php)

| Method |
|--------|
| `toArray()` |

### `App\Http\Resources\PosTransactionItemResource`

File: [`app/Http/Resources/PosTransactionItemResource.php`](../../app/Http/Resources/PosTransactionItemResource.php)

| Method |
|--------|
| `toArray()` |

### `App\Http\Resources\PosTransactionResource`

File: [`app/Http/Resources/PosTransactionResource.php`](../../app/Http/Resources/PosTransactionResource.php)

| Method |
|--------|
| `toArray()` |

### `App\Http\Resources\ServiceCategoryResource`

File: [`app/Http/Resources/ServiceCategoryResource.php`](../../app/Http/Resources/ServiceCategoryResource.php)

| Method |
|--------|
| `toArray()` |

### `App\Http\Resources\ServiceResource`

File: [`app/Http/Resources/ServiceResource.php`](../../app/Http/Resources/ServiceResource.php)

| Method |
|--------|
| `toArray()` |

### `App\Http\Resources\StaffResource`

File: [`app/Http/Resources/StaffResource.php`](../../app/Http/Resources/StaffResource.php)

| Method |
|--------|
| `toArray()` |

## `app/Http/Responses/`

### `App\Http\Responses\ApiResponse`

File: [`app/Http/Responses/ApiResponse.php`](../../app/Http/Responses/ApiResponse.php)

| Method |
|--------|
| `success()` |
| `created()` |
| `noContent()` |
| `error()` |
| `paginated()` |

## `app/Jobs/`

### `App\Jobs\CheckLowStock`

File: [`app/Jobs/CheckLowStock.php`](../../app/Jobs/CheckLowStock.php)

| Method |
|--------|
| `handle()` |

### `App\Jobs\OnboardNewTenant`

File: [`app/Jobs/OnboardNewTenant.php`](../../app/Jobs/OnboardNewTenant.php)

| Method |
|--------|
| `__construct()` |
| `handle()` |

### `App\Jobs\ProcessBirthdayCampaigns`

File: [`app/Jobs/ProcessBirthdayCampaigns.php`](../../app/Jobs/ProcessBirthdayCampaigns.php)

| Method |
|--------|
| `handle()` |

### `App\Jobs\ProcessCsvImport`

File: [`app/Jobs/ProcessCsvImport.php`](../../app/Jobs/ProcessCsvImport.php)

| Method |
|--------|
| `__construct()` |
| `handle()` |

### `App\Jobs\ProcessLapsedClientCampaigns`

File: [`app/Jobs/ProcessLapsedClientCampaigns.php`](../../app/Jobs/ProcessLapsedClientCampaigns.php)

| Method |
|--------|
| `handle()` |

### `App\Jobs\SendAppointmentReminder`

File: [`app/Jobs/SendAppointmentReminder.php`](../../app/Jobs/SendAppointmentReminder.php)

| Method |
|--------|
| `__construct()` |
| `handle()` |
| `failed()` |

### `App\Jobs\SendAppointmentReminders`

File: [`app/Jobs/SendAppointmentReminders.php`](../../app/Jobs/SendAppointmentReminders.php)

| Method |
|--------|
| `handle()` |

### `App\Jobs\SendMarketingCampaign`

File: [`app/Jobs/SendMarketingCampaign.php`](../../app/Jobs/SendMarketingCampaign.php)

| Method |
|--------|
| `__construct()` |
| `middleware()` |
| `handle()` |

### `App\Jobs\SendMarketingEmail`

File: [`app/Jobs/SendMarketingEmail.php`](../../app/Jobs/SendMarketingEmail.php)

| Method |
|--------|
| `__construct()` |
| `handle()` |
| `failed()` |

### `App\Jobs\SendSmsNotification`

File: [`app/Jobs/SendSmsNotification.php`](../../app/Jobs/SendSmsNotification.php)

| Method |
|--------|
| `__construct()` |
| `handle()` |
| `failed()` |

### `App\Jobs\SendWhatsAppNotification`

File: [`app/Jobs/SendWhatsAppNotification.php`](../../app/Jobs/SendWhatsAppNotification.php)

| Method |
|--------|
| `__construct()` |
| `handle()` |
| `failed()` |

## `app/Logging/`

### `App\Logging\AddContextProcessor`

File: [`app/Logging/AddContextProcessor.php`](../../app/Logging/AddContextProcessor.php)

| Method |
|--------|
| `__invoke()` |
| `__invoke()` |

## `app/Mail/`

### `App\Mail\ClientBookingConfirmationMail`

File: [`app/Mail/ClientBookingConfirmationMail.php`](../../app/Mail/ClientBookingConfirmationMail.php)

| Method |
|--------|
| `__construct()` |
| `envelope()` |
| `content()` |

### `App\Mail\ClientExportCsvMail`

File: [`app/Mail/ClientExportCsvMail.php`](../../app/Mail/ClientExportCsvMail.php)

| Method |
|--------|
| `__construct()` |
| `envelope()` |
| `content()` |
| `attachments()` |

### `App\Mail\ClientReviewRequestMail`

File: [`app/Mail/ClientReviewRequestMail.php`](../../app/Mail/ClientReviewRequestMail.php)

| Method |
|--------|
| `__construct()` |
| `envelope()` |
| `content()` |

### `App\Mail\PosTransactionInvoiceMail`

File: [`app/Mail/PosTransactionInvoiceMail.php`](../../app/Mail/PosTransactionInvoiceMail.php)

| Method |
|--------|
| `__construct()` |
| `envelope()` |
| `content()` |

### `App\Mail\TenantCancellationMail`

File: [`app/Mail/TenantCancellationMail.php`](../../app/Mail/TenantCancellationMail.php)

| Method |
|--------|
| `__construct()` |
| `envelope()` |
| `content()` |

### `App\Mail\TenantNewBookingMail`

File: [`app/Mail/TenantNewBookingMail.php`](../../app/Mail/TenantNewBookingMail.php)

| Method |
|--------|
| `__construct()` |
| `envelope()` |
| `content()` |

### `App\Mail\TenantRescheduleMail`

File: [`app/Mail/TenantRescheduleMail.php`](../../app/Mail/TenantRescheduleMail.php)

| Method |
|--------|
| `__construct()` |
| `envelope()` |
| `content()` |

### `App\Mail\WelcomeEmail`

File: [`app/Mail/WelcomeEmail.php`](../../app/Mail/WelcomeEmail.php)

| Method |
|--------|
| `__construct()` |
| `envelope()` |
| `content()` |

## `app/Models/`

### `App\Models\Appointment`

File: [`app/Models/Appointment.php`](../../app/Models/Appointment.php)

| Method |
|--------|
| `salon()` |
| `client()` |
| `staff()` |
| `services()` |
| `transaction()` |
| `review()` |
| `scopeUpcoming()` |
| `scopeToday()` |
| `scopeCompleted()` |
| `getBalanceDueAttribute()` |

### `App\Models\AppointmentService`

File: [`app/Models/AppointmentService.php`](../../app/Models/AppointmentService.php)

| Method |
|--------|
| `appointment()` |
| `service()` |

### `App\Models\AuditLog`

File: [`app/Models/AuditLog.php`](../../app/Models/AuditLog.php)

| Method |
|--------|
| `user()` |
| `salon()` |
| `subject()` |
| `scopeForTenant()` |
| `scopeForUser()` |
| `scopeCategory()` |
| `scopeSeverity()` |
| `scopeCritical()` |
| `scopeWarning()` |
| `scopeRecent()` |
| `scopeSearch()` |
| `severityColor()` |
| `categoryIcon()` |

### `App\Models\BookingSource`

File: [`app/Models/BookingSource.php`](../../app/Models/BookingSource.php)

| Method |
|--------|
| `salon()` |
| `appointment()` |

### `App\Models\BusinessType`

File: [`app/Models/BusinessType.php`](../../app/Models/BusinessType.php)

| Method |
|--------|
| `salons()` |
| `services()` |
| `serviceCategories()` |

### `App\Models\Client`

File: [`app/Models/Client.php`](../../app/Models/Client.php)

| Method |
|--------|
| `extraAuditExcludeFields()` |
| `getFullNameAttribute()` |
| `salon()` |
| `loyaltyTier()` |
| `referredBy()` |
| `preferredStaff()` |
| `notes()` |
| `formulas()` |
| `appointments()` |
| `transactions()` |
| `reviews()` |
| `scopeVip()` |
| `scopeLapsed()` |
| `scopeNew()` |
| `scopeEngagementActive()` |
| `scopeEngagementInactive()` |
| `isEngagementActive()` |
| `engagementStatusLabel()` |
| `recalculateTotalSpent()` |

### `App\Models\ClientFormula`

File: [`app/Models/ClientFormula.php`](../../app/Models/ClientFormula.php)

| Method |
|--------|
| `client()` |
| `staff()` |

### `App\Models\ClientNote`

File: [`app/Models/ClientNote.php`](../../app/Models/ClientNote.php)

| Method |
|--------|
| `client()` |
| `staff()` |

### `App\Models\DynamicPricingRule`

File: [`app/Models/DynamicPricingRule.php`](../../app/Models/DynamicPricingRule.php)

| Method |
|--------|
| `salon()` |

### `App\Models\Facility`

File: [`app/Models/Facility.php`](../../app/Models/Facility.php)

| Method |
|--------|
| `casts()` |
| `salon()` |
| `occupancyPercent()` |
| `isOperational()` |

### `App\Models\InventoryAdjustment`

File: [`app/Models/InventoryAdjustment.php`](../../app/Models/InventoryAdjustment.php)

| Method |
|--------|
| `item()` |
| `staff()` |

### `App\Models\InventoryCategory`

File: [`app/Models/InventoryCategory.php`](../../app/Models/InventoryCategory.php)

| Method |
|--------|
| `salon()` |
| `items()` |

### `App\Models\InventoryItem`

File: [`app/Models/InventoryItem.php`](../../app/Models/InventoryItem.php)

| Method |
|--------|
| `salon()` |
| `category()` |
| `adjustments()` |
| `getIsLowStockAttribute()` |
| `getMarginPercentAttribute()` |
| `getLowStockThresholdAttribute()` |
| `setLowStockThresholdAttribute()` |
| `scopeLowStock()` |
| `scopeRetail()` |
| `scopeWhereStockTierLow()` |
| `scopeWhereStockTierCritical()` |
| `getQuantityAttribute()` |
| `setQuantityAttribute()` |
| `scopePro()` |
| `stockStatusLevel()` |

### `App\Models\Invoice`

File: [`app/Models/Invoice.php`](../../app/Models/Invoice.php)

| Method |
|--------|
| `salon()` |
| `client()` |
| `transaction()` |

### `App\Models\LinkVisit`

File: [`app/Models/LinkVisit.php`](../../app/Models/LinkVisit.php)

| Method |
|--------|
| `salon()` |
| `scopeThisMonth()` |
| `scopeLast30Days()` |
| `scopeConverted()` |

### `App\Models\LoyaltyTier`

File: [`app/Models/LoyaltyTier.php`](../../app/Models/LoyaltyTier.php)

| Method |
|--------|
| `salon()` |
| `clients()` |

### `App\Models\MarketingAutomationTemplate`

File: [`app/Models/MarketingAutomationTemplate.php`](../../app/Models/MarketingAutomationTemplate.php)

| Method |
|--------|
| `refreshChannelsLabel()` |
| `salon()` |

### `App\Models\MarketingCampaign`

File: [`app/Models/MarketingCampaign.php`](../../app/Models/MarketingCampaign.php)

| Method |
|--------|
| `salon()` |
| `creator()` |
| `getOpenRateAttribute()` |
| `getClickRateAttribute()` |
| `getConversionRateAttribute()` |
| `scopeSent()` |
| `scopeScheduled()` |

### `App\Models\MarketingSmsMessage`

File: [`app/Models/MarketingSmsMessage.php`](../../app/Models/MarketingSmsMessage.php)

| Method |
|--------|
| `thread()` |

### `App\Models\MarketingSmsThread`

File: [`app/Models/MarketingSmsThread.php`](../../app/Models/MarketingSmsThread.php)

| Method |
|--------|
| `salon()` |
| `client()` |
| `messages()` |

### `App\Models\PaymentGateway`

File: [`app/Models/PaymentGateway.php`](../../app/Models/PaymentGateway.php)

| Method |
|--------|
| `isConfigured()` |
| `salon()` |
| `isStripe()` |

### `App\Models\PosTransaction`

File: [`app/Models/PosTransaction.php`](../../app/Models/PosTransaction.php)

| Method |
|--------|
| `salon()` |
| `client()` |
| `staff()` |
| `appointment()` |
| `items()` |
| `invoice()` |
| `resolveRouteBinding()` |
| `scopeCompleted()` |
| `scopeForPeriod()` |
| `scopeRecognizedBetweenUtc()` |
| `getNetTotalAttribute()` |

### `App\Models\PosTransactionItem`

File: [`app/Models/PosTransactionItem.php`](../../app/Models/PosTransactionItem.php)

| Method |
|--------|
| `transaction()` |
| `staff()` |
| `itemable()` |

### `App\Models\PurchaseOrder`

File: [`app/Models/PurchaseOrder.php`](../../app/Models/PurchaseOrder.php)

| Method |
|--------|
| `salon()` |
| `creator()` |
| `items()` |

### `App\Models\PurchaseOrderItem`

File: [`app/Models/PurchaseOrderItem.php`](../../app/Models/PurchaseOrderItem.php)

| Method |
|--------|
| `purchaseOrder()` |
| `inventoryItem()` |

### `App\Models\Review`

File: [`app/Models/Review.php`](../../app/Models/Review.php)

| Method |
|--------|
| `salon()` |
| `client()` |
| `appointment()` |
| `staff()` |
| `service()` |
| `reviewLink()` |
| `scopePublic()` |
| `scopeVerified()` |
| `scopeBySource()` |

### `App\Models\ReviewLink`

File: [`app/Models/ReviewLink.php`](../../app/Models/ReviewLink.php)

| Method |
|--------|
| `salon()` |
| `staff()` |

### `App\Models\Salon`

File: [`app/Models/Salon.php`](../../app/Models/Salon.php)

| Method |
|--------|
| `owner()` |
| `businessType()` |
| `businessTypes()` |
| `staff()` |
| `serviceCategories()` |
| `services()` |
| `servicePackages()` |
| `inventoryCategories()` |
| `inventoryItems()` |
| `paymentGateway()` |
| `clients()` |
| `appointments()` |
| `transactions()` |
| `campaigns()` |
| `loyaltyTiers()` |
| `referralSetting()` |
| `marketingAutomationTemplates()` |
| `marketingSmsThreads()` |
| `reviews()` |
| `notifications()` |
| `settings()` |
| `dynamicPricingRules()` |
| `salonResources()` |
| `bufferRule()` |
| `vouchers()` |
| `getSetting()` |
| `openingHoursForWeekdayKey()` |

### `App\Models\SalonActionItem`

File: [`app/Models/SalonActionItem.php`](../../app/Models/SalonActionItem.php)

| Method |
|--------|
| `casts()` |
| `salon()` |
| `staff()` |
| `assignedStaff()` |
| `isOpen()` |
| `isActiveOnBoard()` |

### `App\Models\SalonBufferRule`

File: [`app/Models/SalonBufferRule.php`](../../app/Models/SalonBufferRule.php)

| Method |
|--------|
| `casts()` |
| `salon()` |

### `App\Models\SalonNotification`

File: [`app/Models/SalonNotification.php`](../../app/Models/SalonNotification.php)

| Method |
|--------|
| `salon()` |
| `staff()` |
| `scopeUnread()` |
| `markRead()` |

### `App\Models\SalonPhoto`

File: [`app/Models/SalonPhoto.php`](../../app/Models/SalonPhoto.php)

| Method |
|--------|
| `salon()` |
| `getUrlAttribute()` |

### `App\Models\SalonReferralSetting`

File: [`app/Models/SalonReferralSetting.php`](../../app/Models/SalonReferralSetting.php)

| Method |
|--------|
| `salon()` |

### `App\Models\SalonResource`

File: [`app/Models/SalonResource.php`](../../app/Models/SalonResource.php)

| Method |
|--------|
| `casts()` |
| `salon()` |

### `App\Models\SalonSetting`

File: [`app/Models/SalonSetting.php`](../../app/Models/SalonSetting.php)

| Method |
|--------|
| `salon()` |
| `getCastedValueAttribute()` |

### `App\Models\Service`

File: [`app/Models/Service.php`](../../app/Models/Service.php)

| Method |
|--------|
| `salon()` |
| `businessType()` |
| `category()` |
| `staff()` |
| `packages()` |
| `appointmentServices()` |
| `scopeActive()` |
| `scopeOnline()` |
| `scopeEligibleForPublicBooking()` |
| `isHomeService()` |
| `normalizedVariants()` |
| `normalizedAddons()` |
| `computeAppointmentLine()` |
| `getImageUrlAttribute()` |
| `getIsActiveAttribute()` |
| `normalizedAllowedRoles()` |
| `hasExplicitStaffAssignees()` |
| `allowsStaffRole()` |
| `allowsStaffMember()` |
| `setIsActiveAttribute()` |

### `App\Models\ServiceCategory`

File: [`app/Models/ServiceCategory.php`](../../app/Models/ServiceCategory.php)

| Method |
|--------|
| `salon()` |
| `businessType()` |
| `services()` |
| `resolveRouteBinding()` |

### `App\Models\ServicePackage`

File: [`app/Models/ServicePackage.php`](../../app/Models/ServicePackage.php)

| Method |
|--------|
| `services()` |
| `scopeActive()` |
| `scopeOnline()` |
| `orderedServiceIds()` |
| `totalSpanMinutesForAppointment()` |
| `componentsTotalPrice()` |
| `normalizedAllowedRoles()` |
| `allowsStaffRole()` |
| `getIsActiveAttribute()` |

### `App\Models\Staff`

File: [`app/Models/Staff.php`](../../app/Models/Staff.php)

| Method |
|--------|
| `getNameAttribute()` |
| `scopeWithName()` |
| `setNameAttribute()` |
| `getFullNameAttribute()` |
| `getDisplayInitialsAttribute()` |
| `setAvatarAttribute()` |
| `getAvatarUrlAttribute()` |
| `salon()` |
| `user()` |
| `resolveRouteBinding()` |
| `services()` |
| `appointments()` |
| `leaveRequests()` |
| `attendanceRecords()` |
| `reviews()` |
| `adjustments()` |

### `App\Models\StaffAttendanceRecord`

File: [`app/Models/StaffAttendanceRecord.php`](../../app/Models/StaffAttendanceRecord.php)

| Method |
|--------|
| `casts()` |
| `salon()` |
| `staff()` |
| `recordedBy()` |

### `App\Models\StaffLeaveRequest`

File: [`app/Models/StaffLeaveRequest.php`](../../app/Models/StaffLeaveRequest.php)

| Method |
|--------|
| `casts()` |
| `salon()` |
| `staff()` |
| `isPending()` |

### `App\Models\SupportTicket`

File: [`app/Models/SupportTicket.php`](../../app/Models/SupportTicket.php)

| Method |
|--------|
| `user()` |
| `salon()` |
| `assignee()` |
| `replies()` |
| `publicReplies()` |
| `scopeOpen()` |
| `scopeWaiting()` |
| `scopeResolved()` |
| `scopeUnassigned()` |
| `scopeUrgent()` |
| `scopeForSalon()` |
| `scopeSearch()` |
| `priorityColor()` |
| `statusColor()` |
| `isOpen()` |
| `isClosed()` |
| `responseTime()` |

### `App\Models\SupportTicketReply`

File: [`app/Models/SupportTicketReply.php`](../../app/Models/SupportTicketReply.php)

| Method |
|--------|
| `ticket()` |
| `author()` |

### `App\Models\Tenant`

File: [`app/Models/Tenant.php`](../../app/Models/Tenant.php)

| Method |
|--------|
| `subdomainUrl()` |
| `publicBookingUrl()` |
| `owner()` |
| `businessType()` |
| `staff()` |
| `clients()` |
| `appointments()` |
| `services()` |
| `settings()` |
| `getSetting()` |

### `App\Models\TenantPlanOverride`

File: [`app/Models/TenantPlanOverride.php`](../../app/Models/TenantPlanOverride.php)

| Method |
|--------|
| `scopeActive()` |
| `scopeForSalon()` |
| `scopeExpired()` |
| `salon()` |
| `appliedBy()` |
| `isExpired()` |
| `daysRemaining()` |

### `App\Models\User`

File: [`app/Models/User.php`](../../app/Models/User.php)

| Method |
|--------|
| `sendEmailVerificationNotification()` |
| `sendPasswordResetNotification()` |
| `isSuperAdmin()` |
| `isSupport()` |
| `hasTwoFactorEnabled()` |
| `usesTotpTwoFactor()` |
| `usesEmailTwoFactor()` |
| `enableTotpTwoFactor()` |
| `enableEmailTwoFactor()` |
| `disableTwoFactor()` |
| `generateEmailOtp()` |
| `verifyEmailOtp()` |
| `generateRecoveryCodes()` |
| `useRecoveryCode()` |
| `defaultTokenAbilities()` |
| `currentPlan()` |
| `onPaidPlan()` |
| `onTrial()` |
| `onGracePeriod()` |
| `isPastDue()` |
| `planAllows()` |
| `planLimit()` |
| `dashboardScopedStaffId()` |
| `salons()` |
| `staffProfile()` |

### `App\Models\Voucher`

File: [`app/Models/Voucher.php`](../../app/Models/Voucher.php)

| Method |
|--------|
| `salon()` |
| `client()` |
| `getIsExpiredAttribute()` |
| `getIsUsableAttribute()` |
| `scopeActive()` |
| `scopeValid()` |

## `app/Multitenancy/Tasks/`

### `App\Multitenancy\Tasks\BindTenantToContainer`

File: [`app/Multitenancy/Tasks/BindTenantToContainer.php`](../../app/Multitenancy/Tasks/BindTenantToContainer.php)

| Method |
|--------|
| `makeCurrent()` |
| `forgetCurrent()` |

### `App\Multitenancy\Tasks\ClearTenantCachePrefix`

File: [`app/Multitenancy/Tasks/ClearTenantCachePrefix.php`](../../app/Multitenancy/Tasks/ClearTenantCachePrefix.php)

| Method |
|--------|
| `makeCurrent()` |
| `forgetCurrent()` |

### `App\Multitenancy\Tasks\ForgetTenantSession`

File: [`app/Multitenancy/Tasks/ForgetTenantSession.php`](../../app/Multitenancy/Tasks/ForgetTenantSession.php)

| Method |
|--------|
| `makeCurrent()` |
| `forgetCurrent()` |

### `App\Multitenancy\Tasks\SetTenantCachePrefix`

File: [`app/Multitenancy/Tasks/SetTenantCachePrefix.php`](../../app/Multitenancy/Tasks/SetTenantCachePrefix.php)

| Method |
|--------|
| `makeCurrent()` |
| `forgetCurrent()` |

### `App\Multitenancy\Tasks\SwitchTenantSession`

File: [`app/Multitenancy/Tasks/SwitchTenantSession.php`](../../app/Multitenancy/Tasks/SwitchTenantSession.php)

| Method |
|--------|
| `makeCurrent()` |
| `forgetCurrent()` |

## `app/Multitenancy/TenantFinder/`

### `App\Multitenancy\TenantFinder\DomainOrSubdomainTenantFinder`

File: [`app/Multitenancy/TenantFinder/DomainOrSubdomainTenantFinder.php`](../../app/Multitenancy/TenantFinder/DomainOrSubdomainTenantFinder.php)

| Method |
|--------|
| `findForRequest()` |

## `app/Notifications/`

### `App\Notifications\PaymentFailedNotification`

File: [`app/Notifications/PaymentFailedNotification.php`](../../app/Notifications/PaymentFailedNotification.php)

| Method |
|--------|
| `__construct()` |
| `via()` |
| `toMail()` |

### `App\Notifications\ResetPasswordNotification`

File: [`app/Notifications/ResetPasswordNotification.php`](../../app/Notifications/ResetPasswordNotification.php)

| Method |
|--------|
| `toMail()` |

### `App\Notifications\StaffInviteCredentialsNotification`

File: [`app/Notifications/StaffInviteCredentialsNotification.php`](../../app/Notifications/StaffInviteCredentialsNotification.php)

| Method |
|--------|
| `__construct()` |
| `via()` |
| `toMail()` |

### `App\Notifications\SubscriptionCancelledNotification`

File: [`app/Notifications/SubscriptionCancelledNotification.php`](../../app/Notifications/SubscriptionCancelledNotification.php)

| Method |
|--------|
| `via()` |
| `toMail()` |

### `App\Notifications\SubscriptionCreatedNotification`

File: [`app/Notifications/SubscriptionCreatedNotification.php`](../../app/Notifications/SubscriptionCreatedNotification.php)

| Method |
|--------|
| `__construct()` |
| `via()` |
| `toMail()` |

### `App\Notifications\TrialEndingNotification`

File: [`app/Notifications/TrialEndingNotification.php`](../../app/Notifications/TrialEndingNotification.php)

| Method |
|--------|
| `__construct()` |
| `via()` |
| `toMail()` |

### `App\Notifications\TwoFactorCodeNotification`

File: [`app/Notifications/TwoFactorCodeNotification.php`](../../app/Notifications/TwoFactorCodeNotification.php)

| Method |
|--------|
| `__construct()` |
| `via()` |
| `toMail()` |

### `App\Notifications\VerifyEmailNotification`

File: [`app/Notifications/VerifyEmailNotification.php`](../../app/Notifications/VerifyEmailNotification.php)

| Method |
|--------|
| `toMail()` |
| `verificationUrl()` |

## `app/Notifications/Admin/`

### `App\Notifications\Admin\PlanOverrideNotification`

File: [`app/Notifications/Admin/PlanOverrideNotification.php`](../../app/Notifications/Admin/PlanOverrideNotification.php)

| Method |
|--------|
| `__construct()` |
| `via()` |
| `toMail()` |

### `App\Notifications\Admin\SupportTicketReplyNotification`

File: [`app/Notifications/Admin/SupportTicketReplyNotification.php`](../../app/Notifications/Admin/SupportTicketReplyNotification.php)

| Method |
|--------|
| `__construct()` |
| `via()` |
| `toMail()` |

### `App\Notifications\Admin\TenantSuspendedNotification`

File: [`app/Notifications/Admin/TenantSuspendedNotification.php`](../../app/Notifications/Admin/TenantSuspendedNotification.php)

| Method |
|--------|
| `__construct()` |
| `via()` |
| `toMail()` |

### `App\Notifications\Admin\TenantUnsuspendedNotification`

File: [`app/Notifications/Admin/TenantUnsuspendedNotification.php`](../../app/Notifications/Admin/TenantUnsuspendedNotification.php)

| Method |
|--------|
| `__construct()` |
| `via()` |
| `toMail()` |

## `app/Policies/`

### `App\Policies\AppointmentPolicy`

File: [`app/Policies/AppointmentPolicy.php`](../../app/Policies/AppointmentPolicy.php)

| Method |
|--------|
| `viewAny()` |
| `view()` |
| `create()` |
| `update()` |
| `cancel()` |
| `delete()` |
| `sendReminder()` |

### `App\Policies\ClientPolicy`

File: [`app/Policies/ClientPolicy.php`](../../app/Policies/ClientPolicy.php)

| Method |
|--------|
| `viewAny()` |
| `view()` |
| `create()` |
| `update()` |
| `delete()` |
| `viewMedical()` |
| `export()` |
| `gdpr()` |

### `App\Policies\FacilityPolicy`

File: [`app/Policies/FacilityPolicy.php`](../../app/Policies/FacilityPolicy.php)

| Method |
|--------|
| `viewAny()` |
| `view()` |
| `create()` |
| `update()` |
| `delete()` |

### `App\Policies\InventoryPolicy`

File: [`app/Policies/InventoryPolicy.php`](../../app/Policies/InventoryPolicy.php)

| Method |
|--------|
| `viewAny()` |
| `view()` |
| `create()` |
| `update()` |
| `delete()` |

### `App\Policies\MarketingCampaignPolicy`

File: [`app/Policies/MarketingCampaignPolicy.php`](../../app/Policies/MarketingCampaignPolicy.php)

| Method |
|--------|
| `viewAny()` |
| `view()` |
| `create()` |
| `update()` |
| `delete()` |
| `send()` |

### `App\Policies\PosTransactionPolicy`

File: [`app/Policies/PosTransactionPolicy.php`](../../app/Policies/PosTransactionPolicy.php)

| Method |
|--------|
| `view()` |
| `create()` |
| `refund()` |
| `void()` |

### `App\Policies\ReportPolicy`

File: [`app/Policies/ReportPolicy.php`](../../app/Policies/ReportPolicy.php)

| Method |
|--------|
| `viewAny()` |
| `view()` |
| `export()` |
| `viewFinancial()` |
| `viewStaff()` |

### `App\Policies\ReviewPolicy`

File: [`app/Policies/ReviewPolicy.php`](../../app/Policies/ReviewPolicy.php)

| Method |
|--------|
| `viewAny()` |
| `view()` |
| `reply()` |
| `delete()` |
| `requestReview()` |

### `App\Policies\SalonPolicy`

File: [`app/Policies/SalonPolicy.php`](../../app/Policies/SalonPolicy.php)

| Method |
|--------|
| `view()` |
| `update()` |
| `manageSettings()` |

### `App\Policies\ServicePackagePolicy`

File: [`app/Policies/ServicePackagePolicy.php`](../../app/Policies/ServicePackagePolicy.php)

| Method |
|--------|
| `viewAny()` |
| `view()` |
| `create()` |
| `update()` |
| `delete()` |

### `App\Policies\ServicePolicy`

File: [`app/Policies/ServicePolicy.php`](../../app/Policies/ServicePolicy.php)

| Method |
|--------|
| `viewAny()` |
| `view()` |
| `create()` |
| `update()` |
| `delete()` |

### `App\Policies\SettingsPolicy`

File: [`app/Policies/SettingsPolicy.php`](../../app/Policies/SettingsPolicy.php)

| Method |
|--------|
| `view()` |
| `update()` |
| `manageSettings()` |
| `manageBilling()` |
| `manageDomain()` |
| `delete()` |

### `App\Policies\StaffPolicy`

File: [`app/Policies/StaffPolicy.php`](../../app/Policies/StaffPolicy.php)

| Method |
|--------|
| `viewAny()` |
| `view()` |
| `create()` |
| `update()` |
| `delete()` |
| `viewCommission()` |
| `manageRoles()` |

## `app/Policies/Concerns/`

### `App\Policies\Concerns\ResolvesActiveSalonForPolicy`

File: [`app/Policies/Concerns/ResolvesActiveSalonForPolicy.php`](../../app/Policies/Concerns/ResolvesActiveSalonForPolicy.php)

| Method |
|--------|
| `resolveActiveSalonIdForPolicy()` |

## `app/Providers/`

### `App\Providers\AppServiceProvider`

File: [`app/Providers/AppServiceProvider.php`](../../app/Providers/AppServiceProvider.php)

| Method |
|--------|
| `register()` |
| `boot()` |

### `App\Providers\TenancyServiceProvider`

File: [`app/Providers/TenancyServiceProvider.php`](../../app/Providers/TenancyServiceProvider.php)

| Method |
|--------|
| `register()` |
| `boot()` |

## `app/Scopes/`

### `App\Scopes\TenantScope`

File: [`app/Scopes/TenantScope.php`](../../app/Scopes/TenantScope.php)

| Method |
|--------|
| `apply()` |
| `extend()` |

## `app/Services/`

### `App\Services\AppointmentService`

File: [`app/Services/AppointmentService.php`](../../app/Services/AppointmentService.php)

| Method |
|--------|
| `__construct()` |
| `create()` |
| `update()` |
| `reschedule()` |
| `rescheduleForOnlineBooking()` |
| `isAvailable()` |
| `assertStaffNotOnBlockingLeave()` |
| `assertNoConflict()` |
| `acquireStaffBookingLocks()` |

### `App\Services\AuditLogService`

File: [`app/Services/AuditLogService.php`](../../app/Services/AuditLogService.php)

| Method |
|--------|
| `auth()` |
| `access()` |
| `data()` |
| `billing()` |
| `admin()` |
| `security()` |
| `write()` |
| `login()` |
| `failedLogin()` |
| `logout()` |
| `passwordReset()` |
| `twoFactor()` |
| `policyDenied()` |
| `crossTenantAttempt()` |
| `impersonationStart()` |
| `impersonationStop()` |
| `dataExport()` |
| `planChanged()` |

### `App\Services\AvailabilityService`

File: [`app/Services/AvailabilityService.php`](../../app/Services/AvailabilityService.php)

| Method |
|--------|
| `validateProposedWindow()` |

### `App\Services\BookingService`

File: [`app/Services/BookingService.php`](../../app/Services/BookingService.php)

| Method |
|--------|
| `getAvailableSlots()` |
| `holdSlot()` |
| `confirmFromHold()` |
| `reschedule()` |

### `App\Services\ChatbotService`

File: [`app/Services/ChatbotService.php`](../../app/Services/ChatbotService.php)

| Method |
|--------|
| `__construct()` |
| `respond()` |

### `App\Services\MarketingNotificationBridge`

File: [`app/Services/MarketingNotificationBridge.php`](../../app/Services/MarketingNotificationBridge.php)

| Method |
|--------|
| `__construct()` |
| `sync()` |

### `App\Services\MarketingService`

File: [`app/Services/MarketingService.php`](../../app/Services/MarketingService.php)

| Method |
|--------|
| `countRecipients()` |
| `dispatch()` |
| `getSegments()` |

### `App\Services\NotificationConfigService`

File: [`app/Services/NotificationConfigService.php`](../../app/Services/NotificationConfigService.php)

| Method |
|--------|
| `mergedConfigArray()` |
| `persist()` |
| `isRuleEnabled()` |
| `offsetHours()` |
| `templatesForRule()` |
| `quietHours()` |
| `render()` |
| `buildAppointmentContext()` |
| `buildClientContext()` |
| `hasDispatchKey()` |
| `markDispatchKey()` |
| `inQuietHours()` |
| `shouldSkipForQuietHours()` |

### `App\Services\NotificationService`

File: [`app/Services/NotificationService.php`](../../app/Services/NotificationService.php)

| Method |
|--------|
| `appointmentConfirmation()` |
| `appointmentReminder()` |
| `sendClientBookingConfirmationIfEnabled()` |
| `sendClientBookingConfirmationWhatsAppIfEnabled()` |
| `sendClientScheduledReminder()` |
| `notifyTenantNewClientRegistered()` |
| `appointmentCancellation()` |
| `appointmentRescheduled()` |
| `requestReview()` |
| `staffAlert()` |
| `sendDirectMessage()` |
| `sendSms()` |
| `sendEmail()` |
| `notifyTenantNewBooking()` |
| `notifyTenantCancellation()` |
| `notifyTenantReschedule()` |
| `notifyTenantSms()` |

### `App\Services\PosService`

File: [`app/Services/PosService.php`](../../app/Services/PosService.php)

| Method |
|--------|
| `process()` |
| `refund()` |
| `handlePaymentSuccess()` |
| `handlePaymentFailed()` |

### `App\Services\ReportService`

File: [`app/Services/ReportService.php`](../../app/Services/ReportService.php)

| Method |
|--------|
| `revenue()` |
| `appointments()` |
| `staff()` |
| `clients()` |
| `services()` |
| `inventory()` |
| `marketing()` |
| `payroll()` |

### `App\Services\SalonWebsitePayloadService`

File: [`app/Services/SalonWebsitePayloadService.php`](../../app/Services/SalonWebsitePayloadService.php)

| Method |
|--------|
| `build()` |

### `App\Services\StaffAttendanceService`

File: [`app/Services/StaffAttendanceService.php`](../../app/Services/StaffAttendanceService.php)

| Method |
|--------|
| `buildWeekGrid()` |
| `resolveCell()` |
| `isScheduledWorkingDay()` |
| `freshCell()` |
| `upsert()` |
| `clockIn()` |
| `clockOut()` |
| `syncLeaveToAttendance()` |
| `todayStatus()` |
| `attendanceBookingBlockReason()` |
| `daySchedulingBlockReason()` |
| `isOnDutyToday()` |

### `App\Services\StripeLaravelHttpClient`

File: [`app/Services/StripeLaravelHttpClient.php`](../../app/Services/StripeLaravelHttpClient.php)

| Method |
|--------|
| `__construct()` |
| `request()` |
| `requestStream()` |

## `app/Services/Scheduling/`

### `App\Services\Scheduling\AvailabilityRejectedException`

File: [`app/Services/Scheduling/AvailabilityRejectedException.php`](../../app/Services/Scheduling/AvailabilityRejectedException.php)

| Method |
|--------|
| `__construct()` |

### `App\Services\Scheduling\ScheduleValidationResult`

File: [`app/Services/Scheduling/ScheduleValidationResult.php`](../../app/Services/Scheduling/ScheduleValidationResult.php)

| Method |
|--------|
| `__construct()` |
| `firstMessage()` |
| `codes()` |

## `app/Traits/`

### `App\Traits\AuditLog`

File: [`app/Traits/AuditLog.php`](../../app/Traits/AuditLog.php)

| Method |
|--------|
| `extraAuditExcludeFields()` |

### `App\Traits\BelongsToTenant`

File: [`app/Traits/BelongsToTenant.php`](../../app/Traits/BelongsToTenant.php)

| Method |
|--------|
| `scopeWithoutTenantScope()` |
| `scopeForTenant()` |
| `tenant()` |

---

*258 classes, 1164 methods indexed.*
