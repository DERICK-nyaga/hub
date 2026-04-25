<?php

namespace App\Controllers;

use App\Models\OrderNumber;
use App\Models\Station;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class OrderNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = OrderNumber::with(['station', 'employee']);

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('station', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('employee', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('order_status', $request->status);
        }

        // Filter by station
        if ($request->has('station_id') && $request->station_id != '') {
            $query->where('station_id', $request->station_id);
        }

        $orders = $query->latest()->paginate(20);
        $stations = Station::all();
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];

        return view('order-numbers.index', compact('orders', 'stations', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::all();
        $stations = Station::all();

        // Use try-catch for the scope, fallback to regular query if scope doesn't exist
        try {
            $employees = Employee::active()->get();
        } catch (\Exception $e) {
            // Fallback: get active employees using where clause
            $employees = Employee::where('status', 'active')->get();
        }

        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];

        return view('order-numbers.create', compact('stations', 'employees', 'statuses'));
    }

        public function store(Request $request)
            {
                Log::info('Order Store Request Data:', $request->all());

                $validated = $request->validate([
                    'order_number' => 'required|string|unique:order_numbers,order_number',
                    'station_id' => 'required|exists:stations,station_id',
                    'employee_id' => 'nullable|exists:employees,employee_id',
                    'order_date' => 'required|date',
                    'order_status' => 'required|in:pending,in_progress,completed,cancelled',
                    'total_amount' => 'nullable|numeric|min:0',
                    'description' => 'nullable|string|max:500',
                ]);

                Log::info('Validated Data:', $validated);

                try {
                    $order = OrderNumber::create($validated);
                    Log::info('Order Created Successfully:', $order->toArray());

                    return redirect()->route('order-numbers.index')
                        ->with('success', 'Order created successfully.');

                } catch (\Exception $e) {
                    Log::error('Error creating order:', ['error' => $e->getMessage()]);
                    return redirect()->back()
                        ->with('error', 'Error creating order: ' . $e->getMessage())
                        ->withInput();
                }
            }

    /**
     * Display the specified resource.
     */
    public function show(OrderNumber $orderNumber)
    {
        $orderNumber->load(['station', 'employee', 'deductions.deductionType']);

        return view('order-numbers.show', compact('orderNumber'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrderNumber $orderNumber)
    {
        $stations = Station::all();

        // Use try-catch for the scope, fallback to regular query if scope doesn't exist
        try {
            $employees = Employee::active()->get();
        } catch (\Exception $e) {
            // Fallback: get active employees using where clause
            $employees = Employee::where('status', 'active')->get();
        }

        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];

        return view('order-numbers.edit', compact('orderNumber', 'stations', 'employees', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderNumber $orderNumber)
    {
        $validated = $request->validate([
            'order_number' => [
                'required',
                'string',
                Rule::unique('order_numbers')->ignore($orderNumber->id)
            ],
            'station_id' => 'required|exists:stations,id',
            'employee_id' => 'nullable|exists:employees,id',
            'order_date' => 'required|date',
            'order_status' => 'required|in:pending,in_progress,completed,cancelled',
            'total_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $orderNumber->update($validated);

            return redirect()->route('order-numbers.index')
                ->with('success', 'Order updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating order: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderNumber $orderNumber)
    {
        try {
            // Check if order has deductions before deleting
            if ($orderNumber->deductions()->exists()) {
                return redirect()->back()
                    ->with('error', 'Cannot delete order because it has existing deductions. Please delete the deductions first.');
            }

            $orderNumber->delete();

            return redirect()->route('order-numbers.index')
                ->with('success', 'Order deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting order: ' . $e->getMessage());
        }
    }

    /**
     * Get orders available for deduction selection (API endpoint)
     */
    public function availableForDeduction()
    {
        $orders = OrderNumber::with(['station', 'employee'])
            ->where('order_status', 'completed')
            ->whereDoesntHave('deductions', function ($query) {
                $query->where('status', 'approved');
            })
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'station_name' => $order->station->name,
                    'employee_name' => $order->employee_name,
                    'order_date' => $order->formatted_order_date,
                    'total_amount' => $order->total_amount,
                ];
            });

        return response()->json($orders);
    }

    /**
     * Update order status via AJAX
     */
    public function updateStatus(Request $request, OrderNumber $orderNumber)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled'
        ]);

        try {
            $orderNumber->update(['order_status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'new_status' => $validated['status']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating order status: ' . $e->getMessage()
            ], 500);
        }
    }
}
