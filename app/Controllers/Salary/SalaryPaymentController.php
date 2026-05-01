<?php
// app/Controllers/Salary/SalaryPaymentController.php

namespace App\Controllers\Salary;

use App\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryEmployee;
use App\Models\SalaryPayment;
use App\Models\SalaryDeduction;
use App\Models\SalaryApprovalLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalaryPaymentController extends Controller
{

    public function index(Request $request)
    {
        $query = SalaryPayment::with('employee');
        
        // Apply filters
        if ($request->search) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            })->orWhere('transaction_reference', 'like', "%{$request->search}%");
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->month) {
            $query->whereYear('payment_date', substr($request->month, 0, 4))
                ->whereMonth('payment_date', substr($request->month, 5, 2));
        }
        
        $payments = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Statistics
        $totalPayments = SalaryPayment::count();
        $pendingApprovals = SalaryPayment::where('status', 'pending_approval')->count();
        $monthlyTotal = SalaryPayment::whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->where('status', 'processed')
            ->sum('net_amount');
        $avgPayment = SalaryPayment::where('status', 'processed')->avg('net_amount');
        
        return view('salary.payments.index', compact(
            'payments', 'totalPayments', 'pendingApprovals', 'monthlyTotal', 'avgPayment'
        ));
    }
    
    public function create()
    {
        // Use your existing Employee model
        $employees = Employee::where('status', 'active')->get();
        return view('salary.payments.create', compact('employees'));
    }
    
    /**
     * Sync employee data from main employees table to salary_employees table
     */
    private function syncEmployeeToSalaryTable($employeeId)
    {
        // Get employee from main table
        $mainEmployee = Employee::find($employeeId);
        
        if (!$mainEmployee) {
            return null;
        }
        
        // Sync or create in salary_employees table
        $salaryEmployee = SalaryEmployee::updateOrCreate(
            ['phone' => $mainEmployee->phone], // Match by phone
            [
                'name' => $mainEmployee->full_name ?? $mainEmployee->first_name . ' ' . $mainEmployee->last_name,
                'phone' => $mainEmployee->phone,
                'position' => $mainEmployee->position,
                'station' => $mainEmployee->station?->name ?? 'Main Office',
                'status' => $mainEmployee->status,
                'base_salary' => $mainEmployee->salary,
                'bank_account' => $mainEmployee->bank_account ?? null,
                'mpesa_number' => $mainEmployee->phone,
            ]
        );
        
        return $salaryEmployee;
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',  // From your main employees table
            'type' => 'required|in:regular,advance,adjustment',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:mpesa,bank_transfer',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            // FIRST: Sync the employee to salary_employees table
            $salaryEmployee = $this->syncEmployeeToSalaryTable($validated['employee_id']);
            
            if (!$salaryEmployee) {
                throw new \Exception('Employee not found in main table');
            }
            
            // Get main employee for deduction calculation
            $mainEmployee = Employee::find($validated['employee_id']);
            
            // Calculate deductions from your existing deduction system
            $deductionsTotal = $mainEmployee->deduction_balance ?? 0;
            
            // If you have a deduction_transactions table:
            // $deductionsTotal = $mainEmployee->deductionTransactions()->sum('amount') ?? 0;
            
            $netAmount = $validated['amount'] - $deductionsTotal;
            
            // Generate transaction reference
            $reference = 'PAY-' . strtoupper(uniqid());
            
            // Create payment using the salary_employee ID
            $payment = SalaryPayment::create([
                'employee_id' => $salaryEmployee->id,  // Use the synced salary_employee ID
                'amount' => $validated['amount'],
                'deductions_total' => $deductionsTotal,
                'net_amount' => $netAmount,
                'type' => $validated['type'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $reference,
                'status' => 'pending_approval',
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);
            
            // Log approval request
            SalaryApprovalLog::create([
                'approvable_type' => SalaryPayment::class,
                'approvable_id' => $payment->id,
                'user_id' => Auth::id(),
                'action' => 'requested',
                'comments' => 'Payment request submitted for approval',
            ]);
            
            DB::commit();
            
            return redirect()->route('salary.payments.index')
                ->with('success', 'Payment request created successfully. Employee data synced automatically. Waiting for approval.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create payment: ' . $e->getMessage());
        }
    }
    
    // API endpoint for viewing single payment
    public function show($id)
    {
        $payment = SalaryPayment::with('employee')->findOrFail($id);
        return response()->json($payment);
    }

    // Print receipt method
    public function print($id)
    {
        $payment = SalaryPayment::with('employee')->findOrFail($id);
        return view('salary.payments.print', compact('payment'));
    }

    public function approve(Request $request, SalaryPayment $payment)
    {
        $request->validate([
            'comments' => 'nullable|string',
        ]);
        
        if ($payment->status !== 'pending_approval') {
            return back()->with('error', 'This payment cannot be approved.');
        }
        
        DB::beginTransaction();
        
        try {
            $payment->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            
            // Process automatic payment
            $this->processAutomaticPayment($payment);
            
            SalaryApprovalLog::create([
                'approvable_type' => SalaryPayment::class,
                'approvable_id' => $payment->id,
                'user_id' => Auth::id(),
                'action' => 'approved',
                'comments' => $request->comments ?? 'Payment approved',
            ]);
            
            DB::commit();
            
            return redirect()->route('salary.payments.index')
                ->with('success', 'Payment approved and processed successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve payment: ' . $e->getMessage());
        }
    }
    
    private function processAutomaticPayment(SalaryPayment $payment)
    {
        // Update status to processed
        $payment->update(['status' => 'processed']);
        
        // Get the main employee to update deduction balance
        $salaryEmployee = SalaryEmployee::find($payment->employee_id);
        if ($salaryEmployee) {
            $mainEmployee = Employee::where('phone', $salaryEmployee->phone)->first();
            if ($mainEmployee && $payment->deductions_total > 0) {
                // Update deduction balance if needed
                // $mainEmployee->update(['deduction_balance' => 0]);
            }
        }
        
        \Log::info('Payment processed', [
            'reference' => $payment->transaction_reference,
            'amount' => $payment->net_amount,
            'method' => $payment->payment_method,
            'employee_id' => $payment->employee_id,
        ]);
    }
    
    /**
     * Display payment history with filters and summaries
     */
    public function history(Request $request)
    {
        $summary = collect([]); 
        $yearlyTotal = 0;
        $totalTransactions = 0;
        $averagePayment = 0;
        $totalDeductions = 0;
        
        // Build the query for processed payments
        $query = SalaryPayment::with('employee')
            ->where('status', 'processed');
        
        // Apply filters
        if ($request->year) {
            $query->whereYear('payment_date', $request->year);
        }
        
        if ($request->month) {
            $query->whereMonth('payment_date', $request->month);
        }
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->method) {
            $query->where('payment_method', $request->method);
        }
        
        if ($request->search) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            })->orWhere('transaction_reference', 'like', "%{$request->search}%");
        }
        
        $payments = $query->orderBy('payment_date', 'desc')->paginate(20);
        
        $hasProcessedPayments = SalaryPayment::where('status', 'processed')->exists();
        
        if ($hasProcessedPayments) {
            $summary = SalaryPayment::where('status', 'processed')
                ->select(
                    DB::raw('DATE_FORMAT(payment_date, "%Y-%m") as month'),
                    DB::raw('SUM(net_amount) as total_amount'),
                    DB::raw('COUNT(*) as total_count'),
                    DB::raw('AVG(net_amount) as average_amount')
                )
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get();
            
            $yearlyTotal = SalaryPayment::whereYear('payment_date', date('Y'))
                ->where('status', 'processed')
                ->sum('net_amount');
            
            $totalTransactions = SalaryPayment::where('status', 'processed')->count();
            $averagePayment = SalaryPayment::where('status', 'processed')->avg('net_amount');
            $totalDeductions = SalaryPayment::where('status', 'processed')->sum('deductions_total');
        }
        
        if ($request->export) {
            return $this->exportHistory($query->get());
        }
        
        return view('salary.payments.history', compact(
            'payments', 'summary', 'yearlyTotal', 'totalTransactions', 
            'averagePayment', 'totalDeductions'
        ));
    }
    
    public function chartData(Request $request)
    {
        $year = $request->get('year', date('Y'));
        
        $months = [];
        $amounts = [];
        $counts = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $data = SalaryPayment::whereYear('payment_date', $year)
                ->whereMonth('payment_date', $month)
                ->where('status', 'processed')
                ->select(
                    DB::raw('SUM(net_amount) as total'),
                    DB::raw('COUNT(*) as count')
                )
                ->first();
            
            $months[] = date('F', mktime(0, 0, 0, $month, 1));
            $amounts[] = $data->total ?? 0;
            $counts[] = $data->count ?? 0;
        }
        
        return response()->json([
            'months' => $months,
            'amounts' => $amounts,
            'counts' => $counts
        ]);
    }
    
    public function receipt($id)
    {
        $payment = SalaryPayment::with('employee')->findOrFail($id);
        return response()->json($payment);
    }
    
    private function exportHistory($payments)
    {
        $filename = 'payment_history_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, [
                'Transaction Reference', 'Payment Date', 'Employee Name', 
                'Employee Position', 'Payment Type', 'Gross Amount (KES)',
                'Deductions (KES)', 'Net Amount (KES)', 'Payment Method', 'Status'
            ]);
            
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->transaction_reference,
                    $payment->payment_date,
                    $payment->employee->name ?? 'N/A',
                    $payment->employee->position ?? 'N/A',
                    ucfirst($payment->type),
                    number_format($payment->amount, 2),
                    number_format($payment->deductions_total, 2),
                    number_format($payment->net_amount, 2),
                    strtoupper($payment->payment_method),
                    ucfirst($payment->status)
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    // Delete single transaction
    public function destroy($id)
    {
        $payment = SalaryPayment::findOrFail($id);
        $payment->delete();
        
        return response()->json(['success' => true]);
    }

    // Clear all history (Admin only)
    public function clearHistory()
    {
        SalaryPayment::truncate();
        
        return response()->json(['success' => true]);
    }

    public function getPendingApprovals()
    {
        $pendingItems = [];
        
        // Get pending payments
        $pendingPayments = SalaryPayment::with('employee')
            ->where('status', 'pending_approval')
            ->get();
        
        foreach ($pendingPayments as $payment) {
            $pendingItems[] = [
                'id' => $payment->id,
                'type' => 'Payment',
                'reference' => $payment->transaction_reference,
                'employee_name' => $payment->employee->name ?? 'N/A',
                'amount' => (float)$payment->amount,
                'created_at' => $payment->created_at->toISOString(),
                'reason' => null
            ];
        }
        
        // Get pending deductions (if table exists)
        if (class_exists('App\Models\SalaryDeduction')) {
            $pendingDeductions = SalaryDeduction::with('employee')
                ->where('status', 'pending')
                ->get();
            
            foreach ($pendingDeductions as $deduction) {
                $pendingItems[] = [
                    'id' => $deduction->id,
                    'type' => 'Deduction',
                    'reference' => 'DED-' . $deduction->id,
                    'employee_name' => $deduction->employee->name ?? 'N/A',
                    'amount' => (float)$deduction->amount,
                    'created_at' => $deduction->created_at->toISOString(),
                    'reason' => $deduction->reason
                ];
            }
        }
        
        // Get pending schedules (if table exists)
        if (class_exists('App\Models\SalaryPaymentSchedule')) {
            $pendingSchedules = SalaryPaymentSchedule::with('employee')
                ->where('status', 'pending')
                ->get();
            
            foreach ($pendingSchedules as $schedule) {
                $pendingItems[] = [
                    'id' => $schedule->id,
                    'type' => 'Schedule',
                    'reference' => 'SCH-' . $schedule->id,
                    'employee_name' => $schedule->employee->name ?? 'N/A',
                    'amount' => (float)$schedule->amount,
                    'created_at' => $schedule->created_at->toISOString(),
                    'reason' => null
                ];
            }
        }
        
        // Sort by created_at descending
        usort($pendingItems, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return response()->json(array_values($pendingItems));
    }

    public function getPendingCount()
    {
        $paymentCount = SalaryPayment::where('status', 'pending_approval')->count();
        $deductionCount = 0;
        $scheduleCount = 0;
        
        if (class_exists('App\Models\SalaryDeduction')) {
            $deductionCount = SalaryDeduction::where('status', 'pending')->count();
        }
        
        if (class_exists('App\Models\SalaryPaymentSchedule')) {
            $scheduleCount = SalaryPaymentSchedule::where('status', 'pending')->count();
        }
        
        return response()->json([
            'pending_count' => $paymentCount + $deductionCount + $scheduleCount
        ]);
    }

    public function reject($id)
    {
        $payment = SalaryPayment::findOrFail($id);
        $payment->update(['status' => 'failed']);
        
        if (class_exists('App\Models\SalaryApprovalLog')) {
            SalaryApprovalLog::create([
                'approvable_type' => SalaryPayment::class,
                'approvable_id' => $payment->id,
                'user_id' => Auth::id(),
                'action' => 'rejected',
                'comments' => 'Payment rejected'
            ]);
        }
        
        return response()->json(['success' => true]);
    }
    
}