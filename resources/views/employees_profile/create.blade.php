@extends('layouts.app')

@section('title', 'Add New Employee')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Add New Employee</h1>
        </div>
    </div>

    <form action="{{ route('employees_profile.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="national_id" class="form-label">National ID Number *</label>
                            <input type="text" class="form-control" id="national_id" name="national_id" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="passport_number" class="form-label">Passport Number</label>
                            <input type="text" class="form-control" id="passport_number" name="passport_number">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="passport_expiry" class="form-label">Passport Expiry Date</label>
                            <input type="date" class="form-control" id="passport_expiry" name="passport_expiry">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="kra_pin" class="form-label">KRA PIN *</label>
                            <input type="text" class="form-control" id="kra_pin" name="kra_pin" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="marital_status" class="form-label">Marital Status</label>
                            <select class="form-select" id="marital_status" name="marital_status">
                                <option value="">Select Status</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="divorced">Divorced</option>
                                <option value="widowed">Widowed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="personal_email" class="form-label">Personal Email</label>
                            <input type="email" class="form-control" id="personal_email" name="personal_email">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="company_email" class="form-label">Company Email</label>
                            <input type="email" class="form-control" id="company_email" name="company_email">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-card-checklist"></i> Statutory Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nssf_number" class="form-label">NSSF Number</label>
                            <input type="text" class="form-control" id="nssf_number" name="nssf_number">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nhif_number" class="form-label">NHIF Number</label>
                            <input type="text" class="form-control" id="nhif_number" name="nhif_number">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-briefcase"></i> Employment Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="job_title" class="form-label">Job Title *</label>
                            <input type="text" class="form-control" id="job_title" name="job_title" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="department" class="form-label">Department *</label>
                            <input type="text" class="form-control" id="department" name="department" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="job_grade" class="form-label">Job Grade</label>
                            <input type="text" class="form-control" id="job_grade" name="job_grade">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="employment_type" class="form-label">Employment Type *</label>
                            <select class="form-select" id="employment_type" name="employment_type" required>
                                <option value="">Select Type</option>
                                <option value="permanent">Permanent</option>
                                <option value="contract">Contract</option>
                                <option value="probation">Probation</option>
                                <option value="casual">Casual</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="reporting_to" class="form-label">Reports To</label>
                            <select class="form-select" id="reporting_to" name="reporting_to">
                                <option value="">Select Supervisor</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}">{{ $supervisor->full_name }} ({{ $supervisor->employee_id }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="work_location" class="form-label">Work Location *</label>
                            <input type="text" class="form-control" id="work_location" name="work_location" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="basic_salary" class="form-label">Basic Salary (Ksh) *</label>
                            <input type="number" step="0.01" class="form-control" id="basic_salary" name="basic_salary" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="hire_date" class="form-label">Hire Date *</label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="contract_start_date" class="form-label">Contract Start Date</label>
                            <input type="date" class="form-control" id="contract_start_date" name="contract_start_date">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="contract_end_date" class="form-label">Contract End Date</label>
                            <input type="date" class="form-control" id="contract_end_date" name="contract_end_date">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-bank"></i> Bank Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bank_name" class="form-label">Bank Name</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bank_account" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="bank_account" name="bank_account">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bank_branch" class="form-label">Bank Branch</label>
                            <input type="text" class="form-control" id="bank_branch" name="bank_branch">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Required Documents</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_card_copy" class="form-label">ID Card Copy (PDF/Image)</label>
                            <input type="file" class="form-control" id="id_card_copy" name="id_card_copy" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kra_pin_copy" class="form-label">KRA PIN Copy (PDF/Image)</label>
                            <input type="file" class="form-control" id="kra_pin_copy" name="kra_pin_copy" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="passport_copy" class="form-label">Passport Copy (PDF/Image)</label>
                            <input type="file" class="form-control" id="passport_copy" name="passport_copy" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="application_letter" class="form-label">Application Letter (PDF/Word)</label>
                            <input type="file" class="form-control" id="application_letter" name="application_letter" accept=".pdf,.doc,.docx">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="reference_letter" class="form-label">Reference Letter (PDF/Word)</label>
                            <input type="file" class="form-control" id="reference_letter" name="reference_letter" accept=".pdf,.doc,.docx">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="acceptance_letter" class="form-label">Acceptance Letter (PDF/Word)</label>
                            <input type="file" class="form-control" id="acceptance_letter" name="acceptance_letter" accept=".pdf,.doc,.docx">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="signed_rules" class="form-label">Signed Rules & Regulations (PDF)</label>
                            <input type="file" class="form-control" id="signed_rules" name="signed_rules" accept=".pdf">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-person-check"></i> Next of Kin Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="next_of_kin_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="next_of_kin_name" name="next_of_kin_name" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="next_of_kin_relationship" class="form-label">Relationship *</label>
                            <input type="text" class="form-control" id="next_of_kin_relationship" name="next_of_kin_relationship" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="next_of_kin_phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="next_of_kin_phone" name="next_of_kin_phone" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="next_of_kin_address" class="form-label">Physical Address *</label>
                            <textarea class="form-control" id="next_of_kin_address" name="next_of_kin_address" rows="2" required></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-shield-check"></i> Guarantor Details & Form</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="guarantor_name" class="form-label">Guarantor Full Name *</label>
                            <input type="text" class="form-control" id="guarantor_name" name="guarantor_name" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="guarantor_id_number" class="form-label">Guarantor ID Number *</label>
                            <input type="text" class="form-control" id="guarantor_id_number" name="guarantor_id_number" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="guarantor_phone" class="form-label">Guarantor Phone *</label>
                            <input type="tel" class="form-control" id="guarantor_phone" name="guarantor_phone" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="guarantor_email" class="form-label">Guarantor Email</label>
                            <input type="email" class="form-control" id="guarantor_email" name="guarantor_email">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="guarantor_relationship" class="form-label">Relationship to Employee *</label>
                            <input type="text" class="form-control" id="guarantor_relationship" name="guarantor_relationship" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="guarantor_address" class="form-label">Guarantor Physical Address *</label>
                            <textarea class="form-control" id="guarantor_address" name="guarantor_address" rows="2" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="guarantor_form" class="form-label">Guarantor Form (PDF) *</label>
                            <input type="file" class="form-control" id="guarantor_form" name="guarantor_form" accept=".pdf" required>
                            <small class="text-muted">Upload the signed guarantor form as PDF</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="bi bi-check-circle"></i> Create Employee Profile
            </button>
            <a href="{{ route('employees_profile.index') }}" class="btn btn-secondary btn-lg">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.getElementById('phone_number').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9+]/g, '');
    });

    document.getElementById('next_of_kin_phone').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9+]/g, '');
    });

    document.getElementById('guarantor_phone').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9+]/g, '');
    });
</script>
@endpush
@endsection
