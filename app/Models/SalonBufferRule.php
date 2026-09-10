<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalonBufferRule extends Model
{
    use BelongsToTenant;

    /**
     * Defaults for new salons — match Settings → Buffer time & booking rules screenshots.
     * (Rules are stored now; live availability wiring is still pending.)
     *
     * @return array{
     *     buffer_before_minutes: int,
     *     buffer_after_minutes: int,
     *     max_daily_bookings_per_staff: int,
     *     advance_booking_days: int,
     *     last_minute_cutoff_hours: int,
     *     overbooking_percent: int
     * }
     */
    public static function defaultsForNewSalon(): array
    {
        return [
            'buffer_before_minutes' => 0,
            'buffer_after_minutes' => 0,
            'max_daily_bookings_per_staff' => 12, // UI disabled; retained for DB completeness
            'advance_booking_days' => 60,
            'last_minute_cutoff_hours' => 0,
            'overbooking_percent' => 0,
        ];
    }

    protected $fillable = [
        'salon_id',
        'buffer_before_minutes',
        'buffer_after_minutes',
        'max_daily_bookings_per_staff',
        'advance_booking_days',
        'last_minute_cutoff_hours',
        'overbooking_percent',
    ];

    protected $attributes = [
        'buffer_before_minutes' => 0,
        'buffer_after_minutes' => 0,
        'max_daily_bookings_per_staff' => 12,
        'advance_booking_days' => 60,
        'last_minute_cutoff_hours' => 0,
        'overbooking_percent' => 0,
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
