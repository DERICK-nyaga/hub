<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPaymentSchedule extends Model
{
    use HasFactory;
    
    protected $table = 'salary_payment_schedules';
    protected $fillable = ['employee_id', 'scheduled_date', 'amount', 'status', 'type', 'approved_by', 'approved_at', 'notes'];
    
    protected $casts = [
        'scheduled_date' => 'date',
        'approved_at' => 'datetime',
    ];
    
    public function employee()
    {
        return $this->belongsTo(SalaryEmployee::class, 'employee_id');
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}