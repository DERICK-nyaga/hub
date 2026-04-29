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
    Schema::table('stations', function (Blueprint $table) {
        if (!Schema::hasColumn('stations', 'code')) {
            $table->string('code')->nullable()->after('name');
        }
        if (!Schema::hasColumn('stations', 'address')) {
            $table->text('address')->nullable()->after('location');
        }
        if (!Schema::hasColumn('stations', 'contact_person')) {
            $table->string('contact_person')->nullable();
        }
        if (!Schema::hasColumn('stations', 'contact_phone')) {
            $table->string('contact_phone')->nullable();
        }
        if (!Schema::hasColumn('stations', 'contact_email')) {
            $table->string('contact_email')->nullable();
        }
        if (!Schema::hasColumn('stations', 'opening_date')) {
            $table->date('opening_date')->nullable();
        }
        if (!Schema::hasColumn('stations', 'region')) {
            $table->string('region')->nullable();
        }
        if (!Schema::hasColumn('stations', 'status')) {
            $table->string('status')->default('active');
        }
        if (!Schema::hasColumn('stations', 'notes')) {
            $table->text('notes')->nullable();
        }
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            //
        });
    }
};
