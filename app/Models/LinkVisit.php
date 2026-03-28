<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LinkVisit — tracks visits to a salon's public booking page.
 *
 * Audit fix: removed duplicate brace syntax error + added scopes.
 */
class LinkVisit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'salon_id', 'source', 'page', 'ip_address',
        'country', 'device', 'converted',
    ];

    protected $casts = ['converted' => 'boolean'];

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
}
