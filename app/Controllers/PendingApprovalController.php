<?php

namespace App\Controllers;

use App\Models\PendingApproval;
use App\Models\EmployeeChangeLog;
use App\Services\PendingApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendingApprovalController extends Controller
{
    protected $approvalService;

    public function __construct(PendingApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = PendingApproval::with(['approvable', 'requester', 'approver'])
            ->where('status', 'pending')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc');

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $pendingApprovals = $query->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $pendingApprovals
            ]);
        }

        return view('pending_approvals.index', compact('pendingApprovals'));
    }

    public function show(PendingApproval $pendingApproval)
    {
        $pendingApproval->load(['approvable', 'requester', 'approver']);

        $changes = $pendingApproval->data['changes'] ?? [];
        $originalData = $pendingApproval->data['original_data'] ?? [];

        return view('pending_approvals.show', compact('pendingApproval', 'changes', 'originalData'));
    }

    public function approve(Request $request, PendingApproval $pendingApproval)
    {
        try {
            $validated = $request->validate([
                'comments' => 'nullable|string|max:500'
            ]);

            $employee = $this->approvalService->applyApprovedChanges($pendingApproval);

            $pendingApproval->update([
                'approved_at' => now(),
                'approved_by' => Auth::id(),
                'comments' => $validated['comments'] ?? $pendingApproval->comments
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Changes approved and applied successfully',
                'data' => [
                    'employee' => $employee,
                    'pending_approval' => $pendingApproval
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve changes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, PendingApproval $pendingApproval)
    {
        try {
            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:500'
            ]);

            $pendingApproval->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
                'rejection_reason' => $validated['rejection_reason']
            ]);

            if ($pendingApproval->approvable) {
                $pendingApproval->approvable->update([
                    'status' => 'rejected'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Changes rejected',
                'data' => $pendingApproval
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject changes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
