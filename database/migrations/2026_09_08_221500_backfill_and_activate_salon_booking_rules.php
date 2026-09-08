<?php

use App\Models\Salon;
use App\Models\SalonBufferRule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Retroactively apply screenshot defaults to every existing salon / buffer-rule row.
     * Does not change max_daily_bookings_per_staff.
     * Does not rewrite existing appointment start/end timestamps.
     */
    public function up(): void
    {
        $defaults = SalonBufferRule::defaultsForNewSalon();

        if (Schema::hasTable('salon_buffer_rules')) {
            DB::table('salon_buffer_rules')->update([
                'buffer_before_minutes' => $defaults['buffer_before_minutes'],
                'buffer_after_minutes' => $defaults['buffer_after_minutes'],
                'advance_booking_days' => $defaults['advance_booking_days'],
                'last_minute_cutoff_hours' => $defaults['last_minute_cutoff_hours'],
                'overbooking_percent' => $defaults['overbooking_percent'],
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('salons')) {
            $salonIds = DB::table('salons')->pluck('id');
            foreach ($salonIds as $salonId) {
                $exists = DB::table('salon_buffer_rules')->where('salon_id', $salonId)->exists();
                if (! $exists) {
                    DB::table('salon_buffer_rules')->insert([
                        'salon_id' => $salonId,
                        'buffer_before_minutes' => $defaults['buffer_before_minutes'],
                        'buffer_after_minutes' => $defaults['buffer_after_minutes'],
                        'max_daily_bookings_per_staff' => $defaults['max_daily_bookings_per_staff'],
                        'advance_booking_days' => $defaults['advance_booking_days'],
                        'last_minute_cutoff_hours' => $defaults['last_minute_cutoff_hours'],
                        'overbooking_percent' => $defaults['overbooking_percent'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('salons')->update([
                'booking_advance_days' => $defaults['advance_booking_days'],
                'updated_at' => now(),
            ]);
        }

        // Align column defaults for any future inserts that omit values.
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' && Schema::hasTable('salon_buffer_rules')) {
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY buffer_before_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY buffer_after_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY advance_booking_days SMALLINT UNSIGNED NOT NULL DEFAULT 60');
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY last_minute_cutoff_hours SMALLINT UNSIGNED NOT NULL DEFAULT 6');
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY overbooking_percent TINYINT UNSIGNED NOT NULL DEFAULT 0');
        }
    }

    public function down(): void
    {
        // Non-destructive: keep applied values; column defaults revert is optional.
    }
};
