<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit Logs — dedicated security event table.
 *
 * Separate from Spatie's activity_log (which tracks model-level CRUD changes).
 * This table tracks *security-significant* events:
 *   • Authentication (login, logout, failed attempts, 2FA)
 *   • Authorisation failures (403s, policy denials)
 *   • Data exports and bulk operations
 *   • Administrative actions (impersonation, role changes)
 *   • Billing events (plan changes, cancellations)
 *   • Suspicious activity flags
 *
 * Retention: configurable via config/security.php (default 365 days).
 * Never deleted on user account deletion — retained for compliance.
 *
 * Indexes are designed for the most common query patterns:
 *   1. Per-tenant timeline         → (salon_id, occurred_at)
 *   2. Per-user history            → (user_id, occurred_at)
 *   3. Event type filter           → (event, occurred_at)
 *   4. Severity triage             → (severity, occurred_at)
 *   5. IP investigation            → (ip_address, occurred_at)
 *   6. Admin bulk queries          → occurred_at alone
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // ── Who ────────────────────────────────────────────────────────

            $table->unsignedBigInteger('user_id')->nullable()
                  ->comment('Null for unauthenticated events (e.g. failed login)');

            $table->string('user_email', 255)->nullable()
                  ->comment('Denormalised so logs survive user deletion');

            $table->string('user_name', 255)->nullable()
                  ->comment('Denormalised display name at time of event');

            // ── Which tenant ───────────────────────────────────────────────

            $table->unsignedBigInteger('salon_id')->nullable()
                  ->comment('Null for platform-level events (super-admin actions)');

            // ── What ──────────────────────────────────────────────────────

            $table->string('event', 100)
                  ->comment('Machine-readable event code: auth.login, auth.failed, policy.denied, etc.');

            $table->string('event_category', 50)
                  ->comment('Top-level category: auth | access | data | billing | admin | security');

            $table->string('severity', 20)->default('info')
                  ->comment('info | warning | critical — drives alerting thresholds');

            $table->text('description')->nullable()
                  ->comment('Human-readable description of the event');

            // ── Context ────────────────────────────────────────────────────

            $table->string('subject_type', 100)->nullable()
                  ->comment('Morphed model class (e.g. App\\Models\\Client)');

            $table->unsignedBigInteger('subject_id')->nullable()
                  ->comment('Morphed model ID');

            $table->json('metadata')->nullable()
                  ->comment('Arbitrary event-specific data (old values, request params, etc.)');

            // ── Request fingerprint ────────────────────────────────────────

            $table->string('ip_address', 45)->nullable()
                  ->comment('IPv4 or IPv6; 45 chars covers full IPv6');

            $table->string('user_agent', 500)->nullable();

            $table->string('session_id', 100)->nullable();

            $table->string('request_id', 36)->nullable()
                  ->comment('X-Request-ID header for log correlation across services');

            $table->string('http_method', 10)->nullable();

            $table->string('url', 500)->nullable();

            // ── When ──────────────────────────────────────────────────────

            $table->timestamp('occurred_at')->useCurrent()
                  ->comment('Event time — set to DB NOW(), never trusts client clock');

            // ── Indexes ────────────────────────────────────────────────────

            $table->index(['salon_id',   'occurred_at'], 'al_tenant_timeline');
            $table->index(['user_id',    'occurred_at'], 'al_user_timeline');
            $table->index(['event',      'occurred_at'], 'al_event_type');
            $table->index(['severity',   'occurred_at'], 'al_severity');
            $table->index(['ip_address', 'occurred_at'], 'al_ip');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
