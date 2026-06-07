<?php
/**
 * AUDIT FIX — Onboarding & Help System Tables
 *
 * Adds:
 *  1. onboarding_progress — track multi-step wizard completion per user
 *  2. help_articles       — in-app knowledge base articles
 *  3. help_article_views  — view tracking for help analytics
 *  4. cookie_consents     — GDPR cookie consent records
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Onboarding progress ─────────────────────────────────────────
        Schema::create('onboarding_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            // Each step is a boolean flag
            $table->boolean('step_salon_profile')->default(false);
            $table->boolean('step_opening_hours')->default(false);
            $table->boolean('step_first_service')->default(false);
            $table->boolean('step_first_staff')->default(false);
            $table->boolean('step_stripe_connected')->default(false);
            $table->boolean('step_booking_tested')->default(false);
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'salon_id']);
        });

        // ── 2. Help articles ───────────────────────────────────────────────
        Schema::create('help_articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title', 255);
            $table->string('category', 80)
                  ->comment('getting-started|billing|appointments|staff|marketing|api|troubleshooting');
            $table->text('excerpt')->nullable();
            $table->longText('content')->comment('Markdown content');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->unsignedInteger('not_helpful_count')->default(0);
            $table->timestamps();
            $table->index(['category', 'is_published']);
            $table->index('sort_order');
        });

        // ── 3. Cookie consents — GDPR Regulation (EU) 2016/679 ────────────
        Schema::create('cookie_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 40)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('essential')->default(true);   // always true
            $table->boolean('analytics')->default(false);
            $table->boolean('marketing')->default(false);
            $table->boolean('functional')->default(false);
            $table->string('consent_version', 10)->default('1.0')
                  ->comment('Increment when policy changes to re-prompt users');
            $table->timestamp('consented_at')->useCurrent();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_consents');
        Schema::dropIfExists('help_articles');
        Schema::dropIfExists('onboarding_progress');
    }
};
