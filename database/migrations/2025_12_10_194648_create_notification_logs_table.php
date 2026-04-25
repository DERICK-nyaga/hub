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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->string('channel'); // email, sms, system
            $table->string('recipient'); // email address, phone number, or user ID
            $table->boolean('success')->default(false);
            $table->text('response')->nullable(); // API response or error message
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['notification_id', 'channel']);
            $table->index(['success', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};

