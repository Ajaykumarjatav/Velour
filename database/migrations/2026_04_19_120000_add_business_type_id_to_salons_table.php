<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultId = DB::table('business_types')->where('slug', 'salon')->value('id');
        if (! $defaultId) {
            throw new RuntimeException('business_types must be seeded before adding salons.business_type_id. Run: php artisan db:seed --class=Database\\Seeders\\BusinessTypeSeeder');
        }

        Schema::table('salons', function (Blueprint $table) use ($defaultId) {
            $table->foreignId('business_type_id')
                ->after('owner_id')
                ->default($defaultId)
                ->constrained('business_types')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_type_id');
        });
    }
};
