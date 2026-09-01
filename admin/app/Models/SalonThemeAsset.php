<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Branding a salon has overridden for one specific storefront theme.
 *
 * A missing row — or a NULL column on an existing row — means the storefront
 * falls back: salon-wide logo/cover image first, then the theme default.
 */
class SalonThemeAsset extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'salon_id',
        'theme',
        'logo_path',
        'banner_path',
        'banner_heading',
        'banner_subheading',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    /**
     * Read the override for a salon/theme pair, ignoring tenant scope so this
     * also works on the public storefront where no tenant is current.
     */
    public static function lookup(int $salonId, string $theme): ?self
    {
        return static::query()
            ->withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->where('theme', $theme)
            ->first();
    }
}
