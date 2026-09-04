<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Salon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Storefront URL slug helpers — always derive from business name, not owner name.
 */
final class SalonSlug
{
    public static function uniqueFromName(string $name, ?int $exceptSalonId = null): string
    {
        $base = Str::slug(trim($name)) ?: 'salon';
        if (in_array($base, SalonUrl::RESERVED, true)) {
            $base = 'salon-'.$base;
        }

        $slug = $base;
        $n = 1;

        while (self::slugTaken($slug, $exceptSalonId)) {
            $slug = $base.'-'.$n;
            $n++;
        }

        return $slug;
    }

    public static function findSalonByAlias(string $slug): ?Salon
    {
        $slug = strtolower(trim($slug));
        if ($slug === '' || ! Schema::hasTable('salon_slug_aliases')) {
            return null;
        }

        $salonId = DB::table('salon_slug_aliases')->where('slug', $slug)->value('salon_id');
        if (! $salonId) {
            return null;
        }

        return Salon::withoutGlobalScopes()->find((int) $salonId);
    }

    /** Keep an old slug working after a business-name URL change. */
    public static function rememberAlias(int $salonId, ?string $previousSlug): void
    {
        $previousSlug = strtolower(trim((string) $previousSlug));
        if ($previousSlug === '' || ! Schema::hasTable('salon_slug_aliases')) {
            return;
        }
        if (in_array($previousSlug, SalonUrl::RESERVED, true)) {
            return;
        }
        if (self::slugTaken($previousSlug, $salonId)) {
            return;
        }

        DB::table('salon_slug_aliases')->updateOrInsert(
            ['slug' => $previousSlug],
            ['salon_id' => $salonId, 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public static function applyNewSlug(Salon $salon, string $newSlug): void
    {
        $oldSlug = strtolower(trim((string) ($salon->subdomain ?: $salon->slug)));
        $newSlug = strtolower(trim($newSlug));
        if ($newSlug === '' || $newSlug === $oldSlug) {
            return;
        }

        self::rememberAlias((int) $salon->id, $oldSlug);
        if ($salon->slug && strtolower((string) $salon->slug) !== $oldSlug) {
            self::rememberAlias((int) $salon->id, (string) $salon->slug);
        }

        $salon->slug = $newSlug;
        $salon->subdomain = $newSlug;
    }

    public static function slugTaken(string $slug, ?int $exceptSalonId = null): bool
    {
        if ($slug === '' || in_array($slug, SalonUrl::RESERVED, true)) {
            return true;
        }

        $query = Salon::withoutGlobalScopes()
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug)->orWhere('subdomain', $slug);
            });

        if ($exceptSalonId !== null) {
            $query->where('id', '!=', $exceptSalonId);
        }

        if ($query->exists()) {
            return true;
        }

        if (! Schema::hasTable('salon_slug_aliases')) {
            return false;
        }

        $aliasQuery = DB::table('salon_slug_aliases')->where('slug', $slug);
        if ($exceptSalonId !== null) {
            $aliasQuery->where('salon_id', '!=', $exceptSalonId);
        }

        return $aliasQuery->exists();
    }

    /** Case-insensitive business name uniqueness. */
    public static function nameTaken(string $name, ?int $exceptSalonId = null): bool
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }

        $query = Salon::withoutGlobalScopes()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)]);

        if ($exceptSalonId !== null) {
            $query->where('id', '!=', $exceptSalonId);
        }

        return $query->exists();
    }

    /**
     * Validation rule: business name must be unique (case-insensitive).
     *
     * @return list<\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public static function uniqueNameRules(?int $exceptSalonId = null): array
    {
        return [
            'required',
            'string',
            'min:2',
            'max:150',
            'regex:/^[\pL\pN\s\'&.,\-]+$/u',
            function (string $attribute, mixed $value, \Closure $fail) use ($exceptSalonId): void {
                if (! is_string($value) || trim($value) === '') {
                    return;
                }
                if (mb_strlen(trim($value)) < 2) {
                    return;
                }
                if (self::nameTaken($value, $exceptSalonId)) {
                    $fail('This business name is already taken. Please choose another.');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public static function uniqueNameMessages(string $attribute = 'business_name'): array
    {
        return [
            "{$attribute}.required" => 'Business name is required.',
            "{$attribute}.min" => 'Business name must be at least 2 characters.',
            "{$attribute}.max" => 'Business name must be at most 150 characters.',
            "{$attribute}.regex" => 'Business name may only contain letters, numbers, spaces, and . , \' & -',
        ];
    }

    /**
     * Whether updating the business name should regenerate slug/subdomain.
     */
    public static function shouldSyncFromName(Salon $salon, string $oldName, string $newName): bool
    {
        if (trim($oldName) === trim($newName)) {
            return false;
        }

        $currentSlug = strtolower(trim((string) $salon->slug));
        if ($currentSlug === '') {
            return true;
        }

        foreach (self::autoSlugCandidates($oldName, $salon) as $candidate) {
            if ($currentSlug === $candidate || str_starts_with($currentSlug, $candidate.'-')) {
                return true;
            }
        }

        // Always sync when current slug still matches the previous business name.
        $oldSlug = Str::slug(trim($oldName));
        if ($oldSlug !== '' && ($currentSlug === $oldSlug || str_starts_with($currentSlug, $oldSlug.'-'))) {
            return true;
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private static function autoSlugCandidates(string $salonName, Salon $salon): array
    {
        $candidates = [];

        $fromName = Str::slug(trim($salonName));
        if ($fromName !== '') {
            $candidates[] = $fromName;
        }

        $ownerName = trim((string) ($salon->owner?->name ?? ''));
        if ($ownerName !== '') {
            foreach ([
                "{$ownerName}'s Business",
                "{$ownerName}'s Salon",
                "{$ownerName} Business",
                "{$ownerName} Salon",
                $ownerName,
            ] as $defaultName) {
                $slug = Str::slug($defaultName);
                if ($slug !== '') {
                    $candidates[] = $slug;
                }
            }
        }

        foreach (['My Business', 'My Salon'] as $placeholder) {
            $slug = Str::slug($placeholder);
            if ($slug !== '') {
                $candidates[] = $slug;
            }
        }

        return array_values(array_unique($candidates));
    }
}
