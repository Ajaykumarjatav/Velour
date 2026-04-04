<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalonResource extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'salon_id',
        'name',
        'type',
        'capacity',
        'equipment',
        'status',
        'availability_status',
        'bookable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'equipment' => 'array',
            'bookable'  => 'boolean',
            'capacity'  => 'integer',
            'sort_order'=> 'integer',
        ];
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }
}
