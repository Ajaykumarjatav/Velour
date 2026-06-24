<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Billing\Plan;
use App\Models\Salon;
use App\Models\Tenant;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\ResetPasswordNotification;

/**
 * Roles architecture — two distinct layers:
 *
 *  Layer 1 — system_role column (landlord/platform level)
 *    super_admin  → full platform access; manage all tenants, impersonate users
 *    support      → read-only platform access
 *    null         → regular tenant user (default)
 *
 *  Layer 2 — Spatie Permission roles (per-tenant, guard: web)
 *    tenant_admin  → full access within their salon
 *    manager       → staff, appointments, clients, reports
 *    stylist        → own appointments + own clients
 *    receptionist  → appointments, clients, calendar (read-only reports)
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use \App\Traits\HasSupportId, Billable, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected static function supportIdPrefix(): string { return 'ADM'; }
    protected static function supportIdOffset(): int { return 20001; }

    protected $fillable = [
        'name', 'email', 'email_verified_at', 'password', 'force_password_change', 'avatar', 'phone', 'experience', 'language_proficiency', 'timezone', 'locale', 'plan', 'trial_ends_at',
        'system_role', 'is_active', 'last_login_at',
        'two_factor_secret', 'two_factor_recovery_codes',
        'two_factor_confirmed_at', 'two_factor_method',
        'two_factor_code', 'two_factor_expires_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_code',
    ];

    protected $casts = [
        'email_verified_at'           => 'datetime',
        'trial_ends_at'               => 'datetime',
        'last_login_at'               => 'datetime',
        'two_factor_confirmed_at'     => 'datetime',
        'two_factor_expires_at'       => 'datetime',
        'is_active'                   => 'boolean',
        'force_password_change'       => 'boolean',
        'two_factor_secret'           => 'encrypted',
        'two_factor_recovery_codes'   => 'encrypted:array',
    ];

    // ── Email Verification ────────────────────────────────────────────────────

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    // ── Password Reset ────────────────────────────────────────────────────────

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    // ── System Role Helpers ───────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->system_role === 'super_admin';
    }

    public function isSupport(): bool
    {
        return in_array($this->system_role, ['super_admin', 'support'], true);
    }

    // ── 2FA State ─────────────────────────────────────────────────────────────

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function usesTotpTwoFactor(): bool
    {
        return $this->hasTwoFactorEnabled() && $this->two_factor_method === 'totp';
    }

    public function usesEmailTwoFactor(): bool
    {
        return $this->hasTwoFactorEnabled() && $this->two_factor_method === 'email';
    }

    // ── 2FA Enable / Disable ──────────────────────────────────────────────────

    public function enableTotpTwoFactor(string $secret): void
    {
        $this->update([
            'two_factor_secret'           => $secret,
            'two_factor_method'           => 'totp',
            'two_factor_confirmed_at'     => now(),
            'two_factor_recovery_codes'   => $this->generateRecoveryCodes(),
        ]);
    }

    public function enableEmailTwoFactor(): void
    {
        $this->update([
            'two_factor_method'           => 'email',
            'two_factor_confirmed_at'     => now(),
            'two_factor_secret'           => null,
            'two_factor_recovery_codes'   => $this->generateRecoveryCodes(),
        ]);
    }

    public function disableTwoFactor(): void
    {
        $this->update([
            'two_factor_secret'           => null,
            'two_factor_recovery_codes'   => null,
            'two_factor_confirmed_at'     => null,
            'two_factor_method'           => null,
            'two_factor_code'             => null,
            'two_factor_expires_at'       => null,
        ]);
    }

    // ── 2FA Email OTP ─────────────────────────────────────────────────────────

    public function generateEmailOtp(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->update([
            'two_factor_code'       => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);
        return $code;
    }

    public function verifyEmailOtp(string $code): bool
    {
        if (
            $this->two_factor_code === $code
            && $this->two_factor_expires_at?->isFuture()
        ) {
            $this->update(['two_factor_code' => null, 'two_factor_expires_at' => null]);
            return true;
        }
        return false;
    }

    // ── 2FA Recovery Codes ────────────────────────────────────────────────────

    public function generateRecoveryCodes(): array
    {
        return array_map(
            fn () => Str::upper(Str::random(5)) . '-' . Str::upper(Str::random(5)),
            range(1, 8)
        );
    }

    public function useRecoveryCode(string $code): bool
    {
        $codes = $this->two_factor_recovery_codes ?? [];
        $code  = Str::upper(trim($code));
        if (! in_array($code, $codes, true)) return false;
        $this->update([
            'two_factor_recovery_codes' => array_values(array_filter($codes, fn($c) => $c !== $code)),
        ]);
        return true;
    }

    // ── Sanctum Token Abilities ───────────────────────────────────────────────

    public function defaultTokenAbilities(): array
    {
        if ($this->isSuperAdmin()) return ['*'];
        $map = [
            'tenant_admin' => ['read', 'write', 'admin'],
            'manager'      => ['read', 'write'],
            'stylist'       => ['read', 'write:own'],
            'receptionist' => ['read', 'write:appointments'],
        ];
        foreach ($map as $role => $abilities) {
            if ($this->hasRole($role)) return $abilities;
        }
        return ['read'];
    }

    // ── Billing / Plan Helpers ────────────────────────────────────────────────

    /**
     * Resolve the user's current Plan value object.
     * Falls back to trial when no active subscription exists.
     */
    public function currentPlan(): Plan
    {
        $default = (string) config('billing.default_plan', 'trial');

        return Plan::find($this->plan ?? $default) ?? Plan::findOrFail($default);
    }

    /**
     * Is the user subscribed to a paid plan (or on trial)?
     */
    public function onPaidPlan(): bool
    {
        return $this->subscribed('default') || $this->onTrial();
    }

    /**
     * Is the user currently on a free trial (Cashier convention)?
     */
    public function onTrial(): bool
    {
        return $this->subscription('default')?->onTrial() ?? false;
    }

    /**
     * Is the user's subscription currently cancelled but not yet expired?
     */
    public function onGracePeriod(): bool
    {
        return $this->subscription('default')?->onGracePeriod() ?? false;
    }

    /**
     * Is the subscription past-due (payment failed within grace period)?
     */
    public function isPastDue(): bool
    {
        return $this->subscription('default')?->pastDue() ?? false;
    }

    /**
     * Does the user's plan allow a given feature?
     */
    public function planAllows(string $feature): bool
    {
        return $this->currentPlan()->allows($feature);
    }

    /**
     * Get the numeric limit for a plan resource.
     * Returns -1 for unlimited, 0 when no plan found.
     */
    public function planLimit(string $resource): int
    {
        return $this->currentPlan()->limit($resource);
    }

    /**
     * Staff profile id used to scope salon dashboard metrics to one team member.
     * Non-null for users who only have the stylist role (not admin, manager, or receptionist).
     */
    public function ownsSalonId(int $salonId): bool
    {
        if ($salonId <= 0) {
            return false;
        }

        return Salon::withoutGlobalScopes()
            ->whereKey($salonId)
            ->where('owner_id', $this->id)
            ->exists();
    }

    /** Salon owner for the active tenant / session salon (not merely "has any salon"). */
    public function ownsCurrentSalon(): bool
    {
        $salonIds = [];

        if (Tenant::checkCurrent()) {
            $salonIds[] = (int) Tenant::current()->getKey();
        }

        $sessionSalonId = (int) session('active_salon_id', 0);
        if ($sessionSalonId > 0) {
            $salonIds[] = $sessionSalonId;
        }

        $staffSalonId = (int) ($this->staffProfile?->salon_id ?? 0);
        if ($staffSalonId > 0) {
            $salonIds[] = $staffSalonId;
        }

        foreach (array_unique(array_filter($salonIds)) as $salonId) {
            if ($this->ownsSalonId($salonId)) {
                return true;
            }
        }

        return $this->salons()->exists();
    }

    public function hasExplicitPermission(string $permission, string $guard = 'web'): bool
    {
        return $this->hasPermissionTo($permission, $guard);
    }

    public function dashboardScopedStaffId(): ?int
    {
        if ($this->isSuperAdmin()) {
            return null;
        }
        if ($this->hasPermissionTo('appointments.view-all')) {
            return null;
        }
        if (! $this->can('appointments.view')) {
            return null;
        }

        $id = Staff::withoutGlobalScopes()
            ->where('user_id', $this->id)
            ->whereNull('deleted_at')
            ->value('id');

        return $id ? (int) $id : null;
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function salons()
    {
        return $this->hasMany(Salon::class, 'owner_id');
    }

    public function staffProfile()
    {
        return $this->hasOne(Staff::class);
    }

    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }
}
