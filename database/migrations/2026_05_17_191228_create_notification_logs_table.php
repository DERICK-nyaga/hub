<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationLogsTable extends Migration
{
    public function up()
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->enum('payment_type', ['internet', 'airtime']);
            $table->string('reminder_type'); // 1_week, 3_days, 1_day, due_today, overdue
            $table->integer('days_until_due')->nullable();
            $table->timestamp('sent_at');
            $table->unsignedBigInteger('station_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->timestamps();
            
            $table->index(['payment_id', 'payment_type']);
            $table->index('reminder_type');
            $table->index('sent_at');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('notification_logs');
    }
}