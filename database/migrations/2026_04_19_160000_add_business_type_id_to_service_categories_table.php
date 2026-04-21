<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! Schema::hasColumn('service_categories', 'business_type_id')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->foreignId('business_type_id')
                    ->nullable()
                    ->after('salon_id')
                    ->constrained('business_types')
                    ->restrictOnDelete();
            });
        }

        if ($driver === 'mysql') {
            DB::statement('
                UPDATE service_categories sc
                INNER JOIN salons s ON sc.salon_id = s.id
                SET sc.business_type_id = s.business_type_id
                WHERE sc.business_type_id IS NULL
            ');
        } else {
            foreach (DB::table('service_categories')->select('id', 'salon_id')->get() as $row) {
                $bid = DB::table('salons')->where('id', $row->salon_id)->value('business_type_id');
                if ($bid) {
                    DB::table('service_categories')->where('id', $row->id)->update(['business_type_id' => $bid]);
                }
            }
        }

        try {
            Schema::table('services', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
            });
        } catch (\Throwable) {
            // Already dropped from a previous partial migration run.
        }

        // InnoDB uses the composite unique index for the salon_id FK; add a plain index first.
        try {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->index('salon_id');
            });
        } catch (\Throwable) {
            // Index may already exist from a partial run.
        }

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropUnique(['salon_id', 'slug']);
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->unique(['salon_id', 'business_type_id', 'slug']);
        });

        try {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropIndex(['salon_id']);
            });
        } catch (\Throwable) {
            //
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE service_categories MODIFY business_type_id BIGINT UNSIGNED NOT NULL');
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE services MODIFY category_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('services', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable()->change();
            });
        }

        Schema::table('services', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('service_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        try {
            Schema::table('services', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
            });
        } catch (\Throwable) {
            //
        }

        foreach (DB::table('salons')->pluck('id') as $sid) {
            $catId = DB::table('service_categories')->where('salon_id', $sid)->orderBy('id')->value('id');
            if ($catId) {
                DB::table('services')->where('salon_id', $sid)->whereNull('category_id')->update(['category_id' => $catId]);
            }
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE services MODIFY category_id BIGINT UNSIGNED NOT NULL');
        } else {
            Schema::table('services', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable(false)->change();
            });
        }

        Schema::table('services', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('service_categories')
                ->cascadeOnDelete();
        });

        try {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->index('salon_id');
            });
        } catch (\Throwable) {
            //
        }

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropUnique(['salon_id', 'business_type_id', 'slug']);
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->unique(['salon_id', 'slug']);
        });

        try {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropIndex(['salon_id']);
            });
        } catch (\Throwable) {
            //
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE service_categories MODIFY business_type_id BIGINT UNSIGNED NULL');
        }

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_type_id');
        });
    }
};
