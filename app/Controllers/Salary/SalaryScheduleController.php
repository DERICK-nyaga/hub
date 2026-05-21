<?php

namespace App\Controllers\Salary;

use App\Controllers\Controller;
use App\Models\SalaryEmployee;
use App\Models\SalaryPaymentSchedule;
use App\Models\SalaryApprovalLog;
use App\Traits\ApprovalPeriodTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalaryScheduleController extends Controller
{
    use ApprovalPeriodTrait;
    
    public function index(Request $request)
    {
        $query = SalaryPaymentSchedule::with('employee', 'approver');
        
        // Apply filters
        if ($request->filled('search')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
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
            $query->whereDate('scheduled_date', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('scheduled_date', '<=', $request->to_date);
        }
        
        $schedules = $query->orderBy('scheduled_date', 'asc')->paginate(15);
        
        // Statistics
        $totalSchedules = SalaryPaymentSchedule::count();
        $pendingApprovals = SalaryPaymentSchedule::where('status', 'pending')->count();
        $totalScheduledAmount = SalaryPaymentSchedule::whereIn('status', ['approved', 'pending'])->sum('amount');
        $upcomingCount = SalaryPaymentSchedule::where('status', 'approved')
            ->whereBetween('scheduled_date', [now(), now()->addDays(7)])
            ->count();
        
        $employees = SalaryEmployee::where('status', 'active')->get();
        
        // Get approval info
        $approvalInfo = $this->getApprovalInfo();
        
        return view('salary.schedules.index', compact(
            'schedules', 'totalSchedules', 'pendingApprovals', 
            'totalScheduledAmount', 'upcomingCount', 'employees', 'approvalInfo'
        ));
    }

    /**
     * Show the form for creating a new payment schedule.
     */
    public function create()
    {
        $employees = SalaryEmployee::where('status', 'active')->get();
        $approvalInfo = $this->getApprovalInfo();
        
        return view('salary.schedules.create', compact('employees', 'approvalInfo'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:salary_employees,id',
                'scheduled_date' => 'required|date|after_or_equal:today',
                'amount' => 'required|numeric|min:0.01',
                'type' => 'required|in:regular,advance,adjustment,bonus',
                'notes' => 'nullable|string|max:1000',
            ]);
            
            DB::beginTransaction();
            
            // Check if employee exists and is active
            $employee = SalaryEmployee::find($validated['employee_id']);
            if (!$employee || $employee->status !== 'active') {
                throw new \Exception('Employee not found or is not active.');
            }
            
            // Check if this would create duplicate payment for the month
            $existingSchedule = SalaryPaymentSchedule::where('employee_id', $validated['employee_id'])
                ->whereYear('scheduled_date', date('Y', strtotime($validated['scheduled_date'])))
                ->whereMonth('scheduled_date', date('m', strtotime($validated['scheduled_date'])))
                ->whereIn('status', ['pending', 'approved'])
                ->first();
            
            if ($existingSchedule) {
                throw new \Exception('Employee already has a schedule for ' . date('F Y', strtotime($validated['scheduled_date'])) . '. Only one schedule per month is allowed.');
            }
            
            // Create schedule - only provide the fields that are needed
            $schedule = new SalaryPaymentSchedule();
            $schedule->employee_id = $validated['employee_id'];
            $schedule->scheduled_date = $validated['scheduled_date'];
            $schedule->amount = $validated['amount'];
            $schedule->type = $validated['type'];
            $schedule->status = 'pending';
            $schedule->notes = $validated['notes'] ?? null;
            
            // Leave deduction-related fields as null for standalone schedules
            $schedule->deduction_id = null;
            $schedule->installment_number = null;
            $schedule->total_installments = null;
            $schedule->remaining_balance = 0;
            $schedule->payment_id = null;
            
            $schedule->save();
            
            if (!$schedule->id) {
                throw new \Exception('Failed to create schedule record.');
            }
            
            // Create approval log
            SalaryApprovalLog::create([
                'approvable_type' => SalaryPaymentSchedule::class,
                'approvable_id' => $schedule->id,
                'user_id' => Auth::id(),
                'action' => 'requested',
                'comments' => 'Schedule payment request for KES ' . number_format($validated['amount'], 2),
            ]);
            
            DB::commit();
            
            $message = 'Payment schedule created successfully and is pending approval.';
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'schedule' => $schedule
                ]);
            }
            
            return redirect()->route('salary.schedules.index')
                ->with('success', $message);
                
        } catch (ValidationException $e) {
            DB::rollBack();
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->validator)->withInput();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorMessage = 'Failed to create schedule: ' . $e->getMessage();
            
            \Log::error($errorMessage, [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return back()->with('error', $errorMessage)->withInput();
        }
    }
    
    /**
     * Approve a schedule with role-based restrictions
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
            
            $schedule = SalaryPaymentSchedule::findOrFail($id);
            
            if ($schedule->status !== 'pending') {
                return response()->json([
                    'success' => false, 
                    'message' => 'This schedule cannot be approved. Current status: ' . $schedule->status
                ], 400);
            }
            
            // Determine approval level
            $isDirector = $this->isDirector();
            $isAdmin = $this->isAdmin();
            $approvalLevel = $isDirector ? 'director' : ($isAdmin ? 'admin' : 'unknown');
            
            $schedule->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            
            SalaryApprovalLog::create([
                'approvable_type' => SalaryPaymentSchedule::class,
                'approvable_id' => $schedule->id,
                'user_id' => Auth::id(),
                'action' => 'approved',
                'comments' => $request->input('comments', "Schedule approved by " . ucfirst($approvalLevel)),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Schedule approved successfully',
                'approval_level' => $approvalLevel
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reject a schedule
     */
    public function reject(Request $request, $id)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:500'
            ]);
            
            $schedule = SalaryPaymentSchedule::findOrFail($id);
            
            if (!in_array($schedule->status, ['pending', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This schedule cannot be rejected. Current status: ' . $schedule->status
                ], 400);
            }
            
            $reason = $request->input('reason', 'No reason provided');
            
            $schedule->update([
                'status' => 'cancelled',
                'rejected_by' => Auth::id(),
                'rejected_at' => now(),
                'rejection_reason' => $reason
            ]);
            
            SalaryApprovalLog::create([
                'approvable_type' => SalaryPaymentSchedule::class,
                'approvable_id' => $schedule->id,
                'user_id' => Auth::id(),
                'action' => 'rejected',
                'comments' => "Schedule rejected. Reason: {$reason}",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Schedule rejected successfully'
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $schedule = SalaryPaymentSchedule::with(['employee', 'approver', 'rejector', 'deduction'])->findOrFail($id);
            
            $scheduleData = $schedule->toArray();
            $scheduleData['formatted_amount'] = number_format($schedule->amount, 2);
            $scheduleData['is_installment'] = $schedule->isInstallment();
            
            return response()->json([
                'success' => true,
                'data' => $scheduleData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found'
            ], 404);
        }
    }

    public function edit($id)
    {
        try {
            $schedule = SalaryPaymentSchedule::findOrFail($id);
            
            if ($schedule->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending schedules can be edited.'
                ], 422);
            }
            
            return response()->json([
                'success' => true,
                'data' => $schedule
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $schedule = SalaryPaymentSchedule::findOrFail($id);
            
            if ($schedule->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending schedules can be updated.'
                ], 422);
            }
            
            $validated = $request->validate([
                'employee_id' => 'required|exists:salary_employees,id',
                'scheduled_date' => 'required|date',
                'amount' => 'required|numeric|min:0',
                'type' => 'required|in:regular,advance,adjustment,bonus',
                'notes' => 'nullable|string',
            ]);
            
            $schedule->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'data' => $schedule
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $schedule = SalaryPaymentSchedule::findOrFail($id);
            
            // Don't allow deletion of already processed schedules
            if (in_array($schedule->status, ['paid', 'deducted'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete a schedule that has already been processed.'
                ], 422);
            }
            
            $schedule->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk approve schedules
     */
    public function bulkApprove(Request $request)
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
            
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:salary_payment_schedules,id'
            ]);
            
            $ids = $request->ids;
            $approvedCount = SalaryPaymentSchedule::whereIn('id', $ids)
                ->where('status', 'pending')
                ->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully approved {$approvedCount} schedule(s).",
                'count' => $approvedCount
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete schedules
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:salary_payment_schedules,id'
            ]);
            
            $ids = $request->ids;
            
            // Don't delete processed schedules
            $deletedCount = SalaryPaymentSchedule::whereIn('id', $ids)
                ->whereNotIn('status', ['paid', 'deducted'])
                ->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} schedule(s).",
                'count' => $deletedCount
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export schedules to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = SalaryPaymentSchedule::with('employee');
            
            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            
            if ($request->filled('from_date')) {
                $query->whereDate('scheduled_date', '>=', $request->from_date);
            }
            
            if ($request->filled('to_date')) {
                $query->whereDate('scheduled_date', '<=', $request->to_date);
            }
            
            $schedules = $query->orderBy('scheduled_date', 'desc')->get();
            
            if ($schedules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules found to export.'
                ], 404);
            }
            
            $filename = 'schedules_export_' . date('Y-m-d_His') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($schedules) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
                
                // Headers
                fputcsv($file, [
                    'ID', 'Employee Name', 'Amount (KES)', 'Type', 'Scheduled Date',
                    'Status', 'Is Installment', 'Approved By', 'Approved At', 'Notes'
                ]);
                
                // Data rows
                foreach ($schedules as $schedule) {
                    fputcsv($file, [
                        $schedule->id,
                        $schedule->employee->name ?? 'N/A',
                        number_format($schedule->amount, 2),
                        ucfirst($schedule->type),
                        $schedule->scheduled_date,
                        ucfirst($schedule->status),
                        $schedule->isInstallment() ? 'Yes' : 'No',
                        $schedule->approver->name ?? 'N/A',
                        $schedule->approved_at ?? 'N/A',
                        $schedule->notes ?? ''
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Error exporting schedules: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to export schedules: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processNow(SalaryPaymentSchedule $schedule)
    {
        try {
            if ($schedule->status !== 'approved') {
                return back()->with('error', 'Schedule must be approved first.');
            }
            
            // Check if employee already has payment for this month
            $existingPayment = \App\Models\SalaryPayment::where('employee_id', $schedule->employee_id)
                ->whereYear('payment_date', now()->year)
                ->whereMonth('payment_date', now()->month)
                ->whereIn('status', ['approved', 'processed'])
                ->first();
            
            if ($existingPayment) {
                return back()->with('error', 'Employee already has a payment for this month.');
            }
            
            // Create payment from schedule
            $paymentData = [
                'employee_id' => $schedule->employee_id,
                'type' => $schedule->type,
                'amount' => $schedule->amount,
                'payment_method' => 'bank_transfer',
                'payment_date' => now(),
                'notes' => 'Processed from schedule: ' . ($schedule->notes ?? ''),
            ];
            
            $paymentController = new SalaryPaymentController();
            $request = new Request($paymentData);
            
            return $paymentController->store($request);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process schedule: ' . $e->getMessage());
        }
    }
}