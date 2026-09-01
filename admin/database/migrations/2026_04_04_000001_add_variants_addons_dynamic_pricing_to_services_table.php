<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->json('variants')->nullable()->after('color');
            $table->json('addons')->nullable()->after('variants');
            $table->boolean('dynamic_pricing_enabled')->default(false)->after('addons');
            $table->string('staff_level', 30)->nullable()->after('dynamic_pricing_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['variants', 'addons', 'dynamic_pricing_enabled', 'staff_level']);
        });
    }
};
