<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Station;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    // protected $primaryKey = 'employee_id';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'station_id',
        'employee_id',
        'position',
        'salary',
        'hire_date',
        'status',
        'deduction_balance',
        'termination_reason',
        'termination_date',
        'leave_start_date',
        'leave_end_date',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(OrderNumber::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(DeductionTransaction::class, 'employee_id', 'employee_id');
    }

    public function getTotalDeductionsAttribute()
        {
            return $this->deductions()->sum('amount');
        }

    public function getNetSalaryAttribute()
    {
        return $this->salary - $this->total_deductions;
    }
    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id', 'station_id');
    }
    public function deductionTransactions()
    {
        return $this->hasMany(DeductionTransaction::class)->orderBy('transaction_date', 'desc');
    }
    public function deductionBalance()
    {
        return $this->hasOne(DeductionBalance::class);
    }
    public function getCurrentBalanceAttribute()
    {
        return $this->deductionBalance()->firstOrCreate([], ['balance' => 0])->balance;
    }

        public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
        /**
     * Scope a query to only include active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include employees on leave.
     */
    public function scopeOnLeave($query)
    {
        return $query->where('status', 'on_leave');
    }

    /**
     * Scope a query to only include terminated employees.
     */
    public function scopeTerminated($query)
    {
        return $query->where('status', 'terminated');
    }

    /**
     * Check if employee is currently active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if employee is currently on leave.
     */
    public function getIsOnLeaveAttribute(): bool
    {
        return $this->status === 'on_leave';
    }

    /**
     * Check if employee is terminated.
     */
    public function getIsTerminatedAttribute(): bool
    {
        return $this->status === 'terminated';
    }
    public function updateBalance()
    {
        $balance = $this->deductionTransactions()->sum('amount');

        $this->deductionBalance()->updateOrCreate(
            ['employee_id' => $this->id],
            ['balance' => $balance]
        );

        return $balance;
    }

    // public function updateDebtBalance()
    // {
    //     $this->debt_balance = $this->deductions()->sum('amount');
    //     $this->save();
    // }
}
