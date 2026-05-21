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
        Schema::table('salary_payment_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('salary_payment_schedules', 'deducted_at')) {
                $table->timestamp('deducted_at')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('salary_payment_schedules', 'status')) {
                // Add 'deducted' as a new status option
                $table->enum('status', ['pending', 'approved', 'deducted', 'paid', 'cancelled'])->default('pending')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_payment_schedules', function (Blueprint $table) {
            //
        });
    }
};
