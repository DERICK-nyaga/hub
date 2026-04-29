<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('url');
            $table->enum('type', ['whatsapp', 'group', 'jforce', 'study']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('click_count')->default(0);
            $table->string('created_by')->nullable();
            
            // GDPR: track consent and data retention
            $table->boolean('gdpr_consent')->default(false);
            $table->timestamp('consent_given_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes(); // GDPR right to be forgotten
            
            // Indexes for performance
            $table->index('type');
            $table->index('is_active');
            $table->index('expires_at');
            $table->index('deleted_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('links');
    }
};