<?php

namespace App\Models;

use App\Billing\Plan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingTransaction extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'cashfree_subscription_id',
        'plan_key',
        'interval',
        'amount',
        'currency',
        'status',
        'failure_reason',
        'paid_at',
        'activates_at',
        'meta',
    ];

    protected $casts = [
        'amount'       => 'integer',
        'paid_at'      => 'datetime',
        'activates_at' => 'datetime',
        'meta'         => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): ?Plan
    {
        return Plan::find($this->plan_key);
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }
}
