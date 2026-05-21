@extends('layouts.app')

@section('title', 'Payment Schedules')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Payment Schedules</h1>
            <p class="text-muted">Manage recurring and scheduled payments.</p>
        </div>
        <a href="{{ route('salary.schedules.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Schedule
        </a>
    </div>

    <!-- Bulk Actions Bar -->
    <div id="bulkActionsBar" class="card mb-4" style="display: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-check-square me-2"></i>
                    <strong><span id="selectedCount">0</span> schedule(s) selected</strong>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-light" onclick="confirmBulkApprove()" style="border-radius: 8px 0 0 8px;">
                        <i class="fas fa-check-circle text-success me-1"></i> Approve Selected
                    </button>
                    <button type="button" class="btn btn-light" onclick="confirmBulkDelete()">
                        <i class="fas fa-trash-alt text-danger me-1"></i> Delete Selected
                    </button>
                    <button type="button" class="btn btn-light" onclick="clearSelection()" style="border-radius: 0 8px 8px 0;">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
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
                    <table class="table table-hover" id="schedulesTable">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAllCheckbox" style="cursor: pointer; width: 18px; height: 18px;">
                                </th>
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
                            <tr id="schedule-row-{{ $schedule->id }}" class="schedule-row">
                                <td>
                                    <input type="checkbox" class="schedule-checkbox" value="{{ $schedule->id }}" style="cursor: pointer; width: 18px; height: 18px;">
                                </td>
                                <td>{{ $schedule->id }}</td>
                                <td>
                                    @if($schedule->station)
                                        <a href="{{ route('payments.station.details', ['stationId' => $schedule->station->station_id]) }}" 
                                           class="text-decoration-underline text-primary"
                                           target="_blank"
                                           data-bs-toggle="tooltip" 
                                           title="View Station Details">
                                            <i class="fas fa-building me-1"></i>
                                            {{ $schedule->station->name }}
                                            <i class="fas fa-external-link-alt ms-1 small"></i>
                                        </a>
                                        @if($schedule->station->code)
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-hashtag fa-xs"></i> {{ $schedule->station->code }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
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
                                            'monthly' => 'info',
                                            'quarterly' => 'warning',
                                            'yearly' => 'danger',
                                            'custom' => 'secondary'
                                        ];
                                        $labelClass = $frequencyLabels[$schedule->frequency] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $labelClass }}">
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
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'failed' => 'danger',
                                            'processing' => 'info'
                                        ];
                                        $statusColor = $statusColors[$schedule->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ ucfirst($schedule->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewSchedule({{ $schedule->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ route('salary.schedules.edit', $schedule->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($schedule->status === 'pending')
                                        <button type="button" class="btn btn-sm btn-success" onclick="singleApprove({{ $schedule->id }})">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-danger" onclick="singleDelete({{ $schedule->id }})">
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

<!-- Bulk Approve Modal -->
<div class="modal fade" id="bulkApproveModal" tabindex="-1" aria-labelledby="bulkApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="bulkApproveModalLabel">
                    <i class="fas fa-check-circle me-2"></i>
                    Bulk Approve Schedules
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve <strong id="bulkApproveCount">0</strong> schedule(s)?</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Approved schedules will be processed on their scheduled dates.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmBulkApproveBtn">
                    <i class="fas fa-check-circle me-1"></i> Yes, Approve
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="bulkDeleteModalLabel">
                    <i class="fas fa-trash-alt me-2"></i>
                    Bulk Delete Schedules
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="bulkDeleteCount">0</strong> schedule(s)?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDeleteBtn">
                    <i class="fas fa-trash-alt me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Single Delete Confirmation Modal -->
<div class="modal fade" id="singleDeleteModal" tabindex="-1" aria-labelledby="singleDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="singleDeleteModalLabel">
                    <i class="fas fa-trash-alt me-2"></i>
                    Delete Schedule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this schedule?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmSingleDeleteBtn">
                    <i class="fas fa-trash-alt me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Details Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
// Store selected schedule IDs
let selectedScheduleIds = [];
let pendingDeleteId = null;

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Select All checkbox event
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.schedule-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateBulkActionsBar();
        });
    }
    
    // Individual checkbox events
    const checkboxes = document.querySelectorAll('.schedule-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActionsBar();
        });
    });
    
    // Modal button events
    const confirmBulkApproveBtn = document.getElementById('confirmBulkApproveBtn');
    if (confirmBulkApproveBtn) {
        confirmBulkApproveBtn.addEventListener('click', processBulkApprove);
    }
    
    const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');
    if (confirmBulkDeleteBtn) {
        confirmBulkDeleteBtn.addEventListener('click', processBulkDelete);
    }
    
    const confirmSingleDeleteBtn = document.getElementById('confirmSingleDeleteBtn');
    if (confirmSingleDeleteBtn) {
        confirmSingleDeleteBtn.addEventListener('click', confirmSingleDelete);
    }
    
    // Initial update
    updateBulkActionsBar();
});

// Update bulk actions bar based on selected checkboxes
function updateBulkActionsBar() {
    const checkboxes = document.querySelectorAll('.schedule-checkbox:checked');
    selectedScheduleIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    const count = selectedScheduleIds.length;
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    if (count > 0) {
        bulkBar.style.display = 'block';
        if (selectedCountSpan) selectedCountSpan.textContent = count;
    } else {
        bulkBar.style.display = 'none';
    }
    
    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.schedule-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox && allCheckboxes.length > 0) {
        const checkedCount = document.querySelectorAll('.schedule-checkbox:checked').length;
        selectAllCheckbox.checked = allCheckboxes.length === checkedCount;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < allCheckboxes.length;
    }
}

// Clear all selections
function clearSelection() {
    const checkboxes = document.querySelectorAll('.schedule-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateBulkActionsBar();
    
    // Close any open modals
    const bulkApproveModal = bootstrap.Modal.getInstance(document.getElementById('bulkApproveModal'));
    if (bulkApproveModal) bulkApproveModal.hide();
    
    const bulkDeleteModal = bootstrap.Modal.getInstance(document.getElementById('bulkDeleteModal'));
    if (bulkDeleteModal) bulkDeleteModal.hide();
}

// Confirm bulk approve
function confirmBulkApprove() {
    if (selectedScheduleIds.length === 0) {
        alert('No schedules selected');
        return;
    }
    
    document.getElementById('bulkApproveCount').textContent = selectedScheduleIds.length;
    const modal = new bootstrap.Modal(document.getElementById('bulkApproveModal'));
    modal.show();
}

// Process bulk approve
function processBulkApprove() {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('bulkApproveModal'));
    if (modal) modal.hide();
    
    if (selectedScheduleIds.length === 0) {
        alert('No schedules selected');
        return;
    }
    
    // Disable button to prevent double submission
    const approveBtn = document.getElementById('confirmBulkApproveBtn');
    if (approveBtn) {
        approveBtn.disabled = true;
        approveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
    }
    
    fetch('{{ route("salary.schedules.bulk-approve") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: selectedScheduleIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`${selectedScheduleIds.length} schedule(s) approved successfully!`);
            clearSelection();
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to approve schedules'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing request: ' + error.message);
    })
    .finally(() => {
        if (approveBtn) {
            approveBtn.disabled = false;
            approveBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Yes, Approve';
        }
    });
}

// Confirm bulk delete
function confirmBulkDelete() {
    if (selectedScheduleIds.length === 0) {
        alert('No schedules selected');
        return;
    }
    
    document.getElementById('bulkDeleteCount').textContent = selectedScheduleIds.length;
    const modal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
    modal.show();
}

// Process bulk delete
function processBulkDelete() {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('bulkDeleteModal'));
    if (modal) modal.hide();
    
    if (selectedScheduleIds.length === 0) {
        alert('No schedules selected');
        return;
    }
    
    // Disable button to prevent double submission
    const deleteBtn = document.getElementById('confirmBulkDeleteBtn');
    if (deleteBtn) {
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
    }
    
    fetch('{{ route("salary.schedules.bulk-delete") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: selectedScheduleIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`${selectedScheduleIds.length} schedule(s) deleted successfully!`);
            clearSelection();
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete schedules'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing request: ' + error.message);
    })
    .finally(() => {
        if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';
        }
    });
}

// Single approve
function singleApprove(id) {
    if (!confirm('Are you sure you want to approve this schedule?')) return;
    
    fetch(`/salary/schedules/${id}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Schedule approved successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to approve schedule'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing request: ' + error.message);
    });
}

// Single delete (show modal)
function singleDelete(id) {
    pendingDeleteId = id;
    const modal = new bootstrap.Modal(document.getElementById('singleDeleteModal'));
    modal.show();
}

// Confirm single delete
function confirmSingleDelete() {
    if (!pendingDeleteId) return;
    
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('singleDeleteModal'));
    if (modal) modal.hide();
    
    // Disable button
    const deleteBtn = document.getElementById('confirmSingleDeleteBtn');
    if (deleteBtn) {
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
    }
    
    fetch(`/salary/schedules/${pendingDeleteId}`, {
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
            alert('Schedule deleted successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete schedule'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing request: ' + error.message);
    })
    .finally(() => {
        if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Yes, Delete';
        }
        pendingDeleteId = null;
    });
}

// View schedule details
function viewSchedule(scheduleId) {
    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    const modalBody = document.getElementById('scheduleModalBody');
    
    modalBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    modal.show();
    
    fetch(`/salary/schedules/${scheduleId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(schedule => {
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
                        <tr><th>Status:</th><td><span class="badge bg-${schedule.status === 'approved' ? 'success' : (schedule.status === 'pending' ? 'warning' : 'danger')}">${schedule.status}</span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Recurrence Settings</h6>
                    <table class="table table-sm">
                        <tr><th>Frequency:</th><td>${schedule.frequency}</td></tr>
                        <tr><th>Recurring:</th><td>${schedule.is_recurring ? 'Yes' : 'No'}</td></tr>
                        <tr><th>Auto Pay:</th><td>${schedule.auto_pay ? 'Enabled' : 'Disabled'}</td></tr>
                        <tr><th>Created:</th><td>${new Date(schedule.created_at).toLocaleString()}</td></tr>
                        <tr><th>Description:</th><td>${schedule.description || 'No description'}</td></tr>
                    </table>
                </div>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = '<div class="alert alert-danger">Error loading schedule details</div>';
    });
}
</script>
@endpush
@endsection