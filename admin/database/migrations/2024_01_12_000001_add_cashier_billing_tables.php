<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: add_cashier_billing_tables
 *
 * Adds all columns and tables required by Laravel Cashier (Stripe).
 *
 * Cashier billing model → User (the salon owner pays the subscription).
 *
 * Tables created:
 *   users (columns added)    → Stripe customer linkage + payment method cache
 *   subscriptions            → One per active Stripe subscription
 *   subscription_items       → One per price in a multi-price subscription
 *   webhook_calls            → Audit log of every Stripe webhook received
 *
 * Design decisions:
 *   • Billing is owner-level (User), not salon-level. One owner can have one
 *     active subscription that covers their salon(s).
 *   • `users.plan` (already exists) is kept as the app-level plan slug and is
 *     synced by the webhook handler when a subscription changes.
 *   • Trial is tracked via `trial_ends_at` on the subscriptions table (Cashier
 *     standard) plus a denormalised `users.trial_ends_at` for fast reads.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Add Cashier columns to users ──────────────────────────────────

        Schema::table('users', function (Blueprint $table) {

            // Stripe customer ID (Cashier column name must be `stripe_id`)
            $table->string('stripe_id')->nullable()->index()->after('plan')
                  ->comment('Stripe customer ID: cus_XXXX');

            // Default payment method cache (avoids Stripe API calls on every render)
            $table->string('pm_type', 30)->nullable()->after('stripe_id')
                  ->comment('e.g. card, sepa_debit');
            $table->string('pm_last_four', 4)->nullable()->after('pm_type')
                  ->comment('Last 4 digits of the default payment card');

            // Trial end (denormalised from subscription for fast UI checks)
            $table->timestamp('trial_ends_at')->nullable()->after('pm_last_four')
                  ->comment('Null when not on trial; set from Stripe trial_end');
        });

        // ── subscriptions ─────────────────────────────────────────────────

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            // billable_id + billable_type support polymorphic billing models
            // (We only use User, but polymorphic keeps Cashier happy)
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->string('type')
                  ->comment('Subscription name: "default" (Cashier convention)');

            $table->string('stripe_id')->unique()
                  ->comment('Stripe subscription ID: sub_XXXX');

            $table->string('stripe_status', 30)
                  ->comment('Stripe subscription status: active, trialing, past_due, canceled, etc.');

            $table->string('stripe_price')->nullable()
                  ->comment('Stripe price ID of the main plan: price_XXXX');

            $table->integer('quantity')->nullable()
                  ->comment('Quantity (1 for fixed plans; seat-based for future)');

            $table->timestamp('trial_ends_at')->nullable()
                  ->comment('Trial period end. Null if no trial or trial has ended.');

            $table->timestamp('ends_at')->nullable()
                  ->comment('Set when canceled but not yet expired (cancel-at-period-end)');

            $table->timestamps();

            $table->index(['user_id', 'stripe_status']);
        });

        // ── subscription_items ────────────────────────────────────────────

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subscription_id')
                  ->constrained('subscriptions')
                  ->cascadeOnDelete();

            $table->string('stripe_id')->unique()
                  ->comment('Stripe subscription item ID: si_XXXX');

            $table->string('stripe_product')->nullable()
                  ->comment('Stripe product ID: prod_XXXX');

            $table->string('stripe_price')
                  ->comment('Stripe price ID: price_XXXX');

            $table->integer('quantity')->nullable();

            $table->timestamps();

            $table->index(['subscription_id', 'stripe_price']);
        });

        // ── webhook_calls audit log ───────────────────────────────────────

        Schema::create('webhook_calls', function (Blueprint $table) {
            $table->id();

            $table->string('stripe_event_id')->unique()->nullable()
                  ->comment('Stripe event ID: evt_XXXX — prevents duplicate processing');

            $table->string('type', 100)
                  ->comment('Stripe event type: customer.subscription.updated, etc.');

            $table->json('payload')
                  ->comment('Full Stripe event payload JSON');

            $table->string('status', 20)->default('received')
                  ->comment('received | processed | failed | ignored');

            $table->text('exception')->nullable()
                  ->comment('Exception message if processing failed');

            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_calls');
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at']);
        });
    }
};
