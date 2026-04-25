<?php

namespace App\Controllers;

use App\Http\Resources\InternetPaymentCollection;
use App\Http\Resources\InternetPaymentResource;
use App\Models\InternetPayment;
use App\Models\Station;
use App\Models\InternetProvider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InternetPaymentController extends Controller
{

    public function dueSummary(Request $request)
    {
        try {
            $today = Carbon::today();
            $sevenDaysFromNow = $today->copy()->addDays(7);

            // fetching overdue payments
            $overdueQuery = $this->getOverduePaymentsQuery($request, $today);
            $overdue = $overdueQuery->orderBy('due_date')->get();

            // fetching due soon payments
            $dueSoonQuery = $this->getDueSoonPaymentsQuery($request, $today, $sevenDaysFromNow);
            $dueSoon = $dueSoonQuery->orderBy('due_date')->get();

            // Grouping due soon payments
            $dueSoonGrouped = $this->groupDueSoonPayments($dueSoon, $today);

            // Calculating statistics
            $stats = $this->calculateDueSummaryStats($overdue, $dueSoon);

            $responseData = [
                'stats' => $stats,
                'overdue' => InternetPaymentResource::collection($overdue),
                'due_soon_grouped' => $dueSoonGrouped,
            ];

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            $stations = Station::all();
            $providers = InternetProvider::all();
            return view('internet-payments.due-summary', compact('responseData', 'stations', 'providers'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch due summary');
        }
    }

    private function getOverduePaymentsQuery(Request $request, Carbon $today): \Illuminate\Database\Eloquent\Builder
    {
        $query = InternetPayment::with(['station', 'provider'])
            ->where('status', 'pending')
            ->where('due_date', '<', $today->format('Y-m-d'));

        $this->applyFilters($query, $request);

        return $query;
    }

    private function getDueSoonPaymentsQuery(Request $request, Carbon $today, Carbon $sevenDaysFromNow): \Illuminate\Database\Eloquent\Builder
    {
        $query = InternetPayment::with(['station', 'provider'])
            ->where('status', 'pending')
            ->where('due_date', '>=', $today->format('Y-m-d'))
            ->where('due_date', '<=', $sevenDaysFromNow->format('Y-m-d'));

        $this->applyFilters($query, $request);

        return $query;
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->has('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        if ($request->has('provider_id')) {
            $query->where('vendor_id', $request->provider_id);
        }
    }

    private function groupDueSoonPayments(Collection $payments, Carbon $today): array
    {
        $grouped = [];

        foreach ($payments as $payment) {
            $dueDate = Carbon::parse($payment->due_date);
            $groupKey = $this->getDueSoonGroupKey($dueDate, $today);

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [];
            }

            $grouped[$groupKey][] = $payment;
        }

        $result = [];
        foreach ($grouped as $key => $paymentGroup) {
            $result[$key] = InternetPaymentResource::collection(collect($paymentGroup));
        }

        return $result;
    }

    private function getDueSoonGroupKey(Carbon $dueDate, Carbon $today): string
    {
        if ($dueDate->isToday()) {
            return 'Today';
        } elseif ($dueDate->isTomorrow()) {
            return 'Tomorrow';
        } elseif ($dueDate->lte($today->copy()->endOfWeek())) {
            return 'This Week';
        }

        return 'Next Week';
    }

    private function calculateDueSummaryStats(Collection $overdue, Collection $dueSoon): array
    {
        $overdueByProvider = $this->calculateOverdueByProvider($overdue);
        $dueSoonByStation = $this->calculateDueSoonByStation($dueSoon);

        return [
            'overdue_count' => $overdue->count(),
            'overdue_amount' => (float) $overdue->sum('total_due'),
            'due_soon_count' => $dueSoon->count(),
            'due_soon_amount' => (float) $dueSoon->sum('total_due'),
            'total_due' => (float) ($overdue->sum('total_due') + $dueSoon->sum('total_due')),
            'overdue_by_provider' => $overdueByProvider,
            'due_soon_by_station' => $dueSoonByStation,
        ];
    }

    private function calculateOverdueByProvider(Collection $overdue): array
    {
        $grouped = $overdue->groupBy('vendor_id');
        $result = [];

        foreach ($grouped as $providerId => $payments) {
            $provider = InternetProvider::find($providerId);
            $result[] = [
                'provider_id' => $providerId,
                'provider_name' => $provider ? $provider->name : 'Unknown',
                'count' => $payments->count(),
                'total_amount' => (float) $payments->sum('total_due'),
            ];
        }

        return $result;
    }

    private function calculateDueSoonByStation(Collection $dueSoon): array
    {
        $grouped = $dueSoon->groupBy('station_id');
        $result = [];

        foreach ($grouped as $stationId => $payments) {
            $station = Station::find($stationId);
            $result[] = [
                'station_id' => $stationId,
                'station_name' => $station ? $station->name : 'Unknown',
                'count' => $payments->count(),
                'total_amount' => (float) $payments->sum('total_due'),
            ];
        }

        return $result;
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->wantsJson() || $request->ajax() || $request->get('format') === 'json';
    }

    private function handleError(\Exception $e, Request $request, string $message): JsonResponse|\Exception
    {
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => $e->getMessage()
            ], 500);
        }

        throw $e;
    }

    public function overdue(Request $request)
    {
        try {
            $query = InternetPayment::with(['station', 'provider'])
                ->where('status', 'pending')
                ->where('due_date', '<', Carbon::today()->format('Y-m-d'))
                ->orderBy('due_date');

            $this->applyFilters($query, $request);

            $perPage = $request->get('per_page', 20);
            $payments = $query->paginate($perPage);

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'data' => new InternetPaymentCollection($payments)
                ], 200);
            }

            $stations = Station::all();
            $providers = InternetProvider::all();
            return view('internet-payments.overdue', compact('payments', 'stations', 'providers'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch overdue payments');
        }
    }

    /**
     * Get only due soon internet payments (next 7 days)
     */
    public function dueSoon(Request $request)
    {
        try {
            $today = Carbon::today();
            $sevenDaysFromNow = $today->copy()->addDays(7);

            $query = InternetPayment::with(['station', 'provider'])
                ->where('status', 'pending')
                ->where('due_date', '>=', $today->format('Y-m-d'))
                ->where('due_date', '<=', $sevenDaysFromNow->format('Y-m-d'))
                ->orderBy('due_date');

            $this->applyFilters($query, $request);

            $perPage = $request->get('per_page', 20);
            $payments = $query->paginate($perPage);

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'data' => new InternetPaymentCollection($payments)
                ], 200);
            }

            $stations = Station::all();
            $providers = InternetProvider::all();
            return view('internet-payments.due-soon', compact('payments', 'stations', 'providers'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch due soon payments');
        }
    }

    /**
     * Display a listing of all internet payments
     */
    public function index(Request $request)
    {
        try {
            $query = InternetPayment::with(['station', 'provider'])
                ->orderBy('due_date', 'desc');

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('station_id')) {
                $query->where('station_id', $request->station_id);
            }

            if ($request->has('provider_id')) {
                $query->where('vendor_id', $request->provider_id);
            }

            if ($request->has('month')) {
                $query->where('billing_month', 'like', $request->month . '%');
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('account_number', 'like', "%{$search}%")
                      ->orWhere('mpesa_receipt', 'like', "%{$search}%")
                      ->orWhere('transaction_id', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 20);
            $payments = $query->paginate($perPage);

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'data' => new InternetPaymentCollection($payments)
                ], 200);
            }

            $stations = Station::all();
            $providers = InternetProvider::all();
            $statuses = ['pending', 'paid', 'overdue', 'cancelled'];
            return view('internet-payments.index', compact('payments', 'stations', 'providers', 'statuses'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch internet payments');
        }
    }

    /**
     * Store a newly created internet payment
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateStoreRequest($request);
            $payment = InternetPayment::create($validated);
            $payment->load(['station', 'provider']);

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Internet payment created successfully',
                    'data' => new InternetPaymentResource($payment)
                ], 201);
            }

            return redirect()->route('internet-payments.show', $payment->id)
                ->with('success', 'Internet payment created successfully');

        } catch (ValidationException $e) {
            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to create internet payment');
        }
    }

    private function validateStoreRequest(Request $request): array
    {
        return $request->validate([
            'station_id' => 'required|exists:stations,station_id',
            'vendor_id' => 'required|exists:internet_providers,vendor_id',
            'account_number' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'previous_balance' => 'nullable|numeric|min:0',
            'billing_month' => 'required|date',
            'due_date' => 'required|date|after_or_equal:today',
            'payment_date' => 'nullable|date',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'mpesa_receipt' => 'nullable|string|max:100',
            'transaction_id' => 'nullable|string|max:100',
            'invoice_notes' => 'nullable|string',
            'payment_method' => 'nullable|in:M-Pesa,Bank Transfer,Cash,Cheque',
        ]);
    }

    private function validateUpdateRequest(Request $request): array
    {
        return $request->validate([
            'station_id' => 'sometimes|required|exists:stations,station_id',
            'vendor_id' => 'sometimes|required|exists:internet_providers,vendor_id',
            'account_number' => 'sometimes|required|string|max:100',
            'amount' => 'sometimes|required|numeric|min:0',
            'previous_balance' => 'nullable|numeric|min:0',
            'billing_month' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date',
            'payment_date' => 'nullable|date',
            'status' => 'sometimes|required|in:pending,paid,overdue,cancelled',
            'mpesa_receipt' => 'nullable|string|max:100',
            'transaction_id' => 'nullable|string|max:100',
            'invoice_notes' => 'nullable|string',
            'payment_method' => 'nullable|in:M-Pesa,Bank Transfer,Cash,Cheque',
        ]);
    }

    /**
     * Display the specified internet payment
     */
    public function show(Request $request, InternetPayment $internetPayment)
    {
        try {
            $internetPayment->load(['station', 'provider']);

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'data' => new InternetPaymentResource($internetPayment)
                ], 200);
            }

            return view('internet-payments.show', compact('internetPayment'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch internet payment');
        }
    }

    /**
     * Update the specified internet payment
     */
    public function update(Request $request, InternetPayment $internetPayment)
    {
        try {
            $validated = $this->validateUpdateRequest($request);
            $internetPayment->update($validated);
            $internetPayment->load(['station', 'provider']);

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Internet payment updated successfully',
                    'data' => new InternetPaymentResource($internetPayment)
                ], 200);
            }

            return redirect()->route('internet-payments.show', $internetPayment->id)
                ->with('success', 'Internet payment updated successfully');

        } catch (ValidationException $e) {
            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to update internet payment');
        }
    }

    /**
     * Remove the specified internet payment
     */
    public function destroy(Request $request, InternetPayment $internetPayment)
    {
        try {
            $internetPayment->delete();

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Internet payment deleted successfully'
                ], 200);
            }

            return redirect()->route('internet-payments.index')
                ->with('success', 'Internet payment deleted successfully');

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to delete internet payment');
        }
    }

    /**
     * Mark internet payment as paid
     */
    public function markPaid(Request $request, InternetPayment $internetPayment)
    {
        try {
            $validated = $request->validate([
                'payment_date' => 'nullable|date',
                'payment_method' => 'nullable|in:M-Pesa,Bank Transfer,Cash,Cheque',
                'mpesa_receipt' => 'nullable|string|max:100',
                'transaction_id' => 'nullable|string|max:100',
            ]);

            $internetPayment->update([
                'status' => 'paid',
                'payment_date' => $validated['payment_date'] ?? Carbon::today()->format('Y-m-d'),
                'payment_method' => $validated['payment_method'] ?? 'M-Pesa',
                'mpesa_receipt' => $validated['mpesa_receipt'] ?? null,
                'transaction_id' => $validated['transaction_id'] ?? null,
            ]);

            $internetPayment->load(['station', 'provider']);

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Internet payment marked as paid',
                    'data' => new InternetPaymentResource($internetPayment)
                ], 200);
            }

            return redirect()->back()->with('success', 'Internet payment marked as paid');

        } catch (ValidationException $e) {
            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to mark payment as paid');
        }
    }

    /**
     * Send reminder for internet payment
     */
    public function sendReminder(Request $request, InternetPayment $internetPayment)
    {
        try {
            $totalDue = (float) ($internetPayment->total_due ?? 0);
            $formattedAmount = number_format($totalDue, 2);

            $message = "Reminder: Internet bill for {$internetPayment->station->name} is due on "
                . Carbon::parse($internetPayment->due_date)->format('M d, Y')
                . ". Amount: KES " . $formattedAmount;

            activity()
                ->performedOn($internetPayment)
                ->withProperties(['reminder_sent_at' => now()])
                ->log('Payment reminder sent');

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reminder sent successfully',
                    'data' => [
                        'reminder_message' => $message,
                        'sent_at' => now()->toDateTimeString()
                    ]
                ], 200);
            }

            return redirect()->back()
                ->with('success', 'Reminder sent successfully')
                ->with('reminder_message', $message);

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to send reminder');
        }
    }

    /**
     * Bulk mark payments as paid
     */
    public function bulkMarkPaid(Request $request)
    {
        try {
            $request->validate([
                'payment_ids' => 'required|array',
                'payment_ids.*' => 'exists:internet_payments,id',
                'payment_date' => 'nullable|date',
                'payment_method' => 'nullable|in:M-Pesa,Bank Transfer,Cash,Cheque',
            ]);

            $count = InternetPayment::whereIn('id', $request->payment_ids)
                ->update([
                    'status' => 'paid',
                    'payment_date' => $request->payment_date ?? Carbon::today()->format('Y-m-d'),
                    'payment_method' => $request->payment_method ?? 'M-Pesa',
                ]);

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} payments marked as paid",
                    'data' => ['updated_count' => $count]
                ], 200);
            }

            return redirect()->back()->with('success', "{$count} payments marked as paid");

        } catch (ValidationException $e) {
            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to bulk mark payments');
        }
    }

    /**
     * Bulk send reminders
     */
    public function bulkSendReminders(Request $request)
    {
        try {
            $request->validate([
                'payment_ids' => 'required|array',
                'payment_ids.*' => 'exists:internet_payments,id',
            ]);

            $payments = InternetPayment::with('station')
                ->whereIn('id', $request->payment_ids)
                ->get();

            $reminders = [];
            foreach ($payments as $payment) {
                $totalDue = (float) ($payment->total_due ?? 0);
                $formattedAmount = number_format($totalDue, 2);

                $message = "Reminder: Internet bill for {$payment->station->name} is due on "
                    . Carbon::parse($payment->due_date)->format('M d, Y')
                    . ". Amount: KES " . $formattedAmount;

                $reminders[] = [
                    'payment_id' => $payment->id,
                    'station_name' => $payment->station->name,
                    'amount' => $totalDue,
                    'formatted_amount' => $formattedAmount,
                    'due_date' => $payment->due_date,
                    'reminder_message' => $message,
                    'sent_at' => now()->toDateTimeString()
                ];
            }

            activity()
                ->withProperties([
                    'payment_ids' => $request->payment_ids,
                    'reminders_sent' => count($reminders),
                    'sent_at' => now()
                ])
                ->log('Bulk payment reminders sent');

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'message' => "Reminders sent for " . count($reminders) . " payments",
                    'data' => ['reminders' => $reminders]
                ], 200);
            }

            return redirect()->back()
                ->with('success', "Reminders sent for " . count($reminders) . " payments");

        } catch (ValidationException $e) {
            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to send bulk reminders');
        }
    }

    /*
     * Get internet due payments for a specific station
     */
    public function stationInternetDue($stationId, Request $request)
    {
        try {
            $station = Station::findOrFail($stationId);
            $today = Carbon::today();
            $sevenDaysFromNow = $today->copy()->addDays(7);

            // Overdue payments
            $overdue = InternetPayment::with('provider')
                ->where('station_id', $stationId)
                ->where('status', 'pending')
                ->where('due_date', '<', $today->format('Y-m-d'))
                ->orderBy('due_date')
                ->get();

            // Due soon payments
            $dueSoon = InternetPayment::with('provider')
                ->where('station_id', $stationId)
                ->where('status', 'pending')
                ->where('due_date', '>=', $today->format('Y-m-d'))
                ->where('due_date', '<=', $sevenDaysFromNow->format('Y-m-d'))
                ->orderBy('due_date')
                ->get();

            // Group due soon payments
            $dueSoonGrouped = $this->groupDueSoonPayments($dueSoon, $today);

            $stats = [
                'station' => [
                    'id' => $station->id,
                    'name' => $station->name,
                    'code' => $station->code,
                ],
                'overdue_count' => $overdue->count(),
                'overdue_amount' => (float) $overdue->sum('total_due'),
                'due_soon_count' => $dueSoon->count(),
                'due_soon_amount' => (float) $dueSoon->sum('total_due'),
                'total_due' => (float) ($overdue->sum('total_due') + $dueSoon->sum('total_due')),
            ];

            $responseData = [
                'station' => $station,
                'stats' => $stats,
                'overdue' => InternetPaymentResource::collection($overdue),
                'due_soon_grouped' => $dueSoonGrouped,
            ];

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            return view('internet-payments.station-due', compact('station', 'responseData'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch station internet due');
        }
    }

    /**
     * Get dashboard statistics
     */
    public function dashboard(Request $request)
    {
        try {
            $today = Carbon::today();

            $stats = [
                'totals' => $this->getTotalsStats(),
                'amounts' => $this->getAmountsStats($today),
                'monthly_trend' => $this->getMonthlyTrend(),
                'top_stations' => $this->getTopStations(),
            ];

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'data' => $stats
                ], 200);
            }

            return view('internet-payments.dashboard', compact('stats'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch dashboard data');
        }
    }

    /**
     * Get totals statistics
     */
    private function getTotalsStats(): array
    {
        return [
            'total_payments' => InternetPayment::count(),
            'pending_payments' => InternetPayment::where('status', 'pending')->count(),
            'paid_payments' => InternetPayment::where('status', 'paid')->count(),
            'overdue_payments' => InternetPayment::where('status', 'pending')
                ->where('due_date', '<', Carbon::today())
                ->count(),
        ];
    }

    /**
     * Get amounts statistics
     */
    private function getAmountsStats(Carbon $today): array
    {
        return [
            'total_amount' => (float) InternetPayment::sum('amount'),
            'pending_amount' => (float) InternetPayment::where('status', 'pending')->sum('amount'),
            'paid_amount' => (float) InternetPayment::where('status', 'paid')->sum('amount'),
            'overdue_amount' => (float) InternetPayment::where('status', 'pending')
                ->where('due_date', '<', $today)
                ->sum('amount'),
        ];
    }

    /**
     * Get monthly trend for last 6 months
     */
    private function getMonthlyTrend(): Collection
    {
        return InternetPayment::select(
            DB::raw('DATE_FORMAT(billing_month, "%Y-%m") as month'),
            DB::raw('SUM(amount) as total'),
            DB::raw('COUNT(*) as count')
        )
            ->where('billing_month', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get top 5 stations with pending payments
     */
    private function getTopStations(): Collection
    {
        return InternetPayment::with('station')
            ->where('status', 'pending')
            ->select('station_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('station_id')
            ->orderBy('total_amount', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     *form for creating a new internet payment
     */
    public function create(Request $request)
    {
        try {
            $stations = Station::all();
            $providers = InternetProvider::all();

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'stations' => $stations,
                        'providers' => $providers,
                        'form_fields' => [
                            'station_id', 'vendor_id', 'account_number', 'amount',
                            'previous_balance', 'billing_month', 'due_date', 'status',
                            'payment_method', 'invoice_notes'
                        ]
                    ]
                ], 200);
            }

            return view('internet-payments.create', compact('stations', 'providers'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to load create form');
        }
    }

    /**
     *form for editing the specified internet payment
     */
    public function edit(Request $request, InternetPayment $internetPayment)
    {
        try {
            $stations = Station::all();
            $providers = InternetProvider::all();

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'payment' => new InternetPaymentResource($internetPayment),
                        'stations' => $stations,
                        'providers' => $providers
                    ]
                ], 200);
            }

            return view('internet-payments.edit', compact('internetPayment', 'stations', 'providers'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to load edit form');
        }
    }
}
