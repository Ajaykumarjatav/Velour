<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Defaults: advance booking window = 60 days, last-minute cut-off = 0 hours.
     * Applied to every existing salon on deploy.
     */
    public function up(): void
    {
        if (Schema::hasTable('salon_buffer_rules')) {
            DB::table('salon_buffer_rules')->update([
                'advance_booking_days' => 60,
                'last_minute_cutoff_hours' => 0,
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('salons')) {
            DB::table('salons')->update([
                'booking_advance_days' => 60,
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('salon_settings')) {
            DB::table('salon_settings')
                ->where('key', 'booking_advance_days')
                ->update([
                    'value' => '60',
                    'updated_at' => now(),
                ]);
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            if (Schema::hasTable('salon_buffer_rules')) {
                DB::statement('ALTER TABLE salon_buffer_rules MODIFY advance_booking_days SMALLINT UNSIGNED NOT NULL DEFAULT 60');
                DB::statement('ALTER TABLE salon_buffer_rules MODIFY last_minute_cutoff_hours SMALLINT UNSIGNED NOT NULL DEFAULT 0');
            }
            if (Schema::hasTable('salons')) {
                DB::statement('ALTER TABLE salons MODIFY booking_advance_days INTEGER NOT NULL DEFAULT 60');
            }
        }
    }

    public function down(): void
    {
        // Keep applied values. Column defaults only.
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' && Schema::hasTable('salon_buffer_rules')) {
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY last_minute_cutoff_hours SMALLINT UNSIGNED NOT NULL DEFAULT 6');
        }
    }
};
