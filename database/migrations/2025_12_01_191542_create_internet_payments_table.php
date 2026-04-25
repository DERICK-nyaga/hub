<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('internet_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('stations', 'station_id');
            $table->foreignId('vendor_id')->constrained('internet_providers', 'vendor_id');
            $table->string('account_number'); // Account number for this station
            $table->decimal('amount', 10, 2);
            $table->decimal('previous_balance', 10, 2)->default(0);
            $table->decimal('total_due', 10, 2)->storedAs('amount + previous_balance');
            $table->date('billing_month'); // Which month is being billed
            $table->date('due_date'); // Due date for payment
            $table->date('payment_date')->nullable();
            $table->string('mpesa_receipt')->nullable();
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->text('invoice_notes')->nullable(); // Could contain "Service activated" etc
            $table->string('payment_method')->nullable(); // M-Pesa, Bank Transfer, etc
            $table->timestamps();

            // Indexes for faster queries
            $table->index(['due_date', 'status']);
            $table->index(['account_number', 'vendor_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('internet_payments');
    }
};
