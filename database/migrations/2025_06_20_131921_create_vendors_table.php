<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->foreignId(column: 'category_id')->constrained('vendor_categories');

            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 20)->nullable();

            $table->string('account_number', 50)->nullable();
            $table->string('tax_id', 50)->nullable();

            $table->text('address')->nullable();
            $table->string('website')->nullable();

            $table->enum('payment_terms', ['net_15', 'net_30', 'net_60', 'due_on_receipt']);
            $table->string('contract_path')->nullable()->comment('Path to stored contract file');

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('category_id');
            $table->index('is_active');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
