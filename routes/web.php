<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\TwoFactorController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\CalendarController;
use App\Http\Controllers\Web\AppointmentController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\StaffController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\AvailabilityResourcesController;
use App\Http\Controllers\Web\ServiceCategoryController;
use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\PaymentGatewayController;
use App\Http\Controllers\Web\PosController;
use App\Http\Controllers\Web\MarketingController;
use App\Http\Controllers\Web\MultiLocationController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\CustomizationController;
use App\Http\Controllers\Web\SecuritySupportController;
use App\Http\Controllers\Web\WebsiteSeoController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Admin\SuperAdminController;
use App\Http\Controllers\Admin\TenantAdminController;
use Illuminate\Support\Facades\Route;

/*
|──────────────────────────────────────────────────────────────────────────────
| VELOUR — Web Routes
|──────────────────────────────────────────────────────────────────────────────
|
|  Middleware layers (applied in order):
|    tenant.init  → resolve tenant from domain/subdomain
|    auth         → require login
|    verified     → require email verified
|    2fa          → require 2FA challenge completed
|    tenant       → verify tenant is active
|    super_admin  → restrict to platform super-admins
|    tenant_admin → restrict to salon owners/admins
|
*/

// ── Guest Routes ──────────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('login',   [AuthController::class, 'login'])->name('login.submit');
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register',[AuthController::class, 'register'])->name('register.submit');

    Route::get('forgot-password',        [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('forgot-password',       [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('reset-password',        [AuthController::class, 'resetPassword'])->name('password.update');
});

// ── Logout ────────────────────────────────────────────────────────────────────

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Email Verification ────────────────────────────────────────────────────────

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [AuthController::class, 'verificationNotice'])->name('verification.notice');
    Route::post('verify-email/resend', [AuthController::class, 'resendVerification'])
         ->middleware('throttle:6,1')
         ->name('verification.send');
});

// Signed verification URL (from email link)
Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
     ->middleware(['auth', 'signed', 'throttle:6,1'])
     ->name('verification.verify');

// ── 2FA Challenge (after login, before full session) ─────────────────────────

Route::middleware('auth')->prefix('two-factor')->name('two-factor.')->group(function () {
    Route::get('challenge',        [TwoFactorController::class, 'showChallenge'])->name('challenge');
    Route::post('challenge',       [TwoFactorController::class, 'challenge']);
    Route::post('challenge/resend',[TwoFactorController::class, 'resendCode'])->name('resend');
    Route::post('recovery',        [TwoFactorController::class, 'recovery'])->name('recovery');
});

// ── Authenticated + Verified + 2FA Passed ─────────────────────────────────────

Route::middleware(['auth', 'verified', '2fa'])->group(function () {

    // ── 2FA Settings (within authenticated session) ─────────────────────────
    Route::prefix('settings/two-factor')->name('two-factor.')->group(function () {
        Route::get('/',                    [TwoFactorController::class, 'showSetup'])->name('setup');
        Route::post('totp',                [TwoFactorController::class, 'setupTotp'])->name('totp.setup');
        Route::post('totp/confirm',        [TwoFactorController::class, 'confirmTotp'])->name('totp.confirm');
        Route::post('email',               [TwoFactorController::class, 'setupEmail'])->name('email.setup');
        Route::delete('/',                 [TwoFactorController::class, 'disable'])->name('disable');
        Route::get('recovery',             [TwoFactorController::class, 'showRecovery'])->name('recovery');
        Route::post('recovery/regenerate', [TwoFactorController::class, 'regenerateCodes'])->name('recovery.regenerate');
    });

    // ── Tenant-scoped App Routes ─────────────────────────────────────────────

    Route::middleware('tenant')->group(function () {

        Route::get('/', fn() => redirect()->route('dashboard'));
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('calendar', [CalendarController::class, 'index'])->name('calendar');

        Route::get('appointments/occupied-slots', [AppointmentController::class, 'occupiedSlots'])
            ->name('appointments.occupied-slots');

        Route::resource('appointments', AppointmentController::class);
        Route::patch('appointments/{appointment}/status',     [AppointmentController::class, 'updateStatus'])->name('appointments.status');
        Route::patch('appointments/{appointment}/confirm',    [AppointmentController::class, 'confirm'])->name('appointments.confirm');
        Route::patch('appointments/{appointment}/cancel',     [AppointmentController::class, 'cancel'])->name('appointments.cancel');
        Route::patch('appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
        Route::patch('appointments/{appointment}/complete',   [AppointmentController::class, 'complete'])->name('appointments.complete');

        Route::get('clients/export', [ClientController::class, 'export'])->name('clients.export');
        Route::post('clients/import', [ClientController::class, 'import'])->name('clients.import');
        Route::resource('clients', ClientController::class);
        Route::get('staff/payroll/export', [StaffController::class, 'exportPayroll'])->name('staff.payroll.export');
        Route::put('staff/{staff}/weekly-schedule', [StaffController::class, 'updateWeeklySchedule'])->name('staff.weekly-schedule');
        Route::patch('staff/{staff}/base-salary', [StaffController::class, 'updateBaseSalary'])->name('staff.base-salary');
        Route::resource('staff', StaffController::class)->middleware('plan.limit:staff');
        Route::put('services/pricing-rules', [ServiceController::class, 'updatePricingRules'])->name('services.pricing-rules');
        Route::put('services/{service}/variants', [ServiceController::class, 'updateVariants'])->name('services.variants');
        Route::resource('services', ServiceController::class)->middleware('plan.limit:services');
        Route::middleware('subscription:feature:multi_location')->group(function () {
            Route::get('multi-location', [MultiLocationController::class, 'index'])->name('multi-location.index');
            Route::post('multi-location', [MultiLocationController::class, 'store'])->name('multi-location.store');
            Route::put('multi-location/{location}', [MultiLocationController::class, 'update'])->name('multi-location.update');
            Route::delete('multi-location/{location}', [MultiLocationController::class, 'destroy'])->name('multi-location.destroy');
            Route::post('multi-location/{location}/switch', [MultiLocationController::class, 'switch'])->name('multi-location.switch');
        });

        Route::get('availability', [AvailabilityResourcesController::class, 'index'])->name('availability.index');
        Route::post('availability/resources', [AvailabilityResourcesController::class, 'storeResource'])->name('availability.resources.store');
        Route::put('availability/resources/{resource}', [AvailabilityResourcesController::class, 'updateResource'])->name('availability.resources.update');
        Route::delete('availability/resources/{resource}', [AvailabilityResourcesController::class, 'destroyResource'])->name('availability.resources.destroy');
        Route::post('availability/leave', [AvailabilityResourcesController::class, 'storeLeave'])->name('availability.leave.store');
        Route::patch('availability/leave/{leave}/approve', [AvailabilityResourcesController::class, 'approveLeave'])->name('availability.leave.approve');
        Route::patch('availability/leave/{leave}/reject', [AvailabilityResourcesController::class, 'rejectLeave'])->name('availability.leave.reject');
        Route::put('availability/buffer-rules', [AvailabilityResourcesController::class, 'updateBufferRules'])->name('availability.buffer-rules.update');
        Route::post('availability/staff/{staff}/toggle-day', [AvailabilityResourcesController::class, 'toggleStaffDay'])->name('availability.staff.toggle-day');
        Route::get('service-categories', [ServiceCategoryController::class, 'index'])->name('service-categories.index');
        Route::post('service-categories', [ServiceCategoryController::class, 'store'])->name('service-categories.store');
        Route::put('service-categories/{serviceCategory}', [ServiceCategoryController::class, 'update'])->name('service-categories.update');
        Route::delete('service-categories/{serviceCategory}', [ServiceCategoryController::class, 'destroy'])->name('service-categories.destroy');
        Route::get('inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
        Route::post('inventory/barcode-lookup', [InventoryController::class, 'barcodeLookup'])->name('inventory.barcode-lookup');
        Route::post('inventory/reorder', [InventoryController::class, 'reorder'])->name('inventory.reorder');
        Route::post('inventory/adjust-hub', [InventoryController::class, 'adjustHub'])->name('inventory.adjust-hub');
        Route::resource('inventory', InventoryController::class);
        Route::post('inventory/{item}/adjust', [InventoryController::class, 'adjust'])
             ->name('inventory.adjust');

        Route::resource('pos', PosController::class)->only(['index','create','store','show']);

        // Marketing — requires Pro plan or above (specific routes before {marketing} wildcard)
        Route::middleware('subscription:feature:marketing')->group(function () {
            Route::get('marketing/growth', [MarketingController::class, 'growth'])->name('marketing.growth');
            Route::post('marketing/loyalty/tiers', [MarketingController::class, 'storeLoyaltyTier'])->name('marketing.loyalty.tiers.store');
            Route::put('marketing/loyalty/tiers/{loyalty_tier}', [MarketingController::class, 'updateLoyaltyTier'])->name('marketing.loyalty.tiers.update');
            Route::delete('marketing/loyalty/tiers/{loyalty_tier}', [MarketingController::class, 'destroyLoyaltyTier'])->name('marketing.loyalty.tiers.destroy');
            Route::get('marketing/loyalty/tiers/{loyalty_tier}/members', [MarketingController::class, 'loyaltyTierMembers'])->name('marketing.loyalty.tiers.members');
            Route::put('marketing/referral-settings', [MarketingController::class, 'updateReferralSettings'])->name('marketing.referral-settings.update');
            Route::patch('marketing/automation-templates/{marketing_automation_template}', [MarketingController::class, 'toggleAutomationTemplate'])->name('marketing.automation-templates.toggle');
            Route::put('marketing/automation-templates/{marketing_automation_template}', [MarketingController::class, 'updateAutomationTemplate'])->name('marketing.automation-templates.update');
            Route::post('marketing/sms/threads/{marketing_sms_thread}/messages', [MarketingController::class, 'storeSmsReply'])->name('marketing.sms.reply');
            Route::post('marketing/{marketing}/duplicate', [MarketingController::class, 'duplicate'])->name('marketing.duplicate');
            Route::post('marketing/{marketing}/send', [MarketingController::class, 'send'])->name('marketing.send');
            Route::resource('marketing', MarketingController::class)
                 ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        });

        // Reports — requires Pro plan or above
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index')
             ->middleware('subscription:feature:reports');
        Route::get('reports/analytics', [ReportController::class, 'analytics'])->name('reports.analytics')
             ->middleware('subscription:feature:reports');
        Route::get('reports/{type}', [ReportController::class, 'show'])->name('reports.show')
             ->middleware('subscription:feature:reports');

        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::post('reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');

        // Notifications
        Route::get('notifications',                [\App\Http\Controllers\Web\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{notification}/read', [\App\Http\Controllers\Web\NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/mark-all-read', [\App\Http\Controllers\Web\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
        Route::get('notifications/dropdown',       [\App\Http\Controllers\Web\NotificationController::class, 'dropdown'])->name('notifications.dropdown');

        // Activity log (tenant model change history)
        Route::get('activity-log', [\App\Http\Controllers\Tenant\ActivityLogController::class, 'index'])
             ->name('activity.index')
             ->middleware('can:view-activity-log');

        // Go Live & Share
        Route::get('go-live', [\App\Http\Controllers\Web\GoLiveController::class, 'index'])->name('go-live');
        Route::post('go-live/photos', [\App\Http\Controllers\Web\GoLiveController::class, 'uploadPhoto'])->name('go-live.photos.upload');
        Route::delete('go-live/photos/{photo}', [\App\Http\Controllers\Web\GoLiveController::class, 'deletePhoto'])->name('go-live.photos.delete');
        Route::get('website-seo', [WebsiteSeoController::class, 'index'])->name('website-seo.index');
        Route::post('website-seo/publish', [WebsiteSeoController::class, 'publish'])->name('website-seo.publish');
        Route::get('security-support', [SecuritySupportController::class, 'index'])->name('security-support.index');
        Route::put('security-support/security', [SecuritySupportController::class, 'updateSecurity'])->name('security-support.security.update');
        Route::get('customization', [CustomizationController::class, 'index'])->name('customization.index');
        Route::post('customization/brand', [CustomizationController::class, 'updateBrand'])->name('customization.brand.update');
        Route::put('customization/options', [CustomizationController::class, 'updateOptions'])->name('customization.options.update');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings/salon',         [SettingsController::class, 'updateSalon'])->name('settings.salon');
        Route::put('settings/hours',         [SettingsController::class, 'updateHours'])->name('settings.hours');
        Route::put('settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications');
        Route::put('settings/profile',       [SettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::put('settings/password',      [SettingsController::class, 'updatePassword'])->name('settings.password');
        Route::put('settings/social-links',  [SettingsController::class, 'updateSocialLinks'])->name('settings.social-links');

        // Payment gateway keys (tenant)
        Route::get('payments/gateway',  [PaymentGatewayController::class, 'edit'])->name('payments.gateway');
        Route::put('payments/gateway',  [PaymentGatewayController::class, 'update'])->name('payments.gateway.update');

        // Charge / receive money from clients
        Route::get('payments/charge',   [PaymentGatewayController::class, 'showCharge'])->name('payments.charge');
        Route::post('payments/charge',  [PaymentGatewayController::class, 'charge'])->name('payments.charge.process');

        // Chatbot
        Route::post('chatbot/message', [\App\Http\Controllers\Web\ChatbotController::class, 'message'])->name('chatbot.message');

        // ── Tenant Admin (team/billing/transfer) ────────────────────────────
        Route::middleware('tenant_admin')->prefix('salon-admin')->name('salon-admin.')->group(function () {
            Route::get('team',                         [TenantAdminController::class, 'team'])->name('team');
            Route::post('team/invite',                 [TenantAdminController::class, 'invite'])->name('team.invite');
            Route::patch('team/{user}/role',           [TenantAdminController::class, 'updateMemberRole'])->name('team.role');
            Route::delete('team/{user}',               [TenantAdminController::class, 'removeMember'])->name('team.remove');
            Route::get('subscription',                 [TenantAdminController::class, 'subscription'])->name('subscription');
            Route::post('transfer-ownership',          [TenantAdminController::class, 'transferOwnership'])->name('transfer');
        });

    }); // end tenant middleware

}); // end auth+verified+2fa

// ── Billing & Subscriptions ───────────────────────────────────────────────────
//
//  Billing routes live outside the tenant middleware group because:
//    1. Checkout / plans are available before a salon is configured.
//    2. The Stripe customer portal redirects back to /billing which
//       must work even if no tenant context is active.
//
//  Subscription-gated routes inside the tenant group use:
//    ->middleware('subscription')         → any paid plan
//    ->middleware('subscription:pro')     → pro or enterprise only
//    ->middleware('subscription:feature:marketing') → feature flag

Route::middleware(['auth', 'verified', '2fa'])
    ->prefix('billing')
    ->name('billing.')
    ->group(function () {

    // Pricing & checkout
    Route::get('/',            [BillingController::class, 'plans'])->name('plans');
    Route::post('checkout',    [BillingController::class, 'checkout'])->name('checkout');
    Route::get('success',      [BillingController::class, 'success'])->name('success');

    // Change plan (upgrade / downgrade)
    Route::get('change',       [BillingController::class, 'showChangePlan'])->name('change.show');
    Route::patch('change',     [BillingController::class, 'changePlan'])->name('change');

    // Cancel & resume
    Route::get('cancel',       [BillingController::class, 'showCancel'])->name('cancel');
    Route::delete('cancel',    [BillingController::class, 'cancel']);
    Route::post('resume',      [BillingController::class, 'resume'])->name('resume');

    // Stripe Customer Portal (hosted by Stripe)
    Route::get('portal',       [BillingController::class, 'portal'])->name('portal');

    // Billing dashboard & invoices
    Route::get('dashboard',    [BillingController::class, 'dashboard'])->name('dashboard');
    Route::get('invoices/{id}',[BillingController::class, 'downloadInvoice'])->name('invoice.download');

    // Promo / coupon codes
    Route::post('promo',       [BillingController::class, 'applyPromo'])->name('promo');
});

// ── Super Admin Panel ─────────────────────────────────────────────────────────

use App\Http\Controllers\Admin\AdminTenantController;
use App\Http\Controllers\Admin\AdminRevenueController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Admin\AdminSupportController;
use App\Http\Controllers\Admin\AdminAnalyticsController;

Route::middleware(['auth', 'verified', '2fa', 'super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // ── Dashboard ────────────────────────────────────────────────────────────
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');

    // ── Tenants (full management) ─────────────────────────────────────────────
    Route::get('tenants',                    [AdminTenantController::class, 'index'])->name('tenants');
    Route::get('tenants/export',             [AdminTenantController::class, 'export'])->name('tenants.export');
    Route::get('tenants/{id}',               [AdminTenantController::class, 'show'])->name('tenants.show');
    Route::patch('tenants/{id}/domain',      [AdminTenantController::class, 'updateDomain'])->name('tenants.domain');
    Route::post('tenants/{id}/suspend',      [AdminTenantController::class, 'suspend'])->name('tenants.suspend');
    Route::post('tenants/{id}/unsuspend',    [AdminTenantController::class, 'unsuspend'])->name('tenants.unsuspend');
    Route::post('tenants/bulk-suspend',      [AdminTenantController::class, 'bulkSuspend'])->name('tenants.bulk-suspend');
    Route::post('tenants/{id}/override',     [AdminTenantController::class, 'applyPlanOverride'])->name('tenants.override');
    Route::delete('tenants/{salon}/override/{override}', [AdminTenantController::class, 'revokeOverride'])->name('tenants.override.revoke');

    // ── Users ─────────────────────────────────────────────────────────────────
    Route::get('users',                      [SuperAdminController::class, 'users'])->name('users');
    Route::get('users/{id}',                 [SuperAdminController::class, 'showUser'])->name('users.show');
    Route::patch('users/{id}/toggle',        [SuperAdminController::class, 'toggleUserStatus'])->name('users.toggle');
    Route::post('users/{id}/impersonate',    [SuperAdminController::class, 'impersonate'])->name('users.impersonate');
    Route::post('impersonate/stop',          [SuperAdminController::class, 'stopImpersonating'])->name('impersonate.stop');
    Route::post('users/{id}/promote',        [SuperAdminController::class, 'promoteToSuperAdmin'])->name('users.promote');
    Route::delete('users/{id}/demote',       [SuperAdminController::class, 'demoteFromSuperAdmin'])->name('users.demote');
    Route::delete('users/{id}/tokens',       [SuperAdminController::class, 'revokeAllTokens'])->name('users.revoke-tokens');

    // ── Revenue ───────────────────────────────────────────────────────────────
    Route::get('revenue',                    [AdminRevenueController::class, 'index'])->name('revenue');
    Route::get('revenue/export',             [AdminRevenueController::class, 'export'])->name('revenue.export');

    // ── Plan Management ───────────────────────────────────────────────────────
    Route::get('plans',                      [AdminPlanController::class, 'index'])->name('plans');
    Route::post('plans/migrate',             [AdminPlanController::class, 'migratePlan'])->name('plans.migrate');
    Route::post('plans/bulk-migrate',        [AdminPlanController::class, 'bulkMigrate'])->name('plans.bulk-migrate');
    Route::patch('plans/overrides/{id}/expire', [AdminPlanController::class, 'expireOverride'])->name('plans.override.expire');

    // ── Support Tickets ───────────────────────────────────────────────────────
    Route::get('support',                    [AdminSupportController::class, 'index'])->name('support.index');
    Route::post('support',                   [AdminSupportController::class, 'store'])->name('support.store');
    Route::get('support/{ticket}',           [AdminSupportController::class, 'show'])->name('support.show');
    Route::post('support/{ticket}/reply',    [AdminSupportController::class, 'reply'])->name('support.reply');
    Route::patch('support/{ticket}/assign',  [AdminSupportController::class, 'assign'])->name('support.assign');
    Route::patch('support/{ticket}/status',  [AdminSupportController::class, 'updateStatus'])->name('support.status');

    // ── Usage Analytics ───────────────────────────────────────────────────────
    Route::get('analytics',                  [AdminAnalyticsController::class, 'index'])->name('analytics');

    // ── Billing (Stripe webhooks & MRR — legacy) ──────────────────────────────
    Route::get('billing',                    [\App\Http\Controllers\Admin\AdminBillingController::class, 'index'])->name('billing');
    Route::get('billing/webhooks',           [\App\Http\Controllers\Admin\AdminBillingController::class, 'webhooks'])->name('billing.webhooks');
    Route::post('billing/webhooks/{id}/replay', [\App\Http\Controllers\Admin\AdminBillingController::class, 'replayWebhook'])->name('billing.webhook.replay');

    // ── Security Audit Log ────────────────────────────────────────────────────
    Route::get('audit',                      [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit.index');
    Route::get('audit/stats',                [\App\Http\Controllers\Admin\AuditLogController::class, 'stats'])->name('audit.stats');
    Route::get('audit/export',               [\App\Http\Controllers\Admin\AuditLogController::class, 'export'])->name('audit.export');
    Route::get('audit/{auditLog}',           [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])->name('audit.show');

    // ── Admin Chatbot ─────────────────────────────────────────────────────────
    Route::post('chatbot/message', [\App\Http\Controllers\Web\ChatbotController::class, 'message'])->name('chatbot.message');

});


// ── Onboarding (shown to new users after registration) ────────────────────────
Route::middleware(['auth', 'verified'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/',                    [\App\Http\Controllers\Web\OnboardingController::class, 'index'])->name('index');
    Route::get('/step/{step}',         [\App\Http\Controllers\Web\OnboardingController::class, 'step'])->name('step');
    Route::post('/step/{step}',        [\App\Http\Controllers\Web\OnboardingController::class, 'completeStep'])->name('complete-step');
    Route::get('/complete',            [\App\Http\Controllers\Web\OnboardingController::class, 'complete'])->name('complete');
    Route::get('/skip',                [\App\Http\Controllers\Web\OnboardingController::class, 'skip'])->name('skip');
});

// ── Public Booking Page ───────────────────────────────────────────────────────
Route::get('book/{slug}', [\App\Http\Controllers\Web\BookingController::class, 'show'])->name('booking.show');

// ── Legal & Compliance Pages ──────────────────────────────────────────────────
Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('privacy',  [\App\Http\Controllers\Web\LegalController::class, 'privacy'])->name('privacy');
    Route::get('terms',    [\App\Http\Controllers\Web\LegalController::class, 'terms'])->name('terms');
    Route::get('cookies',  [\App\Http\Controllers\Web\LegalController::class, 'cookies'])->name('cookies');
    Route::post('consent', [\App\Http\Controllers\Web\LegalController::class, 'recordConsent'])->name('cookie-consent');
});

// ── Help Centre ───────────────────────────────────────────────────────────────
Route::prefix('help')->name('help.')->group(function () {
    Route::get('/',                 [\App\Http\Controllers\Web\HelpController::class, 'index'])->name('index');
    Route::get('/{slug}',           [\App\Http\Controllers\Web\HelpController::class, 'article'])->name('article');
    Route::post('/{id}/feedback',   [\App\Http\Controllers\Web\HelpController::class, 'feedback'])->name('feedback');
});

// ── Account Management ────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('account')->name('account.')->group(function () {
    Route::get('sessions',                     [\App\Http\Controllers\Web\AccountController::class, 'sessions'])->name('sessions');
    Route::delete('sessions/{id}',             [\App\Http\Controllers\Web\AccountController::class, 'revokeSession'])->name('sessions.revoke');
    Route::delete('sessions',                  [\App\Http\Controllers\Web\AccountController::class, 'revokeAllOtherSessions'])->name('sessions.revoke-all');
    Route::delete('tokens/{id}',               [\App\Http\Controllers\Web\AccountController::class, 'revokeToken'])->name('tokens.revoke');
    Route::get('delete',                       [\App\Http\Controllers\Web\AccountController::class, 'showDelete'])->name('delete');
    Route::delete('/',                         [\App\Http\Controllers\Web\AccountController::class, 'destroy'])->name('destroy');
});
