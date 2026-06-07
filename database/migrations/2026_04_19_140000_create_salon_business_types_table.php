<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_business_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained('salons')->cascadeOnDelete();
            $table->foreignId('business_type_id')->constrained('business_types')->restrictOnDelete();
            $table->unique(['salon_id', 'business_type_id']);
            $table->timestamps();
        });

        $rows = DB::table('salons')->select('id', 'business_type_id')->get();
        $now = now();
        foreach ($rows as $row) {
            if (! $row->business_type_id) {
                continue;
            }
            DB::table('salon_business_types')->insertOrIgnore([
                'salon_id'         => $row->id,
                'business_type_id' => $row->business_type_id,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_business_types');
    }
};
