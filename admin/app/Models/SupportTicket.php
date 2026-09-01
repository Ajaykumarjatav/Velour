<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id
 * @property string      $ticket_number      EGX-00042
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
        'subject', 'body', 'attachments', 'category', 'priority', 'status',
        'satisfaction_rating', 'satisfaction_feedback',
        'first_replied_at', 'resolved_at', 'closed_at',
    ];

    protected $casts = [
        'attachments'      => 'array',
        'first_replied_at' => 'datetime',
        'resolved_at'      => 'datetime',
        'closed_at'        => 'datetime',
    ];

    const CATEGORIES = [
        'booking_issue',
        'store_issue',
        'payment_billing',
        'staff_services',
        'notifications',
        'technical_issue',
        'account_issue',
        'other',
    ];

    const CATEGORY_LABELS = [
        'booking_issue' => 'Booking Issue',
        'store_issue' => 'Store Issue',
        'payment_billing' => 'Payment & Billing',
        'staff_services' => 'Staff & Services',
        'notifications' => 'Notifications',
        'technical_issue' => 'Technical Issue',
        'account_issue' => 'Account Issue',
        'other' => 'Other',
        'billing' => 'Payment & Billing',
        'technical' => 'Technical Issue',
        'feature_request' => 'Other',
        'account' => 'Account Issue',
        'general' => 'Other',
        'bug' => 'Technical Issue',
    ];

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
            'urgent' => 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800/50',
            'high'   => 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/30 border-orange-200 dark:border-orange-800/50',
            'normal' => 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 border-blue-200 dark:border-blue-800/50',
            default  => 'text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700',
        };
    }

    public static function categoryLabel(?string $slug): string
    {
        $slug = (string) $slug;

        return self::CATEGORY_LABELS[$slug] ?? ucwords(str_replace('_', ' ', $slug));
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'open'                 => 'text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-900/40',
            'in_progress'          => 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/40',
            'waiting_on_customer'  => 'text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/40',
            'resolved', 'closed'   => 'text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700/80',
        };
    }

    public function isOpen(): bool    { return in_array($this->status, ['open', 'in_progress']); }
    public function isClosed(): bool  { return in_array($this->status, ['resolved', 'closed']); }

    /** @return list<array{name: string, path: string, mime: string, size: int}> */
    public function attachmentFiles(): array
    {
        $rows = $this->attachments ?? [];
        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row) || empty($row['path'])) {
                continue;
            }
            $out[] = [
                'name' => (string) ($row['name'] ?? basename((string) $row['path'])),
                'path' => (string) $row['path'],
                'mime' => (string) ($row['mime'] ?? ''),
                'size' => (int) ($row['size'] ?? 0),
                'url' => \App\Support\PublicStorage::url((string) $row['path']),
            ];
        }

        return $out;
    }

    public function responseTime(): ?string
    {
        if (! $this->first_replied_at) return null;
        return $this->created_at->diffForHumans($this->first_replied_at, true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function generateNumber(): string
    {
        $last = static::max('id') ?? 0;
        return 'EGX-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
