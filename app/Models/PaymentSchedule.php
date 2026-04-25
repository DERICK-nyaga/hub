<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSchedule extends Model
{
    protected $fillable = [
        'station_id',
        'vendor_id',
        'payment_type',
        'scheduled_date',
        'scheduled_amount',
        'frequency',
        'is_recurring',
        'auto_pay',
        'status',
        'next_schedule_date',
        'description'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'next_schedule_date' => 'date',
        'scheduled_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'auto_pay' => 'boolean'
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(InternetProvider::class, 'vendor_id');
    }
}
