<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->text('publishable_key')->nullable()->change();
            $table->text('secret_key')->nullable()->change();
            $table->text('webhook_secret')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->string('publishable_key')->nullable()->change();
            $table->string('secret_key')->nullable()->change();
            $table->string('webhook_secret')->nullable()->change();
        });
    }
};
