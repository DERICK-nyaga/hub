<?php

namespace App\Mail;

use App\Models\InternetPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $payment;
    public $messageData;
    
    public function __construct(InternetPayment $payment, array $messageData)
    {
        $this->payment = $payment;
        $this->messageData = $messageData;
    }
    
    public function build()
    {
        return $this->subject($this->messageData['subject'])
                    ->view('emails.payment-reminder')
                    ->with([
                        'payment' => $this->payment,
                        'emailMessage' => $this->messageData['email_message']
                    ]);
    }
}