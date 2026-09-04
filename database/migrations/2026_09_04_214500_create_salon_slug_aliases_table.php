<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Keep old storefront/panel slugs working after business-name URL updates.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('salon_slug_aliases')) {
            Schema::create('salon_slug_aliases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('salon_id')->constrained('salons')->cascadeOnDelete();
                $table->string('slug', 100);
                $table->timestamps();
                $table->unique('slug');
                $table->index('salon_id');
            });
        }

        if (! Schema::hasTable('salons') || ! Schema::hasTable('users')) {
            return;
        }

        $salons = DB::table('salons')
            ->leftJoin('users', 'users.id', '=', 'salons.owner_id')
            ->select('salons.id', 'salons.name', 'salons.slug', 'salons.subdomain', 'users.name as owner_name')
            ->orderBy('salons.id')
            ->get();

        $taken = [];
        foreach ($salons as $salon) {
            foreach ([(string) $salon->slug, (string) $salon->subdomain] as $live) {
                $live = strtolower(trim($live));
                if ($live !== '') {
                    $taken[$live] = true;
                }
            }
        }
        foreach (DB::table('salon_slug_aliases')->pluck('slug') as $existing) {
            $taken[strtolower((string) $existing)] = true;
        }

        $now = now();
        $rows = [];
        foreach ($salons as $salon) {
            $ownerName = trim((string) ($salon->owner_name ?? ''));
            if ($ownerName === '') {
                continue;
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

            $current = strtolower(trim((string) ($salon->subdomain ?: $salon->slug)));
            foreach (array_unique($candidates) as $candidate) {
                if ($candidate === $current || isset($taken[$candidate])) {
                    continue;
                }
                // Only alias when current business-name slug differs from owner-derived candidate.
                $nameSlug = Str::slug(trim((string) $salon->name));
                if ($nameSlug !== '' && ($candidate === $nameSlug || str_starts_with($candidate, $nameSlug.'-'))) {
                    continue;
                }

                $rows[] = [
                    'salon_id' => (int) $salon->id,
                    'slug' => $candidate,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $taken[$candidate] = true;
            }
        }

        if ($rows !== []) {
            DB::table('salon_slug_aliases')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_slug_aliases');
    }
};
