<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * social_share_clicks — Go Live & Share analytics
 *
 * NOTE (MySQL / XAMPP):
 *   reminder_sent, reminder_sent_at, and the utm_* columns on link_visits
 *   are already defined in the base schema migration (2024_01_01_000001).
 *   This migration only creates the social_share_clicks table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_share_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('platform', 40)
                  ->comment('whatsapp|instagram|facebook|google|tiktok|email|copy_link|qr_download|embed');
            $table->string('utm_source')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device', 20)->nullable();
            $table->timestamp('clicked_at')->useCurrent();
            $table->index(['salon_id', 'platform', 'clicked_at']);
            $table->index(['salon_id', 'clicked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_share_clicks');
    }
};
