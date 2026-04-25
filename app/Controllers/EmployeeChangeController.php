<?php

namespace App\Controllers;

use App\Models\EmployeeProfile;
use App\Models\EmployeeChangeLog;
use App\Models\User;
use App\Services\EmployeeChangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeChangeController extends Controller
{
    protected $changeService;

    public function __construct(EmployeeChangeService $changeService)
    {
        $this->changeService = $changeService;
    }

    public function update(Request $request, EmployeeProfile $employee)
    {
        $oldData = $employee->toArray();

        $validated = $request->validate([
        ]);

        $changeLog = $this->changeService->logChange(
            $employee,
            $oldData,
            $validated,
            'profile_update',
            $request->input('change_notes')
        );

        if ($this->requiresApproval($validated)) {
            $approver = $this->getApproverForChange($validated);
            $this->changeService->requestApproval($changeLog, $approver);

            return redirect()->route('employee_profile.show', $employee)
                ->with('success', 'Changes submitted for approval. They will be applied once approved.');
        }

        $this->changeService->approveChange($changeLog, Auth::user());

        return redirect()->route('employee_profile.show', $employee)
            ->with('success', 'Employee profile updated successfully.');
    }

    public function approveChange(Request $request, EmployeeChangeLog $changeLog)
    {
        $this->checkAuthorization($changeLog);

        $this->changeService->approveChange($changeLog, Auth::user());

        return redirect()->route('approvals.pending')
            ->with('success', 'Change approved successfully.');
    }

    public function rejectChange(Request $request, EmployeeChangeLog $changeLog)
    {
        $this->checkAuthorization($changeLog);

        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        $this->changeService->rejectChange($changeLog, Auth::user(), $request->rejection_reason);

        return redirect()->route('approvals.pending')
            ->with('success', 'Change rejected successfully.');
    }

    private function checkAuthorization(EmployeeChangeLog $changeLog): void
    {
        $user = Auth::user();

        $isHrManager = in_array($user->email, ['hr@company.com', 'admin@company.com']);
        $isAdmin = $user->id === 1;

        $isSupervisor = $changeLog->employee && $user->id === $changeLog->employee->reporting_to;

        if (!$isHrManager && !$isAdmin && !$isSupervisor) {
            abort(403, 'You are not authorized to approve/reject this change.');
        }
    }
    private function requiresApproval(array $changes): bool
    {
        $sensitiveFields = [
            'basic_salary',
            'job_title',
            'department',
            'status',
            'employment_type',
            'bank_account',
        ];

        return !empty(array_intersect(array_keys($changes), $sensitiveFields));
    }

    private function getApproverForChange(array $changes): User
    {
        return User::where('email', 'hr@company.com')
        ->orWhere('email', 'admin@company.com')
        ->orWhere('id', 1)
        ->first() ?? User::first();
    }


    public function changeHistory(EmployeeProfile $employee)
    {
        $changeLogs = $this->changeService->getEmployeeChangeHistory($employee, request()->all());

        return view('change-history.bulk-action', compact('employee', 'changeLogs'));
    }
}
