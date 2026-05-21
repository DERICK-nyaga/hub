<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryDeduction extends Model
{
    use HasFactory;
    
    protected $table = 'salary_deductions';
    protected $fillable = ['employee_id', 
    'payment_id',
    'reason', 
    'amount', 
    'type', 
    'deduction_date', 
    'status', 
    'description',
    'deduction_type',
    'number_of_installments',
    'installment_amount',
    'requires_dismissal',
    'dismissal_letter_path',
    'dismissal_notes',
    'dismissal_processed_at',
    'dismissal_processed_by'
    
    ];
    
    protected $casts = [
        'deduction_date' => 'date',
        'requires_dismissal' => 'boolean',
        'dismissal_processed_at' => 'datetime',
        'installment_amount' => 'decimal:2'
    ];
    
    public function employee()
    {
        return $this->belongsTo(SalaryEmployee::class, 'employee_id');
    }
    
    public function schedule(){
        return $this->hasMany(SalaryPayments::class, 'deduction_id');
    }

    public function payment()
    {
        return $this->belongsTo(SalaryPayment::class, 'payment_id');
    }
}