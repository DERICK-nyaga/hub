<?php

namespace App\Services;

use App\Models\SuccessRate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class DocumentParserService
{
    /**
     * Parse uploaded document and extract table data
     */
    public function parseAndStore($file)
    {
        $extension = $file->getClientOriginalExtension();
        $extractedData = [];
        
        // Extract data based on file type
        if ($extension === 'pdf') {
            $extractedData = $this->parsePdf($file);
        } elseif (in_array($extension, ['xlsx', 'xls', 'csv'])) {
            $extractedData = $this->parseExcel($file);
        } elseif ($extension === 'txt') {
            $extractedData = $this->parseText($file);
        } else {
            throw new \Exception('Unsupported file type: ' . $extension);
        }
        
        // Store extracted data
        $count = 0;
        foreach ($extractedData as $data) {
            SuccessRate::updateOrCreate(
                [
                    'station_name' => $data['station_name'],
                    'week_identifier' => $data['week_identifier']
                ],
                $data
            );
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Parse text/copied table data (like from your example)
     */
    public function parseText($file)
    {
        if ($file instanceof UploadedFile) {
            $content = file_get_contents($file->getRealPath());
        } else {
            $content = $file;
        }
        return $this->extractTableFromText($content);
    }
    
    /**
     * Parse Excel files
     */
    public function parseExcel($file)
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            throw new \Exception('PhpSpreadsheet library not installed. Run: composer require phpoffice/phpspreadsheet');
        }
        
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        return $this->extractTableFromArray($rows);
    }
    
    /**
     * Parse PDF files
     */
    public function parsePdf($file)
    {
        if (!class_exists('\Smalot\PdfParser\Parser')) {
            // Fallback: try to extract text using pdftotext if available
            return $this->parsePdfFallback($file);
        }
        
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($file->getRealPath());
            $text = $pdf->getText();
            return $this->extractTableFromText($text);
        } catch (\Exception $e) {
            Log::warning('PDF parsing failed: ' . $e->getMessage());
            return $this->parsePdfFallback($file);
        }
    }
    
    /**
     * Fallback PDF parsing using command line tools
     */
    private function parsePdfFallback($file)
    {
        // Try to use pdftotext (Linux/Mac)
        $outputFile = storage_path('app/temp_' . uniqid() . '.txt');
        $command = 'pdftotext "' . $file->getRealPath() . '" "' . $outputFile . '" 2>&1';
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($outputFile)) {
            $content = file_get_contents($outputFile);
            unlink($outputFile);
            return $this->extractTableFromText($content);
        }
        
        throw new \Exception('Unable to parse PDF. Please install smalot/pdfparser: composer require smalot/pdfparser');
    }
    
    /**
     * Extract table data from text (handles your specific format)
     */
    private function extractTableFromText($text)
    {
        $lines = explode("\n", $text);
        $data = [];
        $foundHeader = false;
        $currentWeek1 = null;
        $currentWeek2 = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Look for header row with weeks (e.g., "2026W10 | 2026W11")
            if (preg_match('/(\d{4}W\d{1,2})\s*[|\t]\s*(\d{4}W\d{1,2})/', $line, $weekMatches)) {
                $currentWeek1 = $weekMatches[1];
                $currentWeek2 = $weekMatches[2];
                $foundHeader = true;
                continue;
            }
            
            // Also try to find weeks in the first row of the table
            if (!$foundHeader && preg_match('/Hub.*W(\d{4})/', $line)) {
                preg_match_all('/W(\d{4})/', $line, $matches);
                if (count($matches[0]) >= 2) {
                    $currentWeek1 = $matches[0][0];
                    $currentWeek2 = $matches[0][1];
                    $foundHeader = true;
                }
                continue;
            }
            
            // Parse data rows (looking for station names)
            if (preg_match('/(KE-3PL-[^\s]+)/', $line, $stationMatch)) {
                $stationName = $stationMatch[1];
                
                // Extract percentages and numbers
                preg_match_all('/(\d{1,3}%)/', $line, $percentageMatches);
                preg_match_all('/(?<!\d)(\d{2,4})(?!\d)/', $line, $numberMatches);
                
                if (count($percentageMatches[0]) >= 2) {
                    $rate1 = $this->extractPercentage($percentageMatches[0][0] ?? '0%');
                    $rate2 = $this->extractPercentage($percentageMatches[0][1] ?? '0%');
                    
                    // Find packages closed (look for numbers between 100-9999)
                    $packagesClosed = 0;
                    foreach ($numberMatches[0] as $num) {
                        if ($num >= 100 && $num <= 9999 && $num != $rate1 && $num != $rate2) {
                            $packagesClosed = (int)$num;
                            break;
                        }
                    }
                    
                    if ($currentWeek1) {
                        $data[] = $this->formatData($stationName, $currentWeek1, $rate1, $packagesClosed);
                    }
                    if ($currentWeek2) {
                        $data[] = $this->formatData($stationName, $currentWeek2, $rate2, $packagesClosed);
                    }
                }
            }
        }
        
        // If no structured data found, try a simpler approach
        if (empty($data)) {
            $data = $this->extractSimpleTable($lines);
        }
        
        return $data;
    }
    
    /**
     * Simple table extraction as fallback
     */
    private function extractSimpleTable($lines)
    {
        $data = [];
        $stations = [];
        
        foreach ($lines as $line) {
            // Look for station patterns
            if (preg_match('/(KE-3PL-[^\s]+).*?(\d{1,3})%.*?(\d{1,3})%.*?(\d+)/', $line, $matches)) {
                $stationName = $matches[1];
                $rate1 = (float)$matches[2];
                $rate2 = (float)$matches[3];
                $packages = (int)$matches[4];
                
                // Create week identifiers (use current year)
                $year = date('Y');
                $data[] = $this->formatData($stationName, $year . 'W' . date('W'), $rate1, $packages);
            }
        }
        
        return $data;
    }
    
    /**
     * Extract table from array (Excel/CSV)
     */
    private function extractTableFromArray($rows)
    {
        $data = [];
        $headers = [];
        $week1 = null;
        $week2 = null;
        
        foreach ($rows as $rowIndex => $row) {
            // Clean up the row
            $row = array_map('trim', $row);
            
            if ($rowIndex === 0) {
                $headers = $row;
                // Try to find week columns
                foreach ($headers as $index => $header) {
                    if (preg_match('/W\d{4}/', $header)) {
                        if (!$week1) $week1 = $header;
                        else if (!$week2) $week2 = $header;
                    }
                }
                continue;
            }
            
            // Skip empty rows
            if (empty($row[0]) || !str_contains($row[0], 'KE-3PL')) {
                continue;
            }
            
            $stationName = $row[0];
            
            // Find rate columns
            $rates = [];
            $packagesClosed = 0;
            
            foreach ($row as $cell) {
                if (preg_match('/^(\d{1,3})%$/', $cell, $rateMatch)) {
                    $rates[] = (float)$rateMatch[1];
                } elseif (is_numeric($cell) && $cell > 100 && $cell < 10000) {
                    $packagesClosed = (int)$cell;
                }
            }
            
            // If we found weeks in headers, use them
            if ($week1 && isset($rates[0])) {
                $data[] = $this->formatData($stationName, $week1, $rates[0], $packagesClosed);
            }
            if ($week2 && isset($rates[1])) {
                $data[] = $this->formatData($stationName, $week2, $rates[1], $packagesClosed);
            }
        }
        
        return $data;
    }
    
    /**
     * Format extracted data for database
     */
    private function formatData($stationName, $weekIdentifier, $successRate, $packagesClosed)
    {
        // Parse week identifier (e.g., "2026W10" or "W202610")
        if (preg_match('/(\d{4})W(\d{1,2})/', $weekIdentifier, $matches)) {
            $year = $matches[1];
            $week = $matches[2];
        } elseif (preg_match('/W(\d{4})(\d{1,2})/', $weekIdentifier, $matches)) {
            $year = $matches[1];
            $week = $matches[2];
        } else {
            // Default to current week
            $year = date('Y');
            $week = date('W');
        }
        
        // Calculate date from week number
        try {
            $date = Carbon::now()->setISODate($year, $week);
        } catch (\Exception $e) {
            $date = Carbon::now();
            $week = $date->week;
            $year = $date->year;
        }
        
        $month = $date->month;
        $quarter = ceil($month / 3);
        $halfYear = $month <= 6 ? 1 : 2;
        
        return [
            'station_name' => $stationName,
            'week_identifier' => $weekIdentifier,
            'week_start_date' => $date,
            'success_rate' => $successRate,
            'packages_closed' => $packagesClosed,
            'week_number' => (int)$week,
            'year' => (int)$year,
            'month' => $month,
            'quarter' => $quarter,
            'half_year' => $halfYear,
            'delta' => null,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
    
    private function extractPercentage($value)
    {
        if (is_string($value)) {
            return (float)str_replace('%', '', $value);
        }
        return (float)$value;
    }
}