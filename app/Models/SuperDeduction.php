<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperDeduction extends Model
{
    protected $table = 'super_deductions';
    
    protected $fillable = [
        'employee_id',
        'created_by',
        'reason',
        'amount',
        'type',
        'deduction_date',
        'description',
        'justification',
        'status',
        'approval_level',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'requires_director_approval'
    ];
    
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_APPLIED = 'applied';
    
    public function employee()
    {
        return $this->belongsTo(SalaryEmployee::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
    
    public function approve($userId, $notes = null)
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->approval_notes = $notes;
        return $this->save();
    }
    
    public function reject($userId, $reason)
    {
        $this->status = self::STATUS_REJECTED;
        $this->rejected_by = $userId;
        $this->rejected_at = now();
        $this->rejection_reason = $reason;
        return $this->save();
    }
    
    public function apply()
    {
        $this->status = self::STATUS_APPLIED;
        return $this->save();
    }
}