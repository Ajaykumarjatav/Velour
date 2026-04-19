<?php

namespace App\Models;
use App\Traits\BelongsToTenant;

use App\Traits\AuditLog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosTransaction extends Model
{
    use AuditLog, BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'salon_id', 'client_id', 'staff_id', 'appointment_id',
        'reference', 'subtotal', 'discount_amount', 'discount_code',
        'discount_type', 'tax_amount', 'tip_amount', 'total',
        'amount_tendered', 'change_given', 'payment_method', 'status',
        'stripe_payment_intent_id', 'notes', 'completed_at',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'tip_amount'       => 'decimal:2',
        'total'            => 'decimal:2',
        'amount_tendered'  => 'decimal:2',
        'change_given'     => 'decimal:2',
        'completed_at'     => 'datetime',
    ];

    /* ── Relationships ─────────────────────────────────────────────────── */

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosTransactionItem::class, 'transaction_id');
    }

    public function invoice(): HasMany
    {
        return $this->hasMany(Invoice::class, 'transaction_id');
    }

    /* ── Scopes ────────────────────────────────────────────────────────── */

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForPeriod($query, $from, $to)
    {
        return $query->whereBetween('completed_at', [$from, $to]);
    }

    /** Revenue recognition: completion time, falling back to created_at when not set. */
    public function scopeRecognizedBetweenUtc($query, $fromUtc, $toUtc)
    {
        return $query->where('status', 'completed')
            ->whereRaw('COALESCE(completed_at, created_at) BETWEEN ? AND ?', [$fromUtc, $toUtc]);
    }

    /* ── Accessors ─────────────────────────────────────────────────────── */

    public function getNetTotalAttribute(): float
    {
        return $this->total - $this->tax_amount;
    }

    /* ── Boot ──────────────────────────────────────────────────────────── */

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (! $model->reference) {
                $model->reference = 'TXN-' . strtoupper(uniqid());
            }
        });
    }
}
