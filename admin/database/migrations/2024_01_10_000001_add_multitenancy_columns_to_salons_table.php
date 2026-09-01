<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|──────────────────────────────────────────────────────────────────────────────
| Migration: add_multitenancy_columns_to_salons_table
|──────────────────────────────────────────────────────────────────────────────
|
| Adds the two columns used for tenant resolution:
|
|   domain    — a fully-qualified custom domain the salon owner has mapped,
|               e.g. "bookings.elegancehair.com".
|               NULL until the salon upgrades and configures their own domain.
|
|   subdomain — the unique, URL-safe slug used as a subdomain on the shared
|               Velour platform, e.g. "elegancehair" → elegancehair.velour.app.
|               Auto-populated from the existing `slug` column on registration.
|
| Both columns have unique constraints so the TenantFinder can do a direct
| indexed lookup on every request without a full-table scan.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table) {

            // Custom domain (e.g. "bookings.mysalon.com").  NULL = not configured.
            $table->string('domain', 253)
                  ->nullable()
                  ->unique()
                  ->after('slug')
                  ->comment('Custom CNAME domain mapped by the salon owner');

            // Subdomain slug (e.g. "mysalon" → mysalon.velour.app).
            $table->string('subdomain', 63)
                  ->nullable()
                  ->unique()
                  ->after('domain')
                  ->comment('Subdomain on the shared velour.app platform');
        });

        // Back-fill `subdomain` from the existing `slug` column so that all
        // existing salons immediately get a working subdomain URL.
        DB::statement('UPDATE salons SET subdomain = slug WHERE subdomain IS NULL');
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropUnique(['domain']);
            $table->dropUnique(['subdomain']);
            $table->dropColumn(['domain', 'subdomain']);
        });
    }
};
