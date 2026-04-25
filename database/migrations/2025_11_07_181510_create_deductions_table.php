<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('deduction_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('station_id')->constrained('stations', 'station_id');
                $table->foreignId('employee_id')->constrained()->onDelete('cascade');
                $table->string('employee_name');
                $table->date('transaction_date');
                $table->enum('type', ['initial', 'additional', 'refund', 'payment']);
                $table->decimal('amount', 10, 2);
                $table->decimal('previous_balance', 10, 2)->default(0);
                $table->decimal('new_balance', 10, 2)->default(0);
                $table->string('reason');
                $table->string('order_number')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'transaction_date']);
        });

        Schema::create('deduction_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade')->unique();
            $table->decimal('balance', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
