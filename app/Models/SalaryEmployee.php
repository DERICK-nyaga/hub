<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryEmployee extends Model
{
    use HasFactory;
    
    protected $table = 'salary_employees';
    protected $fillable = [
        'fullname',
        'name', 
        'phone', 
        'position', 
        'station', 
        'status',
        'status_notes',
        'dismissal_date',
        'dismissal_reason',
        'dismissal_id',
        'needs_review',
        'review_reason',
        'review_date', 
        'base_salary', 
        'bank_account', 
        'mpesa_number',
    ];
    
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DISMISSED = 'dismissed';
    const STATUS_TERMINATED = 'terminated';

    protected $appends = ['full_name'];
    
    public function getFullNameAttribute()
    {
        return $this->name ?? $this->full_name ?? 'Unknown';
    }

    public function payments()
    {
        return $this->hasMany(SalaryPayment::class, 'employee_id');
    }

    public function schedules()
    {
        return $this->hasMany(SalaryPaymentSchedule::class, 'employee_id');
    }
    
    public function getActiveDeductionsTotalAttribute()
    {
        return $this->deductions()->where('status', 'pending')->sum('amount');
    }

    public function dismissals()
    {
        return $this->hasMany(EmployeeDismissal::class, 'employee_id');
    }
    
    public function activeDismissal()
    {
        return $this->hasOne(EmployeeDismissal::class, 'employee_id')->where('status', '!=', EmployeeDismissal::STATUS_COMPLETED);
    }
    
    public function markAsDismissed($dismissalId, $reason, $effectiveDate)
    {
        $this->status = self::STATUS_DISMISSED;
        $this->status_notes = $reason;
        $this->dismissal_date = $effectiveDate;
        $this->dismissal_reason = $reason;
        $this->dismissal_id = $dismissalId;
        $this->needs_review = false;
        $this->review_reason = null;
        $this->review_date = null;
        
        return $this->save();
    }

        
    public function deductions()
    {
        if (class_exists(\App\Models\SalaryDeduction::class)) {
            return $this->hasMany(SalaryDeduction::class, 'employee_id');
        }
        return $this->hasMany(EmployeeDeduction::class, 'employee_id');
    }
}