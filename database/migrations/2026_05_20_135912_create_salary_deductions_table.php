<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryDeductionsTable extends Migration
{
    public function up()
    {
        Schema::create('salary_deductions', function (Blueprint $table) {
            // Original columns
            $table->id();
            $table->foreignId('employee_id')->constrained('salary_employees')->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained('salary_payments')->onDelete('set null');
            $table->string('reason');
            $table->decimal('amount', 12, 2);
            $table->enum('type', ['penalty', 'loan', 'advance_recovery', 'loss', 'other']);
            $table->date('deduction_date');
            $table->enum('status', ['pending', 'applied', 'cancelled'])->default('pending');
            $table->text('description')->nullable();
            
            // New deduction rule columns (without AFTER)
            $table->string('deduction_type')->nullable();
            $table->integer('number_of_installments')->default(0);
            $table->decimal('installment_amount', 10, 2)->default(0);
            $table->boolean('requires_dismissal')->default(false);
            $table->string('dismissal_letter_path')->nullable();
            $table->text('dismissal_notes')->nullable();
            $table->timestamp('dismissal_processed_at')->nullable();
            $table->unsignedBigInteger('dismissal_processed_by')->nullable();
            $table->text('deduction_message')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_deductions');
    }
}