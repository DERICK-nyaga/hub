<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('internet_providers', function (Blueprint $table) {
            $table->id('vendor_id');
            $table->string('name');
            $table->enum('category', ['fiber', 'wireless', 'satellite', 'cable']);
            $table->string('paybill_number')->nullable();
            $table->string('account_prefix')->unique();
            $table->string('support_contact');
            $table->string('billing_email')->nullable();
            $table->decimal('standard_amount', 10, 2)->nullable();
            $table->integer('due_day')->default(1);
            $table->integer('grace_period_days')->default(3);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('internet_providers');
    }
};
