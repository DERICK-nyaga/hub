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
        Schema::create('stations', function (Blueprint $table) {
            $table->id('station_id');
            $table->unsignedBigInteger('manager_employee_id')->nullable()->after('region');
            $table->foreign('manager_employee_id')
                ->references('id')
                ->on('employee_profiles')
                ->onDelete('set null');
            $table->string('name');
            $table->string('mobile_number');
            $table->string('location');
            $table->decimal('monthly_loss', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(2000);
            $table->string('code')->nullable()->after('name');
            $table->text('address')->nullable()->after('location');
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->date('opening_date')->nullable();
            $table->string('region')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
