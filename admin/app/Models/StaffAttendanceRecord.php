<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendanceRecord extends Model
{
    use BelongsToTenant;

    public const STATUS_PRESENT = 'present';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_LATE = 'late';

    public const STATUS_HALF_DAY = 'half_day';

    public const STATUS_ON_LEAVE = 'on_leave';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_PRESENT,
        self::STATUS_ABSENT,
        self::STATUS_LATE,
        self::STATUS_HALF_DAY,
        self::STATUS_ON_LEAVE,
    ];

    protected $fillable = [
        'salon_id',
        'staff_id',
        'attendance_date',
        'status',
        'clock_in_at',
        'clock_out_at',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'clock_in_at'     => 'datetime',
            'clock_out_at'    => 'datetime',
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

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PRESENT   => 'Present',
            self::STATUS_ABSENT    => 'Absent',
            self::STATUS_LATE      => 'Late',
            self::STATUS_HALF_DAY  => 'Half day',
            self::STATUS_ON_LEAVE  => 'On leave',
            default                => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
