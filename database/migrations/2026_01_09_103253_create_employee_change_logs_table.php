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
        Schema::create('employee_change_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
        $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
        $table->json('old_data');
        $table->json('new_data');
        $table->json('changed_fields');
        $table->string('change_type'); // 'create', 'update', 'termination', 'personal_info', 'employment_info', etc.
        $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
        $table->text('rejection_reason')->nullable();
        $table->foreignId('approved_by')->nullable()->constrained('users');
        $table->timestamp('approved_at')->nullable();
        $table->timestamp('rejected_at')->nullable();
        $table->json('approval_flow')->nullable(); // JSON array of approval levels
        $table->integer('current_approval_level')->default(1);
        $table->json('approval_history')->nullable(); // Track each approval step
        $table->boolean('requires_multilevel_approval')->default(false);
        $table->timestamp('escalated_at')->nullable();
        $table->foreignId('escalated_to')->nullable()->constrained('users');
        $table->text('notes')->nullable();
        $table->timestamps();

        $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_change_logs');
    }
};
