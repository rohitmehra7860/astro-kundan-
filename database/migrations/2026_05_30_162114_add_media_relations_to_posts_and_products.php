<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('featured_image_id')->nullable()->constrained('media_uploads')->onDelete('set null');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('product_image_id')->nullable()->constrained('media_uploads')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['featured_image_id']);
            $table->dropColumn('featured_image_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_image_id']);
            $table->dropColumn('product_image_id');
        });
    }
};
