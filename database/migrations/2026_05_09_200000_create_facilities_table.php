<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category', 64)->default('General');
            $table->string('kind', 32)->default('other');
            $table->string('status', 32)->default('operational');
            $table->unsignedInteger('occupancy_current')->default(0);
            $table->unsignedInteger('occupancy_capacity')->default(0);
            $table->json('equipment_features')->nullable();
            $table->date('last_maintenance_on')->nullable();
            $table->date('next_maintenance_on')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['salon_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
