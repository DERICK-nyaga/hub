<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeductionIdToSalaryPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('salary_payments', 'deduction_id')) {
                $table->unsignedBigInteger('deduction_id')->nullable()->after('employee_id');
                $table->foreign('deduction_id')->references('id')->on('salary_deductions')->onDelete('set null');
            }
            
            // Add index for better performance
            $table->index('deduction_id');
        });
    }

    public function down()
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropForeign(['deduction_id']);
            $table->dropColumn('deduction_id');
        });
    }
}