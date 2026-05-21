<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SMSService
{
    protected $apiKey;
    protected $apiSecret;
    protected $senderId;
    protected $apiUrl;
    
    public function __construct()
    {
        $this->apiKey = config('services.sms.api_key');
        $this->apiSecret = config('services.sms.api_secret');
        $this->senderId = config('services.sms.sender_id', 'PAYMENT');
        $this->apiUrl = config('services.sms.api_url');
    }
    
    public function sendSMS(string $phoneNumber, string $message): bool
    {
        // Remove any non-numeric characters from phone number
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Ensure phone number starts with 254 for Kenya
        if (strlen($phoneNumber) == 9) {
            $phoneNumber = '254' . $phoneNumber;
        } elseif (strlen($phoneNumber) == 10 && substr($phoneNumber, 0, 1) == '0') {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        }
        
        try {
            // Example using Africa's Talking - modify based on your SMS provider
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->post($this->apiUrl . '/messaging', [
                    'username' => $this->apiKey,
                    'to' => $phoneNumber,
                    'from' => $this->senderId,
                    'message' => $message
                ]);
            
            if ($response->successful()) {
                Log::info("SMS sent to {$phoneNumber}: {$message}");
                return true;
            }
            
            Log::error("SMS failed: " . $response->body());
            return false;
            
        } catch (\Exception $e) {
            Log::error("SMS exception: " . $e->getMessage());
            return false;
        }
    }
}