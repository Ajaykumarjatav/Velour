<?php

namespace App\Models;
use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/* ════════════════════════════════════════════════════════════════════════════
 * SalonNotification
 * ════════════════════════════════════════════════════════════════════════════ */
class SalonNotification extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'salon_id', 'staff_id', 'type', 'title', 'body',
        'data', 'action_url', 'is_read', 'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function salon(): BelongsTo { return $this->belongsTo(Salon::class); }
    public function staff(): BelongsTo { return $this->belongsTo(Staff::class); }

    public function scopeUnread($q) { return $q->where('is_read', false); }

    public function markRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }
}
