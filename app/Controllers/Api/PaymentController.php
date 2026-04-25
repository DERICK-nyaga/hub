<?php

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Http\Resources\PaymentCollection;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /**
     * Display a listing of upcoming payments.
     *
     * @OA\Get(
     *     path="/api/payments/upcoming",
     *     tags={"Payments"},
     *     summary="Get list of upcoming payments",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="station_id",
     *         in="query",
     *         description="Filter by station ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="vendor_id",
     *         in="query",
     *         description="Filter by vendor ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PaymentResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function upcoming(Request $request)
    {
        $query = Payment::with(['station', 'vendor'])
            ->upcoming()
            ->orderBy('due_date');

        // Apply filters
        if ($request->has('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $perPage = $request->get('per_page', 20);
        $payments = $query->paginate($perPage);

        return new PaymentCollection($payments);
    }

    /**
     * Display a listing of all payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['station', 'vendor'])
            ->orderBy('due_date', 'desc');

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('due_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $perPage = $request->get('per_page', 20);
        $payments = $query->paginate($perPage);

        return new PaymentCollection($payments);
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'station_id' => 'required|exists:stations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date|after_or_equal:today',
            'status' => ['required', Rule::in(['pending', 'paid', 'cancelled'])],
            'type' => ['required', Rule::in(['bill', 'payment', 'subscription', 'other'])],
            'description' => 'nullable|string',
            'reference_number' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $payment = Payment::create($validated);

        return response()->json([
            'message' => 'Payment created successfully',
            'data' => new PaymentResource($payment->load(['station', 'vendor']))
        ], 201);
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load(['station', 'vendor']);
        return new PaymentResource($payment);
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'station_id' => 'sometimes|required|exists:stations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'due_date' => 'sometimes|required|date',
            'status' => ['sometimes', 'required', Rule::in(['pending', 'paid', 'cancelled'])],
            'type' => ['sometimes', 'required', Rule::in(['bill', 'payment', 'subscription', 'other'])],
            'description' => 'nullable|string',
            'reference_number' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'paid_at' => 'nullable|date',
        ]);

        $payment->update($validated);

        return response()->json([
            'message' => 'Payment updated successfully',
            'data' => new PaymentResource($payment->load(['station', 'vendor']))
        ]);
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully'
        ]);
    }

    /**
     * Mark payment as paid.
     */
    public function markAsPaid(Request $request, Payment $payment)
    {
        $request->validate([
            'paid_at' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $payment->update([
            'status' => 'paid',
            'paid_at' => $request->paid_at ?? now(),
            'payment_method' => $request->payment_method ?? $payment->payment_method,
            'reference_number' => $request->reference_number ?? $payment->reference_number,
        ]);

        return response()->json([
            'message' => 'Payment marked as paid',
            'data' => new PaymentResource($payment->load(['station', 'vendor']))
        ]);
    }

    /**
     * Mark payment as cancelled.
     */
    public function markAsCancelled(Payment $payment)
    {
        $payment->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Payment marked as cancelled',
            'data' => new PaymentResource($payment->load(['station', 'vendor']))
        ]);
    }

    /**
     * Get payment summary statistics.
     */
    public function summary(Request $request)
    {
        $query = Payment::query();

        if ($request->has('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('due_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $summary = $query->select(
            DB::raw('COUNT(*) as total_payments'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count'),
            DB::raw('SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_count'),
            DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_count'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_amount'),
            DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as paid_amount'),
            DB::raw('SUM(CASE WHEN due_date < CURDATE() AND status = "pending" THEN 1 ELSE 0 END) as overdue_count'),
            DB::raw('SUM(CASE WHEN due_date < CURDATE() AND status = "pending" THEN amount ELSE 0 END) as overdue_amount')
        )->first();

        return response()->json([
            'data' => $summary
        ]);
    }
}
