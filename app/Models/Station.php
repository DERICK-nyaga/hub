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
}
