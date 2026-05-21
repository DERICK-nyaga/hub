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
use App\Services\Salary\DeductionRuleService;
use App\Traits\ApprovalPeriodTrait;

class SalaryPaymentController extends Controller
{

    use ApprovalPeriodTrait;


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
        
        // Get approval info
        $approvalInfo = $this->getApprovalInfo();
        
        return view('salary.payments.index', compact(
            'payments', 'totalPayments', 'pendingApprovals', 'monthlyTotal', 'avgPayment', 'approvalInfo'
        ));
    }
    
    public function create()
    {
        $employees = Employee::where('status', 'active')->get();
        $approvalInfo = $this->getApprovalInfo();
        
        return view('salary.payments.create', compact('employees', 'approvalInfo'));
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

       /**
     * Get all pending deductions for an employee
     */
    private function getEmployeePendingDeductions($employeeId)
    {
        // Get pending deductions that are applied
        $deductions = SalaryDeduction::where('employee_id', $employeeId)
            ->where('status', 'applied')
            ->with('schedule')
            ->get();
        
        $totalDeduction = 0;
        $deductionDetails = [];
        
        foreach ($deductions as $deduction) {
            // Check if this deduction has installment schedules
            if ($deduction->number_of_installments > 0) {
                $pendingSchedules = $deduction->schedule()
                    ->where('status', 'pending')
                    ->where('scheduled_date', '<=', now()->endOfMonth())
                    ->get();
                
                foreach ($pendingSchedules as $schedule) {
                    $totalDeduction += $schedule->amount;
                    $deductionDetails[] = [
                        'deduction_id' => $deduction->id,
                        'reason' => $deduction->reason,
                        'amount' => $schedule->amount,
                        'installment' => $schedule->installment_number . '/' . $schedule->total_installments,
                        'due_date' => $schedule->scheduled_date
                    ];
                }
            } else {
                // Single deduction
                $totalDeduction += $deduction->amount;
                $deductionDetails[] = [
                    'deduction_id' => $deduction->id,
                    'reason' => $deduction->reason,
                    'amount' => $deduction->amount,
                    'installment' => 'Full',
                    'due_date' => $deduction->deduction_date
                ];
            }
        }
        
        return [
            'total' => $totalDeduction,
            'details' => $deductionDetails,
            'count' => count($deductionDetails)
        ];
    }
    
    /**
     * Create a new payment with integrated deductions
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'type' => 'required|in:regular,advance,adjustment,bonus',
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:mpesa,bank_transfer',
                'payment_date' => 'required|date',
                'notes' => 'nullable|string',
                'include_deductions' => 'boolean'
            ]);
            
            DB::beginTransaction();
            
            $salaryEmployee = $this->syncEmployeeToSalaryTable($validated['employee_id']);
            
            if (!$salaryEmployee) {
                throw new \Exception('Employee not found in salary table');
            }
            
            // Check if employee already has payment for this month
            $monthlyPaymentCheck = $this->hasMonthlyPayment($salaryEmployee->id, $validated['type']);
            if ($monthlyPaymentCheck['exists']) {
                throw new \Exception($monthlyPaymentCheck['message']);
            }
            
            // Get pending deductions
            $deductions = $this->getEmployeePendingDeductions($salaryEmployee->id);
            $deductionsTotal = $validated['include_deductions'] ? $deductions['total'] : 0;
            $netAmount = $validated['amount'] - $deductionsTotal;
            
            // Ensure net amount is not negative
            if ($netAmount < 0) {
                throw new \Exception("Net amount cannot be negative. Gross: " . number_format($validated['amount'], 2) . 
                    ", Deductions: " . number_format($deductionsTotal, 2));
            }
            
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
            
            // Link deductions to this payment
            if ($validated['include_deductions'] && !empty($deductions['details'])) {
                foreach ($deductions['details'] as $deductionDetail) {
                    $deduction = SalaryDeduction::find($deductionDetail['deduction_id']);
                    if ($deduction) {
                        $deduction->payment_id = $payment->id;
                        $deduction->save();
                        
                        // Mark the schedule as deducted if it's an installment
                        if ($deduction->number_of_installments > 0) {
                            $schedule = $deduction->schedule()
                                ->where('status', 'pending')
                                ->where('scheduled_date', '<=', now()->endOfMonth())
                                ->first();
                            if ($schedule) {
                                $schedule->status = 'deducted';
                                $schedule->payment_id = $payment->id;
                                $schedule->save();
                            }
                        }
                    }
                }
            }
            
            SalaryApprovalLog::create([
                'approvable_type' => SalaryPayment::class,
                'approvable_id' => $payment->id,
                'user_id' => Auth::id(),
                'action' => 'requested',
                'comments' => "Payment request submitted for approval. Deductions: KES " . number_format($deductionsTotal, 2),
            ]);
            
            DB::commit();
            
            return redirect()->route('salary.payments.index')
                ->with('success', 'Payment request created successfully. Waiting for approval.')
                ->with('deductions_info', $deductions);
                
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create payment: ' . $e->getMessage())->withInput();
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

    /**
     * Approve payment with role-based restrictions
     */
    public function approve($id, Request $request)
    {
        try {
            \Log::info('Approve payment attempt', ['payment_id' => $id, 'user_id' => Auth::id()]);
            
            // Check approval permissions
            $approvalCheck = $this->canApprove();
            if (!$approvalCheck['can_approve']) {
                return response()->json([
                    'success' => false,
                    'message' => $approvalCheck['message']
                ], 403);
            }
            
            $payment = SalaryPayment::with('employee')->find($id);
            
            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }
            
            // Check if payment is already approved
            if ($payment->status !== 'pending_approval') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment cannot be approved. Current status: ' . $payment->status
                ], 422);
            }
            
            // Determine approval level
            $isDirector = $this->isDirector();
            $isAdmin = $this->isAdmin();
            
            $approvalLevel = $isDirector ? 'director' : ($isAdmin ? 'admin' : 'unknown');
            
            // Update payment status
            $payment->status = 'approved';
            $payment->approved_at = now();
            $payment->approved_by = auth()->id();
            $payment->approval_level = $approvalLevel;
            $payment->save();
            
            // Process linked deductions
            if ($payment->deductions_total > 0) {
                $deductions = SalaryDeduction::where('payment_id', $payment->id)->get();
                foreach ($deductions as $deduction) {
                    // Mark deduction as processed
                    $deduction->status = 'processed';
                    $deduction->processed_at = now();
                    $deduction->save();
                }
            }
            
            // Create approval log
            SalaryApprovalLog::create([
                'approvable_type' => SalaryPayment::class,
                'approvable_id' => $payment->id,
                'user_id' => Auth::id(),
                'action' => 'approved',
                'comments' => "Payment approved by " . ucfirst($approvalLevel) . ". Deductions applied: KES " . number_format($payment->deductions_total, 2),
            ]);
            
            \Log::info('Payment approved successfully', [
                'payment_id' => $id, 
                'approved_by' => Auth::id(),
                'approval_level' => $approvalLevel
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment approved successfully',
                'approval_level' => $approvalLevel
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
    
    /**
     * Get database-agnostic date format expression
     */
    private function getDateFormatExpression($field, $format)
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            if ($format === 'Y-m') {
                return "TO_CHAR({$field}, 'YYYY-MM')";
            } elseif ($format === 'Mon') {
                return "TO_CHAR({$field}, 'Mon')";
            } elseif ($format === 'Y') {
                return "EXTRACT(YEAR FROM {$field})";
            } elseif ($format === 'm') {
                return "EXTRACT(MONTH FROM {$field})";
            }
        } elseif ($driver === 'mysql') {
            if ($format === 'Y-m') {
                return "DATE_FORMAT({$field}, '%Y-%m')";
            } elseif ($format === 'Mon') {
                return "DATE_FORMAT({$field}, '%b')";
            } elseif ($format === 'Y') {
                return "YEAR({$field})";
            } elseif ($format === 'm') {
                return "MONTH({$field})";
            }
        } elseif ($driver === 'sqlite') {
            if ($format === 'Y-m') {
                return "strftime('%Y-%m', {$field})";
            } elseif ($format === 'Mon') {
                return "strftime('%m', {$field})";
            } elseif ($format === 'Y') {
                return "strftime('%Y', {$field})";
            } elseif ($format === 'm') {
                return "strftime('%m', {$field})";
            }
        }
        
        return $field;
    }
    
    public function history(Request $request)
    {
        $driver = DB::connection()->getDriverName();
        
        // Get monthly summary for chart - Database agnostic
        if ($driver === 'pgsql') {
            $summary = SalaryPayment::where('status', 'processed')
                ->selectRaw("TO_CHAR(payment_date, 'YYYY-MM') as month, 
                             SUM(net_amount) as total_amount, 
                             COUNT(*) as total_count, 
                             AVG(net_amount) as average_amount")
                ->groupBy(DB::raw("TO_CHAR(payment_date, 'YYYY-MM')"))
                ->orderBy(DB::raw("TO_CHAR(payment_date, 'YYYY-MM')"), 'desc')
                ->get();
        } else {
            // MySQL and others
            $summary = SalaryPayment::where('status', 'processed')
                ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, 
                             SUM(net_amount) as total_amount, 
                             COUNT(*) as total_count, 
                             AVG(net_amount) as average_amount')
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get();
        }
        
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

    // Method for chart data API endpoint - Fixed for PostgreSQL
    public function chartData(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $driver = DB::connection()->getDriverName();
        
        // Database-agnostic query
        if ($driver === 'pgsql') {
            $data = SalaryPayment::where('status', 'processed')
                ->whereYear('payment_date', $year)
                ->selectRaw("TO_CHAR(payment_date, 'YYYY-MM') as month,
                             TO_CHAR(payment_date, 'Mon') as month_name,
                             SUM(net_amount) as total_amount,
                             COUNT(*) as total_count")
                ->groupBy(DB::raw("TO_CHAR(payment_date, 'YYYY-MM')"), DB::raw("TO_CHAR(payment_date, 'Mon')"))
                ->orderBy(DB::raw("TO_CHAR(payment_date, 'YYYY-MM')"), 'asc')
                ->get();
        } else {
            // MySQL and others
            $data = SalaryPayment::where('status', 'processed')
                ->whereYear('payment_date', $year)
                ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month,
                             DATE_FORMAT(payment_date, "%b") as month_name,
                             SUM(net_amount) as total_amount,
                             COUNT(*) as total_count')
                ->groupBy('month', 'month_name')
                ->orderBy('month', 'asc')
                ->get();
        }
        
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


     /**
     * Process payment with deduction rules
     */
    public function processPaymentWithDeduction(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'gross_salary' => 'required|numeric|min:0',
            'deduction_amount' => 'required|numeric|min:0',
            'deduction_reason' => 'required|string',
            'deduction_type' => 'required|in:penalty,loan,advance_recovery,loss,other',
            'payment_method' => 'required|in:mpesa,bank_transfer',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Get salary employee
            $salaryEmployee = $this->syncEmployeeToSalaryTable($validated['employee_id']);
            
            if (!$salaryEmployee) {
                throw new \Exception('Employee not found');
            }
            
            // Process deduction based on rules
            $deduction = $this->deductionRuleService->applyDeduction(
                $salaryEmployee,
                $validated['deduction_amount'],
                $validated['deduction_reason'],
                $validated['deduction_type']
            );
            
            // Calculate net payable amount
            $payableInfo = $this->deductionRuleService->calculateNetPayable(
                $validated['gross_salary'],
                $validated['deduction_amount'],
                $salaryEmployee
            );
            
            // Check if we can proceed
            if (!$payableInfo['can_proceed']) {
                return response()->json([
                    'success' => false,
                    'requires_dismissal' => true,
                    'message' => $payableInfo['message'],
                    'deduction' => $deduction
                ], 422);
            }
            
            // Create payment record
            $reference = 'PAY-' . strtoupper(uniqid());
            
            $payment = SalaryPayment::create([
                'employee_id' => $salaryEmployee->id,
                'deduction_id' => $deduction->id,
                'amount' => $validated['gross_salary'],
                'deductions_total' => $payableInfo['deduction_amount'],
                'net_amount' => $payableInfo['net_amount'],
                'type' => $validated['deduction_type'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $reference,
                'status' => 'pending_approval',
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);
            
            // Create approval log
            SalaryApprovalLog::create([
                'approvable_type' => SalaryPayment::class,
                'approvable_id' => $payment->id,
                'user_id' => Auth::id(),
                'action' => 'requested',
                'comments' => "Payment request submitted. Deduction: KES " . number_format($validated['deduction_amount'], 2),
            ]);
            
            DB::commit();
            
            // Return success with payment details
            return response()->json([
                'success' => true,
                'payment' => [
                    'reference' => $reference,
                    'gross_salary' => number_format($validated['gross_salary'], 2),
                    'deduction_amount' => number_format($payableInfo['deduction_amount'], 2),
                    'net_amount' => number_format($payableInfo['net_amount'], 2),
                    'message' => $payableInfo['message']
                ],
                'redirect_url' => route('salary.payments.show-payable', $payment->id)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Show the payable amount to employee before confirmation
     */
    public function showPayableAmount($id)
    {
        $payment = SalaryPayment::with('employee', 'deduction')->findOrFail($id);
        
        return view('salary.payments.payable-amount', [
            'payment' => $payment,
            'employee' => $payment->employee,
            'deduction' => $payment->deduction,
            'gross_amount' => $payment->amount,
            'deduction_amount' => $payment->deductions_total,
            'net_amount' => $payment->net_amount,
            'installment_info' => $payment->deduction ? [
                'installment_amount' => $payment->deduction->installment_amount,
                'total_installments' => $payment->deduction->number_of_installments,
                'completed_installments' => SalaryPaymentSchedule::where('deduction_id', $payment->deduction_id)
                    ->where('status', 'paid')
                    ->count()
            ] : null
        ]);
    }
    
    /**
     * Show payment preview with deductions
     */
    public function preview($employeeId)
    {
        try {
            $employee = Employee::findOrFail($employeeId);
            $salaryEmployee = $this->syncEmployeeToSalaryTable($employeeId);
            $deductions = $this->getEmployeePendingDeductions($salaryEmployee->id);
            
            return response()->json([
                'success' => true,
                'employee' => $employee,
                'pending_deductions' => $deductions,
                'has_deductions' => $deductions['count'] > 0,
                'can_process_payment' => !$this->hasMonthlyPayment($salaryEmployee->id)['exists']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get approval information for display
     */
    public function approvalInfo()
    {
        return response()->json([
            'success' => true,
            'approval_info' => $this->getApprovalInfo(),
            'user_role' => Auth::user()->roles->first()->name ?? 'unknown'
        ]);
    }
    
    /**
     * Confirm payment and proceed
     */
    public function confirmPayment(Request $request, $id)
    {
        $validated = $request->validate([
            'confirmation' => 'required|accepted'
        ]);
        
        DB::beginTransaction();
        
        try {
            $payment = SalaryPayment::findOrFail($id);
            
            if ($payment->status !== 'pending_approval') {
                throw new \Exception('Payment cannot be confirmed at this stage');
            }
            
            // Update payment status
            $payment->status = 'approved';
            $payment->approved_at = now();
            $payment->approved_by = auth()->id();
            $payment->save();
            
            // Update deduction schedules if this payment covers an installment
            if ($payment->deduction_id) {
                $schedule = SalaryPaymentSchedule::where('deduction_id', $payment->deduction_id)
                    ->where('status', 'pending')
                    ->where('due_date', '<=', now()->endOfMonth())
                    ->first();
                
                if ($schedule) {
                    $schedule->status = 'paid';
                    $schedule->paid_at = now();
                    $schedule->payment_id = $payment->id;
                    $schedule->save();
                    
                    // Check if all installments are paid
                    $remainingSchedules = SalaryPaymentSchedule::where('deduction_id', $payment->deduction_id)
                        ->where('status', 'pending')
                        ->count();
                    
                    if ($remainingSchedules == 0) {
                        $deduction = SalaryDeduction::find($payment->deduction_id);
                        $deduction->status = 'completed';
                        $deduction->save();
                    }
                }
            }
            
            // Create approval log
            SalaryApprovalLog::create([
                'approvable_type' => SalaryPayment::class,
                'approvable_id' => $payment->id,
                'user_id' => Auth::id(),
                'action' => 'confirmed',
                'comments' => 'Payment confirmed and processed',
            ]);
            
            DB::commit();
            
            return redirect()->route('salary.payments.receipt', $payment->id)
                ->with('success', 'Payment processed successfully! Net amount: KES ' . number_format($payment->net_amount, 2));
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to confirm payment: ' . $e->getMessage());
        }
    }
    
    /**
     * Process dismissal for gross misconduct
     */
    public function processDismissal(Request $request, $deductionId)
    {
        $validated = $request->validate([
            'dismissal_letter' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        
        try {
            $deduction = SalaryDeduction::findOrFail($deductionId);
            
            if (!$deduction->requires_dismissal) {
                throw new \Exception('This deduction does not require dismissal');
            }
            
            // Store dismissal letter
            $dismissalPath = $validated['dismissal_letter']->store('dismissal_letters', 'public');
            
            // Update deduction status
            $deduction->status = 'dismissal_processed';
            $deduction->dismissal_letter_path = $dismissalPath;
            $deduction->dismissal_notes = $validated['notes'] ?? null;
            $deduction->dismissal_processed_at = now();
            $deduction->dismissal_processed_by = auth()->id();
            $deduction->save();
            
            // Update employee status
            $employee = SalaryEmployee::find($deduction->employee_id);
            if ($employee) {
                $employee->status = 'terminated';
                $employee->termination_date = now();
                $employee->termination_reason = "Gross misconduct - Deduction of KES " . number_format($deduction->amount, 2);
                $employee->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Dismissal processed successfully. Employee terminated due to gross misconduct.',
                'dismissal_letter' => $dismissalPath
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process dismissal: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get employee balance after applying deduction rules
     */
    public function previewDeduction(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'gross_salary' => 'required|numeric|min:0',
            'deduction_amount' => 'required|numeric|min:0'
        ]);
        
        try {
            $salaryEmployee = $this->syncEmployeeToSalaryTable($validated['employee_id']);
            
            if (!$salaryEmployee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }
            
            $payableInfo = $this->deductionRuleService->calculateNetPayable(
                $validated['gross_salary'],
                $validated['deduction_amount'],
                $salaryEmployee
            );
            
            return response()->json([
                'success' => true,
                'gross_salary' => number_format($validated['gross_salary'], 2),
                'deduction_amount' => number_format($payableInfo['deduction_amount'], 2),
                'net_amount' => number_format($payableInfo['net_amount'], 2),
                'message' => $payableInfo['message'],
                'requires_dismissal' => $payableInfo['requires_full_payment'] ?? false,
                'installment_info' => $payableInfo['installment_info'] ?? null
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}