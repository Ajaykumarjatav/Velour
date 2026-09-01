<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TenantPlanOverride
 *
 * Allows super-admins to grant a tenant custom plan access without
 * changing their Stripe subscription. Used for:
 *   • Promotional upgrades (e.g. "free Pro for 3 months")
 *   • Partner/agency custom limits
 *   • Trial extensions
 *   • Beta feature access
 *
 * The override is read by CheckSubscription middleware and planAllows()
 * calls on the User model when the tenant has an active override.
 */
class TenantPlanOverride extends Model
{
    protected $fillable = [
        'salon_id', 'applied_by', 'override_type', 'override_plan',
        'override_staff_limit', 'override_client_limit', 'override_services_limit',
        'additional_features', 'trial_extension_days',
        'discount_percentage', 'reason', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'additional_features' => 'array',
        'expires_at'          => 'datetime',
        'is_active'           => 'boolean',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)
                 ->where(fn ($q) =>
                     $q->whereNull('expires_at')
                       ->orWhere('expires_at', '>', now())
                 );
    }

    public function scopeForSalon(Builder $q, int $salonId): Builder
    {
        return $q->where('salon_id', $salonId);
    }

    public function scopeExpired(Builder $q): Builder
    {
        return $q->where('expires_at', '<', now());
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function salon(): BelongsTo    { return $this->belongsTo(Salon::class); }
    public function appliedBy(): BelongsTo { return $this->belongsTo(User::class, 'applied_by'); }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function daysRemaining(): ?int
    {
        if (! $this->expires_at) return null;
        return max(0, (int) now()->diffInDays($this->expires_at, false));
    }
}
