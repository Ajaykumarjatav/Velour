<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyTier extends Model
{
    protected $fillable = [
        'salon_id', 'name', 'slug', 'price_monthly', 'service_discount_percent',
        'benefits', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'benefits'      => 'array',
        'is_active'     => 'boolean',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'loyalty_tier_id');
    }
}
