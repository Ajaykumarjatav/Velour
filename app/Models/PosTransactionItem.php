<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosTransactionItem extends Model
{
    protected $fillable = [
        'transaction_id', 'name', 'type', 'quantity',
        'unit_price', 'discount', 'total', 'staff_id',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount'   => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PosTransaction::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function itemable()
    {
        return $this->morphTo();
    }
}
