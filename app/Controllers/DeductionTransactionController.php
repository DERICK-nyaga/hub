<?php

namespace App\Controllers;

use App\Models\DeductionTransaction;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeductionTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = DeductionTransaction::with('employee');

        if ($request->filled('employee_first_name')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('first_name', $request->employee_first_name);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(20);

        $initialDeductions = $query->clone()->where('type', 'initial')->sum('amount');
        $additionalDeductions = $query->clone()->where('type', 'additional')->sum('amount');
        $refund = $query->clone()->where('type', 'refund')->sum('amount');

        $totalDeductions = $initialDeductions + $additionalDeductions - $refund;

        $salaryAdvances = $query->clone()->where('reason', 'salary advance')->sum('amount');
        $salaryAdvanceCount = $query->clone()->where('reason', 'salary advance')->count();

        $regularDeductionCount = $query->clone()
            ->where('reason', '!=', 'salary advance')
            ->where('type', '!=', 'payment')
            ->count();

        $paymentCount = $query->clone()->where('type', 'payment')->count();

        $totalPayments = $query->clone()->where('type', 'payment')->sum('amount');

        // Total Employee Owes = Regular deductions + salary advances
        $totalEmployeeOwes = $totalDeductions + $salaryAdvances;

        // Outstanding Balance = What employee owes minus payments made
        $outstandingBalance = $totalEmployeeOwes - $totalPayments;

        $employees = Employee::orderBy('first_name')->get();

        return view('deductions.index', compact(
            'transactions',
            'employees',
            'initialDeductions',
            'additionalDeductions',
            'refund',
            'totalDeductions',
            'salaryAdvances',
            'salaryAdvanceCount',
            'regularDeductionCount',
            'paymentCount',
            'totalEmployeeOwes',
            'totalPayments',
            'outstandingBalance'
        ));
    }

    public function create()
    {
        $employees = Employee::all();
        $types = [
            'initial' => 'Initial Deduction',
            'additional' => 'Additional Deduction',
            'refund' => 'Refund to Employee',
            'payment' => 'Payment Made',
        ];

        return view('deductions.create', compact('employees', 'types'));
    }

    public function store(Request $request)
    {
        Log::info('Store method called', ['request_data' => $request->all()]);

        $validated = $request->validate([
            'employee_id' => 'required|string',
            'station_id' => 'required|exists:stations,station_id',
            'type' => 'required|string|in:initial,additional,refund,payment',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'order_number' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            Log::info('Validation passed', ['validated_data' => $validated]);

            $employeeData = explode('|', $validated['employee_id']);
            if (count($employeeData) < 3) {
                throw new \Exception('Invalid employee ID format. Expected: ID|first_name|last_name');
            }

            $employeeId = $employeeData[0];
            $firstName = trim($employeeData[1]);
            $lastName = trim($employeeData[2]);
            $employeeName = $firstName . ' ' . $lastName;

            $latestTransaction = $this->getLatestTransaction($employeeId);
            $previousBalance = $latestTransaction ? $latestTransaction->new_balance : 0;

            Log::info('Balance calculated', ['previous_balance' => $previousBalance]);

            if (in_array($validated['type'], ['refund', 'payment'])) {
                $maxAllowed = abs($previousBalance);
                if ($validated['amount'] > $maxAllowed) {
                    Log::warning('Refund amount exceeded balance', ['amount' => $validated['amount'], 'max_allowed' => $maxAllowed]);
                    return back()->withInput()
                        ->with('error', "Refund amount cannot exceed outstanding balance of " . number_format($maxAllowed, 2));
                }
            }

            $newBalance = $this->calculateNewBalance(
                $previousBalance,
                $validated['amount'],
                $validated['type']
            );

            Log::info('New balance calculated', ['new_balance' => $newBalance]);

            $deductionData = [
                'employee_id' => $employeeId,
                'employee_name' => $employeeName,
                'station_id' => $validated['station_id'],
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'order_number' => $validated['order_number'],
                'transaction_date' => $validated['transaction_date'],
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
            ];

            Log::info('Attempting to create transaction', ['data' => $deductionData]);

            $deduction = DeductionTransaction::create($deductionData);

            Log::info('Transaction created successfully', ['transaction_id' => $deduction->id]);

            DB::commit();

            return redirect()->route('deductions.index')
                ->with('success', 'Deduction transaction recorded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing deduction transaction', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return back()->withInput()
                ->with('error', 'Error recording transaction: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            Log::info('Attempting to load edit form', ['deduction_id' => $id]);

            $deduction = DeductionTransaction::find($id);

            if (!$deduction) {
                Log::warning('Deduction transaction not found in database', [
                    'requested_id' => $id,
                    'available_ids' => DeductionTransaction::pluck('id')->toArray()
                ]);

                return redirect()->route('deductions.index')
                    ->with('error', "Transaction #{$id} not found. Please check if it exists.");
            }

            $employees = Employee::all();
            $types = [
                'initial' => 'Initial Deduction',
                'additional' => 'Additional Deduction',
                'refund' => 'Refund to Employee',
                'payment' => 'Payment Made',
            ];

            Log::info('Edit form loaded successfully', ['deduction_id' => $id]);

            return view('deductions.create', compact('deduction', 'employees', 'types'));

        } catch (\Exception $e) {
            Log::error('Error loading edit form', [
                'deduction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('deductions.index')
                ->with('error', 'Error loading edit form. Please try again.');
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Update method called', ['deduction_id' => $id, 'request_data' => $request->all()]);

        $validated = $request->validate([
            'type' => 'required|string|in:initial,additional,refund,payment',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'order_number' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'station_id' => 'required|exists:stations,station_id',
        ]);

        try {
            DB::beginTransaction();

            $deduction = DeductionTransaction::findOrFail($id);
            $originalAmount = $deduction->amount;
            $originalType = $deduction->type;
            $originalDate = $deduction->transaction_date;

            Log::info('Updating transaction', [
                'original_amount' => $originalAmount,
                'original_type' => $originalType,
                'original_date' => $originalDate,
                'new_data' => $validated
            ]);

            $needsRecalculation = $originalAmount != $validated['amount'] ||
                                $originalType != $validated['type'] ||
                                $originalDate != $validated['transaction_date'];

            if ($needsRecalculation) {
                Log::info('Recalculation needed, updating subsequent balances');
                $this->recalculateBalances($deduction, $validated);
            } else {
                $deduction->update($validated);
                Log::info('Transaction updated without balance recalculation');
            }

            DB::commit();

            return redirect()->route('deductions.index')
                ->with('success', 'Deduction transaction updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating deduction transaction', [
                'deduction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()
                ->with('error', 'Error updating transaction: ' . $e->getMessage());
        }
    }

    private function recalculateBalances($updatedTransaction, $newData)
    {
        try {
            Log::info('Starting balance recalculation', [
                'transaction_id' => $updatedTransaction->id,
                'employee_id' => $updatedTransaction->employee_id
            ]);

            $previousTransaction = DeductionTransaction::where('employee_id', $updatedTransaction->employee_id)
                ->where(function($query) use ($updatedTransaction, $newData) {
                    $query->where('transaction_date', '<', $newData['transaction_date'])
                          ->orWhere(function($q) use ($updatedTransaction, $newData) {
                              $q->where('transaction_date', '=', $newData['transaction_date'])
                                ->where('created_at', '<', $updatedTransaction->created_at);
                          });
                })
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            $previousBalance = $previousTransaction ? $previousTransaction->new_balance : 0;

            Log::info('Previous balance calculated', ['previous_balance' => $previousBalance]);

            $updatedTransaction->update([
                'type' => $newData['type'],
                'amount' => $newData['amount'],
                'reason' => $newData['reason'],
                'notes' => $newData['notes'],
                'order_number' => $newData['order_number'],
                'transaction_date' => $newData['transaction_date'],
                'station_id' => $newData['station_id'],
                'previous_balance' => $previousBalance,
                'new_balance' => $this->calculateNewBalance(
                    $previousBalance,
                    $newData['amount'],
                    $newData['type']
                )
            ]);

            Log::info('Current transaction updated', [
                'new_balance' => $updatedTransaction->new_balance
            ]);

            $subsequentTransactions = DeductionTransaction::where('employee_id', $updatedTransaction->employee_id)
                ->where(function($query) use ($updatedTransaction, $newData) {
                    $query->where('transaction_date', '>', $newData['transaction_date'])
                          ->orWhere(function($q) use ($updatedTransaction, $newData) {
                              $q->where('transaction_date', '=', $newData['transaction_date'])
                                ->where('created_at', '>', $updatedTransaction->created_at);
                          });
                })
                ->orderBy('transaction_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            $currentBalance = $updatedTransaction->new_balance;

            foreach ($subsequentTransactions as $transaction) {
                $transaction->update([
                    'previous_balance' => $currentBalance,
                    'new_balance' => $this->calculateNewBalance(
                        $currentBalance,
                        $transaction->amount,
                        $transaction->type
                    )
                ]);

                $currentBalance = $transaction->new_balance;

                Log::info('Updated subsequent transaction', [
                    'transaction_id' => $transaction->id,
                    'new_balance' => $transaction->new_balance
                ]);
            }

            Log::info('Balance recalculation completed successfully');

        } catch (\Exception $e) {
            Log::error('Error in recalculateBalances', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function employeeTransactions($employeeId)
    {
        try {
            $employee = Employee::findOrFail($employeeId);

            $transactions = DeductionTransaction::where('employee_id', $employeeId)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $totalDeductions = DeductionTransaction::where('employee_id', $employeeId)
                ->whereIn('type', ['initial', 'additional'])
                ->sum('amount');

            $totalRefunds = DeductionTransaction::where('employee_id', $employeeId)
                ->where('type', 'refund')
                ->sum('amount');

            $totalPayments = DeductionTransaction::where('employee_id', $employeeId)
                ->where('type', 'payment')
                ->sum('amount');

            $currentBalance = $this->getLatestTransaction($employeeId)?->new_balance ?? 0;

            $netDeductions = $totalDeductions - $totalRefunds;

            return view('deductions.employee-transactions', compact(
                'employee',
                'transactions',
                'currentBalance',
                'totalDeductions',
                'totalRefunds',
                'totalPayments',
                'netDeductions'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading employee transactions', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('deductions.index')
                ->with('error', 'Error loading employee transactions: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $deduction = DeductionTransaction::findOrFail($id);
            $employeeId = $deduction->employee_id;
            $transactionDate = $deduction->transaction_date;

            Log::info('Deleting transaction', [
                'transaction_id' => $id,
                'employee_id' => $employeeId,
                'transaction_date' => $transactionDate
            ]);

            $deduction->delete();

            Log::info('Transaction deleted, recalculating subsequent balances');

            $this->recalculateBalancesAfterDeletion($employeeId, $transactionDate);

            DB::commit();

            return redirect()->route('deductions.index')
                ->with('success', 'Transaction deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting deduction transaction', [
                'deduction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error deleting transaction: ' . $e->getMessage());
        }
    }

    private function recalculateBalancesAfterDeletion($employeeId, $deletedTransactionDate)
    {
        try {
            Log::info('Recalculating balances after deletion', [
                'employee_id' => $employeeId,
                'deleted_date' => $deletedTransactionDate
            ]);

            $previousTransaction = DeductionTransaction::where('employee_id', $employeeId)
                ->where('transaction_date', '<=', $deletedTransactionDate)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            $currentBalance = $previousTransaction ? $previousTransaction->new_balance : 0;

            Log::info('Starting balance after deletion', ['balance' => $currentBalance]);

            $subsequentTransactions = DeductionTransaction::where('employee_id', $employeeId)
                ->where('transaction_date', '>=', $deletedTransactionDate)
                ->orderBy('transaction_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($subsequentTransactions as $transaction) {
                $transaction->update([
                    'previous_balance' => $currentBalance,
                    'new_balance' => $this->calculateNewBalance(
                        $currentBalance,
                        $transaction->amount,
                        $transaction->type
                    )
                ]);

                $currentBalance = $transaction->new_balance;

                Log::info('Updated transaction after deletion', [
                    'transaction_id' => $transaction->id,
                    'new_balance' => $transaction->new_balance
                ]);
            }

            Log::info('Balance recalculation after deletion completed');

        } catch (\Exception $e) {
            Log::error('Error in recalculateBalancesAfterDeletion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    private function getLatestTransaction($employeeId)
    {
        return DeductionTransaction::where('employee_id', $employeeId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    private function calculateNewBalance($previousBalance, $amount, $type)
    {
        switch ($type) {
            case 'initial':
            case 'additional':
                return $previousBalance - $amount;

            case 'refund':

                return $previousBalance + $amount;

            case 'payment':

                return $previousBalance + $amount;

            default:
                return $previousBalance;
        }
    }

    public function show(DeductionTransaction $deduction)
    {
        return view('deductions.show', compact('deduction'));
    }

    public function employeeHistory($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $transactions = DeductionTransaction::where('employee_id', $employeeId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $currentBalance = $this->getLatestTransaction($employeeId)?->new_balance ?? 0;

        return view('deductions.history', compact('employee', 'transactions', 'currentBalance'));
    }

    public function getCurrentBalance($employeeId)
    {
        try {
            Log::info('getCurrentBalance called', ['employeeId' => $employeeId]);

            $employee = Employee::find($employeeId);
            if (!$employee) {
                Log::warning('Employee not found', ['employeeId' => $employeeId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $latestTransaction = $this->getLatestTransaction($employeeId);
            $currentBalance = $latestTransaction ? $latestTransaction->new_balance : 0;

            Log::info('Balance fetched successfully', [
                'employeeId' => $employeeId,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'current_balance' => $currentBalance
            ]);

            return response()->json([
                'success' => true,
                'current_balance' => $currentBalance,
                'formatted_balance' => number_format($currentBalance, 2),
                'employee_name' => $employee->first_name . ' ' . $employee->last_name
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getCurrentBalance', [
                'employeeId' => $employeeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching balance: ' . $e->getMessage()
            ], 500);
        }
    }
}
