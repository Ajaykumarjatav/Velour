<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: add_auth_upgrade_columns_to_users_table
 *
 * Adds all columns required by the authentication system upgrade:
 *
 *   system_role               — landlord-level role (super_admin | support | null)
 *                               Distinct from Spatie tenant roles which live in
 *                               the permissions tables. This column is intentionally
 *                               lightweight so super-admin checks are a single
 *                               column read rather than a permission table join.
 *
 *   two_factor_secret         — Base32-encoded TOTP secret (encrypted at rest)
 *
 *   two_factor_recovery_codes — JSON array of 8 single-use backup codes (encrypted)
 *
 *   two_factor_confirmed_at   — NULL means 2FA is not yet confirmed/enabled.
 *                               Set to now() after the user verifies their
 *                               authenticator app with a valid TOTP code.
 *
 *   two_factor_method         — 'totp' (authenticator app) | 'email' (OTP email)
 *
 *   two_factor_code           — Temporary email OTP code (plain int, short-lived)
 *
 *   two_factor_expires_at     — Expiry timestamp for the email OTP code
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // ── Landlord / system role ────────────────────────────────────
            $table->string('system_role', 30)
                  ->nullable()
                  ->default(null)
                  ->after('plan')
                  ->comment('super_admin | support | null (null = regular tenant user)');

            // ── Two-Factor Authentication ─────────────────────────────────
            $table->text('two_factor_secret')
                  ->nullable()
                  ->after('system_role')
                  ->comment('Encrypted Base32 TOTP secret');

            $table->text('two_factor_recovery_codes')
                  ->nullable()
                  ->after('two_factor_secret')
                  ->comment('Encrypted JSON array of 8 backup recovery codes');

            $table->timestamp('two_factor_confirmed_at')
                  ->nullable()
                  ->after('two_factor_recovery_codes')
                  ->comment('NULL = 2FA not enabled; set on first successful TOTP verify');

            $table->string('two_factor_method', 10)
                  ->nullable()
                  ->default(null)
                  ->after('two_factor_confirmed_at')
                  ->comment('totp | email');

            $table->string('two_factor_code_hash', 60)
                  ->nullable()
                  ->after('two_factor_method')
                  ->comment('bcrypt hash of the 6-digit email OTP code');

            $table->timestamp('two_factor_expires_at')
                  ->nullable()
                  ->after('two_factor_code_hash')
                  ->comment('Expiry for email OTP code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'system_role',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'two_factor_method',
                'two_factor_code_hash',
                'two_factor_expires_at',
            ]);
        });
    }
};
