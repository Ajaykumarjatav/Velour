<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReviewLink extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'salon_id',
        'staff_id',
        'token',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ReviewLink $link): void {
            if (! $link->token) {
                do {
                    $token = Str::random(48);
                } while (self::query()->where('token', $token)->exists());
                $link->token = $token;
            }
        });
    }

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}

