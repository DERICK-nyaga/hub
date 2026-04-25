@extends('layouts.app')

@section('title',  $employee->full_name . ' - Employee Profile')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <div class="card profile-header" >
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h2 mb-1">{{ $employee->full_name }}</h1>
                            <p class="mb-0">
                                <strong>Employee ID:</strong> {{ $employee->employee_id }} |
                                <strong>Department:</strong> {{ $employee->department }} |
                                <strong>Position:</strong> {{ $employee->job_title }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-{{ $employee->status == 'active' ? 'success' : 'danger' }} fs-6">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>National ID:</strong> {{ $employee->national_id }}</p>
                            <p><strong>Passport No:</strong> {{ $employee->passport_number ?? 'N/A' }}</p>
                            <p><strong>KRA PIN:</strong> {{ $employee->kra_pin }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Date of Birth:</strong> {{ $employee->date_of_birth?->format('d/m/Y') ?? 'N/A' }}</p>
                            <p><strong>Gender:</strong> {{ ucfirst($employee->gender) ?? 'N/A' }}</p>
                            <p><strong>Phone:</strong> {{ $employee->phone_number }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-briefcase"></i> Employment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Hire Date:</strong> {{ $employee->hire_date ? $employee->hire_date->format('d/m/Y') : 'N/A' }}</p>                            <p><strong>Confirmation Date:</strong> {{ $employee->confirmation_date?->format('d/m/Y') ?? 'Pending' }}</p>
                            <p><strong>Employment Type:</strong> {{ ucfirst($employee->employment_type) }}</p>
                            <p><strong>Work Location:</strong> {{ $employee->work_location }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Basic Salary:</strong> {{ $employee->formatted_salary }}</p>
                            @if($employee->contract_end_date)
                                <p><strong>Contract End:</strong> {{ $employee->contract_end_date->format('d/m/Y') }}</p>
                            @endif
                            @if($employee->termination_date)
                                <p><strong>Termination Date:</strong> {{ $employee->termination_date->format('d/m/Y') }}</p>
                                <p><strong>Termination Reason:</strong> {{ $employee->termination_reason }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary">
                    <h5 class="mb-0"><i class="bi bi-folder-check"></i> Employee Documents</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($employee->documents as $document)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $document->document_name }}</h6>
                                    <small class="text-muted">Type: {{ ucfirst($document->document_type) }}</small>
                                    <div class="mt-2">
                                        <a href="{{ route('employees.download', $document) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        <a href="{{ route('employees.view', $document) }}"
                                           class="btn btn-sm btn-outline-success" target="_blank">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-secondary d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-award"></i> Qualifications & Skills</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addQualificationModal">
                        <i class="bi bi-plus-circle"></i> Add Qualification
                    </button>
                </div>
                <div class="card-body">
                    @if($employee->qualifications->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Institution</th>
                                    <th>Year</th>
                                    <th>Certificate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->qualifications as $qualification)
                                <tr>
                                    <td>{{ ucfirst($qualification->qualification_type) }}</td>
                                    <td>{{ $qualification->title }}</td>
                                    <td>{{ $qualification->institution }}</td>
                                    <td>{{ $qualification->year_obtained }}</td>
                                    <td>
                                        @if($qualification->certificate_path)
                                        <a href="{{ Storage::url($qualification->certificate_path) }}"
                                           class="btn btn-sm btn-outline-info" target="_blank">
                                            <i class="bi bi-file-earmark-text"></i> View
                                        </a>
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">No qualifications added yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Next of Kin</h5>
                </div>
                <div class="card-body">
                    @if($employee->next_of_kin)
                        <p><strong>Name:</strong> {{ $employee->next_of_kin['name'] ?? 'N/A' }}</p>
                        <p><strong>Relationship:</strong> {{ $employee->next_of_kin['relationship'] ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $employee->next_of_kin['phone'] ?? 'N/A' }}</p>
                        <p><strong>Address:</strong> {{ $employee->next_of_kin['address'] ?? 'N/A' }}</p>
                    @else
                        <p class="text-muted">No next of kin information available.</p>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-shield-check"></i> Guarantor Details</h5>
                </div>
                <div class="card-body">
                    @if($employee->guarantor_details)
                        <p><strong>Name:</strong> {{ $employee->guarantor_details['name'] ?? 'N/A' }}</p>
                        <p><strong>ID Number:</strong> {{ $employee->guarantor_details['id_number'] ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $employee->guarantor_details['phone'] ?? 'N/A' }}</p>
                        <p><strong>Relationship:</strong> {{ $employee->guarantor_details['relationship'] ?? 'N/A' }}</p>
                        <p><strong>Address:</strong> {{ $employee->guarantor_details['address'] ?? 'N/A' }}</p>
                        @if($employee->guarantor_form_path)
                        <div class="mt-3">
                            <a href="{{ Storage::url($employee->guarantor_form_path) }}"
                               class="btn btn-sm btn-outline-dark" target="_blank">
                                <i class="bi bi-file-pdf"></i> View Guarantor Form
                            </a>
                        </div>
                        @endif
                    @else
                        <p class="text-muted">No guarantor information available.</p>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clock"></i> Work Schedule</h5>
                </div>
                <div class="card-body">
                    @if($employee->work_schedule)
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Start</th>
                                <th>End</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                            @if($employee->work_schedule[$day]['start'] ?? false)
                            <tr>
                                <td>{{ ucfirst($day) }}</td>
                                <td>{{ $employee->work_schedule[$day]['start'] }}</td>
                                <td>{{ $employee->work_schedule[$day]['end'] }}</td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-muted">No work schedule defined.</p>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('employee_profile.edit', ['employee' =>$employee->id]) }}" class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i> Edit Profile
                        </a>

                        @if($employee->status != 'terminated')
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#terminateModal">
                            <i class="bi bi-person-x"></i> Terminate Employee
                        </button>
                        @endif

                        <a href="{{ route('employees_profile.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addQualificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('employees.add-qualification', $employee) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Qualification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="qualification_type" class="form-select" required>
                            <option value="academic">Academic</option>
                            <option value="professional">Professional</option>
                            <option value="skill">Skill/Certification</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Institution</label>
                        <input type="text" name="institution" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Grade</label>
                        <input type="text" name="grade" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Year Obtained</label>
                        <input type="number" name="year_obtained" class="form-control" required min="1900" max="{{ date('Y') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Certificate (Optional)</label>
                        <input type="file" name="certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Qualification</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($employee->status != 'terminated')
<div class="modal fade" id="terminateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('employees.terminate', $employee) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Terminate Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> This action cannot be undone. Please provide termination details.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Termination Date</label>
                        <input type="date" name="termination_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Termination Reason</label>
                        <textarea name="termination_reason" class="form-control" rows="4" required placeholder="Provide detailed reason for termination..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Termination</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
