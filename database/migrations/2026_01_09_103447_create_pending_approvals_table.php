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
        Schema::create('pending_approvals', function (Blueprint $table) {
        $table->id();
        $table->morphs('approvable'); // Polymorphic relation
        $table->string('type'); // 'employee_update', 'employee_termination', 'salary_change', etc.
        $table->json('data'); // The proposed changes
        $table->foreignId('requested_by')->constrained('users');
        $table->foreignId('approver_id')->constrained('users');
        $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
        $table->integer('approval_level')->default(1);
        $table->json('approval_path')->nullable(); // All approvers in hierarchy
        $table->boolean('escalated')->default(false);
        $table->timestamp('escalation_sent_at')->nullable();
        $table->integer('escalation_count')->default(0);
        $table->timestamp('reminder_sent_at')->nullable();
        $table->integer('reminder_count')->default(0);
        $table->timestamp('deadline')->nullable(); // Approval deadline
        $table->text('comments')->nullable();
        $table->timestamp('approved_at')->nullable();
        $table->timestamp('rejected_at')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_approvals');
    }
};
