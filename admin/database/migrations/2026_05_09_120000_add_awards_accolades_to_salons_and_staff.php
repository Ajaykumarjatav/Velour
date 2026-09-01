<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->text('awards_accolades')->nullable()->after('description');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->text('awards_accolades')->nullable()->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn('awards_accolades');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('awards_accolades');
        });
    }
};
