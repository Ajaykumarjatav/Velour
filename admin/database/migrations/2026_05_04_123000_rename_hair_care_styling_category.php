<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('service_categories')
            ->where('slug', 'hair-care-styling')
            ->update(['name' => 'Hair wash & blow dry']);

        DB::table('service_categories')
            ->where('name', 'Hair Care & Styling')
            ->update(['name' => 'Hair wash & blow dry']);
    }

    public function down(): void
    {
        DB::table('service_categories')
            ->where('name', 'Hair wash & blow dry')
            ->where('slug', 'hair-care-styling')
            ->update(['name' => 'Hair Care & Styling']);
    }
};
