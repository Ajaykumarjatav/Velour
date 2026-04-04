<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DynamicPricingRule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'salon_id', 'title', 'description', 'adjustment_percent', 'enabled', 'sort_order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }
}
