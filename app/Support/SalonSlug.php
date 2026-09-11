<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Salon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Storefront URL slug helpers — always derive from business name, not owner name.
 */
final class SalonSlug
{
    private const MAX_LENGTH = 63;

    /**
     * Unique storefront slug from the business name. When the name is already taken the
     * slug gets a word from the business itself (dina-glow-saloon-jaipur), never a digit.
     *
     * @param  array{city?: ?string, area?: ?string, business_type?: ?string, owner_name?: ?string}  $context
     */
    public static function uniqueFromName(string $name, ?int $exceptSalonId = null, array $context = []): string
    {
        $base = Str::slug(trim($name)) ?: 'salon';
        if (in_array($base, SalonUrl::RESERVED, true)) {
            $base = 'salon-'.$base;
        }

        if (! self::slugTaken(self::capped($base), $exceptSalonId)) {
            return self::capped($base);
        }

        foreach (self::suffixCandidates($base, $context) as $suffix) {
            $candidate = self::withSuffix($base, $suffix);
            if (! self::slugTaken($candidate, $exceptSalonId)) {
                return $candidate;
            }
        }

        // Everything descriptive is taken — fall back to a short pronounceable token so
        // the URL still reads like a word rather than ending in a counter.
        for ($attempt = 0; $attempt < 40; $attempt++) {
            $candidate = self::withSuffix($base, self::randomToken($attempt < 20 ? 2 : 3));
            if (! self::slugTaken($candidate, $exceptSalonId)) {
                return $candidate;
            }
        }

        return self::withSuffix($base, self::randomToken(3).self::randomToken(2));
    }

    /**
     * Insert a salon with a fresh slug, retrying when a simultaneous signup claimed the
     * same slug a moment earlier. The unique indexes on salons.slug / salons.subdomain
     * make that collision an error rather than a duplicate URL; the retry just picks the
     * next suffix instead of showing the user a failed signup.
     *
     * @param  array<string, ?string>  $context
     * @param  callable(string): Salon  $create  receives the slug to store
     */
    public static function createWithUniqueSlug(string $name, array $context, callable $create, int $attempts = 4): Salon
    {
        for ($attempt = 1; ; $attempt++) {
            $slug = self::uniqueFromName($name, null, $context);

            try {
                return $create($slug);
            } catch (QueryException $e) {
                if ($attempt >= $attempts || ! self::isDuplicateSlugError($e)) {
                    throw $e;
                }
            }
        }
    }

    private static function isDuplicateSlugError(QueryException $e): bool
    {
        if ((string) $e->getCode() !== '23000') {
            return false;
        }

        $message = strtolower($e->getMessage());

        foreach (['salons_slug_unique', 'salons_subdomain_unique', 'salons.slug', 'salons.subdomain'] as $marker) {
            if (str_contains($message, $marker)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Context for slug suffixes, read from the salon the slug belongs to.
     *
     * @return array{city: ?string, area: ?string, business_type: ?string, owner_name: ?string}
     */
    public static function contextForSalon(?Salon $salon): array
    {
        if (! $salon) {
            return [];
        }

        return [
            'city' => $salon->city,
            'area' => $salon->address_line2,
            'business_type' => $salon->businessType?->name,
            'owner_name' => $salon->owner?->name,
        ];
    }

    /**
     * Suffixes ordered best-first: where the business is, what it is, then who runs it.
     *
     * @param  array<string, ?string>  $context
     * @return list<string>
     */
    private static function suffixCandidates(string $base, array $context): array
    {
        $candidates = [];
        $add = function (?string $value) use (&$candidates, $base): void {
            $slug = Str::slug(trim((string) $value));
            // Skip anything the name already says, so we never get dina-salon-salon.
            if ($slug === '' || strlen($slug) > 20 || str_contains($base, $slug)) {
                return;
            }
            $candidates[] = $slug;
        };

        $add($context['city'] ?? null);
        $add($context['area'] ?? null);
        $add($context['business_type'] ?? null);

        $ownerFirstName = strtok(trim((string) ($context['owner_name'] ?? '')), ' ');
        $add($ownerFirstName !== false ? $ownerFirstName : null);

        foreach (['studio', 'boutique', 'official', 'prime', 'central', 'express', 'store', 'place'] as $word) {
            $add($word);
        }

        return array_values(array_unique($candidates));
    }

    /** Short pronounceable filler like "keli" or "vora". */
    private static function randomToken(int $syllables = 2): string
    {
        $consonants = 'bcdfghjklmnprstvwz';
        $vowels = 'aeiou';
        $token = '';

        for ($i = 0; $i < max(1, $syllables); $i++) {
            $token .= $consonants[random_int(0, strlen($consonants) - 1)];
            $token .= $vowels[random_int(0, strlen($vowels) - 1)];
        }

        return $token;
    }

    private static function capped(string $slug): string
    {
        if (strlen($slug) <= self::MAX_LENGTH) {
            return $slug;
        }
        $trimmed = rtrim(substr($slug, 0, self::MAX_LENGTH), '-');

        return $trimmed !== '' ? $trimmed : 'salon';
    }

    private static function withSuffix(string $base, string $suffix): string
    {
        $suffix = trim($suffix, '-');
        if ($suffix === '') {
            return self::capped($base);
        }

        $room = self::MAX_LENGTH - strlen($suffix) - 1;
        $trimmed = $room > 0 ? rtrim(substr($base, 0, $room), '-') : '';
        if ($trimmed === '') {
            $trimmed = 'salon';
        }

        return $trimmed.'-'.$suffix;
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
     * Validation rules for business name (duplicates allowed; URL slug stays unique).
     *
     * @return list<\Illuminate\Contracts\Validation\ValidationRule|string>
     */
    public static function uniqueNameRules(?int $exceptSalonId = null): array // $exceptSalonId kept for call-site compatibility
    {
        return [
            'required',
            'string',
            'min:2',
            'max:150',
            'regex:/^[\pL\pN\s\'&.,\-]+$/u',
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
            if (self::slugLooksAutoGeneratedFrom($currentSlug, $candidate)) {
                return true;
            }
        }

        // Always sync when current slug still matches the previous business name.
        $oldSlug = Str::slug(trim($oldName));
        if ($oldSlug !== '' && self::slugLooksAutoGeneratedFrom($currentSlug, $oldSlug)) {
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

    /**
     * True when $slug is $base or $base plus an auto suffix — the current word style
     * (ajay-saloon-jaipur) as well as the legacy counters (ajay-saloon1, ajay-saloon-1).
     */
    private static function slugLooksAutoGeneratedFrom(string $slug, string $base): bool
    {
        if ($base === '' || $slug === '') {
            return false;
        }
        if ($slug === $base) {
            return true;
        }

        $quoted = preg_quote($base, '/');

        return (bool) preg_match('/^'.$quoted.'(\d+|-[a-z0-9]+(?:-[a-z0-9]+)?)$/', $slug);
    }
}
