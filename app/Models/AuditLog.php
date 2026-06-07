<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * AuditLog — security event record.
 *
 * Separate from Spatie's activity_log (which tracks model CRUD).
 * This model captures authentication, authorisation, data access,
 * admin, and billing events with full request context.
 *
 * @property int         $id
 * @property int|null    $user_id
 * @property string|null $user_email
 * @property string|null $user_name
 * @property int|null    $salon_id
 * @property string      $event
 * @property string      $event_category
 * @property string      $severity          info | warning | critical
 * @property string|null $description
 * @property string|null $subject_type
 * @property int|null    $subject_id
 * @property array|null  $metadata
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $session_id
 * @property string|null $request_id
 * @property string|null $http_method
 * @property string|null $url
 * @property \Carbon\Carbon $occurred_at
 */
class AuditLog extends Model
{
    // No updated_at — events are immutable once written
    public const UPDATED_AT = null;
    public const CREATED_AT = 'occurred_at';

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id', 'user_email', 'user_name', 'salon_id',
        'event', 'event_category', 'severity', 'description',
        'subject_type', 'subject_id', 'metadata',
        'ip_address', 'user_agent', 'session_id', 'request_id',
        'http_method', 'url', 'occurred_at',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'occurred_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForTenant(Builder $q, int $salonId): Builder
    {
        return $q->where('salon_id', $salonId);
    }

    public function scopeForUser(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    public function scopeCategory(Builder $q, string $category): Builder
    {
        return $q->where('event_category', $category);
    }

    public function scopeSeverity(Builder $q, string $severity): Builder
    {
        return $q->where('severity', $severity);
    }

    public function scopeCritical(Builder $q): Builder
    {
        return $q->where('severity', 'critical');
    }

    public function scopeWarning(Builder $q): Builder
    {
        return $q->whereIn('severity', ['warning', 'critical']);
    }

    public function scopeRecent(Builder $q, int $hours = 24): Builder
    {
        return $q->where('occurred_at', '>=', now()->subHours($hours));
    }

    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(function (Builder $sub) use ($term) {
            $sub->where('event',       'like', "%{$term}%")
                ->orWhere('description','like', "%{$term}%")
                ->orWhere('user_email', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%");
        });
    }

    // ── Presentation helpers ──────────────────────────────────────────────────

    public function severityColor(): string
    {
        return match ($this->severity) {
            'critical' => 'red',
            'warning'  => 'amber',
            default    => 'blue',
        };
    }

    public function categoryIcon(): string
    {
        return match ($this->event_category) {
            'auth'     => '🔐',
            'access'   => '🚫',
            'data'     => '📦',
            'billing'  => '💳',
            'admin'    => '👤',
            'security' => '⚠️',
            default    => '📋',
        };
    }
}
