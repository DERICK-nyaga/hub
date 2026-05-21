<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait ApprovalPeriodTrait
{
    /**
     * Check if current user can approve based on their role and date
     * 
     * @return array
     */
    protected function canApprove()
    {
        $user = Auth::user();
        $currentDay = now()->day;
        
        // Director can approve anytime
        if ($user->hasRole('director')) {
            return [
                'can_approve' => true,
                'message' => 'Director can approve anytime'
            ];
        }
        
        // Admin can only approve between 30th - 7th of each month
        if ($user->hasRole('admin')) {
            $canApprove = ($currentDay >= 30 || $currentDay <= 7);
            
            if (!$canApprove) {
                return [
                    'can_approve' => false,
                    'message' => 'Admin approvals are only allowed between 30th and 7th of each month.'
                ];
            }
            
            return [
                'can_approve' => true,
                'message' => 'Admin approval within allowed period'
            ];
        }
        
        // Other roles cannot approve
        return [
            'can_approve' => false,
            'message' => 'You do not have permission to approve salary items.'
        ];
    }
    
    /**
     * Check if employee already has payment for current month
     * 
     * @param int $employeeId
     * @param string $paymentType
     * @return array
     */
    protected function hasMonthlyPayment($employeeId, $paymentType = null)
    {
        $existingPayment = \App\Models\SalaryPayment::where('employee_id', $employeeId)
            ->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->whereIn('status', ['approved', 'processed', 'pending_approval'])
            ->first();
        
        if ($existingPayment) {
            return [
                'exists' => true,
                'message' => "Employee already has a payment for " . now()->format('F Y') . ". Only one payment per month is allowed.",
                'payment' => $existingPayment
            ];
        }
        
        return [
            'exists' => false,
            'message' => null
        ];
    }
    
    /**
     * Check if user is director
     */
    protected function isDirector()
    {
        return Auth::user()->hasRole('director');
    }
    
    /**
     * Check if user is admin
     */
    protected function isAdmin()
    {
        return Auth::user()->hasRole('admin');
    }
    
    /**
     * Get approval info for display
     */
    protected function getApprovalInfo()
    {
        $currentDay = now()->day;
        
        return [
            'current_day' => $currentDay,
            'admin_can_approve' => ($currentDay >= 30 || $currentDay <= 7),
            'director_can_approve' => true,
            'next_approval_window' => $this->getNextApprovalWindow(),
            'remaining_days' => $this->getRemainingApprovalDays()
        ];
    }
    
    /**
     * Get next approval window
     */
    protected function getNextApprovalWindow()
    {
        $currentDay = now()->day;
        
        if ($currentDay >= 30) {
            return "Current window ends on 7th of next month";
        } elseif ($currentDay <= 7) {
            return "Current window ends on 7th of this month";
        } else {
            $nextStart = now()->endOfMonth()->day;
            return "Next approval window: {$nextStart}th to 7th of next month";
        }
    }
    
    /**
     * Get remaining days in current approval window
     */
    protected function getRemainingApprovalDays()
    {
        $currentDay = now()->day;
        
        if ($currentDay >= 30) {
            $daysInMonth = now()->daysInMonth;
            $daysToEnd = $daysInMonth - $currentDay;
            return $daysToEnd + 7; // Days remaining this month + 7 days next month
        } elseif ($currentDay <= 7) {
            return 7 - $currentDay;
        }
        
        return 0;
    }
}