<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\InitializeTenancyFromDomain;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SalonController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\MarketingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\GdprController;
use App\Http\Controllers\Api\HealthController;

/*
|──────────────────────────────────────────────────────────────────────────────
| VELOUR SALON SaaS — API ROUTES  v1
|──────────────────────────────────────────────────────────────────────────────
| Rate limits:
|   auth    → 10 req/min  (brute-force protection)
|   booking → 30 req/min  (public widget)
|   api     → 120 req/min (authenticated endpoints)
|──────────────────────────────────────────────────────────────────────────────
*/

Route::prefix('v1')->middleware(['sanitize'])->group(function () {

    /* ════════════════════════════════════════════════════════════════════════
     *  PUBLIC — NO AUTH
     * ════════════════════════════════════════════════════════════════════════ */

    // Auth — tight rate limit to prevent brute-force / credential stuffing
    Route::prefix('auth')->middleware(['throttle:10,1'])->group(function () {
        Route::post('register',  [AuthController::class, 'register']);
        Route::post('login',     [AuthController::class, 'login']);
        Route::post('forgot',    [AuthController::class, 'forgotPassword']);
        Route::post('reset',     [AuthController::class, 'resetPassword']);
        Route::get('verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
             ->name('verification.verify');
    });

    // Public booking widget — moderate rate limit
    Route::prefix('book/{salonSlug}')->middleware(['throttle:30,1'])->group(function () {
        Route::get('/',              [BookingController::class, 'info']);
        Route::get('services',       [BookingController::class, 'services']);
        Route::get('staff',          [BookingController::class, 'staff']);
        Route::get('availability',   [BookingController::class, 'availability']);
        Route::post('hold',          [BookingController::class, 'hold']);
        Route::post('confirm',       [BookingController::class, 'confirm']);
        Route::get('appointment/{ref}', [BookingController::class, 'getByRef']);
        Route::post('cancel/{ref}',  [BookingController::class, 'cancel']);
        Route::post('reschedule/{ref}', [BookingController::class, 'reschedule']);
    });

    // Public reviews
    Route::get('salons/{salonSlug}/reviews', [ReviewController::class, 'public'])
         ->middleware('throttle:30,1');
    Route::post('reviews/submit', [ReviewController::class, 'submit'])
         ->middleware('throttle:5,1');

    // Link visit tracking (Go Live & Share analytics)
    Route::post('track/visit', [ShareController::class, 'trackVisit'])
         ->middleware('throttle:60,1');

    // Stripe webhook — NO throttle (Stripe retries); verified by signature
    Route::post('webhooks/stripe', [PosController::class, 'stripeWebhook'])
         ->withoutMiddleware(['sanitize']);

    /* ════════════════════════════════════════════════════════════════════════
     *  AUTHENTICATED — Sanctum token required
     * ════════════════════════════════════════════════════════════════════════ */
    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {

        // Auth/Profile
        Route::prefix('auth')->group(function () {
            Route::post('logout',    [AuthController::class, 'logout']);
            Route::get('me',         [AuthController::class, 'me']);
            Route::put('me',         [AuthController::class, 'update']);
            Route::post('me/avatar', [AuthController::class, 'updateAvatar']);
            Route::post('refresh',   [AuthController::class, 'refresh']);
        });

        /* ── All below require resolved salon context ────────────────────── */
        Route::middleware(['salon.access', 'cross.tenant', 'audit.request', InitializeTenancyFromDomain::class, 'tenant'])->prefix('salon')->group(function () {

            /* ── Salon profile & settings ───────────────────────────────── */
            Route::get('/',        [SalonController::class, 'show']);
            Route::put('/',        [SalonController::class, 'update']);
            Route::post('logo',    [SalonController::class, 'uploadLogo']);
            Route::post('cover',   [SalonController::class, 'uploadCover']);
            Route::get('settings', [SalonController::class, 'settings']);
            Route::put('settings', [SalonController::class, 'updateSettings']);
            Route::get('hours',    [SalonController::class, 'openingHours']);
            Route::put('hours',    [SalonController::class, 'updateHours']);

            /* ── Dashboard ──────────────────────────────────────────────── */
            Route::get('dashboard',              [DashboardController::class, 'index']);
            Route::get('dashboard/kpis',         [DashboardController::class, 'kpis']);
            Route::get('dashboard/today',        [DashboardController::class, 'today']);
            Route::get('dashboard/upcoming',     [DashboardController::class, 'upcoming']);
            Route::get('dashboard/recent-sales', [DashboardController::class, 'recentSales']);
            Route::get('dashboard/alerts',       [DashboardController::class, 'alerts']);

            /* ── Calendar ───────────────────────────────────────────────── */
            Route::get('calendar',               [CalendarController::class, 'index']);
            Route::get('calendar/day',           [CalendarController::class, 'day']);
            Route::get('calendar/week',          [CalendarController::class, 'week']);
            Route::get('calendar/month',         [CalendarController::class, 'month']);
            Route::get('calendar/slots',         [CalendarController::class, 'availableSlots']);
            Route::post('calendar/block',        [CalendarController::class, 'blockTime']);
            Route::delete('calendar/block/{id}', [CalendarController::class, 'removeBlock']);

            /* ── Staff ──────────────────────────────────────────────────── */
            Route::apiResource('staff', StaffController::class);
            Route::post('staff/{staff}/avatar',     [StaffController::class, 'uploadAvatar']);
            Route::get('staff/{staff}/schedule',    [StaffController::class, 'schedule']);
            Route::put('staff/{staff}/schedule',    [StaffController::class, 'updateSchedule']);
            Route::get('staff/{staff}/performance', [StaffController::class, 'performance']);
            Route::get('staff/{staff}/commission',  [StaffController::class, 'commission']);
            Route::post('staff/{staff}/invite',     [StaffController::class, 'sendInvite']);

            /* ── Service Categories ─────────────────────────────────────── */
            Route::get('service-categories',         [ServiceController::class, 'indexCategories']);
            Route::post('service-categories',        [ServiceController::class, 'storeCategory']);
            Route::put('service-categories/{id}',    [ServiceController::class, 'updateCategory']);
            Route::delete('service-categories/{id}', [ServiceController::class, 'destroyCategory']);
            Route::put('service-categories/reorder', [ServiceController::class, 'reorderCategories']);

            /* ── Services ───────────────────────────────────────────────── */
            Route::put('services/reorder',[ServiceController::class,'reorder']);
            Route::post('services/{service}/duplicate', [ServiceController::class, 'duplicate']);
            Route::put('services/reorder',              [ServiceController::class, 'reorder']);
            Route::put('services/{service}/toggle',     [ServiceController::class, 'toggleStatus']);
            Route::put('services/{service}/staff',      [ServiceController::class, 'assignStaff']);

            /* ── Clients ────────────────────────────────────────────────── */
            Route::get('clients/export',[ClientController::class,'export']);
            Route::get('clients/{client}/appointments',        [ClientController::class, 'appointments']);
            Route::get('clients/{client}/transactions',        [ClientController::class, 'transactions']);
            Route::get('clients/{client}/notes',               [ClientController::class, 'notes']);
            Route::post('clients/{client}/notes',              [ClientController::class, 'addNote']);
            Route::put('clients/{client}/notes/{note}',        [ClientController::class, 'updateNote']);
            Route::delete('clients/{client}/notes/{note}',     [ClientController::class, 'deleteNote']);
            Route::get('clients/{client}/formula',             [ClientController::class, 'formula']);
            Route::post('clients/{client}/formula',            [ClientController::class, 'saveFormula']);
            Route::post('clients/{client}/message',            [ClientController::class, 'sendMessage'])->middleware('throttle:20,1');

            /* ── Appointments ───────────────────────────────────────────── */
            Route::get('appointments/export',[AppointmentController::class,'export']);
            Route::put('appointments/{appointment}/status',     [AppointmentController::class, 'updateStatus']);
            Route::put('appointments/{appointment}/checkin',    [AppointmentController::class, 'checkIn']);
            Route::put('appointments/{appointment}/complete',   [AppointmentController::class, 'complete']);
            Route::put('appointments/{appointment}/cancel',     [AppointmentController::class, 'cancelAppointment']);
            Route::put('appointments/{appointment}/noshow',     [AppointmentController::class, 'markNoShow']);
            Route::post('appointments/{appointment}/reschedule',[AppointmentController::class, 'reschedule']);
            Route::post('appointments/{appointment}/reminder',  [AppointmentController::class, 'sendReminder'])->middleware('throttle:10,1');
            Route::get('appointments/export',                   [AppointmentController::class, 'export']);

            /* ── Inventory ──────────────────────────────────────────────── */
            Route::get('inventory-categories',         [InventoryController::class, 'indexCategories']);
            Route::post('inventory-categories',        [InventoryController::class, 'storeCategory']);
            Route::put('inventory-categories/{id}',    [InventoryController::class, 'updateCategory']);
            Route::delete('inventory-categories/{id}', [InventoryController::class, 'destroyCategory']);

            Route::get('inventory/low-stock',[InventoryController::class,'lowStock']);
            Route::post('inventory/{item}/adjust',      [InventoryController::class, 'adjust']);
            Route::get('inventory/{item}/history',      [InventoryController::class, 'history']);

            /* ── Purchase Orders ────────────────────────────────────────── */
            Route::apiResource('purchase-orders', InventoryController::class, ['only' => ['index','show','store','update','destroy']]);
            Route::post('purchase-orders/generate',    [InventoryController::class, 'generatePO']);
            Route::put('purchase-orders/{po}/receive', [InventoryController::class, 'receivePO']);

            /* ── POS ────────────────────────────────────────────────────── */
            Route::get('pos/summary/today',[PosController::class,'todaySummary']);
            Route::post('pos/{transaction}/refund',  [PosController::class, 'refund']);
            Route::post('pos/{transaction}/void',    [PosController::class, 'void']);
            Route::get('pos/{transaction}/receipt',  [PosController::class, 'receipt']);
                                    
            /* ── Vouchers ───────────────────────────────────────────────── */
            Route::post('vouchers/validate',         [PosController::class, 'validateVoucher']);

            /* ── Marketing ──────────────────────────────────────────────── */
            Route::post('marketing/preview',[MarketingController::class,'preview']);
            Route::post('marketing/{campaign}/send',      [MarketingController::class, 'send'])->middleware('throttle:5,1');
            Route::post('marketing/{campaign}/schedule',  [MarketingController::class, 'schedule']);
            Route::post('marketing/{campaign}/pause',     [MarketingController::class, 'pause']);
            Route::post('marketing/{campaign}/duplicate', [MarketingController::class, 'duplicate']);
            Route::get('marketing/{campaign}/stats',      [MarketingController::class, 'stats']);

            /* ── Reviews ────────────────────────────────────────────────── */
            Route::get('reviews',              [ReviewController::class, 'index']);
            Route::get('reviews/{review}',     [ReviewController::class, 'show']);
            Route::post('reviews/{review}/reply',    [ReviewController::class, 'reply']);
            Route::delete('reviews/{review}',        [ReviewController::class, 'destroy']);
            Route::post('reviews/{review}/request',  [ReviewController::class, 'requestReview'])->middleware('throttle:10,1');

            /* ── Reports ────────────────────────────────────────────────── */
            Route::get('reports/revenue',        [ReportController::class, 'revenue']);
            Route::get('reports/appointments',   [ReportController::class, 'appointments']);
            Route::get('reports/staff',          [ReportController::class, 'staff']);
            Route::get('reports/clients',        [ReportController::class, 'clients']);
            Route::get('reports/services',       [ReportController::class, 'services']);
            Route::get('reports/inventory',      [ReportController::class, 'inventory']);
            Route::get('reports/marketing',      [ReportController::class, 'marketing']);
            Route::get('reports/payroll',        [ReportController::class, 'payroll']);
            Route::get('reports/export/{type}',  [ReportController::class, 'export']);

            /* ── Go Live & Share ────────────────────────────────────────── */
            Route::get('share/stats',        [ShareController::class, 'stats']);
            Route::get('share/sources',      [ShareController::class, 'sources']);
            Route::get('share/trend',        [ShareController::class, 'trend']);
            Route::get('share/devices',      [ShareController::class, 'devices']);
            Route::get('share/qr',           [ShareController::class, 'generateQr']);
            Route::get('share/checklist',    [ShareController::class, 'checklist']);
            Route::post('share/customise',   [ShareController::class, 'customise'])->middleware('idempotency');
            Route::get('share/embed-code',   [ShareController::class, 'embedCode']);
            Route::post('share/track-click', [ShareController::class, 'trackClick']);

            /* ── Notifications ──────────────────────────────────────────── */
            Route::get('notifications',                    [NotificationController::class, 'index']);
            Route::put('notifications/{id}/read',          [NotificationController::class, 'markRead']);
            Route::put('notifications/read-all',           [NotificationController::class, 'markAllRead']);
            Route::delete('notifications/{id}',            [NotificationController::class, 'destroy']);
            Route::get('notifications/unread-count',       [NotificationController::class, 'unreadCount']);


            /* ── GDPR & Compliance ───────────────────────────────────────── */
            Route::prefix('gdpr')->middleware(['throttle:10,1'])->group(function () {
                Route::post('clients/{client}/erase',   [GdprController::class, 'erase']);
                Route::get('clients/{client}/export',   [GdprController::class, 'export']);
                Route::put('clients/{client}/consent',  [GdprController::class, 'updateConsent']);
                Route::get('audit-log',                 [GdprController::class, 'auditLog']);
            });

        }); // end salon.access
    }); // end auth:sanctum
}); // end v1

// Detailed health check (for monitoring systems — not throttled)
Route::get('v1/health', [\App\Http\Controllers\Api\HealthController::class, 'check']);
