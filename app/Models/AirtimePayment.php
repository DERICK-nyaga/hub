<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AirtimePayment extends Model
{
    protected $fillable = [
        'station_id',
        'mobile_number',
        'amount',
        'topup_date',
        'last_topup_date',
        'expected_expiry',
        'status',
        'network_provider',
        'transaction_id',
        'notes'
    ];

    protected $casts = [
        'topup_date' => 'date',
        'last_topup_date' => 'date',
        'expected_expiry' => 'date',
        'amount' => 'decimal:2'
    ];

    protected $attributes = [
        'status' => 'active',
        'amount' => 0.00
    ];

    protected static function boot()
    {
        parent::boot();

        // Update status automatically when retrieving records
        static::retrieved(function ($payment) {
            $payment->updateStatusIfExpired();
        });

        // Update status automatically when saving
        static::saving(function ($payment) {
            $payment->updateStatusIfExpired();
        });
    }

    // Method to check and update status if expired
    public function updateStatusIfExpired()
    {
        // If already expired, don't check again
        if ($this->status === 'expired') {
            return;
        }

        // Check if expected expiry date has passed
        $expiryDate = Carbon::parse($this->expected_expiry);
        $today = Carbon::today();

        if ($today->gt($expiryDate)) {
            $this->status = 'expired';

            // Don't save here to avoid infinite loop
            // We'll save in the saving event
        }
    }

    // Accessor to check if payment is expired
    public function getIsExpiredAttribute()
    {
        $expiryDate = Carbon::parse($this->expected_expiry);
        $today = Carbon::today();
        return $today->gt($expiryDate);
    }

    // Accessor to get days since expiry
    public function getDaysSinceExpiryAttribute()
    {
        if (!$this->is_expired) {
            return 0;
        }

        $expiryDate = Carbon::parse($this->expected_expiry);
        $today = Carbon::today();
        return $today->diffInDays($expiryDate);
    }

    // Method to manually mark as expired
    public function markAsExpired()
    {
        $this->status = 'expired';
        $this->save();
        return $this;
    }

    // Method to renew/update airtime
    public function renewAirtime($amount, $topupDate = null)
    {
        $topupDate = $topupDate ? Carbon::parse($topupDate) : Carbon::today();

        $this->update([
            'amount' => $amount,
            'topup_date' => $topupDate->format('Y-m-d'),
            'last_topup_date' => $topupDate->format('Y-m-d'),
            'expected_expiry' => $topupDate->copy()->addMonth()->format('Y-m-d'),
            'status' => 'active'
        ]);

        return $this;
    }

    // Scope for active payments
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for expired payments
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    // Scope for payments expiring soon (within 3 days)
    public function scopeExpiringSoon($query)
    {
        $today = Carbon::today();
        $threeDaysFromNow = Carbon::today()->addDays(3);

        return $query->where('status', 'active')
                     ->whereBetween('expected_expiry', [$today, $threeDaysFromNow]);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    // In AirtimePayment model
public function isExpiringSoon()
{
    return $this->status === 'active' &&
           $this->expected_expiry &&
           $this->expected_expiry->between(now(), now()->addDays(3));
}

public function daysUntilExpiry()
{
    if (!$this->expected_expiry || !$this->status === 'active') {
        return null;
    }
    return now()->diffInDays($this->expected_expiry, false);
}

// In InternetPayment model
public function isDueSoon()
{
    return $this->status === 'pending' &&
           $this->due_date &&
           $this->due_date->between(now(), now()->addDays(3));
}

public function isOverdue()
{
    return $this->status === 'pending' &&
           $this->due_date &&
           $this->due_date->lt(now());
}

public function daysUntilDue()
{
    if (!$this->due_date || $this->status !== 'pending') {
        return null;
    }
    return now()->diffInDays($this->due_date, false);
}
}
