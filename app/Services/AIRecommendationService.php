<?php

namespace App\Services;

use App\Models\SuccessRate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIRecommendationService
{
    /**
     * Generate recommendations based on performance data
     */
    public function generateRecommendations($periodType = 'monthly', $year = null)
    {
        $year = $year ?? date('Y');
        
        // Get performance data using the correct model methods
        switch ($periodType) {
            case 'monthly':
                $stationAverages = SuccessRate::getMonthlyAverages($year);
                $overallAverages = SuccessRate::getOverallAverages('monthly', $year);
                break;
            case 'quarterly':
                $stationAverages = SuccessRate::getQuarterlyAverages($year);
                $overallAverages = SuccessRate::getOverallAverages('quarterly', $year);
                break;
            case 'half_yearly':
                $stationAverages = SuccessRate::getHalfYearlyAverages($year);
                $overallAverages = SuccessRate::getOverallAverages('half_yearly', $year);
                break;
            case 'yearly':
                $stationAverages = SuccessRate::getYearlyAverages();
                $stationAverages = $stationAverages instanceof Collection 
                    ? $stationAverages->filter(fn($item) => $item->year == $year)
                    : collect($stationAverages)->filter(fn($item) => $item->year == $year);
                $overallAverages = SuccessRate::getOverallAverages('yearly');
                break;
            default:
                $stationAverages = SuccessRate::getMonthlyAverages($year);
                $overallAverages = SuccessRate::getOverallAverages('monthly', $year);
        }
        
        // Convert to Collection if it's an array
        if (!($stationAverages instanceof Collection)) {
            $stationAverages = collect($stationAverages);
        }
        
        if (!($overallAverages instanceof Collection)) {
            $overallAverages = collect($overallAverages);
        }
        
        // Check if we have data - use count() for Collection, count() works for both
        if ($stationAverages->isEmpty()) {
            return $this->getEmptyDataResponse();
        }
        
        // Calculate performance metrics
        $performanceData = $this->calculatePerformanceMetrics($stationAverages, $overallAverages);
        
        // Generate recommendations
        $recommendations = $this->getRuleBasedRecommendations($performanceData);
        
        return [
            'performance_data' => $performanceData,
            'recommendations' => $recommendations,
            'summary' => $this->generateSummary($performanceData)
        ];
    }
    
    /**
     * Handle case when no data exists
     */
    private function getEmptyDataResponse()
    {
        return [
            'performance_data' => [
                'stations' => [],
                'overall_averages' => collect(),
                'summary_stats' => [
                    'average_all' => 0,
                    'highest' => 0,
                    'lowest' => 0,
                    'poor_performers' => 0,
                    'excellent_performers' => 0,
                    'needs_attention' => 0
                ],
                'thresholds' => []
            ],
            'recommendations' => [
                'type' => 'no_data',
                'content' => 'No performance data available yet. Please upload documents containing station performance data to generate recommendations.',
                'timestamp' => now()
            ],
            'summary' => [
                'verdict' => 'No data available',
                'key_finding' => 'Upload station performance documents to start analyzing',
                'priority_stations' => 0,
                'recommended_action' => 'Please upload performance data documents'
            ]
        ];
    }
    
    private function calculatePerformanceMetrics($stationAverages, $overallAverages)
    {
        $metrics = [];
        $thresholds = [
            'excellent' => 90,
            'good' => 80,
            'average' => 70,
            'poor' => 60
        ];
        
        // Group by station - handle Collection or array
        $stationGroups = [];
        
        foreach ($stationAverages as $record) {
            $stationName = $record->station_name ?? $record['station_name'] ?? null;
            if (!$stationName) continue;
            
            $avgRate = $record->avg_rate ?? $record['avg_rate'] ?? $record->success_rate ?? 0;
            
            if (!isset($stationGroups[$stationName])) {
                $stationGroups[$stationName] = [];
            }
            $stationGroups[$stationName][] = $avgRate;
        }
        
        foreach ($stationGroups as $stationName => $rates) {
            $avgRate = !empty($rates) ? array_sum($rates) / count($rates) : 0;
            $trend = $this->calculateTrendFromRates($rates);
            
            $metrics[$stationName] = [
                'average_rate' => round($avgRate, 2),
                'trend' => $trend,
                'performance_level' => $this->getPerformanceLevel($avgRate, $thresholds),
                'rank' => null,
                'recommendation_priority' => $this->getPriorityLevel($avgRate)
            ];
        }
        
        // Add ranks
        if (!empty($metrics)) {
            uasort($metrics, function($a, $b) {
                return $b['average_rate'] <=> $a['average_rate'];
            });
            
            $rank = 1;
            foreach ($metrics as $station => &$data) {
                $data['rank'] = $rank++;
            }
        }
        
        // Get overall stats
        $summaryStats = $this->calculateSummaryStats($metrics);
        
        return [
            'stations' => $metrics,
            'overall_averages' => $overallAverages,
            'summary_stats' => $summaryStats,
            'thresholds' => $thresholds
        ];
    }
    
    private function calculateTrendFromRates($rates)
    {
        $count = count($rates);
        if ($count < 2) {
            return 'stable';
        }
        
        $first = $rates[0];
        $last = end($rates);
        $change = $last - $first;
        
        if ($change > 5) return 'improving';
        if ($change < -5) return 'declining';
        return 'stable';
    }
    
    private function getPerformanceLevel($rate, $thresholds)
    {
        if ($rate >= $thresholds['excellent']) return 'Excellent';
        if ($rate >= $thresholds['good']) return 'Good';
        if ($rate >= $thresholds['average']) return 'Average';
        return 'Poor';
    }
    
    private function getPriorityLevel($rate)
    {
        if ($rate < 70) return 'HIGH';
        if ($rate < 80) return 'MEDIUM';
        return 'LOW';
    }
    
    private function calculateSummaryStats($metrics)
    {
        // Check if metrics array is empty
        if (empty($metrics)) {
            return [
                'average_all' => 0,
                'highest' => 0,
                'lowest' => 0,
                'poor_performers' => 0,
                'excellent_performers' => 0,
                'needs_attention' => 0
            ];
        }
        
        $rates = array_column($metrics, 'average_rate');
        
        // Calculate average safely
        $totalRates = array_sum($rates);
        $countRates = count($rates);
        $averageAll = $countRates > 0 ? round($totalRates / $countRates, 2) : 0;
        
        return [
            'average_all' => $averageAll,
            'highest' => !empty($rates) ? max($rates) : 0,
            'lowest' => !empty($rates) ? min($rates) : 0,
            'poor_performers' => count(array_filter($metrics, fn($m) => $m['performance_level'] === 'Poor')),
            'excellent_performers' => count(array_filter($metrics, fn($m) => $m['performance_level'] === 'Excellent')),
            'needs_attention' => count(array_filter($metrics, fn($m) => $m['average_rate'] < 75))
        ];
    }
    
    private function getRuleBasedRecommendations($performanceData)
    {
        $stations = $performanceData['stations'];
        
        if (empty($stations)) {
            return [
                'type' => 'rule_based',
                'content' => [
                    'urgent_actions' => [],
                    'improvement_plans' => [],
                    'best_practices' => [],
                    'targets' => [
                        'overall_target' => 85,
                        'poor_performers_target' => 75,
                        'timeline' => 'Next quarter',
                        'success_metric' => 'Improve average success rate by 5%'
                    ]
                ],
                'timestamp' => now()
            ];
        }
        
        $recommendations = [
            'urgent_actions' => [],
            'improvement_plans' => [],
            'best_practices' => [],
            'targets' => []
        ];
        
        // Identify poor performers (below 70%)
        $poorStations = array_filter($stations, fn($s) => $s['average_rate'] < 70);
        
        foreach ($poorStations as $stationName => $data) {
            $recommendations['urgent_actions'][] = [
                'station' => $stationName,
                'current_rate' => $data['average_rate'],
                'action' => 'Immediate intervention required - schedule operational review',
                'priority' => $data['recommendation_priority']
            ];
            
            $recommendations['improvement_plans'][] = [
                'station' => $stationName,
                'plan' => "Comprehensive performance improvement program",
                'target' => "Improve to 75% within 2 months",
                'actions' => [
                    "Conduct root cause analysis workshop",
                    "Implement daily performance tracking dashboard",
                    "Provide additional staff training (3-day program)",
                    "Assign mentor from top-performing station",
                    "Weekly performance review meetings"
                ]
            ];
        }
        
        // Learn from top performers (above 85%)
        $topStations = array_filter($stations, fn($s) => $s['average_rate'] >= 85);
        $topStations = array_slice($topStations, 0, 3, true);
        
        foreach ($topStations as $stationName => $data) {
            $recommendations['best_practices'][] = [
                'station' => $stationName,
                'rate' => $data['average_rate'],
                'practice' => 'Model for operational excellence',
                'actions_to_copy' => [
                    'Standardized operating procedures',
                    'Regular staff training',
                    'Real-time performance monitoring',
                    'Customer feedback integration'
                ]
            ];
        }
        
        // Set targets
        $currentAvg = $performanceData['summary_stats']['average_all'];
        $targetImprovement = min(95, $currentAvg + 5);
        
        $recommendations['targets'] = [
            'overall_target' => $targetImprovement,
            'poor_performers_target' => 75,
            'timeline' => 'Next quarter (90 days)',
            'success_metric' => 'Improve average success rate by 5% across all stations'
        ];
        
        return [
            'type' => 'rule_based',
            'content' => $recommendations,
            'timestamp' => now()
        ];
    }
    
    private function generateSummary($performanceData)
    {
        $stats = $performanceData['summary_stats'];
        
        if ($stats['average_all'] == 0 && empty($performanceData['stations'])) {
            return [
                'verdict' => 'No data available for analysis',
                'key_finding' => 'Upload performance data to generate insights',
                'priority_stations' => 0,
                'recommended_action' => 'Please upload station performance documents'
            ];
        }
        
        if ($stats['average_all'] >= 85) {
            $verdict = "Excellent overall performance. Maintain current strategies.";
        } elseif ($stats['average_all'] >= 70) {
            $verdict = "Good performance with room for improvement in some stations.";
        } else {
            $verdict = "Critical attention needed. Multiple stations underperforming.";
        }
        
        return [
            'verdict' => $verdict,
            'key_finding' => "{$stats['poor_performers']} stations need immediate attention, while {$stats['excellent_performers']} stations are performing excellently.",
            'priority_stations' => $stats['needs_attention'],
            'recommended_action' => $stats['average_all'] < 75 ? 'Urgent operational review required' : 'Continue monitoring and optimize'
        ];
    }
}