<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('stations', 'station_id')->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->enum('type', ['salary', 'utility', 'rent', 'maintenance', 'tax', 'other']);
            $table->string('attachment_path')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence')->nullable(); // weekly, monthly, yearly
            $table->date('recurrence_ends_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
