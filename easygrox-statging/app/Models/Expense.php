<?php

namespace App\Models;

use App\Traits\AuditLog;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use AuditLog, BelongsToTenant, SoftDeletes;

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'card' => 'Card',
        'upi' => 'UPI',
        'cheque' => 'Cheque',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'recorded' => 'Recorded',
    ];

    public const RECURRING_INTERVALS = [
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'yearly' => 'Yearly',
    ];

    protected $fillable = [
        'salon_id', 'category_id', 'staff_id', 'created_by',
        'title', 'amount', 'expense_date', 'vendor', 'payment_method',
        'reference', 'notes', 'receipt_path', 'status', 'recurring_interval',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
