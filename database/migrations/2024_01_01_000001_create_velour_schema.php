<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * VELOUR SALON SAAS — COMPLETE SCHEMA
 * Laravel 11 + MySQL 8.0 / MariaDB 10.6+  (XAMPP compatible)
 *
 * Changes from PostgreSQL version:
 *  - jsonb(...)      → json(...)       MySQL 5.7.8+ supports JSON natively
 *  - timestampTz(...)→ timestamp(...)  MySQL stores timestamps in UTC
 *  - inet(...)       → string(45, ...) VARCHAR(45) holds IPv4 + IPv6
 */
return new class extends Migration
{
    public function up(): void
    {
        /* ── USERS ──────────────────────────────────────────────────────── */
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('phone', 30)->nullable();
            $table->enum('plan', ['starter','growth','pro','enterprise'])->default('growth');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        /* ── SALONS ─────────────────────────────────────────────────────── */
        Schema::create('salons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('postcode', 20)->nullable();
            $table->string('country', 2)->default('GB');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('timezone')->default('Europe/London');
            $table->string('currency', 3)->default('GBP');
            $table->string('locale', 10)->default('en-GB');
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('social_links')->nullable();           // was jsonb
            $table->string('booking_url')->nullable();
            $table->string('google_place_id')->nullable();
            $table->string('stripe_account_id')->nullable();
            $table->boolean('online_booking_enabled')->default(true);
            $table->boolean('new_client_booking_enabled')->default(true);
            $table->boolean('deposit_required')->default(false);
            $table->decimal('deposit_percentage', 5, 2)->default(20.00);
            $table->boolean('instant_confirmation')->default(true);
            $table->integer('booking_advance_days')->default(60);
            $table->integer('cancellation_hours')->default(24);
            $table->json('opening_hours')->nullable();          // was jsonb
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        /* ── STAFF ──────────────────────────────────────────────────────── */
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('avatar')->nullable();
            $table->string('initials', 3)->nullable();
            $table->string('color', 10)->default('#B8943A');
            $table->string('role')->nullable();
            $table->text('bio')->nullable();
            $table->json('specialisms')->nullable();            // was jsonb
            $table->decimal('commission_rate', 5, 2)->default(40.00);
            $table->enum('access_level', ['staff','senior','manager','owner'])->default('staff');
            $table->time('start_time')->default('09:00:00');
            $table->time('end_time')->default('18:00:00');
            $table->json('working_days')->nullable();           // was jsonb
            $table->date('hired_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('bookable_online')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['salon_id', 'is_active']);
        });

        /* ── SERVICE CATEGORIES ─────────────────────────────────────────── */
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('icon', 10)->nullable();
            $table->string('color', 60)->nullable();
            $table->string('text_color', 60)->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['salon_id', 'slug']);
        });

        /* ── SERVICES ───────────────────────────────────────────────────── */
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->integer('duration_minutes');
            $table->integer('buffer_minutes')->default(10);
            $table->decimal('price', 10, 2);
            $table->decimal('price_from', 10, 2)->nullable();
            $table->boolean('price_on_consultation')->default(false);
            $table->enum('deposit_type', ['none','percentage','fixed','full'])->default('none');
            $table->decimal('deposit_value', 10, 2)->default(0);
            $table->boolean('online_bookable')->default(true);
            $table->boolean('show_in_menu')->default(true);
            $table->enum('status', ['active','inactive','archived'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['salon_id', 'status']);
            $table->index(['salon_id', 'category_id']);
        });

        /* ── SERVICE_STAFF (pivot) ──────────────────────────────────────── */
        Schema::create('service_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_override', 10, 2)->nullable();
            $table->timestamps();
            $table->unique(['service_id', 'staff_id']);
        });

        /* ── INVENTORY CATEGORIES ───────────────────────────────────────── */
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color', 60)->nullable();
            $table->string('text_color', 60)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['salon_id', 'slug']);
        });

        /* ── INVENTORY ITEMS ────────────────────────────────────────────── */
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('inventory_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->string('supplier')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('retail_price', 10, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_level')->default(0);
            $table->integer('reorder_quantity')->default(0);
            $table->enum('type', ['professional','retail','both'])->default('professional');
            $table->string('image')->nullable();
            $table->text('notes')->nullable();
            $table->date('last_ordered_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['salon_id', 'type']);
            $table->index(['salon_id', 'sku']);
        });

        /* ── INVENTORY ADJUSTMENTS ──────────────────────────────────────── */
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['add','use','sell','waste','set','purchase_order'])->default('add');
            $table->integer('quantity_before');
            $table->integer('quantity_change');
            $table->integer('quantity_after');
            $table->text('note')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        /* ── CLIENTS ────────────────────────────────────────────────────── */
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referred_by_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('avatar')->nullable();
            $table->string('color', 10)->nullable();
            $table->json('tags')->nullable();                   // was jsonb
            $table->foreignId('preferred_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->text('allergies')->nullable();
            $table->text('medical_notes')->nullable();
            $table->boolean('marketing_consent')->default(false);
            $table->boolean('sms_consent')->default(false);
            $table->boolean('email_consent')->default(true);
            $table->enum('status', ['active','inactive','blocked','erased'])->default('active');
            $table->boolean('is_vip')->default(false);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->integer('visit_count')->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamp('next_appointment_at')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['salon_id', 'email']);
            $table->index(['salon_id', 'status']);
            $table->index(['salon_id', 'last_visit_at']);
        });

        /* ── CLIENT NOTES ───────────────────────────────────────────────── */
        Schema::create('client_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['general','formula','allergy','medical','preference'])->default('general');
            $table->text('content');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        /* ── CLIENT FORMULAS ────────────────────────────────────────────── */
        Schema::create('client_formulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->string('base_color')->nullable();
            $table->string('highlight_color')->nullable();
            $table->string('toner')->nullable();
            $table->string('developer')->nullable();
            $table->string('olaplex')->nullable();
            $table->string('natural_level')->nullable();
            $table->string('target_level')->nullable();
            $table->string('texture')->nullable();
            $table->string('scalp_condition')->nullable();
            $table->text('technique')->nullable();
            $table->text('result_notes')->nullable();
            $table->string('goal')->nullable();
            $table->boolean('is_current')->default(true);
            $table->date('used_at')->nullable();
            $table->timestamps();
        });

        /* ── APPOINTMENTS ───────────────────────────────────────────────── */
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            // use DATETIME rather than TIMESTAMP: the first TIMESTAMP
            // column gets an implicit CURRENT_TIMESTAMP default, and
            // subsequent ones default to '0000-00-00 00:00:00' which
            // is forbidden when strict SQL modes (NO_ZERO_DATE) are
            // enabled. PostgreSQL's timestampTz maps more closely to
            // MySQL DATETIME, so we use dateTime here instead.
            $table->dateTime('starts_at');                    // was timestampTz
            $table->dateTime('ends_at');                      // was timestampTz
            $table->integer('duration_minutes');
            $table->decimal('total_price', 10, 2);
            $table->decimal('deposit_paid', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->enum('status', [
                'pending','confirmed','checked_in','in_progress',
                'completed','cancelled','no_show','rescheduled','hold',
            ])->default('confirmed');
            $table->enum('source', [
                'online','phone','walk_in','google','instagram',
                'facebook','whatsapp','website_embed','qr_code','manual','other',
            ])->default('manual');
            $table->text('client_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->boolean('review_requested')->default(false);
            $table->timestamp('confirmed_at')->nullable();     // was timestampTz
            $table->timestamp('cancelled_at')->nullable();     // was timestampTz
            $table->string('cancellation_reason')->nullable();
            $table->boolean('deposit_required')->default(false);
            $table->boolean('deposit_paid_flag')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['salon_id', 'starts_at']);
            $table->index(['salon_id', 'status']);
            $table->index(['staff_id', 'starts_at']);
            $table->index(['client_id', 'starts_at']);
            $table->index(['reminder_sent', 'starts_at']);
        });

        /* ── APPOINTMENT_SERVICES (pivot) ───────────────────────────────── */
        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('service_name');
            $table->integer('duration_minutes');
            $table->decimal('price', 10, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        /* ── POS TRANSACTIONS ───────────────────────────────────────────── */
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_code')->nullable();
            $table->string('discount_type')->nullable();
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('tip_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->decimal('amount_tendered', 10, 2)->default(0);
            $table->decimal('change_given', 10, 2)->default(0);
            $table->enum('payment_method', ['cash','card','split','voucher','account'])->default('card');
            $table->enum('status', ['pending','completed','refunded','partial_refund','voided'])->default('completed');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();     // was timestampTz
            $table->timestamps();
            $table->softDeletes();
            $table->index(['salon_id', 'completed_at']);
            $table->index(['salon_id', 'status']);
        });

        /* ── POS TRANSACTION ITEMS ──────────────────────────────────────── */
        Schema::create('pos_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('pos_transactions')->cascadeOnDelete();
            $table->nullableMorphs('itemable');
            $table->string('name');
            $table->string('type')->default('service');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        /* ── MARKETING CAMPAIGNS ────────────────────────────────────────── */
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('staff')->cascadeOnDelete();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->enum('type', ['email','sms','push','offer','recall','birthday','win_back'])->default('email');
            $table->enum('status', ['draft','scheduled','sending','sent','paused','cancelled'])->default('draft');
            $table->text('content')->nullable();
            $table->string('template')->nullable();
            $table->json('offer_details')->nullable();         // was jsonb
            $table->enum('target', ['all','vip','lapsed','new','birthday','custom','segment'])->default('all');
            $table->json('target_filters')->nullable();        // was jsonb
            $table->integer('recipient_count')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->integer('booking_count')->default(0);
            $table->decimal('revenue_generated', 10, 2)->default(0);
            $table->timestamp('scheduled_at')->nullable();     // was timestampTz
            $table->timestamp('sent_at')->nullable();          // was timestampTz
            $table->timestamps();
            $table->softDeletes();
            $table->index(['salon_id', 'status']);
            $table->index(['salon_id', 'scheduled_at']);
        });

        /* ── REVIEWS ────────────────────────────────────────────────────── */
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->enum('source', ['velour','google','facebook','manual'])->default('velour');
            $table->string('reviewer_name')->nullable();
            $table->text('owner_reply')->nullable();
            $table->timestamp('replied_at')->nullable();       // was timestampTz
            $table->boolean('is_public')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->index(['salon_id', 'rating']);
        });

        /* ── BOOKING SOURCES (analytics) ────────────────────────────────── */
        Schema::create('booking_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source');
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('referrer')->nullable();
            $table->string('ip_address', 45)->nullable();      // was inet
            $table->text('user_agent')->nullable();
            $table->boolean('converted')->default(false);
            $table->timestamps();
            $table->index(['salon_id', 'source', 'created_at']);
        });

        /* ── LINK VISITS (Go Live & Share analytics) ────────────────────── */
        Schema::create('link_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('source');
            $table->string('page')->nullable();
            $table->string('ip_address', 45)->nullable();      // was inet
            $table->string('country', 2)->nullable();
            $table->string('device')->nullable();
            $table->boolean('converted')->default(false);
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('referrer', 500)->nullable();
            $table->timestamps();
            $table->index(['salon_id', 'source', 'created_at']);
        });

        /* ── SALON NOTIFICATIONS ────────────────────────────────────────── */
        Schema::create('salon_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('data')->nullable();                  // was jsonb
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();          // was timestampTz
            $table->timestamps();
            $table->index(['salon_id', 'is_read', 'created_at']);
        });

        /* ── SALON SETTINGS ─────────────────────────────────────────────── */
        Schema::create('salon_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
            $table->unique(['salon_id', 'key']);
        });

        /* ── VOUCHERS ───────────────────────────────────────────────────── */
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->enum('type', ['percentage','fixed','gift_card'])->default('percentage');
            $table->decimal('value', 10, 2);
            $table->decimal('remaining_balance', 10, 2)->nullable();
            $table->decimal('min_spend', 10, 2)->default(0);
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['salon_id', 'code']);
        });

        /* ── INVOICES ───────────────────────────────────────────────────── */
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('pos_transactions')->nullOnDelete();
            $table->string('number')->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('status', ['draft','sent','paid','overdue','cancelled'])->default('paid');
            $table->date('issued_at');
            $table->date('due_at')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->index(['salon_id', 'status']);
        });

        /* ── PURCHASE ORDERS ────────────────────────────────────────────── */
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('staff')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('supplier')->nullable();
            $table->enum('status', ['draft','sent','received','partial','cancelled'])->default('draft');
            $table->decimal('total', 10, 2)->default(0);
            $table->date('ordered_at')->nullable();
            $table->date('expected_at')->nullable();
            $table->date('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });

        /* ── PASSWORD RESET TOKENS ──────────────────────────────────────── */
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        /* ── SANCTUM PERSONAL ACCESS TOKENS ────────────────────────────── */
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tables = [
            'purchase_order_items','purchase_orders','invoices','vouchers',
            'salon_settings','salon_notifications','link_visits','booking_sources',
            'reviews','marketing_campaigns','pos_transaction_items','pos_transactions',
            'appointment_services','appointments','client_formulas','client_notes',
            'clients','inventory_adjustments','inventory_items','inventory_categories',
            'service_staff','services','service_categories','staff','salons',
            'personal_access_tokens','password_reset_tokens','users',
        ];
        foreach ($tables as $t) Schema::dropIfExists($t);
    }
};
