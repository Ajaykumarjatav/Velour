<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Replace legacy plan keys (free/starter/pro/enterprise) with trial/standard/premium.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Widen column so new values can be written before the final ENUM is applied.
        DB::statement("ALTER TABLE users MODIFY COLUMN plan VARCHAR(30) NOT NULL DEFAULT 'trial'");

        DB::statement("UPDATE users SET plan = 'trial' WHERE plan IN ('free', 'starter') OR plan IS NULL OR plan = ''");
        DB::statement("UPDATE users SET plan = 'standard' WHERE plan = 'pro'");
        DB::statement("UPDATE users SET plan = 'premium' WHERE plan = 'enterprise'");

        DB::statement("UPDATE tenant_plan_overrides SET override_plan = 'trial' WHERE override_plan IN ('free', 'starter')");
        DB::statement("UPDATE tenant_plan_overrides SET override_plan = 'standard' WHERE override_plan = 'pro'");
        DB::statement("UPDATE tenant_plan_overrides SET override_plan = 'premium' WHERE override_plan = 'enterprise'");

        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN plan ENUM('trial','standard','premium')
            NOT NULL DEFAULT 'trial'
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN plan VARCHAR(30) NOT NULL DEFAULT 'free'");

        DB::statement("UPDATE users SET plan = 'free' WHERE plan = 'trial'");
        DB::statement("UPDATE users SET plan = 'starter' WHERE plan = 'standard'");
        DB::statement("UPDATE users SET plan = 'enterprise' WHERE plan = 'premium'");

        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN plan ENUM('free','starter','pro','enterprise')
            NOT NULL DEFAULT 'free'
        ");
    }
};
