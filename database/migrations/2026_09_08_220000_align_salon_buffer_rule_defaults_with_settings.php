<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Align salon_buffer_rules column defaults with Settings screenshots for new salons:
     * buffer before/after 0, advance 60 days, last-minute cut-off 6 hours, overbooking 0%.
     */
    public function up(): void
    {
        if (! Schema::hasTable('salon_buffer_rules')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY buffer_before_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY buffer_after_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY advance_booking_days SMALLINT UNSIGNED NOT NULL DEFAULT 60');
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY last_minute_cutoff_hours SMALLINT UNSIGNED NOT NULL DEFAULT 6');
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY overbooking_percent TINYINT UNSIGNED NOT NULL DEFAULT 0');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN buffer_before_minutes SET DEFAULT 0');
            DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN buffer_after_minutes SET DEFAULT 0');
            DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN advance_booking_days SET DEFAULT 60');
            DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN last_minute_cutoff_hours SET DEFAULT 6');
            DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN overbooking_percent SET DEFAULT 0');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('salon_buffer_rules')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY buffer_before_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 10');
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY buffer_after_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 15');
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY advance_booking_days SMALLINT UNSIGNED NOT NULL DEFAULT 60');
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY last_minute_cutoff_hours SMALLINT UNSIGNED NOT NULL DEFAULT 2');
            DB::statement('ALTER TABLE salon_buffer_rules MODIFY overbooking_percent TINYINT UNSIGNED NOT NULL DEFAULT 0');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN buffer_before_minutes SET DEFAULT 10');
            DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN buffer_after_minutes SET DEFAULT 15');
            DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN advance_booking_days SET DEFAULT 60');
            DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN last_minute_cutoff_hours SET DEFAULT 2');
            DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN overbooking_percent SET DEFAULT 0');
        }
    }
};
