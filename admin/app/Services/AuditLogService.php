<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * AuditLogService
 *
 * Single entry point for writing security audit events.
 *
 * Two types of logging:
 *   1. security_audit → audit_logs table (this service)
 *   2. activity_log   → Spatie activity_log table (via AuditLog trait on models)
 *
 * Event categories:
 *   auth     — login, logout, failed login, 2FA, password changes
 *   access   — policy denials, cross-tenant attempts, 403s
 *   data     — data exports, bulk operations, GDPR requests
 *   billing  — subscription changes, plan upgrades/cancellations
 *   admin    — impersonation, role changes, super-admin actions
 *   security — suspicious activity, lockouts, header violations
 *
 * Usage:
 *   app(AuditLogService::class)->auth('login', 'User logged in');
 *   app(AuditLogService::class)->access('policy.denied', 'Unauthorised', $model);
 *   app(AuditLogService::class)->write('admin', 'impersonate.start', 'critical', 'Admin impersonated user', $user);
 */
class AuditLogService
{
    // ── Category shortcuts ────────────────────────────────────────────────────

    public function auth(string $event, string $description, ?Model $subject = null, array $meta = []): void
    {
        if (! config('security.audit.auth', true)) return;
        $this->write('auth', $event, $this->severity($event), $description, $subject, $meta);
    }

    public function access(string $event, string $description, ?Model $subject = null, array $meta = []): void
    {
        if (! config('security.audit.access', true)) return;
        $this->write('access', $event, 'warning', $description, $subject, $meta);
    }

    public function data(string $event, string $description, ?Model $subject = null, array $meta = []): void
    {
        if (! config('security.audit.data', true)) return;
        $this->write('data', $event, 'info', $description, $subject, $meta);
    }

    public function billing(string $event, string $description, ?Model $subject = null, array $meta = []): void
    {
        if (! config('security.audit.billing', true)) return;
        $this->write('billing', $event, 'info', $description, $subject, $meta);
    }

    public function admin(string $event, string $description, ?Model $subject = null, array $meta = []): void
    {
        if (! config('security.audit.admin', true)) return;
        $this->write('admin', $event, 'warning', $description, $subject, $meta);
    }

    public function security(string $event, string $description, ?Model $subject = null, array $meta = []): void
    {
        if (! config('security.audit.security', true)) return;
        $this->write('security', $event, 'critical', $description, $subject, $meta);
    }

    // ── Core write ────────────────────────────────────────────────────────────

    public function write(
        string  $category,
        string  $event,
        string  $severity,
        string  $description,
        ?Model  $subject  = null,
        array   $metadata = [],
        ?int    $userId   = null,
        ?int    $salonId  = null
    ): void {
        try {
            $request = app()->bound('request') ? request() : null;
            $user    = $userId ? \App\Models\User::find($userId) : Auth::user();
            $salon   = $salonId ?? $this->resolveSalonId($request);

            AuditLog::create([
                'user_id'        => $user?->id,
                'user_email'     => $user?->email,
                'user_name'      => $user?->name,
                'salon_id'       => $salon,
                'event'          => $event,
                'event_category' => $category,
                'severity'       => $severity,
                'description'    => $description,
                'subject_type'   => $subject ? get_class($subject) : null,
                'subject_id'     => $subject?->getKey(),
                'metadata'       => $metadata ?: null,
                'ip_address'     => $request?->ip(),
                'user_agent'     => $request ? substr($request->userAgent() ?? '', 0, 500) : null,
                'session_id'     => $request?->session()?->getId(),
                'request_id'     => $request?->header('X-Request-ID'),
                'http_method'    => $request?->method(),
                'url'            => $request ? substr($request->fullUrl(), 0, 500) : null,
                'occurred_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // Never let audit logging break the main request
            Log::error('[AuditLog] Failed to write audit event', [
                'event'  => $event,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    // ── Structured event helpers ──────────────────────────────────────────────

    /** Record a successful login. */
    public function login(\App\Models\User $user, bool $via2fa = false): void
    {
        $this->write('auth', 'auth.login', 'info',
            "User logged in" . ($via2fa ? ' (2FA verified)' : ''),
            null,
            ['via_2fa' => $via2fa, 'plan' => $user->plan],
            $user->id
        );
    }

    /** Record a failed login attempt. */
    public function failedLogin(string $email): void
    {
        $this->write('auth', 'auth.failed', 'warning',
            "Failed login attempt for {$email}",
            null,
            ['attempted_email' => $email]
        );

        // Escalate to critical if threshold exceeded
        $this->checkFailedLoginThreshold($email);
    }

    /** Record a logout. */
    public function logout(\App\Models\User $user): void
    {
        $this->write('auth', 'auth.logout', 'info', 'User logged out', null, [], $user->id);
    }

    /** Record a password reset. */
    public function passwordReset(\App\Models\User $user): void
    {
        $this->write('auth', 'auth.password_reset', 'info', 'Password reset completed', null, [], $user->id);
    }

    /** Record a 2FA event. */
    public function twoFactor(\App\Models\User $user, string $action): void
    {
        // action: enabled | disabled | failed | backup_used
        $this->write('auth', "auth.2fa.{$action}", 'info',
            "Two-factor authentication {$action}",
            null, ['method' => $user->two_factor_method ?? 'unknown'], $user->id
        );
    }

    /** Record a policy / Gate denial. */
    public function policyDenied(string $ability, ?Model $model = null): void
    {
        $subject = $model ? class_basename($model) . ' #' . $model->getKey() : 'unknown';
        $this->access('access.policy_denied',
            "Authorisation denied: {$ability} on {$subject}",
            $model,
            ['ability' => $ability]
        );
    }

    /** Record a cross-tenant data access attempt. */
    public function crossTenantAttempt(string $resourceType, mixed $resourceId): void
    {
        $this->write('security', 'security.cross_tenant',  'critical',
            "Cross-tenant access attempt: {$resourceType} #{$resourceId}",
            null,
            ['resource_type' => $resourceType, 'resource_id' => $resourceId]
        );
    }

    /** Record an admin impersonation. */
    public function impersonationStart(\App\Models\User $admin, \App\Models\User $target): void
    {
        $this->write('admin', 'admin.impersonate.start', 'warning',
            "Admin '{$admin->email}' impersonating user '{$target->email}'",
            $target,
            ['target_id' => $target->id, 'target_email' => $target->email],
            $admin->id
        );
    }

    public function impersonationStop(\App\Models\User $admin, \App\Models\User $target): void
    {
        $this->write('admin', 'admin.impersonate.stop', 'info',
            "Impersonation ended for user '{$target->email}'",
            $target,
            ['target_id' => $target->id],
            $admin->id
        );
    }

    /** Record a data export. */
    public function dataExport(string $resource, int $count, array $filters = []): void
    {
        $this->data('data.export',
            "Exported {$count} {$resource} records",
            null,
            ['resource' => $resource, 'count' => $count, 'filters' => $filters]
        );
    }

    /** Record a billing event. */
    public function planChanged(\App\Models\User $user, string $oldPlan, string $newPlan): void
    {
        $this->write('billing', 'billing.plan_changed', 'info',
            "Plan changed from {$oldPlan} to {$newPlan}",
            null,
            ['old_plan' => $oldPlan, 'new_plan' => $newPlan],
            $user->id
        );
    }

    // ── Suspicious activity detection ─────────────────────────────────────────

    private function checkFailedLoginThreshold(string $email): void
    {
        $threshold = config('security.suspicious.failed_logins_per_hour', 10);
        $count     = AuditLog::where('event', 'auth.failed')
            ->where(function ($q) use ($email) {
                $q->where('metadata->attempted_email', $email)
                  ->orWhere('ip_address', request()->ip());
            })
            ->recent(1)
            ->count();

        if ($count >= $threshold) {
            $this->write('security', 'security.brute_force', 'critical',
                "Brute-force detected: {$count} failed logins in 1 hour for {$email}",
                null,
                ['email' => $email, 'count' => $count, 'ip' => request()->ip()]
            );
        }
    }

    private function resolveSalonId(?Request $request): ?int
    {
        if (! $request) return null;
        return $request->attributes->get('salon_id');
    }

    private function severity(string $event): string
    {
        return match (true) {
            str_contains($event, 'failed')    => 'warning',
            str_contains($event, 'reset')     => 'info',
            str_contains($event, 'disabled')  => 'warning',
            str_contains($event, 'locked')    => 'critical',
            default                           => 'info',
        };
    }
}
