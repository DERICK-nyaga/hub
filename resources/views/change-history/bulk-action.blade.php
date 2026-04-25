@extends('layouts.app')

@section('title', 'Bulk Approval Actions')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Bulk Approval Actions</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Select Changes for Bulk Action</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="bulkActionTable">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>Employee</th>
                                    <th>Change Type</th>
                                    <th>Requested By</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingChanges as $change)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="change-checkbox"
                                               value="{{ $change->id }}"
                                               data-change-type="{{ $change->change_type }}">
                                    </td>
                                    <td>
                                        <strong>{{ $change->employee->employee_id }}</strong><br>
                                        {{ $change->employee->full_name }}
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($change->change_type) }}</span>
                                    </td>
                                    <td>{{ $change->changer->name }}</td>
                                    <td>{{ $change->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-warning">Pending</span><br>
                                        <small>Level {{ $change->current_approval_level }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Bulk Actions</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>Selected: <span id="selectedCount">0</span> changes</h6>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comments (Optional)</label>
                        <textarea class="form-control" id="bulkComments" rows="3"
                                  placeholder="Add comments for all selected approvals..."></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-lg"
                                onclick="bulkApprove()" id="approveBtn" disabled>
                            <i class="bi bi-check-circle"></i> Approve Selected
                        </button>

                        <button type="button" class="btn btn-danger btn-lg"
                                data-bs-toggle="modal" data-bs-target="#bulkRejectModal"
                                id="rejectBtn" disabled>
                            <i class="bi bi-x-circle"></i> Reject Selected
                        </button>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h3>{{ $pendingChanges->count() }}</h3>
                                <small class="text-muted">Total Pending</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h3>{{ $overdueCount }}</h3>
                                <small class="text-muted">Overdue</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Reject Modal -->
<div class="modal fade" id="bulkRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Selected Changes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Reason for Rejection *</label>
                    <textarea class="form-control" id="bulkRejectReason" rows="4" required></textarea>
                    <div class="form-text">This reason will apply to all selected changes.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="bulkReject()">Reject Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedChanges = [];

document.getElementById('selectAll').addEventListener('change', function(e) {
    const checkboxes = document.querySelectorAll('.change-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = e.target.checked;
        updateSelectedChanges();
    });
});

document.querySelectorAll('.change-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedChanges);
});

function updateSelectedChanges() {
    selectedChanges = Array.from(document.querySelectorAll('.change-checkbox:checked'))
        .map(cb => cb.value);

    document.getElementById('selectedCount').textContent = selectedChanges.length;
    document.getElementById('approveBtn').disabled = selectedChanges.length === 0;
    document.getElementById('rejectBtn').disabled = selectedChanges.length === 0;
}

function bulkApprove() {
    if (selectedChanges.length === 0) return;

    if (!confirm(`Approve ${selectedChanges.length} changes?`)) return;

    const comments = document.getElementById('bulkComments').value;

    fetch('{{ route("approvals.bulk_approve") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            change_log_ids: selectedChanges,
            comments: comments
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showBulkResults(data.results);
        }
    });
}

function bulkReject() {
    const reason = document.getElementById('bulkRejectReason').value;
    if (!reason.trim()) {
        alert('Please provide a rejection reason.');
        return;
    }

    if (!confirm(`Reject ${selectedChanges.length} changes?`)) return;

    fetch('{{ route("approvals.bulk_reject") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            change_log_ids: selectedChanges,
            rejection_reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showBulkResults(data.results);
            $('#bulkRejectModal').modal('hide');
        }
    });
}

function showBulkResults(results) {
    let message = `Bulk action completed:\n\n`;
    message += `✓ Approved/Rejected: ${results.approved?.length || results.rejected?.length}\n`;
    message += `✗ Failed: ${results.failed?.length}\n`;
    message += `⚠ Already processed: ${results.already_processed?.length}\n`;

    if (results.failed?.length > 0) {
        message += `\nFailed items:\n`;
        results.failed.forEach(f => {
            message += `- ID ${f.id}: ${f.reason}\n`;
        });
    }

    alert(message);
    location.reload();
}
</script>
@endsection
