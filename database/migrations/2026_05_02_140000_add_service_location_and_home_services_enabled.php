<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table): void {
            $table->boolean('home_services_enabled')->default(false)->after('online_booking_enabled');
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->string('service_location', 16)->default('onsite')->after('allowed_roles');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn('service_location');
        });

        Schema::table('salons', function (Blueprint $table): void {
            $table->dropColumn('home_services_enabled');
        });
    }
};
