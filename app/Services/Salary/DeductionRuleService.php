<?php

namespace App\Services\Salary;

use App\Models\SalaryDeduction;
use App\Models\SalaryPaymentSchedule;
use App\Models\SalaryEmployee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DeductionRuleService
{
    /**
     * Process deduction based on amount rules
     * 
     * @param float $deductionAmount
     * @param SalaryEmployee $employee
     * @param string $reason
     * @param string $type
     * @return array
     */
    public function processDeductionRules($deductionAmount, SalaryEmployee $employee, $reason, $type)
    {
        try {
            $result = [
                'action' => '',
                'message' => '',
                'deduction_type' => '',
                'installments' => [],
                'requires_dismissal' => false,
                'deduction_per_month' => 0,
                'number_of_months' => 0,
                'status' => 'pending'
            ];

            // Validate input
            if ($deductionAmount <= 0) {
                throw new Exception('Deduction amount must be greater than 0.');
            }

            // Rule 1: Deduction <= 5000 - deduct full amount
            if ($deductionAmount <= 5000) {
                $result['action'] = 'full_deduction';
                $result['message'] = "Deduction of KES " . number_format($deductionAmount, 2) . " will be applied in full.";
                $result['deduction_type'] = 'single';
                $result['deduction_per_month'] = $deductionAmount;
                $result['number_of_months'] = 1;
                $result['installments'][] = [
                    'month' => 1,
                    'amount' => $deductionAmount,
                    'due_date' => now()->endOfMonth()
                ];
            }
            // Rule 2: Deduction >= 10000 - deduct 50/50 per month (exactly 10000)
            elseif ($deductionAmount == 10000) {
                $halfAmount = $deductionAmount / 2;
                $result['action'] = 'fifty_fifty';
                $result['message'] = "Deduction of KES " . number_format($deductionAmount, 2) . " will be split into 2 equal monthly installments of KES " . number_format($halfAmount, 2);
                $result['deduction_type'] = 'installment';
                $result['deduction_per_month'] = $halfAmount;
                $result['number_of_months'] = 2;
                
                for ($i = 1; $i <= 2; $i++) {
                    $result['installments'][] = [
                        'month' => $i,
                        'amount' => $halfAmount,
                        'due_date' => now()->addMonths($i)->endOfMonth()
                    ];
                }
            }
            // Rule 3: Deduction >= 10001 and <= 15000 - deduct in 3 months equally
            elseif ($deductionAmount >= 10001 && $deductionAmount <= 15000) {
                $monthlyAmount = $deductionAmount / 3;
                $result['action'] = 'three_months';
                $result['message'] = "Deduction of KES " . number_format($deductionAmount, 2) . " will be split into 3 equal monthly installments of KES " . number_format($monthlyAmount, 2);
                $result['deduction_type'] = 'installment';
                $result['deduction_per_month'] = $monthlyAmount;
                $result['number_of_months'] = 3;
                
                for ($i = 1; $i <= 3; $i++) {
                    $result['installments'][] = [
                        'month' => $i,
                        'amount' => $monthlyAmount,
                        'due_date' => now()->addMonths($i)->endOfMonth()
                    ];
                }
            }
            // Rule 4: Deduction >= 15001 and <= 25000 - deduct for 5 months equally
            elseif ($deductionAmount >= 15001 && $deductionAmount <= 25000) {
                $monthlyAmount = $deductionAmount / 5;
                $result['action'] = 'five_months';
                $result['message'] = "Deduction of KES " . number_format($deductionAmount, 2) . " will be split into 5 equal monthly installments of KES " . number_format($monthlyAmount, 2);
                $result['deduction_type'] = 'installment';
                $result['deduction_per_month'] = $monthlyAmount;
                $result['number_of_months'] = 5;
                
                for ($i = 1; $i <= 5; $i++) {
                    $result['installments'][] = [
                        'month' => $i,
                        'amount' => $monthlyAmount,
                        'due_date' => now()->addMonths($i)->endOfMonth()
                    ];
                }
            }
            // Rule 5: Deduction >= 30000 - gross misconduct
            elseif ($deductionAmount >= 30000) {
                $result['action'] = 'gross_misconduct';
                $result['message'] = "⚠️ GROSS MISCONDUCT: Deduction of KES " . number_format($deductionAmount, 2) . " requires full payment or dismissal letter.";
                $result['deduction_type'] = 'gross_misconduct';
                $result['requires_dismissal'] = true;
                $result['status'] = 'pending_dismissal';
                $result['deduction_per_month'] = $deductionAmount;
                $result['number_of_months'] = 0;
            }

            return $result;
            
        } catch (Exception $e) {
            Log::error('Error processing deduction rules: ' . $e->getMessage(), [
                'deduction_amount' => $deductionAmount,
                'employee_id' => $employee->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Apply deduction to employee's balance and create payment schedules
     * 
     * @param SalaryEmployee $employee
     * @param float $deductionAmount
     * @param string $reason
     * @param string $type
     * @return SalaryDeduction
     * @throws Exception
     */
    public function applyDeduction($employee, $deductionAmount, $reason, $type)
    {
        if (!$employee instanceof SalaryEmployee) {
            throw new Exception('Invalid employee object provided.');
        }
        
        if ($deductionAmount <= 0) {
            throw new Exception('Deduction amount must be greater than 0.');
        }
        
        if (empty($reason)) {
            throw new Exception('Deduction reason is required.');
        }
        
        DB::beginTransaction();
        
        try {
            // Process deduction rules
            $ruleResult = $this->processDeductionRules($deductionAmount, $employee, $reason, $type);
            
            // Create main deduction record
            $deduction = SalaryDeduction::create([
                'employee_id' => $employee->id,
                'reason' => $reason,
                'amount' => $deductionAmount,
                'type' => $type,
                'deduction_date' => now(),
                'description' => $ruleResult['message'],
                'status' => $ruleResult['status'],
                'deduction_type' => $ruleResult['deduction_type'],
                'number_of_installments' => $ruleResult['number_of_months'],
                'installment_amount' => $ruleResult['deduction_per_month'],
                'requires_dismissal' => $ruleResult['requires_dismissal'],
                'message' => $ruleResult['message']
            ]);
            
            if (!$deduction) {
                throw new Exception('Failed to create deduction record.');
            }
            
            // Create payment schedules for installments if not gross misconduct
            if (!$ruleResult['requires_dismissal'] && $ruleResult['number_of_months'] > 0) {
                $remainingBalance = $deductionAmount;
                
                foreach ($ruleResult['installments'] as $index => $installment) {
                    $remainingBalance -= $installment['amount'];
                    
                    $schedule = SalaryPaymentSchedule::create([
                        'deduction_id' => $deduction->id,
                        'employee_id' => $employee->id,
                        'amount' => $installment['amount'],
                        'installment_number' => $installment['month'],
                        'total_installments' => $ruleResult['number_of_months'],
                        'scheduled_date' => $installment['due_date'],
                        'status' => 'pending',
                        'remaining_balance' => max(0, $remainingBalance),
                        'type' => $type
                    ]);
                    
                    if (!$schedule) {
                        throw new Exception("Failed to create schedule for installment {$installment['month']}");
                    }
                }
            }
            
            DB::commit();
            
            Log::info('Deduction applied successfully', [
                'deduction_id' => $deduction->id,
                'employee_id' => $employee->id,
                'amount' => $deductionAmount,
                'action' => $ruleResult['action']
            ]);
            
            return $deduction;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to apply deduction', [
                'employee_id' => $employee->id ?? null,
                'amount' => $deductionAmount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate net payable amount after deductions
     * 
     * @param float $grossSalary
     * @param float $deductionAmount
     * @param SalaryEmployee $employee
     * @return array
     */
    public function calculateNetPayable($grossSalary, $deductionAmount, $employee)
    {
        try {
            if ($grossSalary < 0) {
                throw new Exception('Gross salary cannot be negative.');
            }
            
            if ($deductionAmount < 0) {
                throw new Exception('Deduction amount cannot be negative.');
            }
            
            $ruleResult = $this->processDeductionRules($deductionAmount, $employee, '', '');
            
            if ($ruleResult['requires_dismissal']) {
                return [
                    'net_amount' => 0,
                    'deduction_amount' => $deductionAmount,
                    'gross_salary' => $grossSalary,
                    'message' => $ruleResult['message'],
                    'requires_full_payment' => true,
                    'can_proceed' => false,
                    'installment_info' => null
                ];
            }
            
            // Get pending installments for this employee for the current month
            $pendingSchedules = SalaryPaymentSchedule::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->where('scheduled_date', '<=', now()->endOfMonth())
                ->sum('amount');
            
            $totalDeductionThisMonth = $pendingSchedules + $ruleResult['deduction_per_month'];
            $netAmount = $grossSalary - $totalDeductionThisMonth;
            
            $installmentInfo = null;
            if ($ruleResult['number_of_months'] > 0) {
                // Get the current deduction ID if exists (for completed installments count)
                $completedCount = 0;
                $currentDeduction = SalaryDeduction::where('employee_id', $employee->id)
                    ->where('amount', $deductionAmount)
                    ->latest()
                    ->first();
                
                if ($currentDeduction) {
                    $completedCount = SalaryPaymentSchedule::where('deduction_id', $currentDeduction->id)
                        ->where('status', 'paid')
                        ->count();
                }
                
                $installmentInfo = [
                    'installment_amount' => $ruleResult['deduction_per_month'],
                    'total_installments' => $ruleResult['number_of_months'],
                    'remaining_balance' => max(0, $deductionAmount - ($ruleResult['deduction_per_month'] * $completedCount)),
                    'completed_installments' => $completedCount,
                    'remaining_installments' => max(0, $ruleResult['number_of_months'] - $completedCount)
                ];
            }
            
            return [
                'net_amount' => max(0, $netAmount),
                'deduction_amount' => $totalDeductionThisMonth,
                'gross_salary' => $grossSalary,
                'installment_info' => $installmentInfo,
                'message' => $ruleResult['message'],
                'can_proceed' => true,
                'requires_full_payment' => false
            ];
            
        } catch (Exception $e) {
            Log::error('Error calculating net payable: ' . $e->getMessage(), [
                'gross_salary' => $grossSalary,
                'deduction_amount' => $deductionAmount,
                'employee_id' => $employee->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'net_amount' => 0,
                'deduction_amount' => $deductionAmount,
                'gross_salary' => $grossSalary,
                'message' => 'Error calculating payment: ' . $e->getMessage(),
                'can_proceed' => false,
                'requires_full_payment' => false,
                'installment_info' => null
            ];
        }
    }
    
    /**
     * Get deduction summary for an employee
     * 
     * @param int $employeeId
     * @return array
     */
    public function getEmployeeDeductionSummary($employeeId)
    {
        try {
            $totalDeductions = SalaryDeduction::where('employee_id', $employeeId)
                ->whereIn('status', ['applied', 'pending'])
                ->sum('amount');
            
            $activeInstallments = SalaryPaymentSchedule::where('employee_id', $employeeId)
                ->where('status', 'pending')
                ->where('scheduled_date', '>=', now())
                ->count();
            
            $nextDueAmount = SalaryPaymentSchedule::where('employee_id', $employeeId)
                ->where('status', 'pending')
                ->where('scheduled_date', '>=', now())
                ->orderBy('scheduled_date')
                ->value('amount');
            
            return [
                'total_pending' => $totalDeductions,
                'active_installments' => $activeInstallments,
                'next_due_amount' => $nextDueAmount ?? 0,
                'next_due_date' => SalaryPaymentSchedule::where('employee_id', $employeeId)
                    ->where('status', 'pending')
                    ->where('scheduled_date', '>=', now())
                    ->orderBy('scheduled_date')
                    ->value('scheduled_date')
            ];
            
        } catch (Exception $e) {
            Log::error('Error getting employee deduction summary: ' . $e->getMessage(), [
                'employee_id' => $employeeId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'total_pending' => 0,
                'active_installments' => 0,
                'next_due_amount' => 0,
                'next_due_date' => null,
                'error' => $e->getMessage()
            ];
        }
    }
}