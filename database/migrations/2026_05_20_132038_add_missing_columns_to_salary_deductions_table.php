<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToSalaryDeductionsTable extends Migration
{
    public function up()
    {
        Schema::table('salary_deductions', function (Blueprint $table) {
            // Add message column if not exists
            if (!Schema::hasColumn('salary_deductions', 'message')) {
                $table->text('message')->nullable()->after('description');
            }
            
            // Add approved_by and approved_at
            if (!Schema::hasColumn('salary_deductions', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('status');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            
            // Add rejection tracking
            if (!Schema::hasColumn('salary_deductions', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });
    }

    public function down()
    {
        Schema::table('salary_deductions', function (Blueprint $table) {
            $table->dropColumn(['message', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
        });
    }
}