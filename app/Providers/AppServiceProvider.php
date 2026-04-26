<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\MarketingCampaign;
use App\Models\PosTransaction;
use App\Models\Review;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use App\Policies\AppointmentPolicy;
use App\Policies\ClientPolicy;
use App\Policies\InventoryPolicy;
use App\Policies\MarketingCampaignPolicy;
use App\Policies\PosTransactionPolicy;
use App\Helpers\CurrencyHelper;
use App\Support\DisplayFormatter;
use App\Policies\ReportPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\SalonPolicy;
use App\Policies\ServicePolicy;
use App\Policies\SettingsPolicy;
use App\Policies\StaffPolicy;
use App\Services\AuditLogService;
use App\Support\ProfileCompletion;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Stripe\ApiRequestor;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Service container bindings ─────────────────────────────────────
        $this->app->singleton(\App\Services\AppointmentService::class);
        $this->app->singleton(\App\Services\AvailabilityService::class);
        $this->app->singleton(\App\Services\BookingService::class);
        $this->app->singleton(\App\Services\PosService::class);
        $this->app->singleton(\App\Services\MarketingService::class);
        $this->app->singleton(\App\Services\NotificationService::class);
        $this->app->singleton(\App\Services\ReportService::class);
        $this->app->singleton(AuditLogService::class);
    }

    public function boot(): void
    {
        // Stripe PHP defaults to ext-curl. Apache (XAMPP) may run PHP without curl while CLI has it.
        // Use Laravel HTTP (Guzzle + streams) when curl is unavailable so billing/portal works.
        if (! function_exists('curl_version')) {
            $stripeHttp = new \App\Services\StripeLaravelHttpClient;
            ApiRequestor::setHttpClient($stripeHttp);
            ApiRequestor::setStreamingHttpClient($stripeHttp);
        }

        // ── Eloquent strict mode (catches N+1, lazy loads in dev) ──────────
        Model::shouldBeStrict(! app()->isProduction());

        // ── Share current salon + apply viewer locale for translated dates ─
        View::composer('*', function ($view) {
            if (auth()->check()) {
                try {
                    $user = auth()->user();
                    DisplayFormatter::applyUserLocale($user);
                    $activeSalonId = (int) session('active_salon_id', 0);
                    $salon = $activeSalonId > 0
                        ? $user->salons()->where('id', $activeSalonId)->first()
                        : null;
                    $salon = $salon ?: $user->salons()->first();
                    $view->with('currentSalon', $salon);
                    $view->with('headerProfileCompletion', $salon ? ProfileCompletion::forSalon($salon) : null);
                } catch (\Throwable) {}
            }
        });

        // ── @money(amount) Blade directive — uses currentSalon currency ────
        Blade::directive('money', function ($expression) {
            return "<?php echo \\App\\Helpers\\CurrencyHelper::format((float)($expression), isset(\$currentSalon) && \$currentSalon ? \$currentSalon->currency ?? 'GBP' : 'GBP'); ?>";
        });

        // ── @moneycode(amount) — include ISO code when symbol is ambiguous ─
        Blade::directive('moneycode', function ($expression) {
            return "<?php echo \\App\\Helpers\\CurrencyHelper::formatWithCode((float)($expression), isset(\$currentSalon) && \$currentSalon ? \$currentSalon->currency ?? 'GBP' : 'GBP'); ?>";
        });

        // ── @currency Blade directive — outputs just the symbol ───────────
        Blade::directive('currency', function ($expression) {
            return "<?php echo \\App\\Helpers\\CurrencyHelper::symbol($expression ?? (isset(\$currentSalon) && \$currentSalon ? \$currentSalon->currency ?? 'GBP' : 'GBP')); ?>";
        });

        Blade::directive('currencyLabel', function () {
            return "<?php echo \\App\\Helpers\\CurrencyHelper::label(isset(\$currentSalon) && \$currentSalon ? (\$currentSalon->currency ?? 'GBP') : 'GBP'); ?>";
        });

        Blade::directive('bizdatetime', function ($expression) {
            return "<?php echo \\App\\Support\\DisplayFormatter::businessDateTime(isset(\$currentSalon) && \$currentSalon ? \$currentSalon : null, $expression); ?>";
        });

        Blade::directive('bizdate', function ($expression) {
            return "<?php echo \\App\\Support\\DisplayFormatter::businessDate(isset(\$currentSalon) && \$currentSalon ? \$currentSalon : null, $expression); ?>";
        });

        Blade::directive('bizshortdate', function ($expression) {
            return "<?php echo \\App\\Support\\DisplayFormatter::businessShortDate(isset(\$currentSalon) && \$currentSalon ? \$currentSalon : null, $expression); ?>";
        });

        Blade::directive('biztime', function ($expression) {
            return "<?php echo \\App\\Support\\DisplayFormatter::businessTime(isset(\$currentSalon) && \$currentSalon ? \$currentSalon : null, $expression); ?>";
        });

        Blade::directive('bizclock', function ($expression) {
            return "<?php echo \\App\\Support\\DisplayFormatter::businessClock(isset(\$currentSalon) && \$currentSalon ? \$currentSalon : null, $expression); ?>";
        });

        Blade::directive('userdatetime', function ($expression) {
            return "<?php echo \\App\\Support\\DisplayFormatter::userDateTime(auth()->user(), isset(\$currentSalon) && \$currentSalon ? \$currentSalon : null, $expression); ?>";
        });

        // ── Force HTTPS ────────────────────────────────────────────────────
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        // ── Global password rules ──────────────────────────────────────────
        Password::defaults(fn () =>
            Password::min(8)->letters()->mixedCase()->numbers()
        );

        // ── Super-admin Gate bypass ────────────────────────────────────────
        // Super-admins bypass ALL policy checks — registered FIRST.
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true; // short-circuits all other checks
            }
        });

        // ── Policy registrations ───────────────────────────────────────────
        Gate::policy(Appointment::class,      AppointmentPolicy::class);
        Gate::policy(Client::class,           ClientPolicy::class);
        Gate::policy(Staff::class,            StaffPolicy::class);
        Gate::policy(Service::class,          ServicePolicy::class);
        Gate::policy(InventoryItem::class,    InventoryPolicy::class);
        Gate::policy(MarketingCampaign::class,MarketingCampaignPolicy::class);
        Gate::policy(PosTransaction::class,   PosTransactionPolicy::class);
        Gate::policy(Review::class,           ReviewPolicy::class);
        Gate::policy(Salon::class,            SalonPolicy::class);
        // Settings and Reports don't have a model; define as Gates below.

        // ── Feature Gates (non-model, plan / role based) ───────────────────

        /**
         * Gate: view-reports
         * Plan: Pro or Enterprise (feature: reports)
         * Role: tenant_admin or manager
         */
        Gate::define('view-reports', function (User $user) {
            return $user->isSuperAdmin()
                || ($user->planAllows('reports') && $user->hasAnyRole(['tenant_admin', 'manager']));
        });

        /**
         * Gate: export-reports
         * Same as view-reports but additionally requires the export permission.
         */
        Gate::define('export-reports', function (User $user) {
            return $user->isSuperAdmin()
                || ($user->planAllows('reports')
                    && $user->hasAnyRole(['tenant_admin', 'manager'])
                    && $user->hasPermissionTo('view reports'));
        });

        /**
         * Gate: send-marketing
         * Plan: Pro or Enterprise (feature: marketing)
         * Role: tenant_admin only (sending campaigns is high-impact)
         */
        Gate::define('send-marketing', function (User $user) {
            return $user->isSuperAdmin()
                || ($user->planAllows('marketing') && $user->hasRole('tenant_admin'));
        });

        /**
         * Gate: manage-marketing
         * Plan: Pro or Enterprise
         * Role: tenant_admin or manager
         */
        Gate::define('manage-marketing', function (User $user) {
            return $user->isSuperAdmin()
                || ($user->planAllows('marketing') && $user->hasAnyRole(['tenant_admin', 'manager']));
        });

        /**
         * Gate: access-api
         * Plan: Pro or Enterprise (feature: api_access)
         * Role: any authenticated user on that plan
         */
        Gate::define('access-api', function (User $user) {
            return $user->isSuperAdmin() || $user->planAllows('api_access');
        });

        /**
         * Gate: manage-billing
         * The salon owner manages billing; super-admins can always access.
         */
        Gate::define('manage-billing', function (User $user) {
            return $user->isSuperAdmin()
                || $user->salons()->exists()      // salon owner
                || $user->hasRole('tenant_admin');
        });

        /**
         * Gate: manage-team
         * Who can invite / remove staff members.
         */
        Gate::define('manage-team', function (User $user) {
            return $user->isSuperAdmin()
                || $user->hasAnyRole(['tenant_admin', 'manager'])
                || $user->salons()->exists();
        });

        /**
         * Gate: manage-settings
         * Salon-level configuration (opening hours, booking rules, etc.)
         */
        Gate::define('manage-settings', function (User $user) {
            return $user->isSuperAdmin()
                || $user->salons()->exists()
                || $user->hasAnyRole(['tenant_admin', 'manager']);
        });

        /**
         * Gate: manage-integrations
         * Stripe Connect, webhooks, API keys — owner only.
         */
        Gate::define('manage-integrations', function (User $user) {
            return $user->isSuperAdmin() || $user->salons()->exists();
        });

        /**
         * Gate: export-data
         * CSV/PDF exports of client lists, appointment history, financial records.
         */
        Gate::define('export-data', function (User $user) {
            return $user->isSuperAdmin()
                || $user->hasAnyRole(['tenant_admin', 'manager'])
                || $user->salons()->exists();
        });

        /**
         * Gate: view-audit-logs
         * Only super-admins can view platform-wide logs.
         * Tenant admins can view their own salon's activity log.
         */
        Gate::define('view-audit-logs', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('view-activity-log', function (User $user) {
            return $user->isSuperAdmin()
                || $user->hasRole('tenant_admin')
                || $user->salons()->exists();
        });

        /**
         * Gate: impersonate-users
         * Platform super-admins only.
         */
        Gate::define('impersonate-users', function (User $user) {
            return $user->isSuperAdmin();
        });

        /**
         * Gate: use-custom-domain
         * Enterprise plan only.
         */
        Gate::define('use-custom-domain', function (User $user) {
            return $user->isSuperAdmin() || $user->planAllows('custom_domain');
        });

        // ── Custom password reset URL (SPA-aware) ─────────────────────────
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return config('app.frontend_url') . '/auth/reset-password?token=' . $token
                . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
        });

        // ── Rate Limiters ──────────────────────────────────────────────────

        /**
         * 'api' — Standard authenticated API requests.
         * Key: user ID + plan (so plan upgrades take immediate effect).
         */
        RateLimiter::for('api', function (Request $request) {
            $user  = $request->user();
            $plan  = $user?->plan ?? 'free';
            $limit = config("security.rate_limits.api.{$plan}", 60);
            $key   = 'api:' . ($user?->id ? "u{$user->id}:{$plan}" : 'ip:' . $request->ip());

            return Limit::perMinute($limit)->by($key);
        });

        /**
         * 'auth' — Login, register, password reset (brute-force protection).
         * Key: IP address (not user ID — user is unknown at login time).
         */
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(config('security.rate_limits.auth.per_minute', 10))
                ->by('auth:ip:' . $request->ip())
                ->response(fn () => response()->json([
                    'message'     => 'Too many authentication attempts. Please wait and try again.',
                    'retry_after' => 60,
                ], 429));
        });

        /**
         * 'booking' — Public booking widget (unauthenticated).
         * Key: IP address.
         */
        RateLimiter::for('booking', function (Request $request) {
            return Limit::perMinute(config('security.rate_limits.booking.per_minute', 30))
                ->by('booking:ip:' . $request->ip());
        });

        /**
         * 'sending' — Campaign send operations.
         * Key: user ID (per-account limit to prevent spam blasts).
         */
        RateLimiter::for('sending', function (Request $request) {
            return Limit::perMinute(config('security.rate_limits.sending.per_minute', 5))
                ->by('sending:u:' . ($request->user()?->id ?: $request->ip()))
                ->response(fn () => response()->json([
                    'message' => 'Campaign sending is rate-limited. Please wait before sending again.',
                ], 429));
        });

        /**
         * 'exports' — CSV/PDF data exports (per-hour, plan-tiered).
         * Key: user ID + plan.
         */
        RateLimiter::for('exports', function (Request $request) {
            $user  = $request->user();
            $plan  = $user?->plan ?? 'free';
            $limit = config("security.rate_limits.exports.{$plan}", 5);
            $decay = config('security.rate_limits.exports.decay_minutes', 60);
            $key   = 'exports:' . ($user?->id ? "u{$user->id}:{$plan}" : 'ip:' . $request->ip());

            return Limit::perMinutes($decay, $limit)->by($key)
                ->response(fn () => response()->json([
                    'message' => "Export limit reached for your plan. Upgrade for higher limits.",
                ], 429));
        });

        /**
         * 'admin' — Super-admin panel (generous limit; no throttle on read ops).
         */
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(config('security.rate_limits.admin.per_minute', 60))
                ->by('admin:u:' . $request->user()?->id);
        });

        /**
         * 'stripe' — Stripe webhook delivery (high volume; signature-verified).
         */
        RateLimiter::for('stripe', function (Request $request) {
            return Limit::perMinute(config('security.rate_limits.stripe.per_minute', 200))
                ->by('stripe:ip:' . $request->ip());
        });
    }
}
