<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-theme branding overrides for the public storefront.
 *
 * One row per salon + theme, created only when the salon actually overrides
 * something. NULL columns mean "fall back" — first to the salon-wide logo/cover
 * image, then to the theme's own default shipped in config/storefront-themes.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_theme_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('theme', 40);
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->string('banner_heading', 120)->nullable();
            $table->string('banner_subheading', 300)->nullable();
            $table->timestamps();

            $table->unique(['salon_id', 'theme']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_theme_assets');
    }
};
