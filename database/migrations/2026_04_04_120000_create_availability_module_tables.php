<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_buffer_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('buffer_before_minutes')->default(10);
            $table->unsignedSmallInteger('buffer_after_minutes')->default(15);
            $table->unsignedSmallInteger('max_daily_bookings_per_staff')->default(12);
            $table->unsignedSmallInteger('advance_booking_days')->default(60);
            $table->unsignedSmallInteger('last_minute_cutoff_hours')->default(2);
            $table->unsignedTinyInteger('overbooking_percent')->default(0);
            $table->timestamps();
            $table->unique('salon_id');
        });

        Schema::create('salon_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('type', 32)->default('room'); // room, chair, station
            $table->unsignedTinyInteger('capacity')->default(1);
            $table->json('equipment')->nullable();
            $table->string('status', 32)->default('active'); // active, pending
            $table->string('availability_status', 32)->default('available'); // available, in_use
            $table->boolean('bookable')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('staff_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->string('leave_type', 64);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('notes')->nullable();
            $table->boolean('blocks_slots')->default(true);
            $table->string('status', 32)->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_leave_requests');
        Schema::dropIfExists('salon_resources');
        Schema::dropIfExists('salon_buffer_rules');
    }
};
