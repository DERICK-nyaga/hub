<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    use HasFactory;


    protected $primaryKey = 'station_id';
    protected $fillable = ['name', 'location', 'mobile_number', 'monthly_loss', 'deductions'];

        protected $casts = [
        'monthly_loss' => 'decimal:2',
        'deductions' => 'decimal:2',
        ];
    public function order(): HasMany
    {
        return $this->hasMany(OrderNumber::class, 'station_id', 'station_id');
    }
    public function deductions(): HasMany
    {
        return $this->hasMany(DeductionTransaction::class, 'station_id', 'station_id');
    }
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'station_id', 'station_id');
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'station_id', 'station_id');
    }

        public function internetPayments(): HasMany
    {
        return $this->hasMany(InternetPayment::class, 'station_id');
    }

    public function airtimePayments(): HasMany
    {
        return $this->hasMany(AirtimePayment::class, 'station_id');
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class, 'station_id');
    }
    public function serviceProviders(): BelongsToMany
    {
        return $this->belongsToMany(
            InternetProvider::class, 
            'station_service_providers', 
            'station_id', 
            'provider_id'
        )->withPivot('contract_number', 'start_date', 'end_date', 'status')
         ->withTimestamps();
    }
    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(
            Vendor::class,
            'station_vendors',
            'station_id',
            'vendor_id'
        )->withPivot('contract_date', 'service_type', 'status')
         ->withTimestamps();
    }
    public function getActiveEmployeesCountAttribute(): int
    {
        return $this->employees()->where('status', 'active')->count();
    }
    public function getCurrentMonthPaymentsAttribute(): float
    {
        $internetTotal = $this->internetPayments()
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
            
        $airtimeTotal = $this->airtimePayments()
            ->whereMonth('topup_date', now()->month)
            ->whereYear('topup_date', now()->year)
            ->sum('amount');
            
        return (float) ($internetTotal + $airtimeTotal);
    }
    public function getOverduePaymentsAttribute(): array
    {
        $overdueInternet = $this->internetPayments()
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->sum('amount');
            
        return [
            'internet' => (float) $overdueInternet,
            'total' => (float) $overdueInternet
        ];
    }
}
