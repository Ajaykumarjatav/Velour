<?php

namespace App\Models;
use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/* ════════════════════════════════════════════════════════════════════════════
 * Invoice
 * ════════════════════════════════════════════════════════════════════════════ */
class Invoice extends Model
{
    use BelongsToTenant;
{
    protected $fillable = [
        'salon_id', 'client_id', 'transaction_id', 'number',
        'subtotal', 'tax', 'total', 'status',
        'issued_at', 'due_at', 'paid_at', 'pdf_path',
    ];

    protected $casts = [
        'subtotal'  => 'decimal:2',
        'tax'       => 'decimal:2',
        'total'     => 'decimal:2',
        'issued_at' => 'date',
        'due_at'    => 'date',
        'paid_at'   => 'date',
    ];

    public function salon(): BelongsTo       { return $this->belongsTo(Salon::class); }
    public function client(): BelongsTo      { return $this->belongsTo(Client::class); }
    public function transaction(): BelongsTo { return $this->belongsTo(PosTransaction::class); }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $model) {
            if (! $model->number) {
                $last  = static::where('salon_id', $model->salon_id)->max('id') ?? 0;
                $model->number = 'INV-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
