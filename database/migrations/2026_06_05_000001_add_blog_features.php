<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Post Comments
        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('post_comments')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'spam'])->default('pending');
            $table->timestamps();
        });

        // 2. Featured & Pinned columns on posts
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('status');
            $table->boolean('is_pinned')->default(false)->after('is_featured');
        });

        // 3. Newsletter Subscribers
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->enum('status', ['active', 'unsubscribed'])->default('active');
            $table->string('token', 64)->unique();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_comments');
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'is_pinned']);
        });
        Schema::dropIfExists('newsletter_subscribers');
    }
};
