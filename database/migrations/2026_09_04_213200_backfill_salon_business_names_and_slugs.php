<?php

use App\Models\Salon;
use App\Support\SalonSlug;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Backfill missing salon business names from owner full name,
 * and align storefront slug/subdomain with the business name when the
 * current slug still looks owner-derived (e.g. ashutoshs-business).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('salons')) {
            return;
        }

        Salon::withoutGlobalScopes()
            ->with('owner:id,name')
            ->orderBy('id')
            ->chunkById(100, function ($salons): void {
                foreach ($salons as $salon) {
                    $name = trim((string) $salon->name);
                    $ownerName = trim((string) ($salon->owner?->name ?? ''));
                    $nameWasMissing = $name === '';

                    if ($nameWasMissing) {
                        $name = $ownerName !== '' ? $ownerName : 'My Business';
                        // Avoid colliding with an existing unique business name.
                        if (SalonSlug::nameTaken($name, (int) $salon->id)) {
                            $name = $name.' '.(int) $salon->id;
                        }
                        $salon->name = $name;
                    }

                    $currentSlug = strtolower(trim((string) $salon->slug));
                    $shouldSyncSlug = $currentSlug === ''
                        || $nameWasMissing
                        || $this->slugLooksOwnerDerived($salon, $ownerName, $currentSlug);

                    if ($shouldSyncSlug) {
                        $slug = SalonSlug::uniqueFromName($name, (int) $salon->id);
                        $salon->slug = $slug;
                        $salon->subdomain = $slug;
                    }

                    if ($salon->isDirty()) {
                        $salon->saveQuietly();
                    }
                }
            });
    }

    public function down(): void
    {
        // Irreversible data backfill.
    }

    private function slugLooksOwnerDerived(Salon $salon, string $ownerName, string $currentSlug): bool
    {
        if ($ownerName === '') {
            return false;
        }

        $candidates = [];
        foreach ([
            "{$ownerName}'s Business",
            "{$ownerName}'s Salon",
            "{$ownerName} Business",
            "{$ownerName} Salon",
            $ownerName,
        ] as $label) {
            $slug = Str::slug($label);
            if ($slug !== '') {
                $candidates[] = $slug;
            }
        }

        foreach ($candidates as $candidate) {
            if ($currentSlug === $candidate || str_starts_with($currentSlug, $candidate.'-')) {
                // Only rewrite when salon business name no longer matches that owner-derived slug.
                $nameSlug = Str::slug(trim((string) $salon->name));
                if ($nameSlug !== '' && $currentSlug !== $nameSlug && ! str_starts_with($currentSlug, $nameSlug.'-')) {
                    return true;
                }
            }
        }

        return false;
    }
};
