<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('link_visits', function (Blueprint $table): void {
            $table->string('kind', 20)->default('visit')->after('page');
            $table->index(['salon_id', 'kind', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('link_visits', function (Blueprint $table): void {
            $table->dropIndex(['salon_id', 'kind', 'created_at']);
            $table->dropColumn('kind');
        });
    }
};
