<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Facility extends Model
{
    use BelongsToTenant;

    public const STATUS_OPERATIONAL = 'operational';

    public const STATUS_IN_USE = 'in_use';

    public const STATUS_MAINTENANCE = 'maintenance';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'salon_id',
        'name',
        'category',
        'kind',
        'status',
        'occupancy_current',
        'occupancy_capacity',
        'equipment_features',
        'last_maintenance_on',
        'next_maintenance_on',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'equipment_features' => 'array',
            'last_maintenance_on' => 'date',
            'next_maintenance_on' => 'date',
        ];
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    /** @return array<string, string> */
    public static function kindOptions(): array
    {
        return [
            'styling_floor' => 'Styling floor',
            'wash_station' => 'Wash station',
            'treatment_room' => 'Treatment room',
            'spa_suite' => 'Spa suite',
            'retail' => 'Retail area',
            'other' => 'Other',
        ];
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPERATIONAL => 'Operational',
            self::STATUS_IN_USE => 'In use',
            self::STATUS_MAINTENANCE => 'Under maintenance',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    public function occupancyPercent(): ?float
    {
        if ($this->occupancy_capacity <= 0) {
            return null;
        }

        return min(100.0, round(100.0 * $this->occupancy_current / $this->occupancy_capacity, 1));
    }

    public function isOperational(): bool
    {
        return in_array($this->status, [self::STATUS_OPERATIONAL, self::STATUS_IN_USE], true);
    }
}
