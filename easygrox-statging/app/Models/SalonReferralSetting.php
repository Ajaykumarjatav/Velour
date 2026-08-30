<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalonReferralSetting extends Model
{
    protected $fillable = [
        'salon_id',
        'referrer_reward_amount',
        'referee_reward_amount',
        'minimum_spend',
        'credit_expiry_days',
    ];

    protected $casts = [
        'referrer_reward_amount' => 'decimal:2',
        'referee_reward_amount'  => 'decimal:2',
        'minimum_spend'          => 'decimal:2',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }
}
