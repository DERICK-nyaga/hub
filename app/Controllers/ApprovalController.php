<?php

namespace App\Controllers;

use App\Models\{EmployeeChangeLog, PendingApproval, EmployeeProfile};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApprovalController extends Controller
{
    public function pending(Request $request)
    {
        $query = PendingApproval::where('status', 'pending')
            ->where('approver_id', Auth::id())
            ->with(['approvable.employee', 'requester'])
            ->orderBy('created_at', 'desc');

        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        if ($request->has('priority') && $request->priority === 'high') {
            $query->where('deadline', '<=', now()->addHours(12));
        }

        $pendingApprovals = $query->paginate(20);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $pendingApprovals
            ]);
        }

        return view('approvals.pending', compact('pendingApprovals'));
    }

    public function dashboard()
    {
        $stats = [
            'pending_my_approval' => PendingApproval::where('approver_id', Auth::id())
                ->where('status', 'pending')
                ->count(),

            'overdue_approvals' => PendingApproval::where('approver_id', Auth::id())
                ->where('status', 'pending')
                ->where('deadline', '<', now())
                ->count(),

            'recently_approved' => EmployeeChangeLog::whereHas('pendingApproval', function ($query) {
                $query->where('approver_id', Auth::id());
            })
            ->where('status', 'approved')
            ->where('approved_at', '>', now()->subDays(7))
            ->count(),

            'avg_approval_time' => $this->calculateAverageApprovalTime(
                EmployeeChangeLog::where('status', 'approved')
                    ->where('approved_at', '>', now()->subDays(30))
                    ->get()
            ),
        ];

        $upcomingDeadlines = PendingApproval::where('approver_id', Auth::id())
            ->where('status', 'pending')
            ->where('deadline', '>', now())
            ->where('deadline', '<=', now()->addHours(24))
            ->with('approvable.employee')
            ->orderBy('deadline')
            ->limit(10)
            ->get();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'upcoming_deadlines' => $upcomingDeadlines
                ]
            ]);
        }

        return view('approvals.dashboard', compact('stats', 'upcomingDeadlines'));
    }

    public function reports(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'change_type', 'department', 'status']);

        $report = $this->generateApprovalReport($filters);

        $departments = EmployeeProfile::distinct()->pluck('department');
        $changeTypes = EmployeeChangeLog::distinct('change_type')->pluck('change_type');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        }

        return view('approvals.reports', compact('report', 'departments', 'changeTypes', 'filters'));
    }

    public function exportReport(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'change_type', 'department', 'status']);
        $report = $this->generateApprovalReport($filters);

        return response()->json([
            'success' => true,
            'data' => $report,
            'message' => 'Report generated successfully'
        ]);
    }

    public function approve(Request $request, $changeLogId)
    {
        try {
            $request->validate([
                'comments' => 'nullable|string|max:500'
            ]);

            $changeLog = EmployeeChangeLog::findOrFail($changeLogId);

            $pendingApproval = PendingApproval::where('approvable_id', $changeLogId)
                ->where('approvable_type', EmployeeChangeLog::class)
                ->first();

            if (!$pendingApproval || $pendingApproval->approver_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to approve this request'
                ], 403);
            }

            DB::beginTransaction();

            if ($changeLog->change_type === 'termination') {
                $employee = $changeLog->employee;
                $newData = $changeLog->new_data;

                $employee->update([
                    'status' => 'terminated',
                    'termination_date' => $newData['termination_date'] ?? null,
                    'termination_reason' => $newData['termination_reason'] ?? null,
                    'contract_end_date' => $newData['termination_date'] ?? $employee->contract_end_date,
                    'pending_termination_date' => null,
                    'pending_termination_reason' => null
                ]);
            }

            $changeLog->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id()
            ]);

            $pendingApproval->update([
                'status' => 'approved',
                'approved_at' => now(),
                'comments' => $request->comments
            ]);

            $changeLog->addApprovalHistoryRecord([
                'approver_id' => Auth::id(),
                'status' => 'approved',
                'comments' => $request->comments,
                'approved_at' => now(),
                'level' => $changeLog->current_approval_level ?? 1
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Request approved successfully',
                    'data' => $changeLog
                ]);
            }

            return redirect()->back()->with('success', 'Request approved successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve request',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $changeLogId)
    {
        try {
            $request->validate([
                'rejection_reason' => 'required|string|min:10|max:500'
            ]);

            $changeLog = EmployeeChangeLog::findOrFail($changeLogId);

            $pendingApproval = PendingApproval::where('approvable_id', $changeLogId)
                ->where('approvable_type', EmployeeChangeLog::class)
                ->first();

            if (!$pendingApproval || $pendingApproval->approver_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to reject this request'
                ], 403);
            }

            DB::beginTransaction();

            if ($changeLog->change_type === 'termination') {
                $employee = $changeLog->employee;
                $oldData = $changeLog->old_data;

                $employee->update([
                    'status' => $oldData['status'] ?? 'active',
                    'pending_termination_date' => null,
                    'pending_termination_reason' => null
                ]);
            }

            $changeLog->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
                'rejection_reason' => $request->rejection_reason
            ]);

            $pendingApproval->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'comments' => $request->rejection_reason
            ]);

            $changeLog->addApprovalHistoryRecord([
                'approver_id' => Auth::id(),
                'status' => 'rejected',
                'reason' => $request->rejection_reason,
                'rejected_at' => now(),
                'level' => $changeLog->current_approval_level ?? 1
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Request rejected successfully'
                ]);
            }

            return redirect()->back()->with('success', 'Request rejected successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reject request',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to reject request: ' . $e->getMessage());
        }
    }

    public function bulkApproveChanges(array $changeLogIds, $approver, ?string $comments = null): array
    {
        $results = [
            'successful' => [],
            'failed' => []
        ];

        DB::beginTransaction();

        try {
            foreach ($changeLogIds as $changeLogId) {
                try {
                    $changeLog = EmployeeChangeLog::findOrFail($changeLogId);

                    $pendingApproval = PendingApproval::where('approvable_id', $changeLogId)
                        ->where('approvable_type', EmployeeChangeLog::class)
                        ->first();

                    if (!$pendingApproval || $pendingApproval->approver_id !== $approver->id) {
                        $results['failed'][] = [
                            'id' => $changeLogId,
                            'reason' => 'No permission to approve this request'
                        ];
                        continue;
                    }

                    if ($changeLog->change_type === 'termination') {
                        $employee = $changeLog->employee;
                        $newData = $changeLog->new_data;

                        $employee->update([
                            'status' => 'terminated',
                            'termination_date' => $newData['termination_date'] ?? null,
                            'termination_reason' => $newData['termination_reason'] ?? null,
                            'contract_end_date' => $newData['termination_date'] ?? $employee->contract_end_date,
                            'pending_termination_date' => null,
                            'pending_termination_reason' => null
                        ]);
                    }

                    $changeLog->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => $approver->id
                    ]);

                    if ($pendingApproval) {
                        $pendingApproval->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approver_comments' => $comments
                        ]);
                    }

                    $changeLog->addApprovalHistoryRecord([
                        'approver_id' => $approver->id,
                        'status' => 'approved',
                        'comments' => $comments,
                        'approved_at' => now(),
                        'level' => $changeLog->current_approval_level ?? 1
                    ]);

                    $results['successful'][] = $changeLogId;

                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'id' => $changeLogId,
                        'reason' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    public function bulkRejectChanges(array $changeLogIds, $rejecter, string $rejectionReason): array
    {
        $results = [
            'successful' => [],
            'failed' => []
        ];

        DB::beginTransaction();

        try {
            foreach ($changeLogIds as $changeLogId) {
                try {
                    $changeLog = EmployeeChangeLog::findOrFail($changeLogId);

                    $pendingApproval = PendingApproval::where('approvable_id', $changeLogId)
                        ->where('approvable_type', EmployeeChangeLog::class)
                        ->first();

                    if (!$pendingApproval || $pendingApproval->approver_id !== $rejecter->id) {
                        $results['failed'][] = [
                            'id' => $changeLogId,
                            'reason' => 'No permission to reject this request'
                        ];
                        continue;
                    }

                    if ($changeLog->change_type === 'termination') {
                        $employee = $changeLog->employee;
                        $oldData = $changeLog->old_data;

                        $employee->update([
                            'status' => $oldData['status'] ?? 'active',
                            'pending_termination_date' => null,
                            'pending_termination_reason' => null
                        ]);
                    }

                    $changeLog->update([
                        'status' => 'rejected',
                        'rejected_at' => now(),
                        'rejected_by' => $rejecter->id,
                        'rejection_reason' => $rejectionReason
                    ]);

                    if ($pendingApproval) {
                        $pendingApproval->update([
                            'status' => 'rejected',
                            'rejected_at' => now(),
                            'approver_comments' => $rejectionReason
                        ]);
                    }

                    $changeLog->addApprovalHistoryRecord([
                        'approver_id' => $rejecter->id,
                        'status' => 'rejected',
                        'reason' => $rejectionReason,
                        'rejected_at' => now(),
                        'level' => $changeLog->current_approval_level ?? 1
                    ]);

                    $results['successful'][] = $changeLogId;

                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'id' => $changeLogId,
                        'reason' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    public function generateApprovalReport(array $filters = []): array
    {
        $query = EmployeeChangeLog::with(['employee', 'approver', 'rejecter'])
            ->whereIn('status', ['approved', 'rejected']);

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['change_type'])) {
            $query->where('change_type', $filters['change_type']);
        }

        if (!empty($filters['department'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('department', $filters['department']);
            });
        }

        $changes = $query->orderBy('created_at', 'desc')->get();

        $totalChanges = $changes->count();
        $approvedChanges = $changes->where('status', 'approved')->count();
        $rejectedChanges = $changes->where('status', 'rejected')->count();

        $terminations = $changes->where('change_type', 'termination');
        $approvedTerminations = $terminations->where('status', 'approved')->count();
        $rejectedTerminations = $terminations->where('status', 'rejected')->count();

        $averageApprovalTime = $this->calculateAverageApprovalTime($changes->where('status', 'approved'));

        $byChangeType = $changes->groupBy('change_type')->map(function ($items) {
            return [
                'total' => $items->count(),
                'approved' => $items->where('status', 'approved')->count(),
                'rejected' => $items->where('status', 'rejected')->count(),
                'average_approval_time' => $this->calculateAverageApprovalTime($items->where('status', 'approved'))
            ];
        });

        return [
            'summary' => [
                'total_changes' => $totalChanges,
                'approved_changes' => $approvedChanges,
                'rejected_changes' => $rejectedChanges,
                'approval_rate' => $totalChanges > 0 ? round(($approvedChanges / $totalChanges) * 100, 2) : 0,
                'average_approval_time' => $averageApprovalTime,
                'period' => [
                    'from' => $filters['date_from'] ?? null,
                    'to' => $filters['date_to'] ?? null
                ]
            ],
            'terminations' => [
                'total' => $terminations->count(),
                'approved' => $approvedTerminations,
                'rejected' => $rejectedTerminations,
                'approval_rate' => $terminations->count() > 0 ? round(($approvedTerminations / $terminations->count()) * 100, 2) : 0
            ],
            'by_change_type' => $byChangeType,
            'recent_changes' => $changes->take(50),
            'filters_applied' => $filters
        ];
    }

    public function calculateAverageApprovalTime($approvedChanges): ?float
    {
        if ($approvedChanges->isEmpty()) {
            return null;
        }

        $totalHours = 0;
        $count = 0;

        foreach ($approvedChanges as $change) {
            if ($change->approved_at && $change->created_at) {
                $createdAt = Carbon::parse($change->created_at);
                $approvedAt = Carbon::parse($change->approved_at);
                $totalHours += $createdAt->diffInHours($approvedAt);
                $count++;
            }
        }

        return $count > 0 ? round($totalHours / $count, 2) : null;
    }

    public function getEmployeeChangeHistory(EmployeeProfile $employee, array $filters = [])
    {
        $query = EmployeeChangeLog::where('employee_id', $employee->id)
            ->with(['approver', 'rejecter'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['change_type'])) {
            $query->where('change_type', $filters['change_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 20;

        return $query->paginate($perPage);
    }
}
