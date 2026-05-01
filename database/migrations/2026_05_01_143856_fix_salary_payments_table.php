<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixSalaryPaymentsTable extends Migration
{
    public function up()
    {
        // Drop the table if it exists in a bad state
        if (Schema::hasTable('salary_payments')) {
            Schema::dropIfExists('salary_payments');
        }
        
        // Recreate correctly
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                  ->constrained('employees')
                  ->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->decimal('deductions_total', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->string('type');
            $table->string('payment_method');
            $table->string('transaction_reference')->unique();
            $table->string('status')->default('pending_approval');
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