<?php

namespace App\Services;

use App\Models\PendingApproval;
use App\Models\EmployeeProfile;
use Illuminate\Support\Collection;

class PendingApprovalService
{
    public function requiresApproval(array $changes): bool
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

    public function createApprovalRequest(EmployeeProfile $employee, array $changes, string $type, array $meta = []): PendingApproval
    {
        return PendingApproval::create([
            'approvable_type' => EmployeeProfile::class,
            'approvable_id' => $employee->id,
            'type' => $type,
            'data' => [
                'changes' => $changes,
                'meta' => $meta,
            ],
            'status' => 'pending',
            'requested_by' => auth()->id() ?? null,
        ]);
    }

    public function applyApprovedChanges(PendingApproval $pendingApproval): ?EmployeeProfile
    {
        $approvable = $pendingApproval->approvable;

        if (! $approvable) {
            return null;
        }

        $changes = data_get($pendingApproval, 'data.changes', []);

        if (! empty($changes)) {
            $approvable->update($changes);
        }

        return $approvable;
    }
}
