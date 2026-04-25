<?php

namespace App\Services;

class MpesaService
{
    public function stkPush(string $phoneNumber, $amount, $accountNumber, string $description)
    {
        return (object) [
            'ResponseCode' => '0',
            'ResponseDescription' => 'Success',
            'CheckoutRequestID' => 'CHK_' . uniqid(),
        ];
    }
}
