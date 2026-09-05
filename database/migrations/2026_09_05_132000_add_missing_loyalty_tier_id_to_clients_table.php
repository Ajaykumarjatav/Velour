<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('loyalty_tiers')) {
            return;
        }

        if (Schema::hasColumn('clients', 'loyalty_tier_id')) {
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('loyalty_tier_id')
                ->nullable()
                ->after('salon_id')
                ->constrained('loyalty_tiers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('clients', 'loyalty_tier_id')) {
            return;
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('loyalty_tier_id');
        });
    }
};
