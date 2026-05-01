<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->decimal('deductions_total', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->enum('type', ['regular', 'advance', 'adjustment']);
            $table->enum('payment_method', ['mpesa', 'bank_transfer']);
            $table->string('transaction_reference')->unique();
            $table->enum('status', ['pending_approval', 'approved', 'processed', 'failed'])->default('pending_approval');
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'payment_date']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_payments');
    }
}