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
        Schema::table('seo_metadatas', function (Blueprint $table) {
            $table->string('schema_type')->nullable()->after('faqs');
            $table->json('schema_data')->nullable()->after('schema_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_metadatas', function (Blueprint $table) {
            $table->dropColumn(['schema_type', 'schema_data']);
        });
    }
};
