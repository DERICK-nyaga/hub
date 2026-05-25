<?php

namespace App\Services\Salary;

use App\Models\SalaryEmployee;
use App\Models\SalaryDeduction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class DeductionRuleService
{
    const MAX_DEDUCTION_AMOUNT = 45000;
    const MAX_CUMULATIVE_DEDUCTION_AMOUNT = 45000;
    const SUPER_DEDUCTION_THRESHOLD = 45000;
    const DIRECTOR_APPROVAL_THRESHOLD = 100000;
    const INSTALLMENT_THRESHOLD = 10000;
    const MAX_INSTALLMENTS = 12;
    const MIN_INSTALLMENT_AMOUNT = 1000;
    const DISMISSAL_THRESHOLD = 30000;
    
    public function validateDeductionAmount($amount, $user = null)
    {
        try {
            $maxAmount = 45000;
            $superDeductionThreshold = 100000;
            
            $isAdminOrDirector = false;
            $userRole = 'user';
            
            if ($user) {
                if (method_exists($user, 'hasRole')) {
                    $isAdminOrDirector = $user->hasRole('admin') || $user->hasRole('director');
                    if ($user->hasRole('director')) $userRole = 'director';
                    elseif ($user->hasRole('admin')) $userRole = 'admin';
                }
            }
            
            if (!is_numeric($amount)) {
                return [
                    'valid' => false,
                    'message' => 'Amount must be a numeric value.'
                ];
            }
            
            if ($amount <= 0) {
                return [
                    'valid' => false,
                    'message' => 'Amount must be greater than zero.'
                ];
            }
            
            if ($amount > $superDeductionThreshold) {
                if ($isAdminOrDirector) {
                    return [
                        'valid' => true,
                        'requires_super_deduction' => false,
                        'auto_approve' => false,
                        'requires_director_approval' => $userRole !== 'director',
                        'message' => "Amount of KES " . number_format($amount, 2) . 
                                    " exceeds the super deduction threshold. As an " . $userRole . 
                                    ", you can process this, but it may require additional approval."
                    ];
                } else {
                    return [
                        'valid' => false,
                        'requires_super_deduction' => true,
                        'auto_approve' => false,
                        'requires_director_approval' => true,
                        'message' => "Deduction amount of KES " . number_format($amount, 2) . 
                                    " exceeds the super deduction threshold of KES " . 
                                    number_format($superDeductionThreshold, 2) . ". " .
                                    "Please contact HR or Administration to process this deduction."
                    ];
                }
            }
            
            if ($amount > $maxAmount) {
                if ($isAdminOrDirector) {
                    return [
                        'valid' => true,
                        'requires_super_deduction' => false,
                        'auto_approve' => true,
                        'requires_director_approval' => false,
                        'message' => "Amount of KES " . number_format($amount, 2) . 
                                    " exceeds the standard limit of KES " . number_format($maxAmount, 2) . 
                                    ". As an " . $userRole . ", you can process this directly."
                    ];
                } else {
                    return [
                        'valid' => false,
                        'requires_super_deduction' => true,
                        'auto_approve' => false,
                        'requires_director_approval' => false,
                        'message' => "Deduction amount of KES " . number_format($amount, 2) . 
                                    " exceeds the maximum allowed single deduction of KES " . 
                                    number_format($maxAmount, 2) . ". " .
                                    "Please contact HR or Administration to process this deduction."
                    ];
                }
            }
            
            return [
                'valid' => true,
                'requires_super_deduction' => false,
                'message' => 'Amount validation passed.'
            ];
            
        } catch (Exception $e) {
            Log::error('Error validating deduction amount', [
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            
            return [
                'valid' => false,
                'message' => 'Error validating amount: ' . $e->getMessage()
            ];
        }
    }
    
    private function isAdmin($user = null)
    {
        if ($user === null) {
            $user = Auth::user();
        }
        
        if (!$user) return false;
        
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin') || $user->hasRole('administrator');
        }
        
        if (isset($user->role)) {
            return in_array($user->role, ['admin', 'administrator']);
        }
        
        if (isset($user->user_type)) {
            return in_array($user->user_type, ['admin', 'administrator']);
        }
        
        return false;
    }
    
    private function isDirector($user = null)
    {
        if ($user === null) {
            $user = Auth::user();
        }
        
        if (!$user) return false;
        
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('director');
        }
        
        if (isset($user->role)) {
            return $user->role === 'director';
        }
        
        if (isset($user->user_type)) {
            return $user->user_type === 'director';
        }
        
        return false;
    }
    
    public function processDeductionRules($amount, $employee, $reason, $type)
    {
        try {
            $result = [
                'requires_dismissal' => false,
                'deduction_type' => 'single',
                'number_of_months' => 1,
                'deduction_per_month' => $amount,
                'message' => ''
            ];
            
            if ($amount >= self::DISMISSAL_THRESHOLD) {
                $result['requires_dismissal'] = true;
                $result['deduction_type'] = 'dismissal_pending';
                $result['message'] = "Amount of KES " . number_format($amount, 2) . 
                                     " exceeds the dismissal threshold of KES " . 
                                     number_format(self::DISMISSAL_THRESHOLD, 2) . 
                                     ". This requires HR review and possible dismissal letter.";
                return $result;
            }
            
            if ($amount > self::INSTALLMENT_THRESHOLD) {
                $numberOfMonths = ceil($amount / self::MIN_INSTALLMENT_AMOUNT);
                $numberOfMonths = min($numberOfMonths, self::MAX_INSTALLMENTS);
                
                $perMonthAmount = round($amount / $numberOfMonths, 2);
                
                if ($perMonthAmount < self::MIN_INSTALLMENT_AMOUNT) {
                    $perMonthAmount = self::MIN_INSTALLMENT_AMOUNT;
                    $numberOfMonths = ceil($amount / $perMonthAmount);
                }
                
                $result['deduction_type'] = 'installment';
                $result['number_of_months'] = $numberOfMonths;
                $result['deduction_per_month'] = $perMonthAmount;
                $result['message'] = "Amount of KES " . number_format($amount, 2) . 
                                     " will be deducted in " . $numberOfMonths . 
                                     " installments of KES " . number_format($perMonthAmount, 2) . 
                                     " per month.";
            } else {
                $result['deduction_type'] = 'single';
                $result['number_of_months'] = 1;
                $result['deduction_per_month'] = $amount;
                $result['message'] = "Amount will be deducted as a single payment.";
            }
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Error processing deduction rules', [
                'amount' => $amount,
                'employee_id' => $employee->id ?? null,
                'error' => $e->getMessage()
            ]);
            
            return [
                'requires_dismissal' => false,
                'deduction_type' => 'single',
                'number_of_months' => 1,
                'deduction_per_month' => $amount,
                'message' => 'Deduction processed with default rules due to system error.'
            ];
        }
    }
    
    public function canAddDeduction($employeeId, $newDeductionAmount)
    {
        try {
            $currentTotal = $this->getEmployeePendingBalance($employeeId);
            $totalAfterAdd = $currentTotal + $newDeductionAmount;
            
            $result = [
                'can_add' => $totalAfterAdd <= self::MAX_CUMULATIVE_DEDUCTION_AMOUNT,
                'current_balance' => $currentTotal,
                'new_amount' => $newDeductionAmount,
                'total_after_add' => $totalAfterAdd,
                'max_allowed' => self::MAX_CUMULATIVE_DEDUCTION_AMOUNT,
                'remaining_capacity' => max(0, self::MAX_CUMULATIVE_DEDUCTION_AMOUNT - $currentTotal),
                'requires_dismissal' => $totalAfterAdd > self::MAX_CUMULATIVE_DEDUCTION_AMOUNT
            ];
            
            if ($result['requires_dismissal']) {
                $result['message'] = "Adding this deduction would exceed the maximum cumulative limit of KES " . 
                                     number_format(self::MAX_CUMULATIVE_DEDUCTION_AMOUNT, 2) . 
                                     ". Current balance: KES " . number_format($currentTotal, 2) . 
                                     ". This requires immediate HR review.";
            } else {
                $result['message'] = "Deduction can be added. Remaining capacity: KES " . 
                                     number_format($result['remaining_capacity'], 2);
            }
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Error checking deduction capacity', [
                'employee_id' => $employeeId,
                'amount' => $newDeductionAmount,
                'error' => $e->getMessage()
            ]);
            
            return [
                'can_add' => false,
                'error' => true,
                'message' => 'Unable to verify deduction capacity: ' . $e->getMessage()
            ];
        }
    }
    
    public function getEmployeePendingBalance($employeeId)
    {
        try {
            $pendingDeductions = SalaryDeduction::where('employee_id', $employeeId)
                ->whereIn('status', ['pending', 'applied', 'pending_dismissal'])
                ->get();
            
            $total = 0;
            
            foreach ($pendingDeductions as $deduction) {
                if ($deduction->number_of_installments > 1) {
                    $paidInstallments = $deduction->schedule()
                        ->where('status', 'paid')
                        ->count();
                    
                    $remainingInstallments = $deduction->number_of_installments - $paidInstallments;
                    $total += $remainingInstallments * $deduction->installment_amount;
                } else {
                    $total += $deduction->amount;
                }
            }
            
            return $total;
            
        } catch (Exception $e) {
            Log::error('Error calculating employee pending balance', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }
    
    public function calculateInstallmentSchedule($deductionId)
    {
        try {
            $deduction = SalaryDeduction::findOrFail($deductionId);
            
            if ($deduction->number_of_installments <= 1) {
                return [];
            }
            
            $schedule = [];
            $startDate = now()->startOfMonth();
            
            for ($i = 1; $i <= $deduction->number_of_installments; $i++) {
                $dueDate = $startDate->copy()->addMonths($i - 1);
                
                $schedule[] = [
                    'deduction_id' => $deduction->id,
                    'installment_number' => $i,
                    'total_installments' => $deduction->number_of_installments,
                    'amount' => $deduction->installment_amount,
                    'scheduled_date' => $dueDate,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            return $schedule;
            
        } catch (Exception $e) {
            Log::error('Error calculating installment schedule', [
                'deduction_id' => $deductionId,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    public static function getConstants()
    {
        return [
            'MAX_DEDUCTION_AMOUNT' => self::MAX_DEDUCTION_AMOUNT,
            'MAX_CUMULATIVE_DEDUCTION_AMOUNT' => self::MAX_CUMULATIVE_DEDUCTION_AMOUNT,
            'INSTALLMENT_THRESHOLD' => self::INSTALLMENT_THRESHOLD,
            'MAX_INSTALLMENTS' => self::MAX_INSTALLMENTS,
            'MIN_INSTALLMENT_AMOUNT' => self::MIN_INSTALLMENT_AMOUNT,
            'DISMISSAL_THRESHOLD' => self::DISMISSAL_THRESHOLD,
        ];
    }

    
}