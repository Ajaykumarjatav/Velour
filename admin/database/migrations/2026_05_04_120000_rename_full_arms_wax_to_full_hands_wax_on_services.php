<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')->where('name', 'Full Arms Wax')->update(['name' => 'Full Hands Wax']);

        DB::table('services')
            ->where('slug', 'full-arms-wax')
            ->whereNotExists(function ($q): void {
                $q->select(DB::raw(1))
                    ->from('services as s2')
                    ->whereColumn('s2.salon_id', 'services.salon_id')
                    ->where('s2.slug', 'full-hands-wax');
            })
            ->update(['slug' => 'full-hands-wax']);
    }

    public function down(): void
    {
        DB::table('services')->where('name', 'Full Hands Wax')->update(['name' => 'Full Arms Wax']);
        DB::table('services')->where('slug', 'full-hands-wax')->update(['slug' => 'full-arms-wax']);
    }
};
