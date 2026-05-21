<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReminderFieldsToInternetPayments extends Migration
{
    public function up()
    {
        Schema::table('internet_payments', function (Blueprint $table) {
            $table->timestamp('last_reminder_sent')->nullable();
            $table->integer('reminder_count')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('internet_payments', function (Blueprint $table) {
            $table->dropColumn(['last_reminder_sent', 'reminder_count']);
        });
    }
}