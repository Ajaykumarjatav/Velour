<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_action_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained('salons')->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('kind', 32);
            $table->string('title', 200);
            $table->text('body')->nullable();
            $table->string('priority', 16)->default('normal');
            $table->string('status', 16)->default('open');
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->index(['salon_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_action_items');
    }
};
