<?php

namespace App\Models;
use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/* ════════════════════════════════════════════════════════════════════════════
 * BookingSource
 * ════════════════════════════════════════════════════════════════════════════ */
class BookingSource extends Model
{
    use BelongsToTenant;
{
    protected $fillable = [
        'salon_id', 'appointment_id', 'source', 'utm_medium',
        'utm_campaign', 'referrer', 'ip_address', 'user_agent', 'converted',
    ];

    protected $casts = ['converted' => 'boolean'];

    public function salon(): BelongsTo       { return $this->belongsTo(Salon::class); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
}
