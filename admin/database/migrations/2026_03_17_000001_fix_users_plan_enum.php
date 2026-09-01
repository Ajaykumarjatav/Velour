<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix the `users`.`plan` ENUM to match the application's plan keys.
 *
 * The original migration defined: ['starter','growth','pro','enterprise']
 * The application code uses:      ['free','starter','pro','enterprise']
 *
 * This migration:
 *  1. Converts any existing 'growth' rows to 'pro' (closest equivalent)
 *  2. Converts any NULL rows to 'starter' (the default paid entry plan)
 *  3. Alters the column to the correct ENUM values
 */
return new class extends Migration
{
    public function up(): void
    {
        // Normalise any stale values before changing the ENUM
        DB::statement("UPDATE users SET plan = 'pro'     WHERE plan = 'growth'");
        DB::statement("UPDATE users SET plan = 'starter' WHERE plan IS NULL OR plan = ''");

        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN plan ENUM('free','starter','pro','enterprise')
            NOT NULL DEFAULT 'starter'
        ");
    }

    public function down(): void
    {
        // Revert 'free' rows to 'starter' before restoring old ENUM
        DB::statement("UPDATE users SET plan = 'starter' WHERE plan = 'free'");

        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN plan ENUM('starter','growth','pro','enterprise')
            NOT NULL DEFAULT 'growth'
        ");
    }
};
