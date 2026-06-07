<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalonPhoto extends Model
{
    protected $fillable = ['salon_id', 'path', 'disk', 'sort_order'];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
