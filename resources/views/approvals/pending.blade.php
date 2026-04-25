@extends('layouts.app')

@section('title', 'Pending Approvals')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Pending Approvals</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Change Type</th>
                            <th>Requested By</th>
                            <th>Date</th>
                            <th>Changes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingApprovals as $approval)
                        @php
                            $changeLog = $approval->approvable;
                            $employee = $changeLog->employee ?? null;
                        @endphp
                        @if($employee)
                        <tr>
                            <td>
                                <strong>{{ $employee->employee_id }}</strong><br>
                                {{ $employee->full_name }}
                            </td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $changeLog->change_type)) }}</span>
                            </td>
                            <td>{{ $approval->requester->name }}</td>
                            <td>{{ $changeLog->created_at->format('d/m/Y') }}</td>
                            <td>
                                @foreach($changeLog->changed_fields as $field => $changes)
                                <div class="small">
                                    <strong>{{ str_replace('_', ' ', $field) }}:</strong>
                                    <span class="text-danger">{{ $changes['old'] ?? 'N/A' }}</span>
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    <span class="text-success">{{ $changes['new'] ?? 'N/A' }}</span>
                                </div>
                                @endforeach
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-success"
                                        onclick="approveChange({{ $changeLog->id }})">
                                        <i class="bi bi-check"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectModal{{ $changeLog->id }}">
                                        <i class="bi bi-x"></i> Reject
                                    </button>
                                    <a href="{{ route('employee_profile.show', $employee) }}"
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Reject Modal (same as above) -->
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pendingApprovals->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-check-circle display-1 text-success"></i>
                <h4 class="mt-3">No pending approvals</h4>
                <p class="text-muted">All changes have been processed.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
