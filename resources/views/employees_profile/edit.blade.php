@extends('layouts.app')

@section('title', 'Edit Employee - ' . $employee->full_name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Edit Employee Profile</h1>
            <p class="text-muted">Editing: {{ $employee->full_name }} ({{ $employee->employee_id }})</p>
        </div>
        <div class="col text-end">
            <a href="{{ route('employee_profile.show', $employee) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>

    <form action="{{ route('employees_profile.update', $employee) }}" method="POST">
        @csrf
        @method('PUT')
        @if(auth()->user()->hasRole(['hr_manager', 'department_head']))
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                As HR Manager/Department Head, your changes will be auto-approved.
            </div>
        @else
            <div class="alert alert-warning">
                <i class="bi bi-clock-history"></i>
                Your changes will be submitted for approval before being applied.
            </div>
        @endif
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                   value="{{ old('first_name', $employee->first_name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name"
                                   value="{{ old('middle_name', $employee->middle_name) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                   value="{{ old('last_name', $employee->last_name) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                   value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $employee->gender) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="marital_status" class="form-label">Marital Status</label>
                            <select class="form-select" id="marital_status" name="marital_status">
                                <option value="">Select Status</option>
                                <option value="single" {{ old('marital_status', $employee->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                                <option value="married" {{ old('marital_status', $employee->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                                <option value="divorced" {{ old('marital_status', $employee->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                <option value="widowed" {{ old('marital_status', $employee->marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number"
                                   value="{{ old('phone_number', $employee->phone_number) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="personal_email" class="form-label">Personal Email</label>
                            <input type="email" class="form-control" id="personal_email" name="personal_email"
                                   value="{{ old('personal_email', $employee->personal_email) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="company_email" class="form-label">Company Email</label>
                            <input type="email" class="form-control" id="company_email" name="company_email"
                                   value="{{ old('company_email', $employee->company_email) }}">
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
                            <input type="text" class="form-control" id="nssf_number" name="nssf_number"
                                   value="{{ old('nssf_number', $employee->nssf_number) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nhif_number" class="form-label">NHIF Number</label>
                            <input type="text" class="form-control" id="nhif_number" name="nhif_number"
                                   value="{{ old('nhif_number', $employee->nhif_number) }}">
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
                            <input type="text" class="form-control" id="job_title" name="job_title"
                                   value="{{ old('job_title', $employee->job_title) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="department" class="form-label">Department *</label>
                            <input type="text" class="form-control" id="department" name="department"
                                   value="{{ old('department', $employee->department) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="job_grade" class="form-label">Job Grade</label>
                            <input type="text" class="form-control" id="job_grade" name="job_grade"
                                   value="{{ old('job_grade', $employee->job_grade) }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="employment_type" class="form-label">Employment Type *</label>
                            <select class="form-select" id="employment_type" name="employment_type" required>
                                <option value="">Select Type</option>
                                <option value="permanent" {{ old('employment_type', $employee->employment_type) == 'permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="contract" {{ old('employment_type', $employee->employment_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                                <option value="probation" {{ old('employment_type', $employee->employment_type) == 'probation' ? 'selected' : '' }}>Probation</option>
                                <option value="casual" {{ old('employment_type', $employee->employment_type) == 'casual' ? 'selected' : '' }}>Casual</option>
                                <option value="intern" {{ old('employment_type', $employee->employment_type) == 'intern' ? 'selected' : '' }}>Intern</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="reporting_to" class="form-label">Reports To</label>
                            <select class="form-select" id="reporting_to" name="reporting_to">
                                <option value="">Select Supervisor</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}"
                                        {{ old('reporting_to', $employee->reporting_to) == $supervisor->id ? 'selected' : '' }}>
                                        {{ $supervisor->full_name }} ({{ $supervisor->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="work_location" class="form-label">Work Location *</label>
                            <input type="text" class="form-control" id="work_location" name="work_location"
                                   value="{{ old('work_location', $employee->work_location) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="basic_salary" class="form-label">Basic Salary (Ksh) *</label>
                            <input type="number" step="0.01" class="form-control" id="basic_salary" name="basic_salary"
                                   value="{{ old('basic_salary', $employee->basic_salary) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="hire_date" class="form-label">Hire Date *</label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date"
                                   value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="confirmation_date" class="form-label">Confirmation Date</label>
                            <input type="date" class="form-control" id="confirmation_date" name="confirmation_date"
                                   value="{{ old('confirmation_date', $employee->confirmation_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="contract_start_date" class="form-label">Contract Start Date</label>
                            <input type="date" class="form-control" id="contract_start_date" name="contract_start_date"
                                   value="{{ old('contract_start_date', $employee->contract_start_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="contract_end_date" class="form-label">Contract End Date</label>
                            <input type="date" class="form-control" id="contract_end_date" name="contract_end_date"
                                   value="{{ old('contract_end_date', $employee->contract_end_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="on_leave" {{ old('status', $employee->status) == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                <option value="terminated" {{ old('status', $employee->status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                <option value="suspended" {{ old('status', $employee->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row" id="terminationDetails" style="display: {{ $employee->status == 'terminated' ? 'block' : 'none' }};">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="termination_date" class="form-label">Termination Date</label>
                            <input type="date" class="form-control" id="termination_date" name="termination_date"
                                   value="{{ old('termination_date', $employee->termination_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="termination_reason" class="form-label">Termination Reason</label>
                            <textarea class="form-control" id="termination_reason" name="termination_reason"
                                      rows="2">{{ old('termination_reason', $employee->termination_reason) }}</textarea>
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
                            <input type="text" class="form-control" id="bank_name" name="bank_name"
                                   value="{{ old('bank_name', $employee->bank_name) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bank_account" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="bank_account" name="bank_account"
                                   value="{{ old('bank_account', $employee->bank_account) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bank_branch" class="form-label">Bank Branch</label>
                            <input type="text" class="form-control" id="bank_branch" name="bank_branch"
                                   value="{{ old('bank_branch', $employee->bank_branch) }}">
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
                            <label for="next_of_kin_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="next_of_kin_name" name="next_of_kin_name"
                                   value="{{ old('next_of_kin_name', $employee->next_of_kin['name'] ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="next_of_kin_relationship" class="form-label">Relationship</label>
                            <input type="text" class="form-control" id="next_of_kin_relationship" name="next_of_kin_relationship"
                                   value="{{ old('next_of_kin_relationship', $employee->next_of_kin['relationship'] ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="next_of_kin_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="next_of_kin_phone" name="next_of_kin_phone"
                                   value="{{ old('next_of_kin_phone', $employee->next_of_kin['phone'] ?? '') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="next_of_kin_address" class="form-label">Physical Address</label>
                            <textarea class="form-control" id="next_of_kin_address" name="next_of_kin_address"
                                      rows="2">{{ old('next_of_kin_address', $employee->next_of_kin['address'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-shield-check"></i> Guarantor Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="guarantor_name" class="form-label">Guarantor Full Name</label>
                            <input type="text" class="form-control" id="guarantor_name" name="guarantor_name"
                                   value="{{ old('guarantor_name', $employee->guarantor_details['name'] ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="guarantor_id_number" class="form-label">Guarantor ID Number</label>
                            <input type="text" class="form-control" id="guarantor_id_number" name="guarantor_id_number"
                                   value="{{ old('guarantor_id_number', $employee->guarantor_details['id_number'] ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="guarantor_phone" class="form-label">Guarantor Phone</label>
                            <input type="tel" class="form-control" id="guarantor_phone" name="guarantor_phone"
                                   value="{{ old('guarantor_phone', $employee->guarantor_details['phone'] ?? '') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="guarantor_email" class="form-label">Guarantor Email</label>
                            <input type="email" class="form-control" id="guarantor_email" name="guarantor_email"
                                   value="{{ old('guarantor_email', $employee->guarantor_details['email'] ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="guarantor_relationship" class="form-label">Relationship to Employee</label>
                            <input type="text" class="form-control" id="guarantor_relationship" name="guarantor_relationship"
                                   value="{{ old('guarantor_relationship', $employee->guarantor_details['relationship'] ?? '') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="guarantor_address" class="form-label">Guarantor Physical Address</label>
                            <textarea class="form-control" id="guarantor_address" name="guarantor_address"
                                      rows="2">{{ old('guarantor_address', $employee->guarantor_details['address'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-chat-text"></i> Change Notes</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="change_notes" class="form-label">Reason for Changes</label>
                    <textarea class="form-control" id="change_notes" name="change_notes" rows="3"
                            placeholder="Explain the reason for these changes..."></textarea>
                </div>
            </div>
        </div>

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="bi bi-check-circle"></i> Update Employee Profile
            </button>
            <a href="{{ route('employee_profile.show', $employee) }}" class="btn btn-secondary btn-lg">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Show/hide termination details based on status
    document.getElementById('status').addEventListener('change', function() {
        const terminationDetails = document.getElementById('terminationDetails');
        if (this.value === 'terminated') {
            terminationDetails.style.display = 'block';
        } else {
            terminationDetails.style.display = 'none';
        }
    });

    const phoneFields = ['phone_number', 'next_of_kin_phone', 'guarantor_phone'];
    phoneFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9+]/g, '');
            });
        }
    });
</script>
@endpush
@endsection
