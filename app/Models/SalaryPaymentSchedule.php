<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPaymentSchedule extends Model
{
    use HasFactory;
    
    protected $table = 'salary_payment_schedules';
    
    protected $fillable = [
        'deduction_id',
        'employee_id', 
        'payment_id',
        'installment_number',
        'total_installments',
        'remaining_balance',
        'amount',
        'scheduled_date',
        'status',
        'type',
        'paid_at',
        'deducted_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'notes'
    ];
    
    protected $casts = [
        'scheduled_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'deducted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2'
    ];
    
    protected $attributes = [
        'status' => 'pending',
        'remaining_balance' => 0,
        'installment_number' => null,
        'total_installments' => null,
    ];
    
    public function deduction()
    {
        return $this->belongsTo(SalaryDeduction::class, 'deduction_id');
    }

    public function employee()
    {
        return $this->belongsTo(SalaryEmployee::class, 'employee_id');
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function payment()
    {
        return $this->belongsTo(SalaryPayment::class, 'payment_id');
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
    
    public function scopeDueThisMonth($query)
    {
        return $query->whereYear('scheduled_date', now()->year)
                     ->whereMonth('scheduled_date', now()->month)
                     ->where('status', 'pending');
    }
    
    // Helper methods
    public function isInstallment()
    {
        return !is_null($this->deduction_id);
    }
    
    public function markAsDeducted($paymentId = null)
    {
        $this->status = 'deducted';
        $this->deducted_at = now();
        if ($paymentId) {
            $this->payment_id = $paymentId;
        }
        return $this->save();
    }
    
    public function markAsPaid($paymentId = null)
    {
        $this->status = 'paid';
        $this->paid_at = now();
        if ($paymentId) {
            $this->payment_id = $paymentId;
        }
        return $this->save();
    }
    
    public function canBeEdited()
    {
        return $this->status === 'pending';
    }
    
    public function canBeApproved()
    {
        return $this->status === 'pending';
    }
    
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'approved']);
    }
}