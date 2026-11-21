<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop products table
        Schema::dropIfExists('products');

        // 2. Clean up seo_metadatas for products
        DB::table('seo_metadatas')->where('seoable_type', 'App\Models\Product')->delete();
    }

    public function down(): void
    {
        // No down migration needed since we are permanently removing the shop feature.
    }
};
