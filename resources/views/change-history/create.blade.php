@extends('layouts.app')

@section('title', 'Change History - ' . $employee->full_name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Change History</h1>
            <p class="text-muted">{{ $employee->full_name }} ({{ $employee->employee_id }})</p>
        </div>
        <div class="col text-end">
            <a href="{{ route('employee_profile.show', $employee) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="change_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="profile_update">Profile Update</option>
                        <option value="salary_change">Salary Change</option>
                        <option value="employment_change">Employment Change</option>
                        <option value="termination">Termination</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Logs Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Changed By</th>
                            <th>Change Type</th>
                            <th>Changes</th>
                            <th>Status</th>
                            <th>Approved/Rejected By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($changeLogs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->changer->name }}</td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $log->change_type)) }}</span>
                            </td>
                            <td>
                                @foreach($log->changed_fields as $field => $changes)
                                <div class="small">
                                    <strong>{{ str_replace('_', ' ', $field) }}:</strong>
                                    <span class="text-danger">{{ $changes['old'] ?? 'N/A' }}</span>
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    <span class="text-success">{{ $changes['new'] ?? 'N/A' }}</span>
                                </div>
                                @endforeach
                            </td>
                            <td>
                                @if($log->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($log->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                @if($log->approver)
                                    {{ $log->approver->name }}<br>
                                    <small class="text-muted">
                                        {{ $log->approved_at?->format('d/m/Y') ?? $log->rejected_at?->format('d/m/Y') }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                    <i class="bi bi-eye"></i>
                                </button>

                                @if($log->status == 'pending' && auth()->user()->can('approve', $log))
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-success"
                                        onclick="approveChange({{ $log->id }})">
                                        <i class="bi bi-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectModal{{ $log->id }}">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                @endif
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Change Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Old Values</h6>
                                                <pre class="bg-light p-3"><code>@json($log->old_data, JSON_PRETTY_PRINT)</code></pre>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>New Values</h6>
                                                <pre class="bg-light p-3"><code>@json($log->new_data, JSON_PRETTY_PRINT)</code></pre>
                                            </div>
                                        </div>
                                        @if($log->notes)
                                        <div class="mt-3">
                                            <h6>Notes:</h6>
                                            <p>{{ $log->notes }}</p>
                                        </div>
                                        @endif
                                        @if($log->rejection_reason)
                                        <div class="mt-3">
                                            <h6>Rejection Reason:</h6>
                                            <p class="text-danger">{{ $log->rejection_reason }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal{{ $log->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('employee_profile.reject_change', $log) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Change</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Reason for Rejection *</label>
                                                <textarea class="form-control" name="rejection_reason" rows="4" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reject Change</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $changeLogs->links() }}
        </div>
    </div>
</div>

<script>
function approveChange(logId) {
    if (confirm('Are you sure you want to approve this change?')) {
        fetch(`/employee-change-logs/${logId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}
</script>
@endsection
