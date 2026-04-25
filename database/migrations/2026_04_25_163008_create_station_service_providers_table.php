<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStationServiceProvidersTable extends Migration
{
    public function up()
    {
        Schema::create('station_service_providers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('station_id');
            $table->unsignedBigInteger('provider_id');
            $table->string('contract_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            
            $table->foreign('station_id')->references('station_id')->on('stations')->onDelete('cascade');
            $table->foreign('provider_id')->references('vendor_id')->on('internet_providers')->onDelete('cascade');
            $table->unique(['station_id', 'provider_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('station_service_providers');
    }
}