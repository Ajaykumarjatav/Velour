<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('expenses', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('expenses', 'status')) {
                $table->string('status', 20)->default('recorded')->after('receipt_path');
                $table->index(['salon_id', 'status']);
            }
            if (! Schema::hasColumn('expenses', 'recurring_interval')) {
                $table->string('recurring_interval', 20)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'recurring_interval')) {
                $table->dropColumn('recurring_interval');
            }
            if (Schema::hasColumn('expenses', 'status')) {
                $table->dropIndex(['salon_id', 'status']);
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('expenses', 'receipt_path')) {
                $table->dropColumn('receipt_path');
            }
        });
    }
};
