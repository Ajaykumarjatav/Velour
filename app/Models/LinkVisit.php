<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LinkVisit — tracks visits to a salon's public website and booking links.
 *
 * Audit fix: removed duplicate brace syntax error + added scopes.
 */
class LinkVisit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'salon_id', 'source', 'page', 'kind', 'ip_address',
        'country', 'device', 'is_bot', 'user_agent', 'converted', 'utm_source',
        'utm_medium', 'utm_campaign', 'referrer',
    ];

    protected $casts = [
        'converted' => 'boolean',
        'is_bot' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopeLast30Days($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    public function scopeConverted($query)
    {
        return $query->where('converted', true);
    }

    public function scopePageViews($query)
    {
        return $query->where(function ($q) {
            $q->where('kind', 'visit')->orWhereNull('kind');
        });
    }

    public function scopeClicks($query)
    {
        return $query->where('kind', 'click');
    }
}
