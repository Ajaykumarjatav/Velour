<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id
 * @property string      $ticket_number      VLR-00042
 * @property int|null    $user_id
 * @property int|null    $salon_id
 * @property int|null    $assigned_to
 * @property string      $subject
 * @property string      $body
 * @property string      $category
 * @property string      $priority
 * @property string      $status
 * @property int|null    $satisfaction_rating
 * @property \Carbon\Carbon|null $first_replied_at
 * @property \Carbon\Carbon|null $resolved_at
 * @property \Carbon\Carbon|null $closed_at
 * @property \Carbon\Carbon      $created_at
 */
class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_number', 'user_id', 'salon_id', 'assigned_to',
        'subject', 'body', 'category', 'priority', 'status',
        'satisfaction_rating', 'satisfaction_feedback',
        'first_replied_at', 'resolved_at', 'closed_at',
    ];

    protected $casts = [
        'first_replied_at' => 'datetime',
        'resolved_at'      => 'datetime',
        'closed_at'        => 'datetime',
    ];

    const CATEGORIES = ['billing', 'technical', 'feature_request', 'account', 'general', 'bug'];
    const PRIORITIES = ['low', 'normal', 'high', 'urgent'];
    const STATUSES   = ['open', 'in_progress', 'waiting_on_customer', 'resolved', 'closed'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $ticket) {
            if (! $ticket->ticket_number) {
                $ticket->ticket_number = static::generateNumber();
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function salon(): BelongsTo    { return $this->belongsTo(Salon::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id')->orderBy('created_at');
    }

    public function publicReplies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id')
            ->where('is_internal', false)
            ->orderBy('created_at');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeOpen(Builder $q): Builder         { return $q->whereIn('status', ['open', 'in_progress']); }
    public function scopeWaiting(Builder $q): Builder      { return $q->where('status', 'waiting_on_customer'); }
    public function scopeResolved(Builder $q): Builder     { return $q->whereIn('status', ['resolved', 'closed']); }
    public function scopeUnassigned(Builder $q): Builder   { return $q->whereNull('assigned_to'); }
    public function scopeUrgent(Builder $q): Builder       { return $q->whereIn('priority', ['urgent', 'high']); }
    public function scopeForSalon(Builder $q, int $id): Builder { return $q->where('salon_id', $id); }

    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(function (Builder $sub) use ($term) {
            $sub->where('ticket_number', 'like', "%{$term}%")
                ->orWhere('subject', 'like', "%{$term}%");
        });
    }

    // ── Presentation ──────────────────────────────────────────────────────────

    public function priorityColor(): string
    {
        return match ($this->priority) {
            'urgent' => 'text-red-400 bg-red-900/30 border-red-800/50',
            'high'   => 'text-orange-400 bg-orange-900/30 border-orange-800/50',
            'normal' => 'text-blue-400 bg-blue-900/30 border-blue-800/50',
            default  => 'text-gray-400 bg-gray-800 border-gray-700',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'open'                 => 'text-green-400 bg-green-900/30',
            'in_progress'          => 'text-blue-400 bg-blue-900/30',
            'waiting_on_customer'  => 'text-amber-400 bg-amber-900/30',
            'resolved', 'closed'   => 'text-gray-400 bg-gray-800',
        };
    }

    public function isOpen(): bool    { return in_array($this->status, ['open', 'in_progress']); }
    public function isClosed(): bool  { return in_array($this->status, ['resolved', 'closed']); }

    public function responseTime(): ?string
    {
        if (! $this->first_replied_at) return null;
        return $this->created_at->diffForHumans($this->first_replied_at, true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function generateNumber(): string
    {
        $last = static::max('id') ?? 0;
        return 'VLR-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
