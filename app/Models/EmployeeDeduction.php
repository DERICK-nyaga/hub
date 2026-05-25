<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDeduction extends Model
{
    protected $table = 'employee_deductions';
    
    protected $fillable = [
        'employee_id',
        'dismissal_id',
        'amount',
        'reason',
        'deduction_type',
        'status',
        'created_by',
        'processed_by',
        'processed_at'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime'
    ];
    
    public function employee()
    {
        return $this->belongsTo(SalaryEmployee::class, 'employee_id');
    }
    
    public function dismissal()
    {
        return $this->belongsTo(EmployeeDismissal::class, 'dismissal_id');
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
    
    public function scopeByType($query, $type)
    {
        return $query->where('deduction_type', $type);
    }
}