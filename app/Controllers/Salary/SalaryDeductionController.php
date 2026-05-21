<?php

namespace App\Controllers\Salary;

use App\Controllers\Controller;
use App\Models\SalaryEmployee;
use App\Models\SalaryDeduction;
use App\Models\SalaryApprovalLog;
use App\Services\Salary\DeductionRuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\ApprovalPeriodTrait;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class SalaryDeductionController extends Controller
{
    use ApprovalPeriodTrait;

    protected $deductionRuleService;

    public function __construct(DeductionRuleService $deductionRuleService)
    {
        $this->deductionRuleService = $deductionRuleService;
    }

    public function index(Request $request)
    {
        $query = SalaryDeduction::with('employee');
        
        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('reason', 'like', '%'.$request->search.'%')
                ->orWhereHas('employee', function($emp) use ($request) {
                    $emp->where('name', 'like', '%'.$request->search.'%');
                });
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        
        if ($request->filled('from_date')) {
            $query->whereDate('deduction_date', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('deduction_date', '<=', $request->to_date);
        }
        
        $deductions = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Calculate statistics
        $totalDeductions = SalaryDeduction::where('status', 'applied')->sum('amount');
        $pendingApprovals = SalaryDeduction::where('status', 'pending')->count();
        $monthlyTotal = SalaryDeduction::where('status', 'applied')
            ->whereMonth('deduction_date', now()->month)
            ->sum('amount');
        $affectedEmployees = SalaryDeduction::where('status', 'applied')
            ->distinct('employee_id')
            ->count('employee_id');
        
        
        $employees = SalaryEmployee::orderBy('name')->get();
        
        // Return the index view with all data
        return view('salary.deductions.index', compact(
            'deductions', 
            'employees', 
            'totalDeductions', 
            'pendingApprovals', 
            'monthlyTotal', 
            'affectedEmployees'
        ));
    }
    
    public function create()
    {
        try {
            $employees = SalaryEmployee::where('status', 'active')->get();
            
            if ($employees->isEmpty()) {
                return redirect()->route('salary.deductions.index')
                    ->with('warning', 'No active employees found. Please add employees before creating deductions.');
            }
            
            return view('salary.deductions.create', compact('employees'));
            
        } catch (Exception $e) {
            Log::error('Error loading deduction creation form: ' . $e->getMessage());
            
            return redirect()->route('salary.deductions.index')
                ->with('error', 'Failed to load deduction form. Please try again.');
        }
    }
    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:salary_employees,id',
                'reason' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'type' => 'required|in:penalty,loan,advance_recovery,loss,other',
                'deduction_date' => 'required|date|after_or_equal:today',
                'description' => 'nullable|string|max:1000',
            ]);
            
            // Check if employee exists
            $employee = SalaryEmployee::find($validated['employee_id']);
            if (!$employee) {
                throw new Exception('Selected employee does not exist.');
            }
            
            // Check if employee is active
            if ($employee->status !== 'active') {
                throw new Exception('Cannot create deduction for inactive or terminated employee.');
            }
            
            DB::beginTransaction();
            
            try {
                // Process deduction rules
                $ruleResult = $this->deductionRuleService->processDeductionRules(
                    $validated['amount'],
                    $employee,
                    $validated['reason'],
                    $validated['type']
                );
                
                $deduction = SalaryDeduction::create([
                    'employee_id' => $validated['employee_id'],
                    'reason' => $validated['reason'],
                    'amount' => $validated['amount'],
                    'type' => $validated['type'],
                    'deduction_date' => $validated['deduction_date'],
                    'description' => $validated['description'] ?? null,
                    'status' => $ruleResult['requires_dismissal'] ? 'pending_dismissal' : 'pending',
                    'deduction_type' => $ruleResult['deduction_type'],
                    'number_of_installments' => $ruleResult['number_of_months'],
                    'installment_amount' => $ruleResult['deduction_per_month'],
                    'requires_dismissal' => $ruleResult['requires_dismissal'],
                    'message' => $ruleResult['message']
                ]);
                
                if (!$deduction) {
                    throw new Exception('Failed to create deduction record.');
                }
                
                $approvalLog = SalaryApprovalLog::create([
                    'approvable_type' => SalaryDeduction::class,
                    'approvable_id' => $deduction->id,
                    'user_id' => Auth::id(),
                    'action' => 'requested',
                    'comments' => "Deduction created for approval. Amount: KES " . number_format($validated['amount'], 2),
                ]);
                
                if (!$approvalLog) {
                    Log::warning('Approval log not created for deduction ID: ' . $deduction->id);
                }
                
                DB::commit();
                
                $message = $ruleResult['requires_dismissal'] 
                    ? "Deduction created. Due to the amount (≥ 30,000), this requires HR review and possible dismissal letter."
                    : "Deduction created successfully and pending approval. " . $ruleResult['message'];
                
                return redirect()->route('salary.deductions.index')
                    ->with('success', $message);
                
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
            
        } catch (Exception $e) {
            Log::error('Failed to create deduction: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->with('error', 'Failed to create deduction: ' . $e->getMessage());
        }
    }
    
    /**
     * Approve deduction with role-based restrictions
     */
    public function approve(Request $request, $id)
    {
        try {
            // Check approval permissions
            $approvalCheck = $this->canApprove();
            if (!$approvalCheck['can_approve']) {
                return response()->json([
                    'success' => false,
                    'message' => $approvalCheck['message']
                ], 403);
            }
            
            $deduction = SalaryDeduction::findOrFail($id);
            
            // Validate deduction status
            if ($deduction->status !== 'pending') {
                $statusMessage = $this->getStatusMessage($deduction->status);
                return response()->json([
                    'success' => false,
                    'message' => "This deduction cannot be approved. Current status: {$statusMessage}"
                ], 422);
            }
            
            // Check if employee still exists and is active
            $employee = SalaryEmployee::find($deduction->employee_id);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot approve deduction. Employee record not found.'
                ], 404);
            }
            
            if ($employee->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot approve deduction for inactive or terminated employee.'
                ], 422);
            }
            
            // Determine approval level
            $isDirector = $this->isDirector();
            $isAdmin = $this->isAdmin();
            $approvalLevel = $isDirector ? 'director' : ($isAdmin ? 'admin' : 'unknown');
            
            DB::beginTransaction();
            
            try {
                $deduction->update([
                    'status' => 'applied',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'approval_level' => $approvalLevel
                ]);
                
                $approvalLog = SalaryApprovalLog::create([
                    'approvable_type' => SalaryDeduction::class,
                    'approvable_id' => $deduction->id,
                    'user_id' => Auth::id(),
                    'action' => 'approved',
                    'comments' => $request->input('comments', "Deduction approved by " . ucfirst($approvalLevel)),
                ]);
                
                DB::commit();
                
                Log::info('Deduction approved successfully', [
                    'deduction_id' => $deduction->id,
                    'employee_id' => $deduction->employee_id,
                    'amount' => $deduction->amount,
                    'approved_by' => Auth::id(),
                    'approval_level' => $approvalLevel
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Deduction approved successfully and will be applied to next payment.',
                    'deduction' => $deduction,
                    'approval_level' => $approvalLevel
                ]);
                
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (ModelNotFoundException $e) {
            Log::error('Deduction not found for approval: ' . $id);
            
            return response()->json([
                'success' => false,
                'message' => 'Deduction not found.'
            ], 404);
            
        } catch (Exception $e) {
            Log::error('Error approving deduction: ' . $e->getMessage(), [
                'deduction_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve deduction: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $deduction = SalaryDeduction::with(['employee', 'payment', 'schedule'])->findOrFail($id);
            
            // Add computed fields for response
            $deductionData = $deduction->toArray();
            $deductionData['formatted_amount'] = number_format($deduction->amount, 2);
            $deductionData['installment_info'] = null;
            
            if ($deduction->number_of_installments > 0) {
                $paidInstallments = $deduction->schedule()->where('status', 'paid')->count();
                $deductionData['installment_info'] = [
                    'total' => $deduction->number_of_installments,
                    'paid' => $paidInstallments,
                    'remaining' => $deduction->number_of_installments - $paidInstallments,
                    'per_installment' => number_format($deduction->installment_amount, 2)
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $deductionData
            ]);
            
        } catch (ModelNotFoundException $e) {
            Log::warning('Deduction not found: ' . $id);
            
            return response()->json([
                'success' => false,
                'message' => 'Deduction not found.'
            ], 404);
            
        } catch (Exception $e) {
            Log::error('Error fetching deduction details: ' . $e->getMessage(), [
                'deduction_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve deduction details.'
            ], 500);
        }
    }
    
    public function cancel($id)
    {
        try {
            $deduction = SalaryDeduction::findOrFail($id);
            
            // Check if deduction can be cancelled
            if (!in_array($deduction->status, ['pending', 'pending_dismissal'])) {
                $statusMessage = $this->getStatusMessage($deduction->status);
                return response()->json([
                    'success' => false,
                    'message' => "Cannot cancel deduction with status: {$statusMessage}"
                ], 422);
            }
            
            DB::beginTransaction();
            
            try {
                $deduction->update([
                    'status' => 'cancelled',
                    'message' => 'Deduction cancelled by user'
                ]);
                
                // Log the cancellation
                SalaryApprovalLog::create([
                    'approvable_type' => SalaryDeduction::class,
                    'approvable_id' => $deduction->id,
                    'user_id' => Auth::id(),
                    'action' => 'cancelled',
                    'comments' => 'Deduction cancelled'
                ]);
                
                DB::commit();
                
                Log::info('Deduction cancelled', [
                    'deduction_id' => $deduction->id,
                    'cancelled_by' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Deduction cancelled successfully.'
                ]);
                
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deduction not found.'
            ], 404);
            
        } catch (Exception $e) {
            Log::error('Error cancelling deduction: ' . $e->getMessage(), [
                'deduction_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel deduction: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function reject(Request $request, $id)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:500'
            ]);
            
            $deduction = SalaryDeduction::findOrFail($id);
            
            // Check if deduction can be rejected
            if (!in_array($deduction->status, ['pending', 'pending_dismissal'])) {
                $statusMessage = $this->getStatusMessage($deduction->status);
                return response()->json([
                    'success' => false,
                    'message' => "Cannot reject deduction with status: {$statusMessage}"
                ], 422);
            }
            
            DB::beginTransaction();
            
            try {
                $rejectionReason = $request->input('reason', 'No reason provided');
                
                $deduction->update([
                    'status' => 'rejected',
                    'rejected_by' => Auth::id(),
                    'rejected_at' => now(),
                    'rejection_reason' => $rejectionReason
                ]);
                
                // Log the rejection
                SalaryApprovalLog::create([
                    'approvable_type' => SalaryDeduction::class,
                    'approvable_id' => $deduction->id,
                    'user_id' => Auth::id(),
                    'action' => 'rejected',
                    'comments' => "Deduction rejected. Reason: {$rejectionReason}"
                ]);
                
                DB::commit();
                
                Log::info('Deduction rejected', [
                    'deduction_id' => $deduction->id,
                    'rejected_by' => Auth::id(),
                    'reason' => $rejectionReason
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Deduction rejected successfully.'
                ]);
                
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deduction not found.'
            ], 404);
            
        } catch (Exception $e) {
            Log::error('Error rejecting deduction: ' . $e->getMessage(), [
                'deduction_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject deduction: ' . $e->getMessage()
            ], 500);
        }
    }

     /**
     * Get pending deductions ready for current month's payment
     */
    public function getPendingForPayment($employeeId)
    {
        try {
            $employee = SalaryEmployee::findOrFail($employeeId);
            
            $pendingDeductions = SalaryDeduction::where('employee_id', $employeeId)
                ->where('status', 'applied')
                ->with('schedule')
                ->get();
            
            $totalAmount = 0;
            $deductionsList = [];
            
            foreach ($pendingDeductions as $deduction) {
                if ($deduction->number_of_installments > 0) {
                    $pendingSchedule = $deduction->schedule()
                        ->where('status', 'pending')
                        ->where('scheduled_date', '<=', now()->endOfMonth())
                        ->first();
                    
                    if ($pendingSchedule) {
                        $totalAmount += $pendingSchedule->amount;
                        $deductionsList[] = [
                            'id' => $deduction->id,
                            'reason' => $deduction->reason,
                            'amount' => $pendingSchedule->amount,
                            'installment' => $pendingSchedule->installment_number . '/' . $pendingSchedule->total_installments,
                            'type' => $deduction->type
                        ];
                    }
                } else {
                    $totalAmount += $deduction->amount;
                    $deductionsList[] = [
                        'id' => $deduction->id,
                        'reason' => $deduction->reason,
                        'amount' => $deduction->amount,
                        'installment' => 'Full',
                        'type' => $deduction->type
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'employee' => $employee->name,
                'total_deductions' => $totalAmount,
                'deductions' => $deductionsList,
                'count' => count($deductionsList)
            ]);
            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get pending deductions for an employee
     */
    public function getEmployeePendingDeductions($employeeId)
    {
        try {
            $employee = SalaryEmployee::findOrFail($employeeId);
            
            $pendingDeductions = SalaryDeduction::where('employee_id', $employeeId)
                ->whereIn('status', ['pending', 'applied'])
                ->with('schedule')
                ->get();
            
            $totalPendingAmount = $pendingDeductions->sum('amount');
            $nextInstallments = [];
            
            foreach ($pendingDeductions as $deduction) {
                if ($deduction->number_of_installments > 0) {
                    $nextSchedule = $deduction->schedule()
                        ->where('status', 'pending')
                        ->where('scheduled_date', '>=', now())
                        ->orderBy('scheduled_date')
                        ->first();
                    
                    if ($nextSchedule) {
                        $nextInstallments[] = [
                            'deduction_id' => $deduction->id,
                            'reason' => $deduction->reason,
                            'amount' => $nextSchedule->amount,
                            'due_date' => $nextSchedule->scheduled_date
                        ];
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name
                ],
                'total_pending_amount' => number_format($totalPendingAmount, 2),
                'deductions_count' => $pendingDeductions->count(),
                'next_installments' => $nextInstallments,
                'deductions' => $pendingDeductions
            ]);
            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.'
            ], 404);
            
        } catch (Exception $e) {
            Log::error('Error fetching employee pending deductions: ' . $e->getMessage(), [
                'employee_id' => $employeeId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending deductions.'
            ], 500);
        }
    }
    
    /**
     * Bulk approve pending deductions
     */
    public function bulkApprove(Request $request)
    {
        try {
            $request->validate([
                'deduction_ids' => 'required|array',
                'deduction_ids.*' => 'exists:salary_deductions,id'
            ]);
            
            $deductionIds = $request->deduction_ids;
            $approvedCount = 0;
            $failedIds = [];
            
            DB::beginTransaction();
            
            try {
                foreach ($deductionIds as $id) {
                    $deduction = SalaryDeduction::find($id);
                    
                    if ($deduction && $deduction->status === 'pending') {
                        $deduction->update([
                            'status' => 'applied',
                            'approved_by' => Auth::id(),
                            'approved_at' => now()
                        ]);
                        
                        SalaryApprovalLog::create([
                            'approvable_type' => SalaryDeduction::class,
                            'approvable_id' => $deduction->id,
                            'user_id' => Auth::id(),
                            'action' => 'approved',
                            'comments' => 'Bulk approval'
                        ]);
                        
                        $approvedCount++;
                    } else {
                        $failedIds[] = $id;
                    }
                }
                
                DB::commit();
                
                $message = "Successfully approved {$approvedCount} deduction(s).";
                if (!empty($failedIds)) {
                    $message .= " Failed to approve " . count($failedIds) . " deduction(s).";
                }
                
                Log::info('Bulk approval completed', [
                    'approved_count' => $approvedCount,
                    'failed_ids' => $failedIds,
                    'approved_by' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'approved_count' => $approvedCount,
                    'failed_ids' => $failedIds
                ]);
                
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (Exception $e) {
            Log::error('Error in bulk approval: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk approval: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export deductions to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = SalaryDeduction::with('employee');
            
            // Apply filters similar to index
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            
            if ($request->filled('from_date')) {
                $query->whereDate('deduction_date', '>=', $request->from_date);
            }
            
            if ($request->filled('to_date')) {
                $query->whereDate('deduction_date', '<=', $request->to_date);
            }
            
            $deductions = $query->orderBy('created_at', 'desc')->get();
            
            if ($deductions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No deductions found to export.'
                ], 404);
            }
            
            $filename = 'deductions_export_' . date('Y-m-d_His') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($deductions) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
                
                // Headers
                fputcsv($file, [
                    'ID', 'Employee Name', 'Reason', 'Amount (KES)', 'Type',
                    'Deduction Date', 'Status', 'Installments', 'Installment Amount',
                    'Requires Dismissal', 'Created At', 'Description'
                ]);
                
                // Data rows
                foreach ($deductions as $deduction) {
                    fputcsv($file, [
                        $deduction->id,
                        $deduction->employee->name ?? 'N/A',
                        $deduction->reason,
                        number_format($deduction->amount, 2),
                        ucfirst($deduction->type),
                        $deduction->deduction_date,
                        ucfirst($deduction->status),
                        $deduction->number_of_installments ?: 'Single',
                        $deduction->installment_amount ? number_format($deduction->installment_amount, 2) : 'N/A',
                        $deduction->requires_dismissal ? 'Yes' : 'No',
                        $deduction->created_at,
                        $deduction->description ?? ''
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (Exception $e) {
            Log::error('Error exporting deductions: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to export deductions: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Helper method to get readable status message
     */
    private function getStatusMessage($status)
    {
        $statusMessages = [
            'pending' => 'Pending',
            'pending_dismissal' => 'Pending Dismissal Review',
            'applied' => 'Already Applied',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
            'dismissal_processed' => 'Dismissal Processed'
        ];
        
        return $statusMessages[$status] ?? ucfirst($status);
    }
    
    /**
     * Get deduction summary statistics
     */
    public function getSummary()
    {
        try {
            $summary = [
                'total_deductions' => SalaryDeduction::sum('amount') ?? 0,
                'pending_total' => SalaryDeduction::where('status', 'pending')->sum('amount') ?? 0,
                'applied_total' => SalaryDeduction::where('status', 'applied')->sum('amount') ?? 0,
                'completed_total' => SalaryDeduction::where('status', 'completed')->sum('amount') ?? 0,
                'cancelled_total' => SalaryDeduction::where('status', 'cancelled')->sum('amount') ?? 0,
                'pending_dismissal_count' => SalaryDeduction::where('status', 'pending_dismissal')->count() ?? 0,
                'active_installments' => SalaryDeduction::where('number_of_installments', '>', 0)
                    ->whereIn('status', ['applied', 'pending'])
                    ->count() ?? 0,
                'by_type' => SalaryDeduction::select('type', DB::raw('SUM(amount) as total'))
                    ->groupBy('type')
                    ->get()
                    ->map(function($item) {
                        return [
                            'type' => ucfirst($item->type),
                            'total' => number_format($item->total, 2)
                        ];
                    })
            ];
            
            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
            
        } catch (Exception $e) {
            Log::error('Error fetching deduction summary: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch summary statistics.'
            ], 500);
        }
    }
}