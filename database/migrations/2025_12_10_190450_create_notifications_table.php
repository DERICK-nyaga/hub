<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // notification type
            $table->text('message');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // For linking to different models (airtime_payments, internet_payments, etc.)
            $table->nullableMorphs('related');

            $table->timestamp('read_at')->nullable();
            $table->json('data')->nullable(); // metadata
            $table->string('channel')->default('system');
            $table->boolean('sent')->default(false);
            $table->timestamps();

            // Efficient query indexes
            $table->index(['user_id', 'read_at']);
            $table->index(['type', 'sent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
