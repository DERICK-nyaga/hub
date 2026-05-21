<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SuccessRate extends Model
{
    protected $table = 'successrate';
    
    protected $fillable = [
        'station_name', 'year', 'week_number', 'week_identifier',
        'week_start_date', 'success_rate', 'packages_closed', 'delta',
        'month', 'quarter', 'half_year'
    ];
    
    protected $casts = [
        'week_start_date' => 'date',
        'success_rate' => 'decimal:2',
        'delta' => 'decimal:2'
    ];
    
    /**
     * Get weekly averages for a station
     */
    public static function getStationWeeklyAverages($stationName, $year)
    {
        return self::where('station_name', $stationName)
            ->where('year', $year)
            ->orderBy('week_number')
            ->get(['week_number', 'success_rate', 'packages_closed', 'delta']);
    }
    
    /**
     * Get monthly averages per station
     */
    public static function getMonthlyAverages($year = null, $stationName = null)
    {
        $query = self::query();
        
        if ($year) {
            $query->where('year', $year);
        }
        
        if ($stationName) {
            $query->where('station_name', $stationName);
        }
        
        return $query->select(
                'station_name',
                'year',
                'month',
                DB::raw('AVG(success_rate) as avg_rate'),
                DB::raw('SUM(packages_closed) as total_packages'),
                DB::raw('COUNT(DISTINCT week_number) as weeks_count')
            )
            ->groupBy('station_name', 'year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get(); // This returns a Collection
    }
    
    /**
     * Get quarterly averages per station
     */
    public static function getQuarterlyAverages($year = null, $stationName = null)
    {
        $query = self::query();
        
        if ($year) {
            $query->where('year', $year);
        }
        
        if ($stationName) {
            $query->where('station_name', $stationName);
        }
        
        return $query->select(
                'station_name',
                'year',
                'quarter',
                DB::raw('AVG(success_rate) as avg_rate'),
                DB::raw('SUM(packages_closed) as total_packages')
            )
            ->groupBy('station_name', 'year', 'quarter')
            ->orderBy('year', 'desc')
            ->orderBy('quarter', 'desc')
            ->get();
    }
    
    /**
     * Get half-yearly averages
     */
    public static function getHalfYearlyAverages($year = null, $stationName = null)
    {
        $query = self::query();
        
        if ($year) {
            $query->where('year', $year);
        }
        
        if ($stationName) {
            $query->where('station_name', $stationName);
        }
        
        return $query->select(
                'station_name',
                'year',
                'half_year',
                DB::raw('AVG(success_rate) as avg_rate'),
                DB::raw('SUM(packages_closed) as total_packages')
            )
            ->groupBy('station_name', 'year', 'half_year')
            ->orderBy('year', 'desc')
            ->orderBy('half_year', 'desc')
            ->get();
    }
    
    /**
     * Get yearly averages
     */
    public static function getYearlyAverages($stationName = null)
    {
        $query = self::query();
        
        if ($stationName) {
            $query->where('station_name', $stationName);
        }
        
        return $query->select(
                'station_name',
                'year',
                DB::raw('AVG(success_rate) as avg_rate'),
                DB::raw('SUM(packages_closed) as total_packages'),
                DB::raw('COUNT(DISTINCT week_number) as weeks_analyzed')
            )
            ->groupBy('station_name', 'year')
            ->orderBy('year', 'desc')
            ->get();
    }
    
    /**
     * Get overall averages across all stations
     */
    public static function getOverallAverages($periodType = 'monthly', $year = null)
    {
        switch ($periodType) {
            case 'weekly':
                $query = self::where('year', $year)
                    ->select('week_number', DB::raw('AVG(success_rate) as overall_avg'))
                    ->groupBy('week_number')
                    ->orderBy('week_number');
                return $year ? $query->get() : $query->get();
                
            case 'monthly':
                $query = self::select('year', 'month', DB::raw('AVG(success_rate) as overall_avg'))
                    ->groupBy('year', 'month')
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc');
                if ($year) $query->where('year', $year);
                return $query->get();
                
            case 'quarterly':
                $query = self::select('year', 'quarter', DB::raw('AVG(success_rate) as overall_avg'))
                    ->groupBy('year', 'quarter')
                    ->orderBy('year', 'desc')
                    ->orderBy('quarter', 'desc');
                if ($year) $query->where('year', $year);
                return $query->get();
                
            case 'half_yearly':
                $query = self::select('year', 'half_year', DB::raw('AVG(success_rate) as overall_avg'))
                    ->groupBy('year', 'half_year')
                    ->orderBy('year', 'desc')
                    ->orderBy('half_year', 'desc');
                if ($year) $query->where('year', $year);
                return $query->get();
                
            case 'yearly':
                $query = self::select('year', DB::raw('AVG(success_rate) as overall_avg'))
                    ->groupBy('year')
                    ->orderBy('year', 'desc');
                return $query->get();
                
            default:
                return collect();
        }
    }
    
    /**
     * Identify poor and better performing stations
     */
    public static function getPerformanceRanking($year, $period = 'yearly')
    {
        switch ($period) {
            case 'monthly':
                $data = self::getMonthlyAverages($year);
                break;
            case 'quarterly':
                $data = self::getQuarterlyAverages($year);
                break;
            default:
                $data = self::getYearlyAverages();
                $data = $data->filter(fn($item) => $item->year == $year);
        }
        
        // Group by station and calculate overall average
        $stationPerformance = [];
        foreach ($data as $record) {
            if (!isset($stationPerformance[$record->station_name])) {
                $stationPerformance[$record->station_name] = [
                    'rates' => [],
                    'avg_rate' => 0,
                    'trend' => 'stable'
                ];
            }
            $stationPerformance[$record->station_name]['rates'][] = $record->avg_rate;
        }
        
        // Calculate averages and identify trends
        foreach ($stationPerformance as $station => &$perf) {
            $perf['avg_rate'] = round(array_sum($perf['rates']) / count($perf['rates']), 2);
            
            // Calculate trend based on recent periods
            if (count($perf['rates']) >= 2) {
                $trend = end($perf['rates']) - reset($perf['rates']);
                if ($trend > 3) $perf['trend'] = 'improving';
                elseif ($trend < -3) $perf['trend'] = 'declining';
                else $perf['trend'] = 'stable';
            }
        }
        
        // Sort by average rate
        uasort($stationPerformance, fn($a, $b) => $b['avg_rate'] <=> $a['avg_rate']);
        
        // Classify performance
        $classified = [
            'excellent' => [], // >= 90%
            'good' => [],      // 80-89%
            'average' => [],   // 70-79%
            'poor' => [],      // 60-69%
            'critical' => []   // < 60%
        ];
        
        foreach ($stationPerformance as $station => $data) {
            $rate = $data['avg_rate'];
            if ($rate >= 90) $classified['excellent'][$station] = $data;
            elseif ($rate >= 80) $classified['good'][$station] = $data;
            elseif ($rate >= 70) $classified['average'][$station] = $data;
            elseif ($rate >= 60) $classified['poor'][$station] = $data;
            else $classified['critical'][$station] = $data;
        }
        
        return [
            'all_stations' => $stationPerformance,
            'classified' => $classified,
            'summary' => [
                'total_stations' => count($stationPerformance),
                'excellent_count' => count($classified['excellent']),
                'good_count' => count($classified['good']),
                'average_count' => count($classified['average']),
                'poor_count' => count($classified['poor']),
                'critical_count' => count($classified['critical']),
                'overall_average' => collect($stationPerformance)->avg('avg_rate')
            ]
        ];
    }
    
    /**
     * Get weekly trend for a station
     */
    public static function getStationTrend($stationName, $year, $weeks = 12)
    {
        return self::where('station_name', $stationName)
            ->where('year', $year)
            ->orderBy('week_number', 'desc')
            ->limit($weeks)
            ->get(['week_number', 'success_rate', 'delta', 'packages_closed']);
    }
    
    /**
     * Get comparison between two weeks
     */
    public static function compareWeeks($week1, $week2, $year)
    {
        $week1Data = self::where('year', $year)
            ->where('week_number', $week1)
            ->get()
            ->keyBy('station_name');
            
        $week2Data = self::where('year', $year)
            ->where('week_number', $week2)
            ->get()
            ->keyBy('station_name');
        
        $comparison = [];
        foreach ($week1Data as $station => $data) {
            if (isset($week2Data[$station])) {
                $comparison[$station] = [
                    'week1_rate' => $data->success_rate,
                    'week2_rate' => $week2Data[$station]->success_rate,
                    'change' => round($week2Data[$station]->success_rate - $data->success_rate, 2),
                    'week1_packages' => $data->packages_closed,
                    'week2_packages' => $week2Data[$station]->packages_closed
                ];
            }
        }
        
        return $comparison;
    }

    /**
     * Get station averages for a specific period type
     */
    public static function getStationAverages($stationName = null, $periodType = 'monthly', $year = null)
    {
        $year = $year ?? date('Y');
        
        switch ($periodType) {
            case 'monthly':
                $query = self::getMonthlyAverages($year);
                break;
            case 'quarterly':
                $query = self::getQuarterlyAverages($year);
                break;
            case 'half_yearly':
                $query = self::getHalfYearlyAverages($year);
                break;
            case 'yearly':
                $query = self::getYearlyAverages();
                if ($year) {
                    $query = $query->filter(fn($item) => $item->year == $year);
                }
                break;
            default:
                $query = self::getMonthlyAverages($year);
        }
        
        if ($stationName) {
            $query = $query->filter(fn($item) => $item->station_name === $stationName);
        }
        
        return $query;
    }
}