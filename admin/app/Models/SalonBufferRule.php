<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalonBufferRule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'salon_id',
        'buffer_before_minutes',
        'buffer_after_minutes',
        'max_daily_bookings_per_staff',
        'advance_booking_days',
        'last_minute_cutoff_hours',
        'overbooking_percent',
    ];

    protected function casts(): array
    {
        return [
            'buffer_before_minutes'           => 'integer',
            'buffer_after_minutes'            => 'integer',
            'max_daily_bookings_per_staff'    => 'integer',
            'advance_booking_days'            => 'integer',
            'last_minute_cutoff_hours'        => 'integer',
            'overbooking_percent'             => 'integer',
        ];
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }
}
