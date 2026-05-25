<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Station;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\support\Facades\Schema;
use App\Traits\ConditionalSoftDeletes;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, ConditionalSoftDeletes;
    // use SoftDeletes;

    protected $dates = ['dismissed_at'];
    protected $casts = ['dismissed_at' => 'datetime'];
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
    
    public function newQueryWithoutScopes()
    {
        $query = parent::newQueryWithoutScopes();
        
        if (Schema::hasColumn('employees', 'deleted_at')) {
            return $query;
        }
        
        return $query;
    }

    protected static function boot()
    {
        parent::boot();
        static::bootConditionalSoftDeletes();

        static::created(function ($employee) {
            $employee->syncToSalaryTable();
        });
        
        static::updated(function ($employee) {
            $employee->syncToSalaryTable();
        });
        
        static::deleted(function ($employee) {
            $employee->removeFromSalaryTable();
        });
    }
    
    public function syncToSalaryTable()
    {
        \App\Models\SalaryEmployee::updateOrCreate(
            ['phone' => $this->phone], 
            [
                'name' => $this->full_name,
                'phone' => $this->phone,
                'position' => $this->position,
                'station' => $this->station?->name ?? 'Main Office',
                'status' => $this->status,
                'base_salary' => $this->salary,
                'bank_account' => $this->bank_account ?? null,
                'mpesa_number' => $this->phone,
            ]
        );
    }
    
    public function removeFromSalaryTable()
    {
        \App\Models\SalaryEmployee::where('phone', $this->phone)->delete();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OrderNumber::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(DeductionTransaction::class, 'employee_id', 'id');
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

    public function scopeOnLeave($query)
    {
        return $query->where('status', 'on_leave');
    }

    public function scopeTerminated($query)
    {
        return $query->where('status', 'terminated');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsOnLeaveAttribute(): bool
    {
        return $this->status === 'on_leave';
    }

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

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->whereNull('dismissed_at');
    }
    
    public function scopeNotDismissed($query)
    {
        return $query->whereNull('dismissed_at');
    }
    
    public function terminationLogs()
    {
        return $this->hasMany(TerminationLog::class);
    }
    
    public function getIsDismissedAttribute()
    {
        return !is_null($this->dismissed_at) || $this->status === 'terminated';
    }

    public function latestTerminationLog(): HasOne
    {
        return $this->hasOne(TerminationLog::class)->latestOfMany();
    }

    public function activeTerminationLog(): HasOne
    {
        return $this->hasOne(TerminationLog::class)
            ->where('is_reversed', false)
            ->latestOfMany();
    }

    public function dismissedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dismissed_by');
    }

    public function salaryEmployee(): HasOne
    {
        return $this->hasOne(SalaryEmployee::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function stations()
    {
        return $this->belongsToMany(Station::class, 'employee_station', 'employee_id', 'station_id')
                    ->withPivot('deleted_at', 'assigned_at', 'assigned_by')
                    ->whereNull('employee_station.deleted_at');
    }

    public function allStations()
    {
        return $this->belongsToMany(Station::class, 'employee_station', 'employee_id', 'station_id')
                    ->withPivot('deleted_at', 'assigned_at', 'assigned_by');
    }

    public function scopeForReports($query)
    {
        return $query->withTrashed();
    }

    public function scopeForTransactions($query)
    {
        return $query->whereNull('dismissed_at')->where('status', 'active');
    }
}
