<?php

namespace App\Controllers\Salary;

use App\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryEmployee;
use App\Models\SalaryPayment;
use App\Models\SalaryDeduction;
use App\Models\SalaryPaymentSchedule;
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
        $employees = Employee::where('status', 'active')->get();
        return view('salary.payments.create', compact('employees'));
    }
    
    private function syncEmployeeToSalaryTable($employeeId)
    {
        $mainEmployee = Employee::find($employeeId);
        
        if (!$mainEmployee) {
            return null;
        }
        
        $salaryEmployee = SalaryEmployee::updateOrCreate(
            ['phone' => $mainEmployee->phone],
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
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:regular,advance,adjustment',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:mpesa,bank_transfer',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            $salaryEmployee = $this->syncEmployeeToSalaryTable($validated['employee_id']);
            
            if (!$salaryEmployee) {
                throw new \Exception('Employee not found in main table');
            }
            
            $mainEmployee = Employee::find($validated['employee_id']);
            $deductionsTotal = $mainEmployee->deduction_balance ?? 0;
            $netAmount = $validated['amount'] - $deductionsTotal;
            $reference = 'PAY-' . strtoupper(uniqid());
            
            $payment = SalaryPayment::create([
                'employee_id' => $salaryEmployee->id,
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
            
            SalaryApprovalLog::create([
                'approvable_type' => SalaryPayment::class,
                'approvable_id' => $payment->id,
                'user_id' => Auth::id(),
                'action' => 'requested',
                'comments' => 'Payment request submitted for approval',
            ]);
            
            DB::commit();
            
            return redirect()->route('salary.payments.index')
                ->with('success', 'Payment request created successfully. Waiting for approval.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create payment: ' . $e->getMessage());
        }
    }
    
    public function show($id)
    {
        $payment = SalaryPayment::with('employee')->findOrFail($id);
        return response()->json($payment);
    }

    public function print($id)
    {
        $payment = SalaryPayment::with('employee')->findOrFail($id);
        return view('salary.payments.print', compact('payment'));
    }

public function approve($id, Request $request)
{
    try {
        \Log::info('Approve payment attempt', ['payment_id' => $id]);
        
        $payment = SalaryPayment::with('employee')->find($id);
        
        if (!$payment) {
            \Log::error('Payment not found', ['payment_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }
        
        \Log::info('Payment found', [
            'payment_id' => $payment->id,
            'current_status' => $payment->status,
            'employee_id' => $payment->employee_id
        ]);
        
        // Update payment status
        $payment->status = 'approved';
        $payment->approved_at = now();
        $payment->approved_by = auth()->id();
        $payment->save();
        
        \Log::info('Payment approved successfully', ['payment_id' => $id]);
        
        return response()->json([
            'success' => true,
            'message' => 'Payment approved successfully'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Payment approval error', [
            'payment_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
}
    private function processAutomaticPayment(SalaryPayment $payment)
    {
        $payment->update(['status' => 'processed']);
        
        $salaryEmployee = SalaryEmployee::find($payment->employee_id);
        if ($salaryEmployee) {
            $mainEmployee = Employee::where('phone', $salaryEmployee->phone)->first();
            if ($mainEmployee && $payment->deductions_total > 0) {
                // Update deduction balance if needed
            }
        }
        
        \Log::info('Payment processed', [
            'reference' => $payment->transaction_reference,
            'amount' => $payment->net_amount,
            'method' => $payment->payment_method,
            'employee_id' => $payment->employee_id,
        ]);
    }
    
public function history(Request $request)
{
    // Get monthly summary for chart
    $summary = SalaryPayment::where('status', 'processed')
        ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, 
                     SUM(net_amount) as total_amount, 
                     COUNT(*) as total_count, 
                     AVG(net_amount) as average_amount')
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->get();
    
    // Get yearly totals
    $yearlyTotal = SalaryPayment::where('status', 'processed')
        ->whereYear('payment_date', date('Y'))
        ->sum('net_amount');
    
    $totalTransactions = SalaryPayment::where('status', 'processed')->count();
    
    $averagePayment = SalaryPayment::where('status', 'processed')->avg('net_amount');
    
    $totalDeductions = SalaryPayment::where('status', 'processed')->sum('deductions_total');
    
    // Get paginated payments for the table
    $payments = SalaryPayment::with('employee')
        ->when($request->year, function($query, $year) {
            return $query->whereYear('payment_date', $year);
        })
        ->when($request->month, function($query, $month) {
            return $query->whereMonth('payment_date', $month);
        })
        ->when($request->type, function($query, $type) {
            return $query->where('type', $type);
        })
        ->when($request->method, function($query, $method) {
            return $query->where('payment_method', $method);
        })
        ->orderBy('payment_date', 'desc')
        ->paginate(15);
    
    return view('salary.payments.history', compact(
        'summary', 'yearlyTotal', 'totalTransactions', 
        'averagePayment', 'totalDeductions', 'payments'
    ));
}

// Method for chart data API endpoint
public function chartData(Request $request)
{
    $year = $request->get('year', date('Y'));
    
    $data = SalaryPayment::where('status', 'processed')
        ->whereYear('payment_date', $year)
        ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month,
                     DATE_FORMAT(payment_date, "%b") as month_name,
                     SUM(net_amount) as total_amount,
                     COUNT(*) as total_count')
        ->groupBy('month', 'month_name')
        ->orderBy('month', 'asc')
        ->get();
    
    $months = [];
    $amounts = [];
    $counts = [];
    
    // Fill in all months (even those with no data)
    for ($i = 1; $i <= 12; $i++) {
        $monthName = date('M', mktime(0, 0, 0, $i, 1));
        $monthKey = date('Y-m', mktime(0, 0, 0, $i, 1));
        $months[] = $monthName;
        
        $found = $data->firstWhere('month', $monthKey);
        $amounts[] = $found ? (float) $found->total_amount : 0;
        $counts[] = $found ? (int) $found->total_count : 0;
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

    public function destroy($id)
    {
        $payment = SalaryPayment::findOrFail($id);
        $payment->delete();
        
        return response()->json(['success' => true]);
    }

    public function clearHistory()
    {
        SalaryPayment::truncate();
        
        return response()->json(['success' => true]);
    }

    public function getPendingApprovals()
    {
        $pendingItems = [];
        
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