<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('payment_status', 32)->default('unpaid')->after('source');
        });

        foreach (DB::table('appointments')->orderBy('id')->cursor() as $row) {
            DB::table('appointments')->where('id', $row->id)->update([
                'payment_status' => $this->inferPaymentStatus($row),
            ]);
        }
    }

    private function inferPaymentStatus(object $row): string
    {
        $total = (float) ($row->total_price ?? 0);
        $paid = (float) ($row->amount_paid ?? 0);
        if ($total <= 0) {
            return $paid > 0 ? 'paid' : 'unpaid';
        }
        if ($paid >= $total) {
            return 'paid';
        }
        if ($paid > 0) {
            return 'partial';
        }

        return 'unpaid';
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
