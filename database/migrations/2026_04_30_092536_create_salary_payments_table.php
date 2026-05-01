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
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            
            // PostgreSQL compatible - creates column AND foreign key
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
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // PostgreSQL compatible indexes
            $table->index(['employee_id', 'payment_date'], 'salary_payments_emp_payment_idx');
            $table->index('status', 'salary_payments_status_idx');
            $table->index('transaction_reference', 'salary_payments_trans_ref_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};