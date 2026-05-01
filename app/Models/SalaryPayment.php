<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    use HasFactory;
    
    protected $table = 'salary_payments';
    protected $fillable = [
        'employee_id', 'amount', 'deductions_total', 'net_amount', 'type', 
        'payment_method', 'transaction_reference', 'status', 'payment_date', 
        'notes', 'approved_by', 'approved_at'
    ];
    
    protected $casts = [
        'payment_date' => 'date',
        'approved_at' => 'datetime',
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function deductions()
    {
        return $this->hasMany(SalaryDeduction::class, 'payment_id');
    }
}