<?php

namespace App\Support;

use App\Models\Salon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Rules for customer-facing salon website, booking widget, and client portal.
 */
final class PublicSalonAccess
{
    public static function query(): Builder
    {
        return Salon::query()
            ->where('is_active', true)
            ->whereHas('owner', fn (Builder $q) => $q->where('is_active', true));
    }

    public static function findBySlug(string $slug): ?Salon
    {
        return static::query()->where('slug', $slug)->first();
    }

    public static function findBySlugOrFail(string $slug): Salon
    {
        return static::query()->where('slug', $slug)->firstOrFail();
    }

    public static function isAccessible(Salon $salon): bool
    {
        if (! $salon->is_active) {
            return false;
        }

        $owner = $salon->relationLoaded('owner')
            ? $salon->owner
            : $salon->owner()->first();

        return $owner && $owner->is_active;
    }

    /** @return list<string> */
    public static function unavailableReasons(Salon $salon): array
    {
        $salon->loadMissing('owner');

        if ($salon->owner && ! $salon->owner->is_active) {
            return ['This business is temporarily unavailable. Please contact the salon directly.'];
        }

        if (! $salon->is_active) {
            return ['This location is temporarily unavailable. Please contact the salon directly.'];
        }

        return ['This business is temporarily unavailable. Please contact the salon directly.'];
    }
}
