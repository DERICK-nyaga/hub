<?php

namespace App\Controllers\Salary;

use App\Controllers\Controller;
use App\Models\SalaryEmployee;
use App\Models\SalaryPaymentSchedule;
use App\Models\SalaryApprovalLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalaryScheduleController extends Controller
{
   
    public function index(Request $request)
    {
        $query = SalaryPaymentSchedule::with('employee', 'approver');
        
        // Apply filters
        if ($request->search) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        
        if ($request->from_date) {
            $query->whereDate('scheduled_date', '>=', $request->from_date);
        }
        
        if ($request->to_date) {
            $query->whereDate('scheduled_date', '<=', $request->to_date);
        }
        
        $schedules = $query->orderBy('scheduled_date', 'asc')->paginate(15);
        
        // Statistics
        $totalSchedules = SalaryPaymentSchedule::count();
        $pendingApprovals = SalaryPaymentSchedule::where('status', 'pending')->count();
        $totalScheduledAmount = SalaryPaymentSchedule::where('status', 'approved')
            ->orWhere('status', 'pending')
            ->sum('amount');
        $upcomingCount = SalaryPaymentSchedule::where('status', 'approved')
            ->whereBetween('scheduled_date', [now(), now()->addDays(7)])
            ->count();
        
        $employees = SalaryEmployee::where('status', 'active')->get();
        
        return view('salary.schedules.index', compact(
            'schedules', 'totalSchedules', 'pendingApprovals', 
            'totalScheduledAmount', 'upcomingCount', 'employees'
        ));
    }

    /**
     * Show the form for creating a new payment schedule.
     */
    public function create()
    {
        $employees = SalaryEmployee::where('status', 'active')->get();
        return view('salary.schedules.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:salary_employees,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:regular,advance,adjustment',
            'notes' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            $schedule = SalaryPaymentSchedule::create([
                'employee_id' => $validated['employee_id'],
                'scheduled_date' => $validated['scheduled_date'],
                'amount' => $validated['amount'],
                'type' => $validated['type'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);
            
            SalaryApprovalLog::create([
                'approvable_type' => SalaryPaymentSchedule::class,
                'approvable_id' => $schedule->id,
                'user_id' => Auth::id(),
                'action' => 'requested',
                'comments' => 'Schedule payment request',
            ]);
            
            DB::commit();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function show($id)
    {
        $schedule = SalaryPaymentSchedule::with('employee', 'approver')->findOrFail($id);
        return response()->json($schedule);
    }

    public function edit($id)
    {
        $schedule = SalaryPaymentSchedule::findOrFail($id);
        return response()->json($schedule);
    }

    public function update(Request $request, $id)
    {
        $schedule = SalaryPaymentSchedule::findOrFail($id);
        
        $validated = $request->validate([
            'employee_id' => 'required|exists:salary_employees,id',
            'scheduled_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:regular,advance,adjustment',
            'notes' => 'nullable|string',
        ]);
        
        $schedule->update($validated);
        
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $schedule = SalaryPaymentSchedule::findOrFail($id);
        $schedule->delete();
        
        return response()->json(['success' => true]);
    }

    public function reject($id)
    {
        $schedule = SalaryPaymentSchedule::findOrFail($id);
        $schedule->update(['status' => 'failed']);
        
        return response()->json(['success' => true]);
    }
    
    public function approve(Request $request, SalaryPaymentSchedule $schedule)
    {
        if ($schedule->status !== 'pending') {
            return back()->with('error', 'This schedule cannot be approved.');
        }
        
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
            'comments' => $request->comments ?? 'Schedule approved',
        ]);
        
        return redirect()->route('salary.schedules.index')
            ->with('success', 'Payment schedule approved. Payment will be processed on scheduled date.');
    }
    
    // Bulk approve schedules
    public function bulkApprove(Request $request)
    {
        $ids = $request->ids;
        SalaryPaymentSchedule::whereIn('id', $ids)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);
        
        return response()->json(['success' => true]);
    }

    // Bulk delete schedules
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        SalaryPaymentSchedule::whereIn('id', $ids)->delete();
        
        return response()->json(['success' => true]);
    }

    // Export schedules
    public function export(Request $request)
    {
        $schedules = SalaryPaymentSchedule::with('employee')->get();
        
        $filename = 'schedules_' . date('Y-m-d') . '.csv';
        // CSV export logic here
    }

    public function processNow(SalaryPaymentSchedule $schedule)
    {
        if ($schedule->status !== 'approved') {
            return back()->with('error', 'Schedule must be approved first.');
        }
        
        // Create payment from schedule
        $paymentController = new SalaryPaymentController();
        $request = new Request([
            'employee_id' => $schedule->employee_id,
            'type' => $schedule->type,
            'amount' => $schedule->amount,
            'payment_method' => 'bank_transfer', // Default, can be customized
            'payment_date' => now(),
            'notes' => 'Processed from schedule: ' . ($schedule->notes ?? ''),
        ]);
        
        return $paymentController->store($request);
    }
}