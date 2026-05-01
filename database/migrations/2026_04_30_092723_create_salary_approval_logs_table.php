<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryApprovalLogsTable extends Migration
{
    public function up()
    {
        Schema::create('salary_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('action', ['requested', 'approved', 'rejected', 'cancelled']);
            $table->text('comments')->nullable();
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_approval_logs');
    }
}