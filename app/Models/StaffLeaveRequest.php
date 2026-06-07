<?php

namespace App\Models;

use App\Scopes\TenantScope;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffLeaveRequest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'salon_id',
        'staff_id',
        'leave_type',
        'start_date',
        'end_date',
        'notes',
        'blocks_slots',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date'    => 'date',
            'end_date'      => 'date',
            'blocks_slots'  => 'boolean',
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

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * True when the staff member has approved leave that blocks booking on this calendar date (Y-m-d).
     */
    public static function approvedBlockingLeaveExists(int $salonId, int $staffId, string $dateYmd): bool
    {
        return static::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salonId)
            ->where('staff_id', $staffId)
            ->where('status', 'approved')
            ->where('blocks_slots', true)
            ->whereDate('start_date', '<=', $dateYmd)
            ->whereDate('end_date', '>=', $dateYmd)
            ->exists();
    }
}
