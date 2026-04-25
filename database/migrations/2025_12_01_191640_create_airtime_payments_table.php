<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('airtime_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('stations', 'station_id');
            $table->string('mobile_number');
            $table->decimal('amount', 10, 2);
            $table->date('topup_date');
            $table->date('last_topup_date')->nullable();
            $table->date('expected_expiry');
            $table->enum('status', ['active', 'expired', 'pending_topup'])->default('active');
            $table->string('network_provider');
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['expected_expiry', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('airtime_payments');
    }
};
