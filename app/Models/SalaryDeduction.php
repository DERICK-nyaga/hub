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
    'dismissal_id',
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
    

    // constants for statuses
    const STATUS_PENDING = 'pending';
    const STATUS_APPLIED = 'applied';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PENDING_DISMISSAL = 'pending_dismissal';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DISMISSED = 'dismissed';
    
    // constants for deduction types
    const TYPE_PENALTY = 'penalty';
    const TYPE_LOAN = 'loan';
    const TYPE_ADVANCE_RECOVERY = 'advance_recovery';
    const TYPE_LOSS = 'loss';
    const TYPE_OTHER = 'other';
    
    // deduction_type values
    const DEDUCTION_TYPE_SINGLE = 'single';
    const DEDUCTION_TYPE_INSTALLMENT = 'installment';
    const DEDUCTION_TYPE_DISMISSAL_PENDING = 'dismissal_pending';
    
    // Validation rules for status
    public static $validStatuses = [
        self::STATUS_PENDING,
        self::STATUS_APPLIED,
        self::STATUS_CANCELLED,
        self::STATUS_REJECTED,
        self::STATUS_PENDING_DISMISSAL,
        self::STATUS_COMPLETED,
        self::STATUS_DISMISSED
    ];
    
    // Validation rules for type
    public static $validTypes = [
        self::TYPE_PENALTY,
        self::TYPE_LOAN,
        self::TYPE_ADVANCE_RECOVERY,
        self::TYPE_LOSS,
        self::TYPE_OTHER
    ];
    
    // Validation rules for deduction_type
    public static $validDeductionTypes = [
        self::DEDUCTION_TYPE_SINGLE,
        self::DEDUCTION_TYPE_INSTALLMENT,
        self::DEDUCTION_TYPE_DISMISSAL_PENDING
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

    public function dismissal()
    {
        return $this->hasOne(EmployeeDismissal::class, 'deduction_id');
    }
}