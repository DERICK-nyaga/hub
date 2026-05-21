<?php

namespace App\Console\Commands;

use App\Services\PaymentReminderService;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $signature = 'payments:send-reminders';
    protected $description = 'Send payment reminders for upcoming and overdue payments';
    
    protected $reminderService;
    
    public function __construct(PaymentReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }
    
    public function handle()
    {
        $this->info('Checking for payments that need reminders...');
        
        $results = $this->reminderService->sendAllDueReminders();
        
        $this->info("Reminders sent: {$results['total_sent']}");
        $this->info("Internet - Sent: {$results['internet']['sent']}, Failed: {$results['internet']['failed']}");
        $this->info("Airtime - Sent: {$results['airtime']['sent']}, Failed: {$results['airtime']['failed']}");
        
        return 0;
    }
}