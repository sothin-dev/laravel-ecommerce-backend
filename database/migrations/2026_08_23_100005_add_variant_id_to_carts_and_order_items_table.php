<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop any existing foreign keys on carts (they shared the unique index we must remove).
        $keys = DB::select(
            "SELECT CONSTRAINT_NAME AS name FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_NAME = 'carts' AND TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL"
        );

        foreach ($keys as $key) {
            try {
                DB::statement("ALTER TABLE carts DROP FOREIGN KEY `{$key->name}`");
            } catch (\Throwable $e) {
                // Already removed; ignore.
            }
        }

        // 2. Drop the old (user_id, product_id) unique index if present.
        try {
            DB::statement("ALTER TABLE carts DROP INDEX `carts_user_id_product_id_unique`");
        } catch (\Throwable $e) {
            // Already gone; ignore.
        }

        // 3. Add variant column if missing.
        if (! Schema::hasColumn('carts', 'variant_id')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->foreignId('variant_id')->nullable()->after('product_id')
                    ->constrained('product_variants')->nullOnDelete();
            });
        }

        // 4. Add the (user_id, product_id, variant_id) unique constraint if missing.
        try {
            Schema::table('carts', function (Blueprint $table) {
                $table->unique(['user_id', 'product_id', 'variant_id']);
            });
        } catch (\Throwable $e) {
            // Already exists; ignore.
        }

        // 5. Re-add the user_id / product_id foreign keys (idempotent).
        try {
            Schema::table('carts', fn (Blueprint $table) =>
                $table->foreign('user_id')->constrained()->cascadeOnDelete());
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('carts', fn (Blueprint $table) =>
                $table->foreign('product_id')->constrained()->cascadeOnDelete());
        } catch (\Throwable $e) {
        }

        // 6. Add variant to order_items if missing.
        if (! Schema::hasColumn('order_items', 'variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('variant_id')->nullable()->after('product_id')
                    ->constrained('product_variants')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id', 'variant_id']);
            $table->dropConstrainedForeignId('variant_id');
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
            $table->unique(['user_id', 'product_id']);
            $table->foreign('user_id')->constrained()->cascadeOnDelete();
            $table->foreign('product_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variant_id');
        });
    }
};
