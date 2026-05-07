<?php

namespace App\Controllers\Salary;

use App\Controllers\Controller;
use App\Models\SalaryEmployee;
use App\Models\SalaryPayment;
use App\Models\SalaryDeduction;
use App\Models\SalaryPaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalaryDashboardController extends Controller
{
    public function index()
    {
        // Monthly stats (works on both MySQL and PostgreSQL)
        $monthlyStats = SalaryPayment::whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->where('status', 'processed')
            ->select(
                DB::raw('SUM(net_amount) as total_paid'),
                DB::raw('SUM(deductions_total) as total_deductions'),
                DB::raw('COUNT(*) as total_transactions')
            )->first();
        
        // Pending counts
        $pendingPayments = SalaryPayment::where('status', 'pending_approval')->count();
        $pendingSchedules = SalaryPaymentSchedule::where('status', 'pending')->count();
        $pendingDeductions = SalaryDeduction::where('status', 'pending')->count();
        
        // Recent transactions
        $recentTransactions = SalaryPayment::with('employee')
            ->where('status', 'processed')
            ->latest()
            ->take(10)
            ->get();
        
        // Monthly summary for chart - DATABASE AGNOSTIC
        $monthlySummary = $this->getMonthlySummary(now()->year);
        
        // Pending lists for quick approval
        $pendingPaymentsList = SalaryPayment::with('employee')
            ->where('status', 'pending_approval')
            ->latest()
            ->take(5)
            ->get();
        
        $pendingDeductionsList = SalaryDeduction::with('employee')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();
        
        return view('salary.dashboard', compact(
            'monthlyStats', 'pendingPayments', 'pendingSchedules', 
            'pendingDeductions', 'recentTransactions', 'monthlySummary',
            'pendingPaymentsList', 'pendingDeductionsList'
        ));
    }
    
    /**
     * Get monthly summary - Database agnostic (works on MySQL & PostgreSQL)
     */
    private function getMonthlySummary($year)
    {
        $payments = SalaryPayment::where('status', 'processed')
            ->whereYear('payment_date', $year)
            ->get();
        
        // Group by month using PHP (database agnostic)
        $monthlyData = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        foreach ($months as $index => $month) {
            $monthNumber = $index + 1;
            $total = $payments->filter(function($payment) use ($monthNumber) {
                return $payment->payment_date->month == $monthNumber;
            })->sum('net_amount');
            
            $monthlyData[] = (object)[
                'month' => $month,
                'total' => $total
            ];
        }
        
        return collect($monthlyData);
    }
}