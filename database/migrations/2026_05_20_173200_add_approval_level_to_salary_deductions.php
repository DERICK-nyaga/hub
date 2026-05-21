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
        Schema::table('salary_deductions', function (Blueprint $table) {
            if (!Schema::hasColumn('salary_deductions', 'approval_level')) {
                $table->enum('approval_level', ['admin', 'director'])->nullable();
            }

            if (!Schema::hasColumn('salary_deductions', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_deductions', function (Blueprint $table) {
            $table->dropColumn(['approval_level']);
        });
    }
};
