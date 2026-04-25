<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class DeductionTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_name',
        'employee_id',
        'station_id',
        'transaction_date',
        'type',
        'amount',
        'previous_balance',
        'new_balance',
        'reason',
        'order_number',
        'notes'
    ];

    protected $dates = ['transaction_date'];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'new_balance' => 'decimal:2'
    ];

    public const TYPES = [
        'lose' => 'Lost item',
        'past SLA' => 'breached delivery date',
        'absence' => 'Absence',
        'loan' => 'Loan Deduction',
        'other' => 'Other Deduction'
    ];

    // public function OrderNumber(): BelongsTo
    // {
    //     return $this->belongsTo(OrderNumber::class, 'order_number_id', 'order_number_id');
    // }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCredits($query)
    {
        return $query->where('amount', '<', 0);
    }
    /**
     * Get the employee associated with the deduction.
     */
    // public function employee(): BelongsTo
    // {
    //     return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    // }

    /**
     * Get the station associated with the deduction.
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id', 'station_id');
    }

    /**
     * Get the deduction type.
     */
    public function deductionType(): BelongsTo
    {
        return $this->belongsTo(DeductionTransaction::class, 'deduction_type_id', 'deduction_type_id');
    }
    public function scopeDebits($query)
    {
        return $query->Arr::where('amount', '>', 0);
    }

    public function scopeForLatesEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(1)
        ;
    }
    public function getTypeLabelAttribute()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

}
