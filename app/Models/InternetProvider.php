<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternetProvider extends Model
{
    protected $primaryKey = 'vendor_id';

    protected $fillable = [
        'name',
        'category',
        'paybill_number',
        'account_prefix',
        'support_contact',
        'billing_email',
        'standard_amount',
        'due_day',
        'grace_period_days'
    ];

    protected $casts = [
        'standard_amount' => 'decimal:2',
        'due_day' => 'integer',
        'grace_period_days' => 'integer'
    ];

    public function internetPayments(): HasMany
    {
        return $this->hasMany(InternetPayment::class, 'vendor_id');
    }

    // Helper method to get full account number
    public function generateAccountNumber($stationId, $customSuffix = null)
    {
        $suffix = $customSuffix ?: str_pad($stationId, 4, '0', STR_PAD_LEFT);
        return $this->account_prefix . '-' . $suffix;
    }
}
