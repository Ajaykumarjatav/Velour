<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('salons') && Schema::hasColumn('salons', 'home_services_enabled')) {
            Schema::table('salons', function (Blueprint $table): void {
                $table->dropColumn('home_services_enabled');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('salons') && ! Schema::hasColumn('salons', 'home_services_enabled')) {
            Schema::table('salons', function (Blueprint $table): void {
                $table->boolean('home_services_enabled')->default(false)->after('online_booking_enabled');
            });
        }
    }
};
