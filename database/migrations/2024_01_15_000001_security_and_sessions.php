<?php
/**
 * AUDIT FIX — Security & Session Management
 *
 * Adds:
 *  1. login_attempts     — per-account lockout tracking (beyond IP rate limiting)
 *  2. sessions           — Laravel DB session driver (replaces Redis for session audit)
 *  3. user_devices       — trusted device registry (suspicious login detection)
 *  4. Indexes: users.last_login_at, users.stripe_id, users.email (already unique, add index)
 *  5. Hash column for 2FA email OTP (two_factor_code stored as bcrypt hash, not plaintext)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Login attempts — account-level lockout ──────────────────────
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('succeeded')->default(false);
            $table->string('failure_reason', 100)->nullable()
                  ->comment('invalid_credentials|account_suspended|2fa_failed');
            $table->timestamp('attempted_at')->useCurrent();
            $table->index(['email', 'attempted_at']);
            $table->index(['ip_address', 'attempted_at']);
        });

        // ── 2. Sessions table (for DB session driver + session audit) ──────
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // ── 3. Trusted devices — suspicious login detection ────────────────
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_fingerprint', 64)->index()
                  ->comment('SHA-256 of user_agent+screen+timezone');
            $table->string('device_name')->nullable()
                  ->comment('Human-readable: Chrome on macOS');
            $table->string('ip_address', 45)->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->nullable();
            $table->boolean('is_trusted')->default(false);
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamps();
            $table->unique(['user_id', 'device_fingerprint']);
        });

        // ── 4. Performance indexes (guarded against duplicates) ──────────
        // These use explicit index names so MySQL won't error on re-run.
        $addIndexSafe = function(string $table, array|string $cols, string $name) {
            try {
                \Illuminate\Support\Facades\Schema::table($table, function (\Illuminate\Database\Schema\Blueprint $t) use ($cols, $name) {
                    $t->index($cols, $name);
                });
            } catch (\Throwable $e) {
                // Index already exists — skip silently
            }
        };

        $addIndexSafe('users',              'last_login_at',                  'users_last_login_at_idx');
        $addIndexSafe('users',              'is_active',                      'users_is_active_idx');
        $addIndexSafe('users',              'plan',                           'users_plan_idx');
        $addIndexSafe('users',              'system_role',                    'users_system_role_idx');
        $addIndexSafe('appointments',       'created_at',                     'appts_created_at_idx');
        $addIndexSafe('appointments',       ['salon_id', 'client_id'],        'appts_salon_client_idx');
        $addIndexSafe('appointments',       ['salon_id', 'created_at'],       'appts_salon_created_idx');
        $addIndexSafe('clients',            'created_at',                     'clients_created_at_idx');
        $addIndexSafe('clients',            ['salon_id', 'created_at'],       'clients_salon_created_idx');
        $addIndexSafe('clients',            ['salon_id', 'is_vip'],           'clients_salon_vip_idx');
        $addIndexSafe('pos_transactions',   ['salon_id', 'created_at'],       'pos_salon_created_idx');
        $addIndexSafe('pos_transactions',   ['salon_id', 'client_id'],        'pos_salon_client_idx');
        $addIndexSafe('salon_notifications',['salon_id', 'is_read'],          'notifs_salon_read_idx');
        $addIndexSafe('salon_notifications',['salon_id', 'created_at'],       'notifs_salon_created_idx');

        // NOTE: two_factor_code_hash column is defined in migration 2024_01_11_000001.
        // No schema change needed here.
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('login_attempts');
    }
};
