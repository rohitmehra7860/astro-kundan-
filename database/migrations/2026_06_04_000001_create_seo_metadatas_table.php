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
        Schema::create('seo_metadatas', function (Blueprint $table) {
            $table->id();
            // Polymorphic relation
            $table->string('seoable_type');
            $table->unsignedBigInteger('seoable_id');
            
            // Meta tags
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            
            // Social Media (Open Graph)
            $table->string('og_title', 255)->nullable();
            $table->text('og_description')->nullable();
            $table->foreignId('og_image_id')
                ->nullable()
                ->constrained('media_uploads')
                ->nullOnDelete();
                
            // Advanced SEO
            $table->string('canonical_url', 500)->nullable();
            $table->string('robots_instructions', 50)->default('index, follow');
            
            $table->timestamps();

            // Indexes
            $table->index(['seoable_type', 'seoable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metadatas');
    }
};
