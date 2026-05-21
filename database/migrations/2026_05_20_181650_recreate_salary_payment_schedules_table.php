<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateSalaryPaymentSchedulesTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('salary_payment_schedules');
        
        // Recreate with nullable fields
        Schema::create('salary_payment_schedules', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys (nullable for standalone schedules)
            $table->unsignedBigInteger('deduction_id')->nullable();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('payment_id')->nullable();
            
            // Installment fields (nullable for standalone schedules)
            $table->integer('installment_number')->nullable();
            $table->integer('total_installments')->nullable();
            $table->decimal('remaining_balance', 15, 2)->nullable()->default(0);
            
            // Schedule fields
            $table->decimal('amount', 15, 2);
            $table->date('scheduled_date');
            $table->enum('status', ['pending', 'approved', 'deducted', 'paid', 'cancelled'])->default('pending');
            $table->string('type')->nullable();
            
            // Tracking fields (all nullable)
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('deducted_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('deduction_id')->references('id')->on('salary_deductions')->onDelete('set null');
            $table->foreign('employee_id')->references('id')->on('salary_employees');
            $table->foreign('payment_id')->references('id')->on('salary_payments')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['employee_id', 'status']);
            $table->index(['deduction_id', 'status']);
            $table->index('scheduled_date');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_payment_schedules');
    }
}