<?php

namespace App\Services;

use App\Models\InternetPayment;
use App\Models\AirtimePayment;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class PaymentReminderService
{
    protected $smsService;
    
    public function __construct(SMSService $smsService = null)
    {
        $this->smsService = $smsService ?? new SMSService();
    }
    
    /**
     * Check and send all due reminders
     */
    public function sendAllDueReminders(): array
    {
        $results = [
            'internet' => $this->processInternetPaymentReminders(),
            'airtime' => $this->processAirtimeReminders(),
            'total_sent' => 0
        ];
        
        $results['total_sent'] = $results['internet']['sent'] + $results['airtime']['sent'];
        
        Log::info('Payment reminders processed', $results);
        
        return $results;
    }
    
    /**
     * Process internet payment reminders
     */
    protected function processInternetPaymentReminders(): array
    {
        $payments = InternetPayment::with(['station', 'provider'])
            ->where('status', 'pending')
            ->where('due_date', '>=', Carbon::today()->subDays(30))
            ->get();
        
        $sent = 0;
        $failed = 0;
        
        foreach ($payments as $payment) {
            $result = $this->sendInternetPaymentReminders($payment);
            if ($result) {
                $sent++;
            } else {
                $failed++;
            }
        }
        
        return ['sent' => $sent, 'failed' => $failed, 'total' => $payments->count()];
    }
    
    /**
     * Send all applicable reminders for an internet payment
     */
    protected function sendInternetPaymentReminders(InternetPayment $payment): bool
    {
        $dueDate = Carbon::parse($payment->due_date);
        $today = Carbon::today();
        $daysUntilDue = $today->diffInDays($dueDate, false);
        
        $reminderTypes = $this->determineRemindersToSend($daysUntilDue, $payment);
        
        if (empty($reminderTypes)) {
            return false;
        }
        
        $success = false;
        
        foreach ($reminderTypes as $reminderType) {
            if ($this->sendReminder($payment, $reminderType, $daysUntilDue)) {
                $success = true;
                $this->logReminder($payment, $reminderType, $daysUntilDue);
            }
        }
        
        return $success;
    }
    
    /**
     * Determine which reminders need to be sent
     */
    protected function determineRemindersToSend(int $daysUntilDue, InternetPayment $payment): array
    {
        $reminders = [];
        
        // Check if reminder already sent for these intervals
        $sentReminders = NotificationLog::where('payment_id', $payment->id)
            ->where('payment_type', 'internet')
            ->pluck('reminder_type')
            ->toArray();
        
        // 1 week reminder (7 days)
        if ($daysUntilDue == 7 && !in_array('1_week', $sentReminders)) {
            $reminders[] = '1_week';
        }
        
        // 3 days reminder
        if ($daysUntilDue == 3 && !in_array('3_days', $sentReminders)) {
            $reminders[] = '3_days';
        }
        
        // 1 day reminder
        if ($daysUntilDue == 1 && !in_array('1_day', $sentReminders)) {
            $reminders[] = '1_day';
        }
        
        // Due today reminder
        if ($daysUntilDue == 0 && !in_array('due_today', $sentReminders)) {
            $reminders[] = 'due_today';
        }
        
        // Overdue reminder (only once)
        if ($daysUntilDue < 0 && !in_array('overdue', $sentReminders)) {
            $reminders[] = 'overdue';
        }
        
        return $reminders;
    }
    
    /**
     * Send individual reminder
     */
    protected function sendReminder(InternetPayment $payment, string $reminderType, int $daysUntilDue): bool
    {
        $station = $payment->station;
        $provider = $payment->provider;
        $totalDue = (float) ($payment->total_due ?? $payment->amount);
        $formattedAmount = number_format($totalDue, 2);
        $dueDate = Carbon::parse($payment->due_date);
        
        $messageData = $this->getReminderMessage($reminderType, $station, $provider, $formattedAmount, $dueDate, $daysUntilDue);
        
        $sent = false;
        
        // Send Email
        if (!empty($station->contact_email)) {
            try {
                Mail::to($station->contact_email)
                    ->send(new \App\Mail\PaymentReminderMail($payment, $messageData));
                $sent = true;
                Log::info("{$reminderType} email reminder sent to {$station->contact_email} for payment {$payment->id}");
            } catch (\Exception $e) {
                Log::error("Email failed for payment {$payment->id}: " . $e->getMessage());
            }
        }
        
        // Send SMS
        if (!empty($station->contact_phone) && config('services.sms.enabled', false)) {
            try {
                $smsSent = $this->smsService->sendSMS($station->contact_phone, $messageData['sms_message']);
                if ($smsSent) {
                    $sent = true;
                    Log::info("{$reminderType} SMS reminder sent to {$station->contact_phone} for payment {$payment->id}");
                }
            } catch (\Exception $e) {
                Log::error("SMS failed for payment {$payment->id}: " . $e->getMessage());
            }
        }
        
        return $sent;
    }
    
    /**
     * Get reminder message based on type
     */
    protected function getReminderMessage(string $type, $station, $provider, string $amount, Carbon $dueDate, int $daysUntilDue): array
    {
        $stationName = $station->name;
        $paybill = $provider->paybill_number ?? 'XXXXXX';
        $accountNo = $payment->account_number ?? 'N/A';
        
        $messages = [
            '1_week' => [
                'subject' => "Payment Reminder: Internet Bill Due in 1 Week - {$stationName}",
                'email_message' => "Dear {$stationName} team,\n\nThis is a friendly reminder that your internet bill of KES {$amount} is due in 7 days on {$dueDate->format('l, F j, Y')}.\n\nPlease ensure payment is made on time to avoid service interruption.\n\nPayment Details:\n• Paybill Number: {$paybill}\n• Account Number: {$accountNo}\n• Amount: KES {$amount}\n\nThank you for your prompt payment.",
                'sms_message' => "REMINDER: {$stationName} internet bill KES {$amount} due in 7 days ({$dueDate->format('M j')}). Pay via Paybill {$paybill} Acc {$accountNo}"
            ],
            '3_days' => [
                'subject' => "URGENT: Internet Payment Due in 3 Days - {$stationName}",
                'email_message' => "Dear {$stationName} team,\n\nYour internet bill of KES {$amount} is due in 3 days on {$dueDate->format('l, F j, Y')}.\n\nPlease make payment immediately to avoid late fees or service disruption.\n\nPayment Details:\n• Paybill Number: {$paybill}\n• Account Number: {$accountNo}\n• Amount: KES {$amount}\n\nThank you for your attention.",
                'sms_message' => "URGENT: {$stationName} internet KES {$amount} due in 3 days ({$dueDate->format('M j')}). Pay via Paybill {$paybill} Acc {$accountNo}"
            ],
            '1_day' => [
                'subject' => "TOMORROW: Internet Payment Due - {$stationName}",
                'email_message' => "Dear {$stationName} team,\n\nIMPORTANT: Your internet bill of KES {$amount} is due TOMORROW ({$dueDate->format('l, F j, Y')}).\n\nPlease make payment today to ensure uninterrupted service.\n\nPayment Details:\n• Paybill Number: {$paybill}\n• Account Number: {$accountNo}\n• Amount: KES {$amount}\n\nThank you.",
                'sms_message' => "TOMORROW: {$stationName} internet KES {$amount} due {$dueDate->format('M j')}. Pay today via Paybill {$paybill} Acc {$accountNo}"
            ],
            'due_today' => [
                'subject' => "DUE TODAY: Internet Payment Required - {$stationName}",
                'email_message' => "Dear {$stationName} team,\n\nPAYMENT DUE TODAY: Your internet bill of KES {$amount} is due today, {$dueDate->format('l, F j, Y')}.\n\nPlease make payment immediately to avoid service interruption.\n\nPayment Details:\n• Paybill Number: {$paybill}\n• Account Number: {$accountNo}\n• Amount: KES {$amount}\n\nThank you for your prompt payment.",
                'sms_message' => "DUE TODAY: {$stationName} internet KES {$amount}. Pay NOW via Paybill {$paybill} Acc {$accountNo}"
            ],
            'overdue' => [
                'subject' => "OVERDUE: Internet Payment Past Due - {$stationName}",
                'email_message' => "Dear {$stationName} team,\n\nYOUR PAYMENT IS OVERDUE: Your internet bill of KES {$amount} was due on {$dueDate->format('l, F j, Y')} and is now {$daysUntilDue} days overdue.\n\nPlease make payment immediately to avoid service disconnection.\n\nPayment Details:\n• Paybill Number: {$paybill}\n• Account Number: {$accountNo}\n• Amount: KES {$amount}\n\nThank you for your prompt attention.",
                'sms_message' => "OVERDUE: {$stationName} internet KES {$amount} is {$daysUntilDue} days overdue. Pay NOW via Paybill {$paybill} Acc {$accountNo}"
            ]
        ];
        
        return $messages[$type] ?? $messages['1_week'];
    }
    
    /**
     * Log reminder sent
     */
    protected function logReminder(InternetPayment $payment, string $reminderType, int $daysUntilDue): void
    {
        NotificationLog::create([
            'payment_id' => $payment->id,
            'payment_type' => 'internet',
            'reminder_type' => $reminderType,
            'days_until_due' => $daysUntilDue,
            'sent_at' => now(),
            'station_id' => $payment->station_id,
            'provider_id' => $payment->vendor_id
        ]);
        
        // Update last_reminder_sent date on payment
        $payment->update(['last_reminder_sent' => now()]);
    }
    
    /**
     * Process airtime reminders (similar structure)
     */
    protected function processAirtimeReminders(): array
    {
        $payments = AirtimePayment::with('station')
            ->where('status', 'active')
            ->where('expected_expiry', '>=', Carbon::today()->subDays(30))
            ->get();
        
        $sent = 0;
        $failed = 0;
        
        foreach ($payments as $payment) {
            $result = $this->sendAirtimeReminders($payment);
            if ($result) {
                $sent++;
            } else {
                $failed++;
            }
        }
        
        return ['sent' => $sent, 'failed' => $failed, 'total' => $payments->count()];
    }
    
    /**
     * Send airtime expiry reminders
     */
    protected function sendAirtimeReminders(AirtimePayment $payment): bool
    {
        $expiryDate = Carbon::parse($payment->expected_expiry);
        $today = Carbon::today();
        $daysUntilExpiry = $today->diffInDays($expiryDate, false);
        
        $sentReminders = NotificationLog::where('payment_id', $payment->id)
            ->where('payment_type', 'airtime')
            ->pluck('reminder_type')
            ->toArray();
        
        $reminderTypes = [];
        
        if ($daysUntilExpiry == 7 && !in_array('1_week', $sentReminders)) {
            $reminderTypes[] = '1_week';
        }
        if ($daysUntilExpiry == 3 && !in_array('3_days', $sentReminders)) {
            $reminderTypes[] = '3_days';
        }
        if ($daysUntilExpiry == 1 && !in_array('1_day', $sentReminders)) {
            $reminderTypes[] = '1_day';
        }
        if ($daysUntilExpiry == 0 && !in_array('expiry_today', $sentReminders)) {
            $reminderTypes[] = 'expiry_today';
        }
        if ($daysUntilExpiry < 0 && !in_array('expired', $sentReminders)) {
            $reminderTypes[] = 'expired';
        }
        
        if (empty($reminderTypes)) {
            return false;
        }
        
        $success = false;
        foreach ($reminderTypes as $type) {
            if ($this->sendAirtimeReminder($payment, $type, $daysUntilExpiry)) {
                $success = true;
                $this->logAirtimeReminder($payment, $type, $daysUntilExpiry);
            }
        }
        
        return $success;
    }
    
    /**
     * Send individual airtime reminder
     */
    protected function sendAirtimeReminder(AirtimePayment $payment, string $type, int $daysUntilExpiry): bool
    {
        $station = $payment->station;
        $amount = number_format($payment->amount, 2);
        $expiryDate = Carbon::parse($payment->expected_expiry);
        
        $messages = [
            '1_week' => "REMINDER: {$station->name} airtime of KES {$amount} expires in 7 days ({$expiryDate->format('M j')}). Please top up.",
            '3_days' => "URGENT: {$station->name} airtime KES {$amount} expires in 3 days ({$expiryDate->format('M j')}). Top up now.",
            '1_day' => "TOMORROW: {$station->name} airtime KES {$amount} expires tomorrow ({$expiryDate->format('M j')}). Please top up today.",
            'expiry_today' => "EXPIRES TODAY: {$station->name} airtime KES {$amount} expires today. Please top up immediately.",
            'expired' => "EXPIRED: {$station->name} airtime of KES {$amount} has expired. Please top up to restore service."
        ];
        
        $sent = false;
        
        if (!empty($station->contact_email)) {
            try {
                Mail::to($station->contact_email)
                    ->send(new \App\Mail\AirtimeReminderMail($payment, $messages[$type]));
                $sent = true;
            } catch (\Exception $e) {
                Log::error("Airtime email failed: " . $e->getMessage());
            }
        }
        
        if (!empty($station->contact_phone) && config('services.sms.enabled', false)) {
            try {
                $smsSent = $this->smsService->sendSMS($station->contact_phone, $messages[$type]);
                if ($smsSent) $sent = true;
            } catch (\Exception $e) {
                Log::error("Airtime SMS failed: " . $e->getMessage());
            }
        }
        
        return $sent;
    }
    
    /**
     * Log airtime reminder
     */
    protected function logAirtimeReminder(AirtimePayment $payment, string $type, int $daysUntilExpiry): void
    {
        NotificationLog::create([
            'payment_id' => $payment->id,
            'payment_type' => 'airtime',
            'reminder_type' => $type,
            'days_until_due' => $daysUntilExpiry,
            'sent_at' => now(),
            'station_id' => $payment->station_id
        ]);
    }
}