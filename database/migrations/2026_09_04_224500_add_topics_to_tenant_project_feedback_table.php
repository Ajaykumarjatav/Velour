<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_project_feedback', function (Blueprint $table) {
            $table->json('topics')->nullable()->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_project_feedback', function (Blueprint $table) {
            $table->dropColumn('topics');
        });
    }
};
