<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            if (!Schema::hasColumn('stations', 'manager_employee_id')) {
                $table->unsignedBigInteger('manager_employee_id')->nullable()->after('region');
                $table->foreign('manager_employee_id')
                    ->references('id')
                    ->on('employee_profiles')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropForeign(['manager_employee_id']);
            $table->dropColumn('manager_employee_id');
        });
    }
};