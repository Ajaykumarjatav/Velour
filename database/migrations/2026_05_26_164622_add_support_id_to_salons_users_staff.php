<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->string('support_id', 20)->nullable()->unique()->after('id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('support_id', 20)->nullable()->unique()->after('id');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->string('support_id', 20)->nullable()->unique()->after('id');
        });

        // Backfill existing records
        $salons = DB::table('salons')->whereNull('support_id')->orderBy('id')->get();
        foreach ($salons as $i => $salon) {
            DB::table('salons')->where('id', $salon->id)->update([
                'support_id' => 'STR-' . (10001 + $i),
            ]);
        }

        $users = DB::table('users')->whereNull('support_id')->orderBy('id')->get();
        foreach ($users as $i => $user) {
            DB::table('users')->where('id', $user->id)->update([
                'support_id' => 'ADM-' . (20001 + $i),
            ]);
        }

        $staff = DB::table('staff')->whereNull('support_id')->orderBy('id')->get();
        foreach ($staff as $i => $member) {
            DB::table('staff')->where('id', $member->id)->update([
                'support_id' => 'STF-' . (30001 + $i),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropColumn('support_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('support_id');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('support_id');
        });
    }
};
