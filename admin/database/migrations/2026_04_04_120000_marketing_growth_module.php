<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 80);
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->unsignedTinyInteger('service_discount_percent')->default(0);
            $table->json('benefits')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['salon_id', 'slug']);
            $table->index(['salon_id', 'sort_order']);
        });

        Schema::create('salon_referral_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->decimal('referrer_reward_amount', 10, 2)->default(0);
            $table->decimal('referee_reward_amount', 10, 2)->default(0);
            $table->decimal('minimum_spend', 10, 2)->default(0);
            $table->unsignedSmallInteger('credit_expiry_days')->default(90);
            $table->timestamps();
            $table->unique('salon_id');
        });

        Schema::create('marketing_automation_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('template_key', 80);
            $table->string('name', 150);
            $table->string('channels_label', 120)->nullable();
            $table->string('trigger_label', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('sms_body')->nullable();
            $table->string('email_subject')->nullable();
            $table->text('email_body')->nullable();
            $table->timestamps();
            $table->unique(['salon_id', 'template_key']);
            $table->index(['salon_id', 'is_active']);
        });

        Schema::create('marketing_sms_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name', 150);
            $table->string('phone', 40)->nullable();
            $table->string('last_preview', 255)->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedSmallInteger('unread_inbound')->default(0);
            $table->timestamps();
            $table->index(['salon_id', 'last_message_at']);
        });

        Schema::create('marketing_sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('marketing_sms_threads')->cascadeOnDelete();
            $table->enum('direction', ['in', 'out']);
            $table->text('body');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('loyalty_tier_id')->nullable()->after('salon_id')->constrained('loyalty_tiers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('loyalty_tier_id');
        });
        Schema::dropIfExists('marketing_sms_messages');
        Schema::dropIfExists('marketing_sms_threads');
        Schema::dropIfExists('marketing_automation_templates');
        Schema::dropIfExists('salon_referral_settings');
        Schema::dropIfExists('loyalty_tiers');
    }
};
