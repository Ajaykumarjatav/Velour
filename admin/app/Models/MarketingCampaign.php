<?php

namespace App\Models;
use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingCampaign extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'salon_id', 'created_by', 'name', 'subject', 'type', 'status',
        'body', 'segment', 'content', 'template', 'offer_details', 'target', 'target_filters',
        'recipient_count', 'sent_count', 'opened_count', 'clicked_count',
        'booking_count', 'revenue_generated', 'scheduled_at', 'sent_at',
    ];

    protected $casts = [
        'offer_details'     => 'array',
        'target_filters'    => 'array',
        'revenue_generated' => 'decimal:2',
        'scheduled_at'      => 'datetime',
        'sent_at'           => 'datetime',
    ];

    /* ── Relationships ─────────────────────────────────────────────────── */

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /* ── Accessors ─────────────────────────────────────────────────────── */

    public function getOpenRateAttribute(): float
    {
        return $this->sent_count > 0
            ? round(($this->opened_count / $this->sent_count) * 100, 1)
            : 0;
    }

    public function getClickRateAttribute(): float
    {
        return $this->opened_count > 0
            ? round(($this->clicked_count / $this->opened_count) * 100, 1)
            : 0;
    }

    public function getConversionRateAttribute(): float
    {
        return $this->sent_count > 0
            ? round(($this->booking_count / $this->sent_count) * 100, 1)
            : 0;
    }

    /* ── Scopes ────────────────────────────────────────────────────────── */

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                     ->whereNotNull('scheduled_at');
    }
}
