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
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('status');
        });

        // Set existing system pages to is_system = true
        \DB::table('pages')
            ->whereIn('slug', ['home', 'about', 'contact', 'blog-category', 'service-category', 'blog', 'services'])
            ->update(['is_system' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
