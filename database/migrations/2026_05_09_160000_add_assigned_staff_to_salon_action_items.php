<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salon_action_items', function (Blueprint $table) {
            $table->foreignId('assigned_staff_id')->nullable()->after('staff_id')->constrained('staff')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('salon_action_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_staff_id');
        });
    }
};
