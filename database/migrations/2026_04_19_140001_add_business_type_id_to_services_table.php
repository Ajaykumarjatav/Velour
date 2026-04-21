<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('business_type_id')
                ->nullable()
                ->after('salon_id')
                ->constrained('business_types')
                ->restrictOnDelete();
        });

        $services = DB::table('services')->select('id', 'salon_id')->get();
        foreach ($services as $service) {
            $bid = DB::table('salons')->where('id', $service->salon_id)->value('business_type_id');
            if ($bid) {
                DB::table('services')->where('id', $service->id)->update(['business_type_id' => $bid]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_type_id');
        });
    }
};
