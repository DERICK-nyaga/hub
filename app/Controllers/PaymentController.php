<?php

namespace App\Controllers;

use App\Models\Payment;
use App\Models\Station;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\InternetPayment;
use App\Models\AirtimePayment;
use App\Models\PaymentSchedule;
use App\Models\InternetProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{

    public function index(Request $request)
    {
        try {
            $payments = Payment::with(['station', 'vendor'])
                ->upcoming()
                ->paginate(20);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'payments' => $payments,
                    'total' => $payments->total(),
                    'per_page' => $payments->perPage(),
                    'current_page' => $payments->currentPage(),
                ]);
            }

            return view('payments.index', compact('payments'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch payments');
        }
    }

    public function create(Request $request)
    {
        try {
            $types = config('payment.types');
            $stations = Station::all();
            $vendors = Vendor::all();
            $defaultDueDate = now()->addDays(30)->format('Y-m-d');
            $statuses = ['pending', 'approved', 'paid', 'rejected'];

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'form_fields' => [
                        'station_id', 'vendor_id', 'title', 'description', 'amount',
                        'due_date', 'status', 'type', 'attachment', 'is_recurring',
                        'recurrence', 'recurrence_ends_at'
                    ],
                    'stations' => $stations,
                    'vendors' => $vendors,
                    'types' => $types,
                    'default_due_date' => $defaultDueDate,
                    'statuses' => $statuses
                ]);
            }

            return view('payments.create', compact('stations', 'vendors', 'types', 'defaultDueDate', 'statuses'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to load create form');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validatePaymentStore($request);
            $validated = $this->handlePaymentAttachment($request, $validated);
            $validated = $this->setPaymentApproval($validated);
            $validated['created_by'] = Auth::id();

            $payment = Payment::create($validated);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($payment->load(['station', 'vendor']), 'Payment created successfully', 201);
            }

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment created successfully!');

        } catch (ValidationException $e) {
            return $this->handleValidationError($e, $request);
        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Error creating payment');
        }
    }

    public function show(Request $request, Payment $payment)
    {
        try {
            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($payment->load(['station', 'vendor']));
            }

            return view('payments.show', compact('payment'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch payment');
        }
    }

    public function edit(Request $request, Payment $payment)
    {
        try {
            $stations = Station::all();
            $vendors = Vendor::all();
            $types = config('payment.types');

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'payment' => $payment,
                    'stations' => $stations,
                    'vendors' => $vendors,
                    'types' => $types
                ]);
            }

            return view('payments.edit', compact('payment', 'stations', 'vendors', 'types'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to load edit form');
        }
    }

    public function update(Request $request, Payment $payment)
    {
        try {
            $this->authorizePaymentUpdate($payment);
            $validated = $this->validatePaymentUpdate($request);
            $validated = $this->handlePaymentAttachment($request, $validated, $payment);

            $payment->update($validated);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($payment->load(['station', 'vendor']), 'Payment updated successfully');
            }

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment updated successfully!');

        } catch (ValidationException $e) {
            return $this->handleValidationError($e, $request);
        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Error updating payment');
        }
    }

    public function destroy(Request $request, Payment $payment)
    {
        try {
            if ($payment->attachment_path) {
                Storage::delete($payment->attachment_path);
            }

            $payment->delete();

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess(null, 'Payment deleted successfully');
            }

            return redirect()->route('payments.index')
                ->with('success', 'Payment deleted successfully!');

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Error deleting payment');
        }
    }

    /**
     * Approve payment
     */
    public function approve(Request $request, Payment $payment)
    {
        try {
            $payment->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($payment, 'Payment approved successfully');
            }

            return back()->with('success', 'Payment approved!');

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Error approving payment');
        }
    }

    public function markAsPaid(Request $request, Payment $payment)
    {
        try {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($payment, 'Payment marked as paid');
            }

            return back()->with('success', 'Payment marked as paid!');

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Error marking payment as paid');
        }
    }

    public function stationPayments(Request $request, $stationId)
    {
        try {
            $station = Station::with(['internetPayments.provider', 'airtimePayments'])
                ->findOrFail($stationId);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($station);
            }

            return view('payments.station', compact('station'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch station payments');
        }
    }

    public function indexInternetPayments(Request $request)
    {
        try {
            $query = $this->buildInternetPaymentQuery($request);
            $payments = $query->orderBy('due_date', 'desc')->paginate(20);
            $stations = Station::all();
            $providers = InternetProvider::all();

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'payments' => $payments,
                    'stations' => $stations,
                    'providers' => $providers,
                    'filters' => $request->all()
                ]);
            }

            return view('payments.internet.index', compact('payments', 'stations', 'providers'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch internet payments');
        }
    }

    public function createInternetPayment(Request $request)
    {
        try {
            $stations = Station::all();
            $providers = InternetProvider::all();

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'stations' => $stations,
                    'providers' => $providers,
                    'form_fields' => [
                        'station_id', 'vendor_id', 'account_number', 'amount',
                        'previous_balance', 'billing_month', 'due_date', 'payment_date',
                        'mpesa_receipt', 'transaction_id', 'invoice_notes',
                        'payment_method', 'status', 'create_schedule'
                    ]
                ]);
            }

            return view('payments.internet.create', compact('stations', 'providers'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to load create form');
        }
    }

    public function storeInternetPayment(Request $request)
    {
        try {
            Log::info('=== STORE INTERNET PAYMENT DEBUG ===');
            Log::info('Request data:', $request->all());

            $validated = $this->validateInternetPaymentStore($request);
            $payment = $this->createInternetPaymentRecord($validated);

            if ($request->boolean('create_schedule')) {
                $this->createPaymentSchedule($validated);
            }

            Log::info('Payment created successfully. ID: ' . $payment->id);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($payment->load(['station', 'provider']), 'Internet payment recorded successfully', 201);
            }

            return redirect()->route('payments.internet.index')
                ->with('success', 'Internet payment recorded successfully!');

        } catch (ValidationException $e) {
            return $this->handleValidationError($e, $request);
        } catch (\Exception $e) {
            Log::error('Error creating internet payment: ' . $e->getMessage());
            return $this->handleError($e, $request, 'Error creating internet payment');
        }
    }

    public function editInternetPayment(Request $request, $id)
    {
        try {
            $payment = InternetPayment::findOrFail($id);
            $stations = Station::all();
            $providers = InternetProvider::all();

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'payment' => $payment,
                    'stations' => $stations,
                    'providers' => $providers
                ]);
            }

            return view('payments.internet.edit', compact('payment', 'stations', 'providers'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to load edit form');
        }
    }

    public function updateInternetPayment(Request $request, $id)
    {
        try {
            $payment = InternetPayment::findOrFail($id);
            $validated = $this->validateInternetPaymentUpdate($request, $id);

            $payment->update($this->prepareInternetPaymentData($validated));

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($payment->load(['station', 'provider']), 'Internet payment updated successfully');
            }

            return redirect()->route('payments.internet.index')
                ->with('success', 'Internet payment updated successfully!');

        } catch (ValidationException $e) {
            return $this->handleValidationError($e, $request);
        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Error updating internet payment');
        }
    }

    public function destroyInternetPayment(Request $request, $id)
    {
        try {
            $payment = InternetPayment::findOrFail($id);
            $payment->delete();

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess(null, 'Internet payment deleted successfully');
            }

            return redirect()->route('payments.internet.index')
                ->with('success', 'Internet payment deleted successfully!');

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Error deleting internet payment');
        }
    }

    public function showInternetPayment(Request $request, $id)
    {
        try {
            $payment = InternetPayment::with(['station', 'provider'])->findOrFail($id);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($payment);
            }

            return view('payments.internet.show', compact('payment'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch internet payment');
        }
    }

    public function indexAirtimePayments(Request $request)
    {
        try {
            $this->updateExpiredAirtimePayments();

            $query = $this->buildAirtimePaymentQuery($request);
            $payments = $query->orderBy('expected_expiry', 'desc')->paginate(20);
            $stations = Station::all();

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'payments' => $payments,
                    'stations' => $stations,
                    'filters' => $request->all()
                ]);
            }

            return view('payments.airtime.index', compact('payments', 'stations'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch airtime payments');
        }
    }

    public function createAirtimePayment(Request $request)
    {
        try {
            $stations = Station::all();
            $stats = $this->getAirtimeStats();
            $recentPayments = $this->getRecentAirtimePayments();
            $recentNumbers = $this->getRecentMobileNumbers();

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'stations' => $stations,
                    'stats' => $stats,
                    'recent_payments' => $recentPayments,
                    'recent_numbers' => $recentNumbers,
                    'form_fields' => [
                        'station_id', 'mobile_number', 'amount', 'topup_date',
                        'network_provider', 'transaction_id', 'expected_expiry',
                        'payment_method', 'notes', 'auto_renew', 'send_notification'
                    ]
                ]);
            }

            return view('payments.airtime.create', compact('stations', 'stats', 'recentPayments', 'recentNumbers'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to load create form');
        }
    }

    public function storeAirtimePayment(Request $request)
    {
        try {
            $validated = $this->validateAirtimePaymentStore($request);
            $validated = $this->prepareAirtimePaymentData($validated);

            $airtimePayment = AirtimePayment::create($validated);

            if ($validated['send_notification']) {
                $this->sendAirtimeNotification($airtimePayment);
            }

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($airtimePayment->load('station'), 'Airtime payment saved successfully', 201);
            }

            if ($request->input('action') === 'save_and_new') {
                return redirect()->route('payments.airtime.create')
                    ->with('success', 'Airtime payment saved successfully!')
                    ->with('old_data', $validated);
            }

            return redirect()->route('payments.airtime.index')
                ->with('success', 'Airtime payment saved successfully!');

        } catch (ValidationException $e) {
            return $this->handleValidationError($e, $request);
        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Error saving airtime payment');
        }
    }

    public function showAirtimeDetails(Request $request, $id)
    {
        try {
            $payment = AirtimePayment::with('station')->findOrFail($id);
            $payment->updateStatusIfExpired();

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($payment);
            }

            if (request()->ajax()) {
                return view('payments.airtime.partials.details', compact('payment'))->render();
            }

            return view('payments.airtime.show', compact('payment'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch airtime payment');
        }
    }

    public function renewAirtimePayment(Request $request, $id)
    {
        try {
            $payment = AirtimePayment::findOrFail($id);

            $oldData = [
                'station_id' => $payment->station_id,
                'mobile_number' => $payment->mobile_number,
                'network_provider' => $payment->network_provider,
                'notes' => "Renewal of previous payment #{$payment->id}"
            ];

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($oldData, 'Renewal data prepared');
            }

            return redirect()->route('payments.airtime.create')
                ->with('old_data', $oldData);

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to prepare renewal');
        }
    }

    public function destroyAirtimePayment(Request $request, $id)
    {
        try {
            $payment = AirtimePayment::findOrFail($id);

            if ($payment->status != 'expired') {
                if ($this->isApiRequest($request)) {
                    return $this->jsonError('Only expired payments can be deleted', 422);
                }
                return back()->with('error', 'Only expired payments can be deleted.');
            }

            $payment->delete();

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess(null, 'Expired payment deleted successfully');
            }

            return redirect()->route('payments.airtime.index')
                ->with('success', 'Expired payment deleted successfully.');

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Error deleting airtime payment');
        }
    }

    public function getDueSoonPayments(Request $request)
    {
        try {
            $today = Carbon::today();
            $nextWeek = Carbon::today()->addWeek();

            $dueSoonPayments = InternetPayment::whereBetween('due_date', [$today, $nextWeek])
                ->where('status', 'pending')
                ->with(['station', 'provider'])
                ->orderBy('due_date')
                ->get();

            $formattedPayments = $this->formatDueSoonPayments($dueSoonPayments, $today);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'payments' => $formattedPayments,
                    'count' => $formattedPayments->count(),
                    'date_range' => [
                        'start' => $today->format('Y-m-d'),
                        'end' => $nextWeek->format('Y-m-d')
                    ]
                ]);
            }

            $dueSoon = $dueSoonPayments;
            return view('payments.due-soon', compact('dueSoon'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch due soon payments');
        }
    }

    public function upcomingPayments(Request $request)
    {
        try {
            $today = Carbon::today();
            $nextWeek = Carbon::today()->addWeek();

            $upcomingInternet = $this->getUpcomingInternetPayments($today, $nextWeek);
            $upcomingAirtime = $this->getUpcomingAirtimePayments($today, $nextWeek);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'internet' => $this->formatUpcomingInternetPayments($upcomingInternet, $today),
                    'airtime' => $this->formatUpcomingAirtimePayments($upcomingAirtime, $today),
                    'internet_count' => $upcomingInternet->count(),
                    'airtime_count' => $upcomingAirtime->count()
                ]);
            }

            return view('payments.due-soon', compact('upcomingInternet', 'upcomingAirtime'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch upcoming payments');
        }
    }

    public function overduePayments(Request $request)
    {
        try {
            $today = Carbon::today();

            $overdueInternet = $this->getOverdueInternetPayments($today);
            $overdueAirtime = $this->getOverdueAirtimePayments($today);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'internet' => $this->formatOverdueInternetPayments($overdueInternet, $today),
                    'airtime' => $this->formatOverdueAirtimePayments($overdueAirtime, $today),
                    'internet_count' => $overdueInternet->count(),
                    'airtime_count' => $overdueAirtime->count(),
                    'internet_total' => (float) $overdueInternet->sum('total_due'),
                    'airtime_total' => (float) $overdueAirtime->sum('amount')
                ]);
            }

            return view('payments.overdue', compact('overdueInternet', 'overdueAirtime'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch overdue payments');
        }
    }

    public function sendReminder(Request $request, $paymentId)
    {
        try {
            $payment = InternetPayment::with(['station', 'provider'])->findOrFail($paymentId);
            $reminderSent = $this->sendPaymentReminder($payment);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'payment_id' => $payment->id,
                    'reminder_sent_at' => now(),
                    'reminder_sent' => $reminderSent
                ], 'Reminder sent successfully');
            }

            return redirect()->back()
                ->with('success', 'Reminder sent successfully!');

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to send reminder');
        }
    }

    public function indexSchedules(Request $request)
    {
        try {
            $schedules = PaymentSchedule::with('station')
                ->orderBy('scheduled_date')
                ->paginate(20);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'schedules' => $schedules,
                    'total' => $schedules->total(),
                    'per_page' => $schedules->perPage()
                ]);
            }

            return view('payments.schedules.index', compact('schedules'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to fetch schedules');
        }
    }

    public function createSchedule(Request $request)
    {
        try {
            $stations = Station::all();

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess([
                    'stations' => $stations,
                    'form_fields' => [
                        'station_id', 'payment_type', 'scheduled_date',
                        'scheduled_amount', 'frequency', 'is_recurring',
                        'auto_pay', 'description'
                    ]
                ]);
            }

            return view('payments.schedules.create', compact('stations'));

        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Failed to load create form');
        }
    }

    public function storeSchedule(Request $request)
    {
        try {
            $validated = $request->validate([
                'station_id' => 'required|exists:stations,station_id',
                'payment_type' => 'required|in:internet,airtime',
                'scheduled_date' => 'required|date',
                'scheduled_amount' => 'required|numeric|min:0',
                'frequency' => 'required|in:monthly,quarterly,yearly,custom',
                'is_recurring' => 'boolean',
                'auto_pay' => 'boolean',
                'description' => 'nullable|string'
            ]);

            $schedule = PaymentSchedule::create($validated);

            if ($this->isApiRequest($request)) {
                return $this->jsonSuccess($schedule->load('station'), 'Payment schedule created successfully', 201);
            }

            return redirect()->route('payments.schedules.index')
                ->with('success', 'Payment schedule created successfully!');

        } catch (ValidationException $e) {
            return $this->handleValidationError($e, $request);
        } catch (\Exception $e) {
            return $this->handleError($e, $request, 'Error creating schedule');
        }
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->wantsJson() || $request->ajax() || $request->get('format') === 'json';
    }

    private function jsonSuccess($data = null, string $message = '', int $status = 200): JsonResponse
    {
        $response = ['success' => true];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    private function jsonError(string $message, int $status = 500, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    private function handleValidationError(ValidationException $e, Request $request)
    {
        if ($this->isApiRequest($request)) {
            return $this->jsonError('Validation failed', 422, $e->errors());
        }
        throw $e;
    }

    private function handleError(\Exception $e, Request $request, string $message)
    {
        Log::error($message . ': ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        if ($this->isApiRequest($request)) {
            return $this->jsonError($message . ': ' . $e->getMessage(), 500);
        }

        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
            return back()->withInput()->with('error', $message . ': ' . $e->getMessage());
        }

        throw $e;
    }

    private function validatePaymentStore(Request $request): array
    {
        return $request->validate([
            'station_id' => 'required|exists:stations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date|after_or_equal:today',
            'status' => 'required|in:pending,approved,paid,rejected',
            'type' => 'required|in:utility,service,product,other',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'is_recurring' => 'required|boolean',
            'recurrence' => 'required_if:is_recurring,true|in:weekly,monthly,yearly',
            'recurrence_ends_at' => 'nullable|date|after:due_date'
        ]);
    }

    private function validatePaymentUpdate(Request $request): array
    {
        return $request->validate([
            'station_id' => 'required|exists:stations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,approved,paid,rejected',
            'type' => 'required|in:' . implode(',', array_keys(config('payment.types'))),
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'is_recurring' => 'boolean',
            'recurrence' => 'required_if:is_recurring,true|in:weekly,monthly,yearly',
            'recurrence_ends_at' => 'nullable|date|after:due_date'
        ]);
    }

    private function handlePaymentAttachment(Request $request, array $data, $payment = null): array
    {
        if ($request->hasFile('attachment')) {
            if ($payment && $payment->attachment_path) {
                Storage::delete($payment->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('payments/attachments');
        }

        return $data;
    }

    private function setPaymentApproval(array $data): array
    {
        if (Auth::user()->role === 'admin' && $data['status'] === 'approved') {
            $data['approved_by'] = Auth::id();
            $data['approved_at'] = now();
        }

        return $data;
    }

    private function authorizePaymentUpdate(Payment $payment): void
    {
        $user = Auth::user();
        abort_unless($user !== null, 403, 'Unauthorized access');
        abort_unless(Gate::allows('update', $payment), 403, 'You are not authorized to update this payment');
    }

    private function buildInternetPaymentQuery(Request $request): Builder
    {
        $query = InternetPayment::with(['station', 'provider']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        if ($request->filled('month')) {
            $month = Carbon::parse($request->month . '-01')->format('Y-m-d');
            $query->where('billing_month', $month);
        }

        if ($request->filled('search')) {
            $query->where('account_number', 'like', '%' . $request->search . '%');
        }

        return $query;
    }

    private function validateInternetPaymentStore(Request $request): array
    {
        return $request->validate([
            'station_id' => 'required|exists:stations,station_id',
            'vendor_id' => 'required|exists:internet_providers,vendor_id',
            'account_number' => 'required',
            'amount' => 'required|numeric|min:0',
            'previous_balance' => 'nullable|numeric|min:0',
            'billing_month' => 'required|date',
            'due_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'mpesa_receipt' => 'nullable|string',
            'transaction_id' => 'nullable|string',
            'invoice_notes' => 'nullable|string',
            'payment_method' => 'nullable|in:M-Pesa,Bank Transfer,Cash,Cheque',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'create_schedule' => 'nullable|boolean'
        ]);
    }

    private function validateInternetPaymentUpdate(Request $request, $id): array
    {
        return $request->validate([
            'station_id' => 'required|exists:stations,station_id',
            'vendor_id' => 'required|exists:internet_providers,vendor_id',
            'account_number' => 'required|unique:internet_payments,account_number,' . $id,
            'amount' => 'required|numeric|min:0',
            'previous_balance' => 'nullable|numeric|min:0',
            'billing_month' => 'required|date',
            'due_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'mpesa_receipt' => 'nullable|string',
            'transaction_id' => 'nullable|string',
            'invoice_notes' => 'nullable|string',
            'payment_method' => 'nullable|in:M-Pesa,Bank Transfer,Cash,Cheque'
        ]);
    }

    private function createInternetPaymentRecord(array $data): InternetPayment
    {
        $paymentData = [
            'station_id' => $data['station_id'],
            'vendor_id' => $data['vendor_id'],
            'account_number' => $data['account_number'],
            'amount' => (float) $data['amount'],
            'previous_balance' => isset($data['previous_balance']) ? (float) $data['previous_balance'] : 0.00,
            'billing_month' => Carbon::parse($data['billing_month'])->format('Y-m-d'),
            'due_date' => Carbon::parse($data['due_date'])->format('Y-m-d'),
            'payment_date' => $data['payment_date'] ? Carbon::parse($data['payment_date'])->format('Y-m-d') : null,
            'mpesa_receipt' => $data['mpesa_receipt'] ?? null,
            'transaction_id' => $data['transaction_id'] ?? null,
            'status' => $data['status'],
            'invoice_notes' => $data['invoice_notes'] ?? null,
            'payment_method' => $data['payment_method'] ?? null
        ];

        return InternetPayment::create($paymentData);
    }

    private function prepareInternetPaymentData(array $data): array
    {
        return [
            'station_id' => $data['station_id'],
            'vendor_id' => $data['vendor_id'],
            'account_number' => $data['account_number'],
            'amount' => (float) $data['amount'],
            'previous_balance' => isset($data['previous_balance']) ? (float) $data['previous_balance'] : 0.00,
            'billing_month' => Carbon::parse($data['billing_month'])->format('Y-m-d'),
            'due_date' => Carbon::parse($data['due_date'])->format('Y-m-d'),
            'payment_date' => $data['payment_date'] ? Carbon::parse($data['payment_date'])->format('Y-m-d') : null,
            'status' => $data['status'],
            'mpesa_receipt' => $data['mpesa_receipt'] ?? null,
            'transaction_id' => $data['transaction_id'] ?? null,
            'invoice_notes' => $data['invoice_notes'] ?? null,
            'payment_method' => $data['payment_method'] ?? null
        ];
    }

    private function createPaymentSchedule(array $data): void
    {
        $provider = InternetProvider::find($data['vendor_id']);

        PaymentSchedule::create([
            'station_id' => $data['station_id'],
            'payment_type' => 'internet',
            'scheduled_date' => Carbon::parse($data['due_date'])->subDays(7)->format('Y-m-d'),
            'scheduled_amount' => $data['amount'],
            'frequency' => 'monthly',
            'is_recurring' => true,
            'description' => "Monthly internet payment to {$provider->name} for account {$data['account_number']}",
            'vendor_id' => $provider->vendor_id
        ]);
    }

    private function buildAirtimePaymentQuery(Request $request): Builder
    {
        $query = AirtimePayment::with('station');

        if ($request->filled('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        if ($request->filled('network_provider')) {
            $query->where('network_provider', $request->network_provider);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $month = $request->month;
            $query->whereYear('topup_date', substr($month, 0, 4))
                ->whereMonth('topup_date', substr($month, 5, 2));
        }

        if ($request->filled('search')) {
            $query->where('mobile_number', 'like', '%' . $request->search . '%');
        }

        return $query;
    }

    private function validateAirtimePaymentStore(Request $request): array
    {
        return $request->validate([
            'station_id' => 'required|exists:stations,station_id',
            'mobile_number' => 'required|string|max:15',
            'amount' => 'required|numeric|min:0',
            'topup_date' => 'required|date',
            'network_provider' => 'required|string|in:Safaricom,Airtel,Telkom,Faiba',
            'transaction_id' => 'nullable|string|unique:airtime_payments',
            'expected_expiry' => 'nullable|date',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
            'auto_renew' => 'nullable|boolean',
            'send_notification' => 'nullable|boolean',
        ]);
    }

    private function prepareAirtimePaymentData(array $data): array
    {
        if (empty($data['transaction_id'])) {
            $data['transaction_id'] = 'TXN' . date('YmdHis') . rand(100, 999);
        }

        if (empty($data['expected_expiry'])) {
            $data['expected_expiry'] = Carbon::parse($data['topup_date'])->addDays(30);
        }

        $data['auto_renew'] = $data['auto_renew'] ?? false;
        $data['send_notification'] = $data['send_notification'] ?? false;

        return $data;
    }

    private function getAirtimeStats(): array
    {
        return [
            'active_topups' => AirtimePayment::where('status', 'active')->count(),
            'expiring_soon' => AirtimePayment::where('status', 'active')
                ->where('expected_expiry', '<=', Carbon::now()->addDays(3))
                ->count(),
            'monthly_total' => AirtimePayment::whereMonth('topup_date', Carbon::now()->month)
                ->sum('amount')
        ];
    }

    private function getRecentAirtimePayments(): Collection
    {
        return AirtimePayment::with('station')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function getRecentMobileNumbers(): Collection
    {
        return AirtimePayment::select('mobile_number')
            ->selectRaw('MAX(created_at) as latest_created_at')
            ->groupBy('mobile_number')
            ->orderBy('latest_created_at', 'desc')
            ->limit(10)
            ->pluck('mobile_number');
    }

    /**
     * pending implementation
     */
    private function sendAirtimeNotification(AirtimePayment $payment): void
    {
        Log::info('Airtime notification sent for payment: ' . $payment->id);
        // to implement actual notification logic
    }

    private function updateExpiredAirtimePayments(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        $updated = AirtimePayment::where('status', 'active')
            ->where('expected_expiry', '<', $today)
            ->update(['status' => 'expired']);

        if ($updated > 0) {
            Log::info("Updated {$updated} airtime payments to expired status.");
        }
    }

    private function formatDueSoonPayments(Collection $payments, Carbon $today): Collection
    {
        return $payments->map(function (InternetPayment $payment) use ($today) {
            $dueDate = $payment->due_date instanceof Carbon ? $payment->due_date : Carbon::parse($payment->due_date);

            return [
                'id' => $payment->id,
                'station' => $payment->station->name,
                'station_id' => $payment->station_id,
                'provider' => $payment->provider->name,
                'provider_id' => $payment->vendor_id,
                'account_number' => $payment->account_number,
                'amount_due' => (float) $payment->total_due,
                'due_date' => $dueDate->format('Y-m-d'),
                'formatted_due_date' => $dueDate->format('d/m/Y'),
                'contact_person' => $payment->station->contact_person ?? null,
                'contact_phone' => $payment->station->contact_phone ?? null,
                'contact_email' => $payment->station->contact_email ?? null,
                'paybill_number' => $payment->provider->paybill_number ?? null,
                'support_contact' => $payment->provider->support_contact ?? null,
                'is_due_today' => $dueDate->isToday(),
                'days_until_due' => $today->diffInDays($dueDate),
                'reminder_message' => $this->generateReminderMessage($payment)
            ];
        });
    }

    private function formatUpcomingInternetPayments(Collection $payments, Carbon $today): Collection
    {
        return $payments->map(function (InternetPayment $payment) use ($today) {
            $dueDate = Carbon::parse($payment->due_date);

            return [
                'id' => $payment->id,
                'station' => $payment->station->name,
                'provider' => $payment->provider->name,
                'account_number' => $payment->account_number,
                'amount_due' => (float) $payment->total_due,
                'due_date' => $dueDate->format('Y-m-d'),
                'formatted_due_date' => $dueDate->format('d/m/Y'),
                'days_until_due' => $today->diffInDays($dueDate),
                'payment_type' => 'internet'
            ];
        });
    }

    private function formatUpcomingAirtimePayments(Collection $payments, Carbon $today): Collection
    {
        return $payments->map(function (AirtimePayment $payment) use ($today) {
            $expiryDate = Carbon::parse($payment->expected_expiry);

            return [
                'id' => $payment->id,
                'station' => $payment->station->name,
                'mobile_number' => $payment->mobile_number,
                'amount' => (float) $payment->amount,
                'expiry_date' => $expiryDate->format('Y-m-d'),
                'formatted_expiry_date' => $expiryDate->format('d/m/Y'),
                'days_until_expiry' => $today->diffInDays($expiryDate),
                'network_provider' => $payment->network_provider,
                'payment_type' => 'airtime'
            ];
        });
    }

    private function formatOverdueInternetPayments(Collection $payments, Carbon $today): Collection
    {
        return $payments->map(function (InternetPayment $payment) use ($today) {
            $dueDate = Carbon::parse($payment->due_date);

            return [
                'id' => $payment->id,
                'station' => $payment->station->name,
                'provider' => $payment->provider->name,
                'account_number' => $payment->account_number,
                'amount_due' => (float) $payment->total_due,
                'due_date' => $dueDate->format('Y-m-d'),
                'formatted_due_date' => $dueDate->format('d/m/Y'),
                'days_overdue' => $dueDate->diffInDays($today),
                'status' => $payment->status,
                'payment_type' => 'internet'
            ];
        });
    }

    private function formatOverdueAirtimePayments(Collection $payments, Carbon $today): Collection
    {
        return $payments->map(function (AirtimePayment $payment) use ($today) {
            $expiryDate = Carbon::parse($payment->expected_expiry);

            return [
                'id' => $payment->id,
                'station' => $payment->station->name,
                'mobile_number' => $payment->mobile_number,
                'amount' => (float) $payment->amount,
                'expiry_date' => $expiryDate->format('Y-m-d'),
                'formatted_expiry_date' => $expiryDate->format('d/m/Y'),
                'days_overdue' => $expiryDate->diffInDays($today),
                'network_provider' => $payment->network_provider,
                'status' => $payment->status,
                'payment_type' => 'airtime'
            ];
        });
    }

    private function getUpcomingInternetPayments(Carbon $today, Carbon $nextWeek): Collection
    {
        return InternetPayment::whereBetween('due_date', [$today, $nextWeek])
            ->where('status', 'pending')
            ->with(['station', 'provider'])
            ->orderBy('due_date')
            ->get();
    }

    private function getUpcomingAirtimePayments(Carbon $today, Carbon $nextWeek): Collection
    {
        return AirtimePayment::whereBetween('expected_expiry', [$today, $nextWeek])
            ->where('status', 'active')
            ->with('station')
            ->orderBy('expected_expiry')
            ->get();
    }

    private function getOverdueInternetPayments(Carbon $today): Collection
    {
        return InternetPayment::where('due_date', '<', $today->format('Y-m-d'))
            ->whereIn('status', ['pending', 'overdue'])
            ->with(['station', 'provider'])
            ->orderBy('due_date')
            ->get();
    }

    private function getOverdueAirtimePayments(Carbon $today): Collection
    {
        return AirtimePayment::where('expected_expiry', '<', $today->format('Y-m-d'))
            ->where('status', 'active')
            ->with('station')
            ->orderBy('expected_expiry')
            ->get();
    }

    private function generateReminderMessage(InternetPayment $payment): string
    {
        $totalDue = (float) ($payment->total_due ?? 0);
        $formattedAmount = number_format($totalDue, 2);
        $dueDate = Carbon::parse($payment->due_date);

        return "Reminder: Internet bill for {$payment->station->name} is due on "
            . $dueDate->format('M d, Y')
            . ". Amount: KES " . $formattedAmount;
    }

    private function sendPaymentReminder(InternetPayment $payment): bool
    {
        try {
            $station = $payment->station;
            $provider = $payment->provider;

            $totalDue = (float) ($payment->total_due ?? 0);
            $formattedAmount = number_format($totalDue, 2);
            $dueDate = Carbon::parse($payment->due_date);

            $emailSubject = "Internet Payment Reminder - {$station->name}";
            $smsMessage = "REMINDER: Internet bill for {$station->name} of KES {$formattedAmount} is due on {$dueDate->format('M d, Y')}. Pay via Paybill {$provider->paybill_number} Acc {$payment->account_number}";

            $reminderSent = false;

            if (!empty($station->contact_email)) {
                try {
                    Mail::to($station->contact_email)
                        ->cc($provider->billing_email ?? null)
                        ->send(new \App\Mail\InternetPaymentReminder($payment));

                    Log::info("Email reminder sent to {$station->contact_email}");
                    $reminderSent = true;
                } catch (\Exception $e) {
                    Log::error("Email failed for payment {$payment->id}: " . $e->getMessage());
                }
            }

            if (!empty($station->contact_phone) && config('services.sms.enabled', false)) {
                try {
                    $smsService = new \App\Services\SMSService();
                    $smsSent = $smsService->sendSMS($station->contact_phone, $smsMessage);

                    if ($smsSent) {
                        $reminderSent = true;
                        Log::info("SMS reminder sent to {$station->contact_phone}");
                    }
                } catch (\Exception $e) {
                    Log::error("SMS failed for payment {$payment->id}: " . $e->getMessage());
                }
            }

            \App\Models\Notification::create([
                'type' => 'payment_reminder',
                'notifiable_type' => 'station',
                'notifiable_id' => $station->id,
                'data' => json_encode([
                    'payment_id' => $payment->id,
                    'message' => $smsMessage,
                    'amount' => $totalDue,
                    'due_date' => $dueDate->format('Y-m-d')
                ]),
                'sent_at' => now()
            ]);

            // Logging the reminder
            activity()
                ->performedOn($payment)
                ->withProperties([
                    'reminder_sent_at' => now(),
                    'email_sent' => !empty($station->contact_email),
                    'sms_sent' => !empty($station->contact_phone),
                    'station_email' => $station->contact_email,
                    'station_phone' => $station->contact_phone
                ])
                ->log('Payment reminder sent');

            return $reminderSent;

        } catch (\Exception $e) {
            Log::error('Failed to send payment reminder: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
