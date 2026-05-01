<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixSalaryPaymentsForeignKey extends Migration
{
    public function up()
    {
        // Drop the existing foreign key constraint
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
        
        // Re-add the foreign key constraint pointing to your employees table
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('salary_employees')
                  ->onDelete('cascade');
        });
    }
}