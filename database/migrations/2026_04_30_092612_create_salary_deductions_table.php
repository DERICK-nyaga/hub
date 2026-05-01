<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryDeductionsTable extends Migration
{
    public function up()
    {
        Schema::create('salary_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('salary_employees')->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained('salary_payments')->onDelete('set null');
            $table->string('reason');
            $table->decimal('amount', 12, 2);
            $table->enum('type', ['penalty', 'loan', 'advance_recovery', 'loss', 'other']);
            $table->date('deduction_date');
            $table->enum('status', ['pending', 'applied', 'cancelled'])->default('pending');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_deductions');
    }
}