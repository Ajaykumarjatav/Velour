<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One-time project feedback from a tenant (salon), reviewed by super admins.
 *
 * @property int         $id
 * @property int         $salon_id
 * @property int         $user_id
 * @property int|null    $rating
 * @property array|null  $topics
 * @property string      $message
 * @property string      $status
 * @property \Carbon\Carbon|null $reviewed_at
 * @property \Carbon\Carbon $created_at
 */
class TenantProjectFeedback extends Model
{
    public const TOPIC_LABELS = [
        'easy_to_use' => 'Easy to use',
        'performance' => 'Performance',
        'design' => 'Design',
        'booking' => 'Booking',
        'reports' => 'Reports',
        'feature_request' => 'Feature request',
    ];

    protected $table = 'tenant_project_feedback';

    protected $fillable = [
        'salon_id',
        'user_id',
        'rating',
        'topics',
        'message',
        'status',
        'reviewed_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'topics' => 'array',
        'reviewed_at' => 'datetime',
    ];

    /**
     * @return list<string>
     */
    public function topicLabels(): array
    {
        $topics = is_array($this->topics) ? $this->topics : [];

        return array_values(array_filter(array_map(
            fn ($id) => self::TOPIC_LABELS[$id] ?? null,
            $topics
        )));
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('message', 'like', $like)
                ->orWhereHas('user', function (Builder $uq) use ($like) {
                    $uq->where('name', 'like', $like)->orWhere('email', 'like', $like);
                })
                ->orWhereHas('salon', function (Builder $sq) use ($like) {
                    $sq->where('name', 'like', $like)->orWhere('slug', 'like', $like);
                });
        });
    }

    public function markReviewed(): void
    {
        if ($this->status === 'reviewed') {
            return;
        }

        $this->update([
            'status' => 'reviewed',
            'reviewed_at' => now(),
        ]);
    }
}
