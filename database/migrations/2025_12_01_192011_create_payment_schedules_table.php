<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('stations', 'station_id');
            $table->foreignId('vendor_id')->nullable()->constrained('internet_providers', 'vendor_id');
            $table->enum('payment_type', ['internet', 'airtime']);
            $table->date('scheduled_date');
            $table->decimal('scheduled_amount', 10, 2);
            $table->enum('frequency', ['monthly', 'quarterly', 'yearly', 'custom']);
            $table->boolean('is_recurring')->default(true);
            $table->boolean('auto_pay')->default(false);
            $table->enum('status', ['scheduled', 'completed', 'skipped', 'failed'])->default('scheduled');
            $table->date('next_schedule_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['scheduled_date', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_schedules');
    }
};
