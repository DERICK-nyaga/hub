<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryPaymentSchedulesTable extends Migration
{
    public function up()
    {
            Schema::create('salary_payment_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('deduction_id');
                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('payment_id')->nullable();
                $table->integer('installment_number');
                $table->integer('total_installments');
                $table->decimal('amount', 15, 2);
                $table->decimal('remaining_balance', 15, 2)->default(0);
                $table->date('scheduled_date');
                $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
                $table->string('type')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('deduction_id')->references('id')->on('salary_deductions')->onDelete('cascade');
                $table->foreign('employee_id')->references('id')->on('salary_employees');
                $table->foreign('payment_id')->references('id')->on('salary_payments')->onDelete('set null');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
                
                // Indexes
                $table->index(['deduction_id', 'status']);
                $table->index(['employee_id', 'status']);
                $table->index('scheduled_date');
            });
        
    }

    public function down()
    {
        Schema::dropIfExists('salary_payment_schedules');
    }
}