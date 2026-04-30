<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('salary_employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('position');
            $table->string('station');
            $table->enum('status', ['active', 'on_leave', 'terminated'])->default('active');
            $table->decimal('base_salary', 12, 2);
            $table->string('bank_account')->nullable();
            $table->string('mpesa_number')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_employees');
    }
}