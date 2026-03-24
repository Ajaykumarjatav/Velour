<?php

namespace App\Models;
use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/* ════════════════════════════════════════════════════════════════════════════
 * Voucher
 * ════════════════════════════════════════════════════════════════════════════ */
class Voucher extends Model
{
    use BelongsToTenant;
{
    protected $fillable = [
        'salon_id', 'client_id', 'code', 'type', 'value',
        'remaining_balance', 'min_spend', 'usage_limit',
        'usage_count', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value'             => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'min_spend'         => 'decimal:2',
        'expires_at'        => 'date',
        'is_active'         => 'boolean',
    ];

    public function salon(): BelongsTo  { return $this->belongsTo(Salon::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsUsableAttribute(): bool
    {
        return $this->is_active
            && !$this->is_expired
            && ($this->usage_limit === null || $this->usage_count < $this->usage_limit)
            && ($this->type !== 'gift_card' || $this->remaining_balance > 0);
    }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeValid($q)
    {
        return $q->where('is_active', true)
                 ->where(fn($s) => $s->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }
}
