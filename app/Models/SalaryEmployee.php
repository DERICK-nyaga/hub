<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryEmployee extends Model
{
    use HasFactory;
    
    protected $table = 'salary_employees';
    protected $fillable = ['name', 'phone', 'position', 'station', 'status', 'base_salary', 'bank_account', 'mpesa_number'];
    
    public function payments()
    {
        return $this->hasMany(SalaryPayment::class, 'employee_id');
    }
    
    public function deductions()
    {
        return $this->hasMany(SalaryDeduction::class, 'employee_id');
    }
    
    public function schedules()
    {
        return $this->hasMany(SalaryPaymentSchedule::class, 'employee_id');
    }
    
    public function getActiveDeductionsTotalAttribute()
    {
        return $this->deductions()->where('status', 'pending')->sum('amount');
    }
}