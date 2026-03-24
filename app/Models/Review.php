<?php

namespace App\Models;
use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/* ════════════════════════════════════════════════════════════════════════════
 * Review
 * ════════════════════════════════════════════════════════════════════════════ */
class Review extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'salon_id', 'client_id', 'appointment_id', 'staff_id',
        'rating', 'comment', 'source', 'reviewer_name',
        'owner_reply', 'replied_at', 'is_public', 'is_verified',
    ];

    protected $casts = [
        'rating'     => 'integer',
        'replied_at' => 'datetime',
        'is_public'  => 'boolean',
        'is_verified'=> 'boolean',
    ];

    public function salon(): BelongsTo       { return $this->belongsTo(Salon::class); }
    public function client(): BelongsTo      { return $this->belongsTo(Client::class); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function staff(): BelongsTo       { return $this->belongsTo(Staff::class); }

    public function scopePublic($q)   { return $q->where('is_public', true); }
    public function scopeVerified($q) { return $q->where('is_verified', true); }
    public function scopeBySource($q, string $source) { return $q->where('source', $source); }
}
