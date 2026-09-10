<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('link_visits', function (Blueprint $table): void {
            $table->boolean('is_bot')->default(false)->after('device');
            $table->text('user_agent')->nullable()->after('is_bot');
        });
    }

    public function down(): void
    {
        Schema::table('link_visits', function (Blueprint $table): void {
            $table->dropColumn(['is_bot', 'user_agent']);
        });
    }
};
