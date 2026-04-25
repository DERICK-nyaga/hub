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
        Schema::create('order_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            // Use foreignId which automatically references the 'id' column
            $table->foreignId('station_id')->constrained('stations', 'station_id')->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');

            $table->date('order_date');
            $table->enum('order_status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index('order_number');
            $table->index('order_date');
            $table->index('order_status');
            $table->index(['station_id', 'order_status']);
            $table->index(['employee_id', 'order_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_numbers');
    }
};
