<?php

namespace App\Services\Salary;

use App\Models\EmployeeDismissal;
use App\Models\SalaryEmployee;
use App\Models\SalaryDeduction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DismissalService
{
    public function createDismissalRequest(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Create dismissal
            $dismissal = EmployeeDismissal::create([
                'employee_id' => $data['employee_id'],
                'dismissal_type' => $data['dismissal_type'],
                'dismissal_reason' => $data['reason'],
                'details' => $data['reason'],
                'effective_date' => $data['effective_date'],
                'requested_by' => $data['requested_by'] ?? Auth::id(),
                'requested_at' => now(),
                'hr_notes' => $data['comments'] ?? null,
                'status' => 'pending',
                'total_deductions_amount' => $data['deduction_amount'] ?? 0,
            ]);
            
            // Create deduction if provided
            if (!empty($data['deduction_amount']) && $data['deduction_amount'] > 0) {
                $deductionData = [
                    'employee_id' => $data['employee_id'],
                    'amount' => $data['deduction_amount'],
                    'reason' => $data['deduction_reason'],
                    'status' => 'pending'
                ];
                
                // Add dismissal_id if column exists
                if (Schema::hasColumn('salary_deductions', 'dismissal_id')) {
                    $deductionData['dismissal_id'] = $dismissal->id;
                }
                
                // Add deduction_type if column exists
                if (Schema::hasColumn('salary_deductions', 'deduction_type')) {
                    $deductionData['deduction_type'] = 'dismissal_related';
                }
                
                // Add deduction_date if column exists (required field)
                if (Schema::hasColumn('salary_deductions', 'deduction_date')) {
                    $deductionData['deduction_date'] = now()->format('Y-m-d');
                }
                
                // Add created_by if column exists
                if (Schema::hasColumn('salary_deductions', 'created_by')) {
                    $deductionData['created_by'] = Auth::id();
                }
                
                $deduction = SalaryDeduction::create($deductionData);
                
                // Update dismissal with deduction_id if column exists
                if (Schema::hasColumn('employee_dismissals', 'deduction_id')) {
                    $dismissal->update(['deduction_id' => $deduction->id]);
                }
            }
            
            return $dismissal->load(['employee']);
        });
    }

    public function getPendingDismissals()
    {
        return EmployeeDismissal::with(['employee', 'requestedBy'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }
    
    public function approveDismissal($id, $comments = null)
    {
        return DB::transaction(function () use ($id, $comments) {
            $dismissal = EmployeeDismissal::findOrFail($id);
            
            if ($dismissal->status !== 'pending') {
                throw new \Exception('Only pending dismissals can be approved');
            }
            
            $dismissal->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_comments' => $comments
            ]);
            
            return $dismissal;
        });
    }
    
    public function rejectDismissal($id, $reason)
    {
        return DB::transaction(function () use ($id, $reason) {
            $dismissal = EmployeeDismissal::findOrFail($id);
            
            if ($dismissal->status !== 'pending') {
                throw new \Exception('Only pending dismissals can be rejected');
            }
            
            $dismissal->update([
                'status' => 'rejected',
                'rejected_by' => Auth::id(),
                'rejected_at' => now(),
                'rejection_reason' => $reason
            ]);
            
            return $dismissal;
        });
    }
 
    public function processDismissal($id, $effectiveDate, $notes = null, array $deductionIds = [])
    {
        return DB::transaction(function () use ($id, $effectiveDate, $notes, $deductionIds) {
            $dismissal = EmployeeDismissal::findOrFail($id);
            
            if ($dismissal->status !== 'approved') {
                throw new \Exception('Only approved dismissals can be processed');
            }
            
            // Update last working date
            $lastWorkingDate = date('Y-m-d', strtotime($effectiveDate . ' -1 day'));
            
            $dismissal->update([
                'status' => 'completed',
                'effective_date' => $effectiveDate,
                'last_working_date' => $lastWorkingDate,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'processing_notes' => $notes
            ]);
            
            // Update employee status
            $dismissal->employee->update([
                'status' => 'terminated',
                'status_notes' => $dismissal->dismissal_reason,
                'dismissal_date' => $effectiveDate,
                'dismissal_reason' => $dismissal->dismissal_reason,
                'dismissal_id' => $dismissal->id
            ]);
            
            // Update any pending deductions
            if ($dismissal->deduction_id) {
                $updateData = [
                    'status' => 'processed',
                    'processed_at' => now()
                ];
                
                // Add processed_by if column exists
                if (Schema::hasColumn('salary_deductions', 'processed_by')) {
                    $updateData['processed_by'] = Auth::id();
                }
                
                SalaryDeduction::where('id', $dismissal->deduction_id)->update($updateData);
            }
            
            return $dismissal;
        });
    }

    public function completeDismissal($id, $settlementAmount = null, $comments = null)
    {
        return DB::transaction(function () use ($id, $settlementAmount, $comments) {
            $dismissal = EmployeeDismissal::findOrFail($id);
            
            if ($dismissal->status !== 'completed') {
                throw new \Exception('Only processed dismissals can be completed');
            }
            
            $dismissal->update([
                'final_settlement_amount' => $settlementAmount,
                'settlement_paid' => !empty($settlementAmount),
                'settlement_paid_at' => !empty($settlementAmount) ? now() : null,
                'clearance_completed' => true,
                'clearance_completed_at' => now(),
                'clearance_notes' => $comments
            ]);
            
            return $dismissal;
        });
    }

    public function getDismissalStats()
    {
        return [
            'pending' => EmployeeDismissal::where('status', 'pending')->count(),
            'approved' => EmployeeDismissal::where('status', 'approved')->count(),
            'completed' => EmployeeDismissal::where('status', 'completed')->count(),
            'rejected' => EmployeeDismissal::where('status', 'rejected')->count(),
            'total_deductions_pending' => SalaryDeduction::where('status', 'pending')
                ->where('deduction_type', 'dismissal_related')
                ->count(),
            'total_deductions_amount' => SalaryDeduction::where('status', 'pending')
                ->where('deduction_type', 'dismissal_related')
                ->sum('amount')
        ];
    }
}