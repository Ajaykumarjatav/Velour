<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalonActionItem extends Model
{
    use BelongsToTenant;

    public const KIND_ADMIN_TODO = 'admin_todo';

    public const KIND_STAFF_SUGGESTION = 'staff_suggestion';

    public const KIND_INVENTORY_REQUEST = 'inventory_request';

    public const KIND_GENERAL = 'general';

    protected $fillable = [
        'salon_id',
        'staff_id',
        'assigned_staff_id',
        'kind',
        'title',
        'body',
        'priority',
        'status',
        'due_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
        ];
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_staff_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isActiveOnBoard(): bool
    {
        return in_array($this->status, ['open', 'in_progress'], true);
    }

    public static function kindLabels(): array
    {
        return [
            self::KIND_ADMIN_TODO => 'Admin to-do',
            self::KIND_STAFF_SUGGESTION => 'Suggestion',
            self::KIND_INVENTORY_REQUEST => 'Product / supplies',
            self::KIND_GENERAL => 'General',
        ];
    }
}
