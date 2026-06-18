<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'salon_id', 'name', 'slug', 'sort_order', 'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
