<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Super Admin Panel — Supporting Tables
 *
 * Creates four new tables:
 *
 *  1. support_tickets       — Customer support queue (admins + tenants)
 *  2. support_ticket_replies — Threaded replies on tickets
 *  3. salon_suspensions      — Full audit trail for every suspension/unsuspension
 *  4. tenant_plan_overrides  — Per-tenant plan overrides (custom limits, extended trials)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Support Tickets ────────────────────────────────────────────────
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();

            // Unique ticket reference shown to users (e.g. VLR-00042)
            $table->string('ticket_number', 20)->unique();

            // Requester context
            $table->unsignedBigInteger('user_id')->nullable()
                  ->comment('User who raised the ticket. Null if created by admin on behalf of tenant.');
            $table->unsignedBigInteger('salon_id')->nullable()
                  ->comment('Associated salon. Null for platform-level tickets (e.g. billing).');

            // Assignment
            $table->unsignedBigInteger('assigned_to')->nullable()
                  ->comment('Super-admin user ID assigned to this ticket.');

            // Content
            $table->string('subject', 255);
            $table->text('body')->comment('Initial ticket body (plain text or Markdown).');
            $table->string('category', 50)->default('general')
                  ->comment('billing | technical | feature_request | account | general | bug');
            $table->string('priority', 20)->default('normal')
                  ->comment('low | normal | high | urgent');
            $table->string('status', 30)->default('open')
                  ->comment('open | in_progress | waiting_on_customer | resolved | closed');

            // Satisfaction rating (0–5, null until rated)
            $table->unsignedTinyInteger('satisfaction_rating')->nullable();
            $table->text('satisfaction_feedback')->nullable();

            // Timestamps
            $table->timestamp('first_replied_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['salon_id',   'status']);
            $table->index(['assigned_to','status']);
            $table->index(['status',     'priority', 'created_at']);
            $table->index('user_id');
        });

        // ── 2. Support Ticket Replies ────────────────────────────────────────
        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id')->comment('Author of the reply.');

            $table->text('body');
            $table->boolean('is_admin_reply')->default(false)
                  ->comment('True when written by a super-admin.');
            $table->boolean('is_internal')->default(false)
                  ->comment('Internal note — only visible to admins, not the tenant.');

            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('support_tickets')->cascadeOnDelete();
            $table->index(['ticket_id', 'created_at']);
        });

        // ── 3. Salon Suspensions ─────────────────────────────────────────────
        Schema::create('salon_suspensions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salon_id');
            $table->unsignedBigInteger('suspended_by')
                  ->comment('Super-admin user ID who issued the suspension.');

            $table->string('reason', 100)->default('policy_violation')
                  ->comment('payment_failure | policy_violation | fraud | abuse | requested | other');
            $table->text('notes')->nullable()
                  ->comment('Internal notes visible only to admins.');
            $table->text('customer_message')->nullable()
                  ->comment('The message emailed to the salon owner.');

            $table->timestamp('suspended_at')->useCurrent();
            $table->timestamp('unsuspended_at')->nullable();
            $table->unsignedBigInteger('unsuspended_by')->nullable();
            $table->text('unsuspend_reason')->nullable();

            $table->index(['salon_id', 'suspended_at']);
        });

        // ── 4. Tenant Plan Overrides ─────────────────────────────────────────
        Schema::create('tenant_plan_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salon_id');
            $table->unsignedBigInteger('applied_by')
                  ->comment('Super-admin who applied the override.');

            $table->string('override_type', 50)
                  ->comment('plan | trial_extension | custom_limit | discount | feature_flag');
            $table->string('override_plan', 30)->nullable()
                  ->comment('Override to this plan slug (e.g. enterprise).');

            // Custom limit overrides (null = use plan default)
            $table->integer('override_staff_limit')->nullable();
            $table->integer('override_client_limit')->nullable();
            $table->integer('override_services_limit')->nullable();

            // Feature flag additions (JSON array of feature keys)
            $table->json('additional_features')->nullable();

            // Trial extension
            $table->integer('trial_extension_days')->nullable();

            // Discount
            $table->unsignedTinyInteger('discount_percentage')->nullable();

            $table->text('reason')->nullable();
            $table->timestamp('expires_at')->nullable()
                  ->comment('Null = permanent override.');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['salon_id', 'is_active']);
            $table->index('applied_by');
        });

        // Add suspension columns to salons table
        Schema::table('salons', function (Blueprint $table) {
            $table->text('suspension_reason')->nullable()->after('is_active');
            $table->timestamp('suspended_at')->nullable()->after('suspension_reason');
            $table->unsignedBigInteger('suspended_by')->nullable()->after('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn(['suspension_reason', 'suspended_at', 'suspended_by']);
        });
        Schema::dropIfExists('tenant_plan_overrides');
        Schema::dropIfExists('salon_suspensions');
        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
    }
};
