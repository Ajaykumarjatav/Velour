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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        then: function () {
            // Stripe webhooks — loaded bare (no CSRF, no session middleware)
            Route::middleware('throttle:stripe')
                ->group(base_path('routes/stripe.php'));
        },
        apiPrefix: 'api',
        health: '/up',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Sanctum stateful API support
        $middleware->statefulApi();

        // Global API middleware (applies to all API routes)
        $middleware->api(prepend: [
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

        // Global web middleware stack — tenancy initialisation runs on every web request
        // We resolve the tenant based on the logged-in user (not the domain), so
        // this middleware must run after the session/auth middleware.
        $middleware->web(append: [
            \App\Http\Middleware\InitializeTenancyFromDomain::class,
        ]);

        // Named middleware aliases
        $middleware->alias([
            'salon.access'    => \App\Http\Middleware\EnsureSalonAccess::class,
            'sanitize'        => \App\Http\Middleware\SanitizeInput::class,
            'throttle'        => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'role'            => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'      => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'log.slow'        => \App\Http\Middleware\LogSlowQueries::class,
            // ── Multitenancy ──────────────────────────────────────────────
            'tenant'          => \App\Http\Middleware\TenantMiddleware::class,
            'tenant.init'     => \App\Http\Middleware\InitializeTenancyFromDomain::class,
            // ── Authentication & Authorization ────────────────────────────
            'verified'        => \App\Http\Middleware\EnsureEmailIsVerified::class,
            '2fa'             => \App\Http\Middleware\RequireTwoFactor::class,
            'password.changed'=> \App\Http\Middleware\EnsurePasswordChange::class,
            'super_admin'     => \App\Http\Middleware\SuperAdminMiddleware::class,
            'tenant_admin'    => \App\Http\Middleware\TenantAdminMiddleware::class,
            // ── Billing & Subscriptions ────────────────────────────────────
            'subscription'    => \App\Http\Middleware\CheckSubscription::class,
            'plan.limit'      => \App\Http\Middleware\CheckPlanLimits::class,
            'subscriptions.enabled' => \App\Http\Middleware\RedirectUnlessSubscriptionsEnabled::class,
            // ── Security & Audit ───────────────────────────────────────────
            'throttle.tenant'  => \App\Http\Middleware\TenantAwareThrottle::class,
            'audit.request'    => \App\Http\Middleware\AuditRequestMiddleware::class,
            'cross.tenant'     => \App\Http\Middleware\PreventCrossTenantAccess::class,
            'idempotency'      => \App\Http\Middleware\IdempotencyKey::class,
            'account.lockout'  => \App\Http\Middleware\AccountLockout::class,
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
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This action is unauthorised.'], 403);
            }
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
