<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('status', ['new', 'read', 'replied', 'archived'])->default('new')->index();
            $table->string('source')->nullable()->comment('e.g. contact_form, landing_page, chatbot');
            $table->string('ip_address', 45)->nullable();
            $table->text('notes')->nullable()->comment('Internal admin notes');
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_leads');
    }
};
