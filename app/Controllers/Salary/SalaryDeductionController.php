<?php

namespace App\Controllers\Salary;

use App\Controllers\Controller;
use App\Models\SalaryEmployee;
use App\Models\SalaryDeduction;
use App\Models\SalaryApprovalLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalaryDeductionController extends Controller
{

    public function index(Request $request)
    {
        $query = SalaryDeduction::with('employee');
        
        // Apply filters
        if ($request->search) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            })->orWhere('reason', 'like', "%{$request->search}%");
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
            $query->whereDate('deduction_date', '>=', $request->from_date);
        }
        
        if ($request->to_date) {
            $query->whereDate('deduction_date', '<=', $request->to_date);
        }
        
        $deductions = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Statistics
        $totalDeductions = SalaryDeduction::where('status', 'applied')->sum('amount');
        $pendingApprovals = SalaryDeduction::where('status', 'pending')->count();
        $monthlyTotal = SalaryDeduction::whereYear('deduction_date', now()->year)
            ->whereMonth('deduction_date', now()->month)
            ->where('status', 'applied')
            ->sum('amount');
        $affectedEmployees = SalaryDeduction::where('status', 'applied')
            ->distinct('employee_id')
            ->count('employee_id');
        
        $employees = SalaryEmployee::where('status', 'active')->get();
        
        return view('salary.deductions.index', compact(
            'deductions', 'totalDeductions', 'pendingApprovals', 
            'monthlyTotal', 'affectedEmployees', 'employees'
        ));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:salary_employees,id',
            'reason' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:penalty,loan,advance_recovery,loss,other',
            'deduction_date' => 'required|date',
            'description' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            $deduction = SalaryDeduction::create([
                'employee_id' => $validated['employee_id'],
                'reason' => $validated['reason'],
                'amount' => $validated['amount'],
                'type' => $validated['type'],
                'deduction_date' => $validated['deduction_date'],
                'description' => $validated['description'] ?? null,
                'status' => 'pending',
            ]);
            
            SalaryApprovalLog::create([
                'approvable_type' => SalaryDeduction::class,
                'approvable_id' => $deduction->id,
                'user_id' => Auth::id(),
                'action' => 'requested',
                'comments' => 'Deduction created for approval',
            ]);
            
            DB::commit();
            
            return redirect()->route('salary.deductions.index')
                ->with('success', 'Deduction created and pending approval.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create deduction: ' . $e->getMessage());
        }
    }
    
    public function approve(Request $request, SalaryDeduction $deduction)
    {
        if ($deduction->status !== 'pending') {
            return back()->with('error', 'This deduction cannot be approved.');
        }
        
        $deduction->update(['status' => 'applied']);
        
        SalaryApprovalLog::create([
            'approvable_type' => SalaryDeduction::class,
            'approvable_id' => $deduction->id,
            'user_id' => Auth::id(),
            'action' => 'approved',
            'comments' => $request->comments ?? 'Deduction approved',
        ]);
        
        return redirect()->route('salary.deductions.index')
            ->with('success', 'Deduction approved and will be applied to next payment.');
    }

    public function show($id)
    {
        $deduction = SalaryDeduction::with('employee', 'payment')->findOrFail($id);
        return response()->json($deduction);
    }

    public function cancel($id)
    {
        $deduction = SalaryDeduction::findOrFail($id);
        $deduction->update(['status' => 'cancelled']);
        
        return response()->json(['success' => true]);
    }
}