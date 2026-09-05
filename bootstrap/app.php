<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        then: function () {
            // Cashfree webhooks — loaded bare (no CSRF, no session middleware)
            Route::middleware('throttle:stripe')
                ->group(base_path('routes/cashfree.php'));
        },
        apiPrefix: 'api',
        health: '/up',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Sanctum stateful API support
        $middleware->statefulApi();

        $middleware->web(append: [
            \App\Http\Middleware\ForgetLegacySessionCookies::class,
            // After session: default {store} for route() on billing/account/help-adjacent pages.
            \App\Http\Middleware\EnsureStoreUrlDefaults::class,
        ]);

        $middleware->redirectGuestsTo(fn () => \App\Support\AppUrl::login());
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();

            return $user
                ? \App\Support\AuthPanel::homeUrl($user)
                : \App\Support\AppUrl::login();
        });

        // Must run before StartSession so public storefront/booking never
        // overwrite the admin panel session cookie after "Preview".
        $middleware->web(prepend: [
            \App\Http\Middleware\AlignUrlWithRequest::class,
            \App\Http\Middleware\PreventPublicSessionClobber::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\PreventPublicSessionClobber::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\ForceJsonResponse::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Trust all proxies (for Cloudflare / load balancers)
        $middleware->trustProxies(
            at: '*',
            headers:
                Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO
        );

        // Cashfree subscription checkout redirects back via POST (no CSRF token).
        // Public guest booking widget posts from /s/* (session path is the APP_URL subdirectory).
        // Storefront review share forms use array sessions (no durable CSRF cookie).
        $middleware->validateCsrfTokens(except: [
            'billing/return',
            'api/v1/book/*/hold',
            'api/v1/book/*/confirm',
            'api/v1/book/*/cancel/*',
            'api/v1/book/*/reschedule/*',
            's/*/reviews/share/*',
        ]);

        // Cashfree may append status fields when redirecting back to return_url.
        $middleware->validateSignatures(except: [
            'cf_subReferenceId',
            'cf_subscriptionId',
            'cf_authAmount',
            'cf_referenceId',
            'cf_status',
            'cf_message',
            'cf_checkoutStatus',
            'cf_mode',
            'cf_subscriptionPaymentId',
            'cf_umrn',
            'cf_umn',
        ]);

        // Tenancy is initialised on routes that require it, *after* `auth` (see web.php /
        // api.php). Running it globally before `auth` leaves `Auth::check()` false and
        // breaks staff dashboard (TenantMiddleware → 404).

        // Named middleware aliases
        $middleware->alias([
            'salon.panel'     => \App\Http\Middleware\EnsureSalonPanel::class,
            'salon.access'    => \App\Http\Middleware\EnsureSalonAccess::class,
            'store.path'      => \App\Http\Middleware\ResolveStorePath::class,
            'sanitize'        => \App\Http\Middleware\SanitizeInput::class,
            'throttle'        => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'role'            => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'      => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'log.slow'        => \App\Http\Middleware\LogSlowQueries::class,
            // ── Multitenancy ──────────────────────────────────────────────
            'tenant'          => \App\Http\Middleware\TenantMiddleware::class,
            'tenant.init'     => \App\Http\Middleware\InitializeTenancyFromDomain::class,
            'tenant.public'   => \App\Http\Middleware\InitializeTenantFromSalonSlug::class,
            // ── Authentication & Authorization ────────────────────────────
            'verified'        => \App\Http\Middleware\EnsureEmailIsVerified::class,
            '2fa'             => \App\Http\Middleware\RequireTwoFactor::class,
            'password.changed'=> \App\Http\Middleware\EnsurePasswordChange::class,
            'super_admin'     => \App\Http\Middleware\SuperAdminMiddleware::class,
            'tenant_admin'    => \App\Http\Middleware\TenantAdminMiddleware::class,
            'route.permission'=> \App\Http\Middleware\EnsureRoutePermission::class,
            'sync.staff.role' => \App\Http\Middleware\SyncStaffSpatieRole::class,
            // ── Billing & Subscriptions ────────────────────────────────────
            'subscription'    => \App\Http\Middleware\CheckSubscription::class,
            'plan.limit'      => \App\Http\Middleware\CheckPlanLimits::class,
            'subscriptions.enabled' => \App\Http\Middleware\RedirectUnlessSubscriptionsEnabled::class,
            'plan.access'       => \App\Http\Middleware\EnsureActivePlanAccess::class,
            // ── Security & Audit ───────────────────────────────────────────
            'throttle.tenant'  => \App\Http\Middleware\TenantAwareThrottle::class,
            'audit.request'    => \App\Http\Middleware\AuditRequestMiddleware::class,
            'cross.tenant'     => \App\Http\Middleware\PreventCrossTenantAccess::class,
            'idempotency'      => \App\Http\Middleware\IdempotencyKey::class,
            'account.lockout'  => \App\Http\Middleware\AccountLockout::class,
            'profile.complete' => \App\Http\Middleware\EnsureSalonProfileComplete::class,
            'admin.store.readonly' => \App\Http\Middleware\EnsureAdminStoreBrowseReadOnly::class,
            'admin.store.browse.readonly-pages' => \App\Http\Middleware\RedirectAdminBrowseWritePages::class,
            'user.activity'    => \App\Http\Middleware\LogUserActivity::class,
            'signed.flexible'  => \App\Http\Middleware\ValidateFlexibleSignature::class,
            'client.auth'      => \App\Http\Middleware\AuthenticateClientToken::class,
            'client.portal'    => \App\Http\Middleware\EnsureClientPortalAuth::class,
            'client.salon'     => \App\Http\Middleware\ResolveClientSalon::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                $model = class_basename($e->getModel());
                return response()->json(['message' => "{$model} not found."], 404);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(\App\Support\AppUrl::login());
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This action is unauthorised.'], 403);
            }
        });

        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh the page and try again.',
                ], 419);
            }

            $referer = $request->headers->get('referer');
            $refererPath = is_string($referer) ? (parse_url($referer, PHP_URL_PATH) ?: '') : '';
            $fallback = (is_string($referer) && $referer !== '' && ! str_ends_with($refererPath, '/login'))
                ? $referer
                : $request->getSchemeAndHttpHost().rtrim((string) $request->getBasePath(), '/').'/';

            return redirect()
                ->to($fallback)
                ->with('error', 'Your session expired for security. Please try that action again.');
        });

        $exceptions->render(function (InvalidSignatureException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Invalid or expired link.'], 403);
            }

            // Email verification links — send user to login with a clear next step
            if ($request->is('verify-email/*') || $request->routeIs('verification.verify')) {
                $id = $request->route('id');
                $hash = (string) $request->route('hash', '');
                $user = is_numeric($id) ? \App\Models\User::find((int) $id) : null;
                if (
                    $user
                    && $hash !== ''
                    && hash_equals(sha1($user->getEmailForVerification()), $hash)
                    && $user->hasVerifiedEmail()
                ) {
                    return redirect()
                        ->route('login')
                        ->with('success', 'Your email is already verified. Please sign in to continue.');
                }

                return redirect()
                    ->route('login')
                    ->with('error', 'This verification link is invalid or expired. Sign in and use “Resend verification email”, or register again if needed.');
            }

            // Signed invoice / other signed links
            if ($request->routeIs('pos.invoice.pdf.signed') || $request->is('invoice/*')) {
                return redirect()
                    ->route('login')
                    ->with('error', 'This invoice link is invalid or has expired. Please ask the salon to send a new one.');
            }

            return response()->view('errors.403', [
                'exception' => new HttpException(403, 'This link is invalid or has expired. Please request a new one.'),
            ], 403);
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message'     => 'Too many requests. Please slow down.',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? 60,
                ], 429);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Endpoint not found.'], 404);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'HTTP method not allowed.'], 405);
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson() && app()->environment('production')) {
                \Illuminate\Support\Facades\Log::error('Unhandled exception', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => $e->getTraceAsString(),
                ]);
                return response()->json(['message' => 'An unexpected server error occurred.'], 500);
            }
        });
    })
    ->create();
