@extends('layouts.app')

@section('title', 'Payment Schedules')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Payment Schedules</h1>
            <p class="text-muted">Manage recurring and scheduled payments.</p>
        </div>
        <a href="{{ route('payments.schedules.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Schedule
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($schedules->isEmpty())
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    No payment schedules found. Click "Create Schedule" to add one.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Station</th>
                                <th>Payment Type</th>
                                <th>Scheduled Amount</th>
                                <th>Scheduled Date</th>
                                <th>Frequency</th>
                                <th>Recurring</th>
                                <th>Auto Pay</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->id }}</td>
                                    <td>
                                        {{ $schedule->station->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $schedule->payment_type == 'internet' ? 'bg-primary' : 'bg-success' }}">
                                            {{ ucfirst($schedule->payment_type) }}
                                        </span>
                                    </td>
                                    <td><strong>KES {{ number_format($schedule->scheduled_amount, 2) }}</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($schedule->scheduled_date)->format('d/m/Y') }}</td>
                                    <td>
                                        @php
                                            $frequencyLabels = [
                                                'monthly' => 'badge-info',
                                                'quarterly' => 'badge-warning',
                                                'yearly' => 'badge-danger',
                                                'custom' => 'badge-secondary'
                                            ];
                                            $labelClass = $frequencyLabels[$schedule->frequency] ?? 'badge-secondary';
                                        @endphp
                                        <span class="badge {{ $labelClass }}">
                                            {{ ucfirst($schedule->frequency) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($schedule->is_recurring)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($schedule->auto_pay)
                                            <span class="badge bg-primary">Enabled</span>
                                        @else
                                            <span class="badge bg-secondary">Disabled</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $scheduleDate = \Carbon\Carbon::parse($schedule->scheduled_date);
                                            $isUpcoming = $scheduleDate->isFuture();
                                            $isToday = $scheduleDate->isToday();
                                            $isPast = $scheduleDate->isPast();
                                        @endphp
                                        @if($isToday)
                                            <span class="badge bg-warning text-dark">Due Today</span>
                                        @elseif($isPast)
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-success">Upcoming</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-info" onclick="viewSchedule({{ $schedule->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="editSchedule({{ $schedule->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteSchedule({{ $schedule->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $schedules->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Schedule Details Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="scheduleModalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewSchedule(scheduleId) {
    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    const modalBody = document.getElementById('scheduleModalBody');
    
    modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    modal.show();
    
    fetch(`/payments/schedules/${scheduleId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const schedule = data.data;
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Schedule Information</h6>
                        <table class="table table-sm">
                            <tr><th>ID:</th><td>${schedule.id}</td></tr>
                            <tr><th>Station:</th><td>${schedule.station?.name || 'N/A'}</td></tr>
                            <tr><th>Payment Type:</th><td>${schedule.payment_type}</td></tr>
                            <tr><th>Amount:</th><td>KES ${parseFloat(schedule.scheduled_amount).toLocaleString()}</td></tr>
                            <tr><th>Scheduled Date:</th><td>${schedule.scheduled_date}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Recurrence Settings</h6>
                        <table class="table table-sm">
                            <tr><th>Frequency:</th><td>${schedule.frequency}</td></tr>
                            <tr><th>Recurring:</th><td>${schedule.is_recurring ? 'Yes' : 'No'}</td></tr>
                            <tr><th>Auto Pay:</th><td>${schedule.auto_pay ? 'Enabled' : 'Disabled'}</td></tr>
                            <tr><th>Description:</th><td>${schedule.description || 'No description'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
        } else {
            modalBody.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = '<div class="alert alert-danger">Error loading schedule details</div>';
    });
}

function editSchedule(scheduleId) {
    window.location.href = `/payments/schedules/${scheduleId}/edit`;
}

function deleteSchedule(scheduleId) {
    if (confirm('Are you sure you want to delete this schedule? This action cannot be undone.')) {
        fetch(`/payments/schedules/${scheduleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting schedule');
        });
    }
}
</script>
@endpush
@endsection