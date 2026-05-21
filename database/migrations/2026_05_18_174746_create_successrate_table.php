<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuccessrateTable extends Migration
{
    public function up()
    {
        Schema::create('successrate', function (Blueprint $table) {
            $table->id();
            $table->string('station_name', 255);
            $table->integer('year');
            $table->integer('week_number'); // 1-52
            $table->string('week_identifier', 20); // e.g., "2026W10"
            $table->date('week_start_date');
            $table->decimal('success_rate', 5, 2);
            $table->integer('packages_closed');
            $table->decimal('delta', 5, 2)->nullable(); // Change from previous week
            $table->integer('month');
            $table->integer('quarter');
            $table->integer('half_year'); // 1 or 2
            $table->timestamps();
            
            // Unique constraint to prevent duplicates
            $table->unique(['station_name', 'year', 'week_number']);
            
            // Indexes for performance
            $table->index(['year', 'week_number']);
            $table->index(['station_name', 'year']);
            $table->index('week_start_date');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('successrate');
    }
}