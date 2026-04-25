<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Station;
use App\Models\AirtimePayment;
use App\Models\InternetPayment;
use App\Models\InternetProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        try {
            $dates = $this->getDateRanges();

            $expiringAirtime = $this->getExpiringAirtime($dates);
            $dueInternet = $this->getDueInternet($dates);
            $overdueInternet = $this->getOverdueInternet($dates);
            $allOverdueInternet = $this->getAllOverdueInternet();

            $stations = $this->getStationsWithPayments($dates);

            $topStation = $this->getTopStationByMonthlyProfit();

            $stats = $this->calculateStats($dates, $dueInternet, $overdueInternet, $topStation, $allOverdueInternet);

            $expiringAirtimeGrouped = $this->groupAirtimeByExpiry($expiringAirtime);
            $dueInternetGrouped = $this->groupInternetByDueDate($dueInternet);
            $overdueInternetGrouped = $this->groupOverdueInternetByStation($overdueInternet);

            //upcoming and recent items
            $upcomingInternet = $dueInternet->take(5);
            $upcomingAirtime = $expiringAirtime->take(5);
            $recentOverdue = $overdueInternet->take(5);

            $recentInternet = $this->getRecentInternetPayments();
            $recentAirtime = $this->getRecentAirtimePayments();

            $data = [
                'stats' => $stats,
                'stations' => $stations,
                'expiringAirtimeGrouped' => $expiringAirtimeGrouped,
                'dueInternetGrouped' => $dueInternetGrouped,
                'overdueInternetGrouped' => $overdueInternetGrouped,
                'overdueInternet' => $overdueInternet,
                'allOverdueInternet' => $allOverdueInternet,
                'upcomingInternet' => $upcomingInternet,
                'upcomingAirtime' => $upcomingAirtime,
                'recentOverdue' => $recentOverdue,
                'recentInternet' => $recentInternet,
                'recentAirtime' => $recentAirtime,
                'topStation' => $topStation,
            ];

            if ($this->isApiRequest($request)) {
                return $this->jsonResponse($data);
            }

            return view('dashboard', $data);

        } catch (\Exception $e) {
            return $this->handleError($e, $request);
        }
    }

    private function getDateRanges(): object
    {
        return (object) [
            'today' => now()->startOfDay(),
            'threeDaysFromNow' => now()->addDays(3)->endOfDay(),
            'sevenDaysFromNow' => now()->addDays(7)->endOfDay(),
        ];
    }

    private function getExpiringAirtime(object $dates): Collection
    {
        return AirtimePayment::with('station')
            ->where('status', 'active')
            ->whereBetween('expected_expiry', [$dates->today, $dates->sevenDaysFromNow])
            ->orderBy('expected_expiry')
            ->get();
    }

    // due internet payments (next 7 days)

    private function getDueInternet(object $dates): Collection
    {
        return InternetPayment::with(['station', 'provider'])
            ->where('status', 'pending')
            ->whereBetween('due_date', [$dates->today, $dates->sevenDaysFromNow])
            ->orderBy('due_date')
            ->get();
    }

// overdue internet payments (past due)
    private function getOverdueInternet(object $dates): Collection
    {
        return InternetPayment::with(['station', 'provider'])
            ->where('status', 'pending')
            ->where('due_date', '<', $dates->today)
            ->orderBy('due_date', 'asc')
            ->get();
    }

// overdue internet payments (including those marked as overdue)
    private function getAllOverdueInternet(): Collection
    {
        $today = now()->startOfDay();

        return InternetPayment::with(['station', 'provider'])
            ->where(function($query) use ($today) {
                $query->where('status', 'overdue')
                    ->orWhere(function($q) use ($today) {
                        $q->where('status', 'pending')
                          ->where('due_date', '<', $today);
                    });
            })
            ->orderBy('due_date', 'asc')
            ->get();
    }

// overdue internet payments by station
    private function groupOverdueInternetByStation(Collection $overduePayments): Collection
    {
        return $overduePayments->groupBy('station_id')->map(function($payments, $stationId) {
            $station = Station::find($stationId);
            return [
                'station' => $station ? $station->name : 'Unknown',
                'station_id' => $stationId,
                'count' => $payments->count(),
                'total_amount' => $payments->sum('total_due'),
                'payments' => $payments
            ];
        })->sortByDesc('total_amount');
    }

    private function getStationsWithPayments(object $dates)
    {
        return Station::with([
            'internetPayments' => function($query) use ($dates) {
                $query->where('status', 'pending')
                    ->whereBetween('due_date', [$dates->today, $dates->sevenDaysFromNow]);
            },
            'airtimePayments' => function($query) use ($dates) {
                $query->where('status', 'active')
                    ->whereBetween('expected_expiry', [$dates->today, $dates->sevenDaysFromNow]);
            }
        ])->paginate(10);
    }

    private function getTopStationByMonthlyProfit(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $currentMonthStart = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
        $currentMonthEnd = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay();

        $stations = Station::all();

        $stationProfits = $stations->map(function($station) use ($currentMonthStart, $currentMonthEnd) {
            $paidPayments = InternetPayment::where('station_id', $station->station_id)
                ->where('status', 'paid')
                ->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd])
                ->with('provider')
                ->get();

            $revenue = $paidPayments->sum('total_due');

            $providerCosts = 0;
            foreach ($paidPayments as $payment) {
                $costBase = $payment->amount;
                if ($payment->provider && $payment->provider->cost_per_unit) {
                    $providerCosts += $costBase * $payment->provider->cost_per_unit;
                } else {
                    $providerCosts += $costBase * 0.7;
                }
            }

            $profit = $revenue - $providerCosts;
            $profitMargin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            return [
                'station' => $station,
                'revenue' => $revenue,
                'provider_costs' => $providerCosts,
                'profit' => $profit,
                'profit_margin' => $profitMargin,
                'payment_count' => $paidPayments->count()
            ];
        });

        $sortedByProfit = $stationProfits->sortByDesc('profit');
        $topStationByProfit = $sortedByProfit->first();

        return [
            'top_by_profit' => [
                'station_name' => $topStationByProfit ? $topStationByProfit['station']->name : 'N/A',
                'profit' => $topStationByProfit ? $topStationByProfit['profit'] : 0,
                'revenue' => $topStationByProfit ? $topStationByProfit['revenue'] : 0,
                'profit_margin' => $topStationByProfit ? round($topStationByProfit['profit_margin'], 2) : 0
            ]
        ];
    }

    /**
     * dashboard statistics including profit metrics
     */
    private function calculateStats(object $dates, Collection $dueInternet, Collection $overdueInternet, array $topStation, Collection $allOverdueInternet): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $currentMonthStart = Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
        $currentMonthEnd = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay();

        // Calculate total monthly revenue
        $totalRevenue = InternetPayment::where('status', 'paid')
            ->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('total_due');

        // Calculate total monthly costs
        $paidPayments = InternetPayment::where('status', 'paid')
            ->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd])
            ->with('provider')
            ->get();

        $totalProviderCosts = 0;
        foreach ($paidPayments as $payment) {
            if ($payment->provider && $payment->provider->cost_per_unit) {
                $totalProviderCosts += $payment->amount * $payment->provider->cost_per_unit;
            } else {
                $totalProviderCosts += $payment->amount * 0.7;
            }
        }

        $totalAirtimeCosts = AirtimePayment::where('status', 'active')
            ->whereBetween('topup_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount') * 0.85;

        $totalCosts = $totalProviderCosts + $totalAirtimeCosts;
        $totalProfit = $totalRevenue - $totalCosts;
        $overallProfitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // Calculate overdue totals
        $overdueCount = $overdueInternet->count();
        $overdueTotalAmount = $overdueInternet->sum('total_due');
        $allOverdueCount = $allOverdueInternet->count();
        $allOverdueTotalAmount = $allOverdueInternet->sum('total_due');

        return [
            'total_stations' => Station::count(),

            // Internet
            'total_internet_payments' => $this->getMonthlyInternetTotal($currentMonth, $currentYear),
            'internet_due_soon' => $dueInternet->count(),
            'internet_overdue' => $overdueCount,
            'internet_overdue_amount' => $overdueTotalAmount,
            'all_overdue_count' => $allOverdueCount,
            'all_overdue_amount' => $allOverdueTotalAmount,
            'internet_paid' => InternetPayment::where('status', 'paid')->count(),
            'total_internet' => InternetPayment::count(),

            // Airtime
            'total_airtime_payments' => $this->getMonthlyAirtimeTotal($currentMonth, $currentYear),
            'airtime_expiring_soon' => $this->getExpiringSoonCount($dates),
            'airtime_active' => AirtimePayment::where('status', 'active')->count(),
            'total_airtime' => AirtimePayment::count(),

            // Payment
            'total_payments' => InternetPayment::count(),
            'paid_payments' => InternetPayment::where('status', 'paid')->count(),
            'pending_payments' => InternetPayment::where('status', 'pending')->count(),
            'upcoming_payments' => $dueInternet->count(),
            'overdue_payments' => $overdueCount,
            'overdue_amount' => $overdueTotalAmount,

            // Top station by profit
            'top_station' => $topStation['top_by_profit']['station_name'],
            'top_station_profit' => $topStation['top_by_profit']['profit'],
            'top_station_revenue' => $topStation['top_by_profit']['revenue'],
            'top_station_margin' => $topStation['top_by_profit']['profit_margin'],

            // Profit metrics
            'monthly_revenue' => $totalRevenue,
            'monthly_costs' => $totalCosts,
            'monthly_profit' => $totalProfit,
            'profit_margin' => round($overallProfitMargin, 2),

            // Averages
            'average_payment' => $this->getAveragePayment(),

            // Providers
            'total_providers' => InternetProvider::count(),

            // Scheduled payments
            'scheduled_payments' => 0,
            'scheduled_completed' => 0,
            'upcoming_schedules' => 0,

            // Totals
            'total_pending' => InternetPayment::where('status', 'pending')->count(),
        ];
    }

    /**
     *monthly internet payments total
     */
    private function getMonthlyInternetTotal(int $month, int $year): float
    {
        return (float) InternetPayment::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('total_due');
    }

    /**
     *monthly airtime payments total
     */
    private function getMonthlyAirtimeTotal(int $month, int $year): float
    {
        return (float) AirtimePayment::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('amount');
    }

    /**
     *expiring soon airtime count
     */
    private function getExpiringSoonCount(object $dates): int
    {
        return AirtimePayment::where('status', 'active')
            ->whereBetween('expected_expiry', [$dates->today, $dates->threeDaysFromNow])
            ->count();
    }

    /**
     *average payment amount
     */
    private function getAveragePayment(): float
    {
        return (float) InternetPayment::where('status', 'paid')->avg('total_due');
    }

    /**
     * Group airtime payments by expiry timeframe
     */
    private function groupAirtimeByExpiry(Collection $airtimePayments): Collection
    {
        $grouped = collect([]);

        foreach ($airtimePayments as $payment) {
            $days = now()->diffInDays($payment->expected_expiry, false);
            $key = $this->getAirtimeGroupKey($days);

            if (!$grouped->has($key)) {
                $grouped->put($key, collect([]));
            }

            $grouped->get($key)->push($payment);
        }

        return $grouped;
    }

    /**
     *group key for airtime payment
     */
    private function getAirtimeGroupKey(int $days): string
    {
        if ($days <= 0) {
            return 'Today';
        } elseif ($days == 1) {
            return 'Tomorrow';
        } elseif ($days <= 3) {
            return "In {$days} Days";
        }

        return 'Later This Week';
    }

    /**
     *internet payments by due date
     */
    private function groupInternetByDueDate(Collection $internetPayments): Collection
    {
        $grouped = collect([]);

        foreach ($internetPayments as $payment) {
            $days = now()->diffInDays($payment->due_date, false);
            $key = $this->getInternetGroupKey($days);

            if (!$grouped->has($key)) {
                $grouped->put($key, collect([]));
            }

            $grouped->get($key)->push($payment);
        }

        return $grouped;
    }

    /**
     *group key for internet payment
     */
    private function getInternetGroupKey(int $days): string
    {
        if ($days <= 0) {
            return 'Today';
        } elseif ($days == 1) {
            return 'Tomorrow';
        } elseif ($days <= 3) {
            return "In {$days} Days";
        }

        return 'Due Later This Week';
    }

    /**
     *recent internet payments
     */
    private function getRecentInternetPayments(): Collection
    {
        return InternetPayment::with('station')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     *recent airtime payments
     */
    private function getRecentAirtimePayments(): Collection
    {
        return AirtimePayment::with('station')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->wantsJson() || $request->ajax() || $request->get('format') === 'json';
    }

    private function jsonResponse(array $data): \Illuminate\Http\JsonResponse
    {
        if (isset($data['stations']) && method_exists($data['stations'], 'toArray')) {
            $data['stations'] = $data['stations']->toArray();
        }

        if (isset($data['expiringAirtimeGrouped']) && $data['expiringAirtimeGrouped'] instanceof Collection) {
            $data['expiringAirtimeGrouped'] = $data['expiringAirtimeGrouped']->map(function($collection) {
                return $collection->values()->toArray();
            })->toArray();
        }

        if (isset($data['dueInternetGrouped']) && $data['dueInternetGrouped'] instanceof Collection) {
            $data['dueInternetGrouped'] = $data['dueInternetGrouped']->map(function($collection) {
                return $collection->values()->toArray();
            })->toArray();
        }

        if (isset($data['overdueInternetGrouped']) && $data['overdueInternetGrouped'] instanceof Collection) {
            $data['overdueInternetGrouped'] = $data['overdueInternetGrouped']->values()->toArray();
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    private function handleError(\Exception $e, Request $request)
    {
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard',
                'error' => $e->getMessage()
            ], 500);
        }

        throw $e;
    }

    public function sidebar(Request $request)
    {
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'partial' => 'payment-sidebar'
                ]
            ], 200);
        }

        return view('partials.payment-sidebar');
    }
}
