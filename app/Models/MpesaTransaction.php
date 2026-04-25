<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaTransaction extends Model
{
    use HasFactory;

    protected $table = 'mpesa_transactions';

    protected $fillable = [
        'checkout_request_id',
        'payment_id',
        'phone_number',
        'amount',
        'status',
        'mpesa_receipt'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the payment that owns the M-Pesa transaction
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(InternetPayment::class, 'payment_id');
    }
}
