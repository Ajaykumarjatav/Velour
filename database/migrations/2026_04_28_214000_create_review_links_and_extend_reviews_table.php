<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->string('token', 80)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['salon_id', 'staff_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('staff_id')->constrained()->nullOnDelete();
            $table->foreignId('review_link_id')->nullable()->after('service_id')->constrained('review_links')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('review_link_id');
            $table->dropConstrainedForeignId('service_id');
        });

        Schema::dropIfExists('review_links');
    }
};

