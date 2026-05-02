<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')->where('name', 'Hydrating Facial')->update(['name' => 'Hydra Facial']);

        DB::table('services')
            ->where('slug', 'hydrating-facial')
            ->whereNotExists(function ($q): void {
                $q->select(DB::raw(1))
                    ->from('services as s2')
                    ->whereColumn('s2.salon_id', 'services.salon_id')
                    ->where('s2.slug', 'hydra-facial');
            })
            ->update(['slug' => 'hydra-facial']);

        DB::table('appointment_services')
            ->where('service_name', 'Hydrating Facial')
            ->update(['service_name' => 'Hydra Facial']);
    }

    public function down(): void
    {
        DB::table('services')->where('name', 'Hydra Facial')->update(['name' => 'Hydrating Facial']);

        DB::table('services')
            ->where('slug', 'hydra-facial')
            ->whereNotExists(function ($q): void {
                $q->select(DB::raw(1))
                    ->from('services as s2')
                    ->whereColumn('s2.salon_id', 'services.salon_id')
                    ->where('s2.slug', 'hydrating-facial');
            })
            ->update(['slug' => 'hydrating-facial']);

        DB::table('appointment_services')
            ->where('service_name', 'Hydra Facial')
            ->update(['service_name' => 'Hydrating Facial']);
    }
};
