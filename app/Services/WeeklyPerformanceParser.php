<?php

namespace App\Services;

use App\Models\SuccessRate;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class WeeklyPerformanceParser
{
    /**
     * Parse weekly upload file (always contains current week vs previous week)
     */
    public function parseAndStore($file, $uploadYear = null)
    {
        $uploadYear = $uploadYear ?? date('Y');
        $extension = $file->getClientOriginalExtension();
        
        // Extract data from file
        if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
            $weeklyData = $this->parseExcel($file, $uploadYear);
        } else {
            $content = file_get_contents($file->getRealPath());
            $weeklyData = $this->parseText($content, $uploadYear);
        }
        
        // Store the data
        $stored = 0;
        foreach ($weeklyData as $data) {
            // Check if data for this station/week already exists
            $existing = SuccessRate::where('station_name', $data['station_name'])
                ->where('year', $data['year'])
                ->where('week_number', $data['week_number'])
                ->first();
            
            if ($existing) {
                // Update existing record
                $existing->update($data);
            } else {
                // Create new record
                SuccessRate::create($data);
            }
            $stored++;
        }
        
        return [
            'total_records' => $stored,
            'weeks_processed' => $this->getUniqueWeeks($weeklyData),
            'stations_processed' => $this->getUniqueStations($weeklyData)
        ];
    }
    
    /**
     * Parse Excel file with your specific format
     */
    private function parseExcel($file, $year)
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        $data = [];
        $currentWeek = null;
        $previousWeek = null;
        
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex === 0) {
                // Extract week numbers from headers
                foreach ($row as $cell) {
                    if (preg_match('/(\d{4})W(\d{1,2})/', $cell, $matches)) {
                        $weekNum = (int)$matches[2];
                        if (!$currentWeek || $weekNum > $currentWeek) {
                            $previousWeek = $currentWeek;
                            $currentWeek = $weekNum;
                        } elseif (!$previousWeek && $weekNum < $currentWeek) {
                            $previousWeek = $weekNum;
                        }
                    }
                }
                continue;
            }
            
            // Parse data row
            if (!empty($row[0]) && str_contains($row[0], 'KE-3PL')) {
                $stationName = trim($row[0]);
                
                // Extract percentages and numbers
                $rates = [];
                $packages = [];
                
                foreach ($row as $cell) {
                    if (preg_match('/^(\d{1,3})%$/', trim($cell), $matches)) {
                        $rates[] = (float)$matches[1];
                    } elseif (is_numeric($cell) && $cell >= 100 && $cell <= 10000) {
                        $packages[] = (int)$cell;
                    }
                }
                
                // Determine which is current week (higher rate usually, or based on position)
                $currentRate = $rates[0] ?? 0;
                $previousRate = $rates[1] ?? 0;
                $currentPackages = $packages[0] ?? 0;
                $previousPackages = $packages[1] ?? 0;
                
                // Calculate delta
                $delta = $previousRate > 0 ? round($currentRate - $previousRate, 2) : null;
                
                // Add current week data
                if ($currentWeek && $currentRate > 0) {
                    $data[] = $this->formatWeeklyData(
                        $stationName, $year, $currentWeek, 
                        $currentRate, $currentPackages, $delta
                    );
                }
                
                // Add previous week data
                if ($previousWeek && $previousRate > 0) {
                    $data[] = $this->formatWeeklyData(
                        $stationName, $year, $previousWeek,
                        $previousRate, $previousPackages, null
                    );
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Parse text/copied table
     */
    private function parseText($content, $year)
    {
        $lines = explode("\n", $content);
        $data = [];
        $currentWeek = null;
        $previousWeek = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Extract week numbers
            if (preg_match_all('/W(\d{1,2})/', $line, $matches)) {
                $weeks = array_unique($matches[1]);
                if (count($weeks) >= 2) {
                    $previousWeek = (int)min($weeks);
                    $currentWeek = (int)max($weeks);
                }
            }
            
            // Parse station row
            if (preg_match('/(KE-3PL-[^\s]+)\s+(\d{1,3})%\s+(\d{1,3})%\s+(\d+)\s+(\d+)/', $line, $matches)) {
                $stationName = $matches[1];
                $currentRate = (float)$matches[2];
                $previousRate = (float)$matches[3];
                $currentPackages = (int)$matches[4];
                $previousPackages = (int)$matches[5];
                
                $delta = round($currentRate - $previousRate, 2);
                
                if ($currentWeek) {
                    $data[] = $this->formatWeeklyData(
                        $stationName, $year, $currentWeek,
                        $currentRate, $currentPackages, $delta
                    );
                }
                
                if ($previousWeek) {
                    $data[] = $this->formatWeeklyData(
                        $stationName, $year, $previousWeek,
                        $previousRate, $previousPackages, null
                    );
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Format data for database
     */
    private function formatWeeklyData($stationName, $year, $weekNumber, $successRate, $packagesClosed, $delta = null)
    {
        // Ensure week number is 2 digits
        $weekPadded = str_pad($weekNumber, 2, '0', STR_PAD_LEFT);
        $weekIdentifier = $year . 'W' . $weekPadded;
        
        // Calculate date from week number
        $date = Carbon::now()->setISODate($year, $weekNumber);
        
        $month = $date->month;
        $quarter = ceil($month / 3);
        $halfYear = $month <= 6 ? 1 : 2;
        
        return [
            'station_name' => $stationName,
            'year' => $year,
            'week_number' => $weekNumber,
            'week_identifier' => $weekIdentifier,
            'week_start_date' => $date,
            'success_rate' => $successRate,
            'packages_closed' => $packagesClosed,
            'delta' => $delta,
            'month' => $month,
            'quarter' => $quarter,
            'half_year' => $halfYear,
        ];
    }
    
    private function getUniqueWeeks($data)
    {
        $weeks = array_unique(array_column($data, 'week_number'));
        sort($weeks);
        return $weeks;
    }
    
    private function getUniqueStations($data)
    {
        $stations = array_unique(array_column($data, 'station_name'));
        sort($stations);
        return $stations;
    }
}