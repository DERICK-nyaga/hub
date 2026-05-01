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
            $table->foreignId('employee_id')->constrained('salary_employees')->onDelete('cascade');
            $table->date('scheduled_date');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'approved', 'processed', 'failed'])->default('pending');
            $table->enum('type', ['regular', 'advance', 'adjustment']);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['scheduled_date', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_payment_schedules');
    }
}