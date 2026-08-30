<?php

namespace App\Models;
use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/* ════════════════════════════════════════════════════════════════════════════
 * PurchaseOrder
 * ════════════════════════════════════════════════════════════════════════════ */
class PurchaseOrder extends Model
{
    use BelongsToTenant;
{
    protected $fillable = [
        'salon_id', 'created_by', 'reference', 'supplier',
        'status', 'total', 'ordered_at', 'expected_at',
        'received_at', 'notes',
    ];

    protected $casts = [
        'total'       => 'decimal:2',
        'ordered_at'  => 'date',
        'expected_at' => 'date',
        'received_at' => 'date',
    ];

    public function salon(): BelongsTo   { return $this->belongsTo(Salon::class); }
    public function creator(): BelongsTo { return $this->belongsTo(Staff::class, 'created_by'); }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $model) {
            if (! $model->reference) {
                $model->reference = 'PO-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }
}
