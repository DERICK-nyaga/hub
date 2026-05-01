<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryDeduction extends Model
{
    use HasFactory;
    
    protected $table = 'salary_deductions';
    protected $fillable = ['employee_id', 'payment_id', 'reason', 'amount', 'type', 'deduction_date', 'status', 'description'];
    
    protected $casts = [
        'deduction_date' => 'date',
    ];
    
    public function employee()
    {
        return $this->belongsTo(SalaryEmployee::class, 'employee_id');
    }
    
    public function payment()
    {
        return $this->belongsTo(SalaryPayment::class, 'payment_id');
    }
}