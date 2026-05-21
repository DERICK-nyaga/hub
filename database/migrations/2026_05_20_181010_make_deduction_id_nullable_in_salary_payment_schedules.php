<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeDeductionIdNullableInSalaryPaymentSchedules extends Migration
{
    public function up()
    {
        Schema::table('salary_payment_schedules', function (Blueprint $table) {
            // Make deduction_id nullable
            $table->unsignedBigInteger('deduction_id')->nullable()->change();
            
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('salary_payment_schedules', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_by');
            }
            
            if (!Schema::hasColumn('salary_payment_schedules', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            
            if (!Schema::hasColumn('salary_payment_schedules', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
            
            if (!Schema::hasColumn('salary_payment_schedules', 'deducted_at')) {
                $table->timestamp('deducted_at')->nullable()->after('paid_at');
            }
        });
    }

    public function down()
    {
        Schema::table('salary_payment_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('deduction_id')->nullable(false)->change();
            $table->dropColumn(['rejected_by', 'rejected_at', 'rejection_reason', 'deducted_at']);
        });
    }
}