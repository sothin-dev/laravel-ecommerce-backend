<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Provide an index for the order_id foreign key before dropping the unique
            $table->index('order_id', 'order_items_order_id_index');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique('order_items_order_id_product_id_unique');
            $table->unique(['order_id', 'product_id', 'variant_id'], 'order_items_order_product_variant_unique');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique('order_items_order_product_variant_unique');
            $table->unique(['order_id', 'product_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_order_id_index');
        });
    }
};
