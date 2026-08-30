<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Public contact-form submissions (marketing / website).
 *
 * @property int         $id
 * @property string      $help_topics
 * @property string      $full_name
 * @property string      $email
 * @property string|null $business_name
 * @property string|null $message
 * @property \Carbon\Carbon $created_at
 */
class ContactUsQuery extends Model
{
    protected $table = 'contact_us_query';

    public const UPDATED_AT = null;

    protected $fillable = [
        'help_topics',
        'full_name',
        'email',
        'business_name',
        'message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * @return list<string>
     */
    public function topicList(): array
    {
        $raw = trim((string) $this->help_topics);
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', $raw) ?: [])));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('full_name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('business_name', 'like', $like)
                ->orWhere('help_topics', 'like', $like)
                ->orWhere('message', 'like', $like);
        });
    }
}
