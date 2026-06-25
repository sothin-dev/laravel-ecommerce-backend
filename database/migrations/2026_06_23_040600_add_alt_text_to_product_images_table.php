<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_images') && !Schema::hasColumn('product_images', 'alt_text')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->string('alt_text')->nullable()->after('image_path');
            });
        }
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('alt_text');
        });
    }
};
