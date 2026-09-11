<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('salons') && Schema::hasColumn('salons', 'is_freelancer')) {
            Schema::table('salons', function (Blueprint $table): void {
                $table->dropColumn('is_freelancer');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('salons') && ! Schema::hasColumn('salons', 'is_freelancer')) {
            Schema::table('salons', function (Blueprint $table): void {
                $table->boolean('is_freelancer')->default(false)->after('home_services_enabled');
            });
        }
    }
};
