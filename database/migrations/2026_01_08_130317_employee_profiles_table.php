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
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('company_email')->nullable();
            $table->string('phone_number');
            $table->string('national_id')->unique();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('kra_pin')->unique();
            $table->string('nssf_number')->nullable();
            $table->string('nhif_number')->nullable();
            $table->string('marital_status')->nullable();

            // Next of Kin
            $table->json('next_of_kin')->nullable();

            // Job Details
            $table->string('job_title');
            $table->string('department');
            $table->string('job_grade')->nullable();
            $table->enum('employment_type', ['permanent', 'contract', 'probation', 'casual', 'intern']);
            $table->foreignId('reporting_to')->nullable()->constrained('employee_profiles')->onDelete('set null'); // Fixed reference

            // Dates
            $table->date('hire_date');
            $table->date('confirmation_date')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();

            // Compensation
            $table->decimal('basic_salary', 12, 2);
            $table->json('allowances')->nullable();
            $table->json('deductions')->nullable();

            // Work Details
            $table->string('work_location');
            $table->json('work_schedule');
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_branch')->nullable();

            // Document Paths
            $table->string('passport_copy_path')->nullable();
            $table->string('id_card_copy_path')->nullable();
            $table->string('kra_pin_copy_path')->nullable();
            $table->string('application_letter_path')->nullable();
            $table->string('reference_letter_path')->nullable();
            $table->string('acceptance_letter_path')->nullable();
            $table->string('signed_rules_path')->nullable();
            $table->string('qualifications_path')->nullable();
            $table->string('guarantor_form_path')->nullable();

            // Guarantor Details
            $table->json('guarantor_details')->nullable();

            // Status
            $table->enum('status', ['active', 'on_leave', 'terminated', 'suspended', 'pending_termination'])->default('active');
            $table->softDeletes();
            $table->timestamps();

            $table->json('pending_changes')->nullable()->after('status');

            $table->index(['department', 'status']);
            $table->index('employee_id');
        });

        // Create employee_documents table for multiple documents
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->onDelete('cascade'); // Fixed reference
            $table->string('document_type'); // passport, id_card, kra_pin, academic, professional, etc.
            $table->string('document_name');
            $table->string('file_path');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Create employee_qualifications table
        Schema::create('employee_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employee_profiles')->onDelete('cascade'); // Fixed reference
            $table->string('qualification_type'); // academic, professional, skill
            $table->string('title');
            $table->string('institution');
            $table->string('grade')->nullable();
            $table->year('year_obtained');
            $table->string('certificate_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_qualifications');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employee_profiles');
    }
};
