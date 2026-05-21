<?php

namespace App\Controllers;

use App\Models\SuccessRate;
use App\Services\WeeklyPerformanceParser;
use App\Services\AIRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SuccessRateController extends Controller
{
    protected $parserService;
    protected $aiService;
    
    public function __construct(WeeklyPerformanceParser $parserService, AIRecommendationService $aiService)
    {
        $this->parserService = $parserService;
        $this->aiService = $aiService;
    }
    
    public function index()
    {
        $currentYear = date('Y');
        $availableYears = SuccessRate::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $currentWeek = SuccessRate::where('year', $currentYear)->max('week_number') ?? 0;
        $totalStations = SuccessRate::where('year', $currentYear)->distinct('station_name')->count('station_name');
        
        // Get latest week data for dashboard
        $latestData = collect(); // Default to empty collection
        if ($currentWeek > 0) {
            $latestData = SuccessRate::where('year', $currentYear)
                ->where('week_number', $currentWeek)
                ->orderBy('success_rate', 'desc')
                ->get(); // This returns a Collection
        }
        
        return view('successrate.index', compact('currentYear', 'availableYears', 'currentWeek', 'totalStations', 'latestData'));
    }
    
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document' => 'required|file|mimes:pdf,xlsx,xls,csv,txt|max:10240',
            'year' => 'required|integer|min:2020|max:2100'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            $result = $this->parserService->parseAndStore(
                $request->file('document'), 
                $request->get('year')
            );
            
            return redirect()->route('successrate.analysis')
                ->with('success', sprintf(
                    'Successfully processed %d records for weeks %s across %d stations',
                    $result['total_records'],
                    implode(', ', $result['weeks_processed']),
                    count($result['stations_processed'])
                ));
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to parse document: ' . $e->getMessage());
        }
    }
    
    public function showAnalysis(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $periodType = $request->get('period', 'monthly');
        
        // Get all performance data
        $monthlyAverages = SuccessRate::getMonthlyAverages($year);
        $quarterlyAverages = SuccessRate::getQuarterlyAverages($year);
        $halfYearlyAverages = SuccessRate::getHalfYearlyAverages($year);
        $yearlyAverages = SuccessRate::getYearlyAverages();
        $weeklyAverages = SuccessRate::getOverallAverages('weekly', $year);
        
        // Get performance ranking
        $performanceRanking = SuccessRate::getPerformanceRanking($year, $periodType);
        
        // IMPORTANT: Create $stationPerformance for the view
        $stationPerformance = [];
        if (isset($performanceRanking['all_stations'])) {
            foreach ($performanceRanking['all_stations'] as $station => $data) {
                $stationPerformance[$station] = [
                    'average_rate' => $data['avg_rate'],
                    'trend' => $data['trend'],
                    'performance_level' => $this->getPerformanceLevel($data['avg_rate']),
                    'rank' => $data['rank'] ?? null,
                    'recommendation_priority' => $data['avg_rate'] < 70 ? 'HIGH' : ($data['avg_rate'] < 80 ? 'MEDIUM' : 'LOW')
                ];
            }
        }
        
        // Get overall averages
        $overallMonthly = SuccessRate::getOverallAverages('monthly', $year);
        $overallQuarterly = SuccessRate::getOverallAverages('quarterly', $year);
        $overallHalfYearly = SuccessRate::getOverallAverages('half_yearly', $year);
        $overallYearly = SuccessRate::getOverallAverages('yearly');
        
        // Get current week for trend analysis
        $currentWeek = SuccessRate::where('year', $year)->max('week_number') ?? 0;
        $previousWeek = $currentWeek - 1;
        
        $weekComparison = [];
        if ($currentWeek > 0 && $previousWeek > 0) {
            $weekComparison = SuccessRate::compareWeeks($previousWeek, $currentWeek, $year);
        }
        
        // Generate AI recommendations
        $recommendations = $this->aiService->generateRecommendations($periodType, $year);
        
        // Get weekly trends for bottom and top performers
        $bottomPerformers = array_slice($performanceRanking['classified']['critical'] ?? [], 0, 5, true);
        $topPerformers = array_slice($performanceRanking['classified']['excellent'] ?? [], 0, 5, true);
        
        $trends = [];
        foreach (array_merge(array_keys($bottomPerformers), array_keys($topPerformers)) as $station) {
            $trends[$station] = SuccessRate::getStationTrend($station, $year, 8);
        }
        
        return view('successrate.analysis', compact(
            'year',
            'periodType',
            'monthlyAverages',
            'quarterlyAverages', 
            'halfYearlyAverages',
            'yearlyAverages',
            'weeklyAverages',
            'performanceRanking',
            'stationPerformance',  // Make sure this is included
            'overallMonthly',
            'overallQuarterly',
            'overallHalfYearly',
            'overallYearly',
            'weekComparison',
            'recommendations',
            'trends',
            'currentWeek',
            'previousWeek'
        ));
    }

    // Add this helper method to the controller
    private function getPerformanceLevel($rate)
    {
        if ($rate >= 90) return 'Excellent';
        if ($rate >= 80) return 'Good';
        if ($rate >= 70) return 'Average';
        if ($rate >= 60) return 'Poor';
        return 'Critical';
    }

    public function getWeeklyProgress(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $station = $request->get('station');
        
        $query = SuccessRate::where('year', $year);
        
        if ($station) {
            $query->where('station_name', $station);
            $data = $query->orderBy('week_number')->get(['week_number', 'success_rate', 'packages_closed']);
            $title = "Weekly Progress - $station";
        } else {
            // Overall average per week
            $data = SuccessRate::where('year', $year)
                ->select('week_number', SuccessRate::raw('AVG(success_rate) as success_rate'))
                ->groupBy('week_number')
                ->orderBy('week_number')
                ->get();
            $title = "Overall Weekly Average Performance";
        }
        
        return response()->json([
            'title' => $title,
            'data' => $data,
            'weeks' => $data->pluck('week_number'),
            'rates' => $data->pluck('success_rate')
        ]);
    }
    
    public function exportReport(Request $request)
    {
        $year = $request->get('year', date('Y'));
        
        $stations = SuccessRate::getPerformanceRanking($year);
        $monthlyData = SuccessRate::getMonthlyAverages($year);
        $quarterlyData = SuccessRate::getQuarterlyAverages($year);
        
        // Generate CSV export
        $filename = "performance_report_{$year}.csv";
        $handle = fopen('php://temp', 'w');
        
        // Headers
        fputcsv($handle, ['Station', 'Average Rate', 'Performance Level', 'Trend', 'Monthly Averages']);
        
        foreach ($stations['all_stations'] as $station => $data) {
            $monthlyRates = $monthlyData->filter(fn($m) => $m->station_name === $station)
                ->pluck('avg_rate')
                ->implode('; ');
            
            fputcsv($handle, [
                $station,
                $data['avg_rate'] . '%',
                $this->getPerformanceLabel($data['avg_rate']),
                $data['trend'],
                $monthlyRates
            ]);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
    
    private function getPerformanceLabel($rate)
    {
        if ($rate >= 90) return 'Excellent';
        if ($rate >= 80) return 'Good';
        if ($rate >= 70) return 'Average';
        if ($rate >= 60) return 'Poor';
        return 'Critical';
    }
}