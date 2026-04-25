<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\EmployeeChangeLog;
use App\Models\User;
use Illuminate\Support\Collection;

class EmployeeChangeService
{
    public function getEmployeeChangeHistory(EmployeeProfile $employee, array $filters = []): Collection
    {
        return EmployeeChangeLog::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function logChange(EmployeeProfile $employee, array $oldData, array $changes, string $type, ?string $notes = null): EmployeeChangeLog
    {
        return EmployeeChangeLog::create([
            'employee_id' => $employee->id,
            'old_data' => $oldData,
            'changes' => $changes,
            'type' => $type,
            'notes' => $notes,
            'status' => 'pending',
            'created_by' => auth()->id() ?? null,
        ]);
    }

    public function requestApproval(EmployeeChangeLog $changeLog, User $approver): bool
    {
        $changeLog->update([
            'approver_id' => $approver->id,
            'status' => 'pending',
        ]);

        return true;
    }

    public function approveChange(EmployeeChangeLog $changeLog, $user): bool
    {
        $changeLog->update([
            'approved_by' => $user->id ?? null,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return true;
    }

    public function rejectChange(EmployeeChangeLog $changeLog, $user, string $reason): bool
    {
        $changeLog->update([
            'rejected_by' => $user->id ?? null,
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'rejected_at' => now(),
        ]);

        return true;
    }
}
