<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE services MODIFY buffer_minutes INT NOT NULL DEFAULT 0');
            if (Schema::hasTable('salon_buffer_rules')) {
                DB::statement('ALTER TABLE salon_buffer_rules MODIFY buffer_before_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0');
                DB::statement('ALTER TABLE salon_buffer_rules MODIFY buffer_after_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0');
            }

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE services ALTER COLUMN buffer_minutes SET DEFAULT 0');
            if (Schema::hasTable('salon_buffer_rules')) {
                DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN buffer_before_minutes SET DEFAULT 0');
                DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN buffer_after_minutes SET DEFAULT 0');
            }
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE services MODIFY buffer_minutes INT NOT NULL DEFAULT 10');
            if (Schema::hasTable('salon_buffer_rules')) {
                DB::statement('ALTER TABLE salon_buffer_rules MODIFY buffer_before_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 10');
                DB::statement('ALTER TABLE salon_buffer_rules MODIFY buffer_after_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 15');
            }

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE services ALTER COLUMN buffer_minutes SET DEFAULT 10');
            if (Schema::hasTable('salon_buffer_rules')) {
                DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN buffer_before_minutes SET DEFAULT 10');
                DB::statement('ALTER TABLE salon_buffer_rules ALTER COLUMN buffer_after_minutes SET DEFAULT 15');
            }
        }
    }
};
