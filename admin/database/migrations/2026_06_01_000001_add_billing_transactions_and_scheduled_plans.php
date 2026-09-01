<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('scheduled_plan', 30)->nullable()->after('trial_ends_at');
            $table->string('scheduled_plan_interval', 10)->nullable()->after('scheduled_plan');
            $table->timestamp('scheduled_plan_starts_at')->nullable()->after('scheduled_plan_interval');
        });

        Schema::create('billing_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('cashfree_subscription_id')->nullable()->index();
            $table->string('plan_key', 30);
            $table->string('interval', 10)->default('monthly');
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('INR');
            $table->string('status', 20)->default('pending');
            $table->string('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('activates_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_transactions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'scheduled_plan',
                'scheduled_plan_interval',
                'scheduled_plan_starts_at',
            ]);
        });
    }
};
