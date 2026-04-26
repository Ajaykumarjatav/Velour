<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /** Tenants that offer this vertical (many-to-many via pivot). */
    public function salons(): BelongsToMany
    {
        return $this->belongsToMany(Salon::class, 'salon_business_types')->withTimestamps();
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'business_type_id');
    }

    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class, 'business_type_id');
    }

    /** Default “Salon” type id for migrations/factories when a specific type is not chosen. */
    public static function defaultId(): int
    {
        $id = (int) static::query()->orderBy('sort_order')->value('id');
        if ($id > 0) {
            return $id;
        }

        throw new \RuntimeException('No business_types rows found. Run BusinessTypeSeeder.');
    }
}
