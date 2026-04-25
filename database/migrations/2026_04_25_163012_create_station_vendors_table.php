<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStationVendorsTable extends Migration
{
    public function up()
    {
        Schema::create('station_vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('station_id');
            $table->unsignedBigInteger('vendor_id');
            $table->date('contract_date')->nullable();
            $table->string('service_type')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            
            $table->foreign('station_id')->references('station_id')->on('stations')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->unique(['station_id', 'vendor_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('station_vendors');
    }
}