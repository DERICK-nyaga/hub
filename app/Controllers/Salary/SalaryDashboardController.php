<?php

namespace App\Controllers\Salary;

use App\Controllers\Controller;
use App\Models\SalaryEmployee;
use App\Models\SalaryPayment;
use App\Models\SalaryDeduction;
use App\Models\SalaryPaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryDashboardController extends Controller
{
    
    public function index()
    {
        // Monthly stats
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
        
        // Monthly summary for chart
        $monthlySummary = SalaryPayment::where('status', 'processed')
            ->select(
                DB::raw("TO_CHAR(payment_date, 'Mon') as month"),
                DB::raw('SUM(net_amount) as total')
            )
            ->whereYear('payment_date', now()->year)
            ->groupBy(DB::raw("TO_CHAR(payment_date, 'Mon')"), DB::raw("EXTRACT(MONTH FROM payment_date)"))
            ->orderBy(DB::raw("EXTRACT(MONTH FROM payment_date)"))
            ->get();
        
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
}