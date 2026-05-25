<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDismissal extends Model
{
    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    
    // Dismissal Type Constants
    const TYPE_GROSS_MISCONDUCT = 'gross_misconduct';
    const TYPE_PERFORMANCE = 'performance';
    const TYPE_ATTENDANCE = 'attendance';
    const TYPE_REDUNDANCY = 'redundancy';
    const TYPE_OTHER = 'other';
    
    protected $table = 'employee_dismissals';
    
    protected $fillable = [
        // Employee relations
        'employee_id',
        'deduction_id',  
        
        // Dismissal details
        'dismissal_type',
        'dismissal_reason',  
        'reason', 
        'details',
        'total_deductions_amount',
        'effective_date',
        'last_working_date',
        
        // Status tracking
        'status',
        
        // HR / Admin notes
        'hr_notes',
        'comments',
        
        // Request tracking
        'requested_by',
        'requested_at',
        
        // Approval tracking
        'approved_by',
        'approved_at',
        'approval_comments',
        
        // Rejection tracking
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        
        // Processing tracking
        'processed_by',
        'processed_at',
        'processing_notes',
        
        // Completion tracking
        'completed_by',
        'completed_at',
        'completion_comments',
        
        // Exit interview
        'exit_interview_completed',
        'exit_interview_notes',
        'exit_interview_date',
        
        // Clearance
        'clearance_completed',
        'clearance_notes',
        'clearance_completed_at',
        
        // Settlement
        'requires_settlement',
        'settlement_amount',
        'final_settlement_amount',
        'settlement_paid',
        'settlement_paid_at',
    ];
    
    protected $casts = [
        'effective_date' => 'date',
        'last_working_date' => 'date',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'exit_interview_completed' => 'boolean',
        'exit_interview_date' => 'date',
        'clearance_completed' => 'boolean',
        'clearance_completed_at' => 'datetime',
        'settlement_paid' => 'boolean',
        'settlement_paid_at' => 'datetime',
        'requires_settlement' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    protected $dates = [
        'effective_date',
        'last_working_date',
        'approved_at',
        'rejected_at',
        'processed_at',
        'completed_at',
        'exit_interview_date',
        'created_at',
        'updated_at'
    ];
        
    // employee associated with this dismissal
    public function employee(): BelongsTo
    {
        return $this->belongsTo(SalaryEmployee::class, 'employee_id', 'id');
    }
    
    // deduction that triggered this dismissal
    public function deduction(): BelongsTo
    {
        return $this->belongsTo(SalaryDeduction::class, 'deduction_id');
    }
    
    // user who requested the dismissal
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    
    // user who approved the dismissal
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    // user who rejected the dismissal
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
    
    // user who processed the dismissal
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
    
    // user who completed the dismissal
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // pending dismissals
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    
    // approved dismissals
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
    
    // rejected dismissals
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
    
    // processing dismissals
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }
    
    // completed dismissals
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
    // specific dismissal types
    public function scopeOfType($query, $type)
    {
        return $query->where('dismissal_type', $type);
    }
    
    public function approve($userId, $comments = null): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->approval_comments = $comments;
        return $this->save();
    }
    
    public function reject($userId, $reason): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->rejected_by = $userId;
        $this->rejected_at = now();
        $this->rejection_reason = $reason;
        return $this->save();
    }
    
    public function startProcessing($userId, $notes = null): bool
    {
        $this->status = self::STATUS_PROCESSING;
        $this->processed_by = $userId;
        $this->processed_at = now();
        $this->processing_notes = $notes;
        return $this->save();
    }
    
    public function process($userId, $notes = null): bool
    {
        $this->status = self::STATUS_PROCESSING;
        $this->processed_by = $userId;
        $this->processed_at = now();
        $this->processing_notes = $notes;
        return $this->save();
    }
    
    public function complete($userId, $comments = null): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_by = $userId;
        $this->completed_at = now();
        $this->completion_comments = $comments;
        return $this->save();
    }

    public function completeExitInterview($notes = null): bool
    {
        $this->exit_interview_completed = true;
        $this->exit_interview_notes = $notes;
        $this->exit_interview_date = now();
        return $this->save();
    }
    
    public function completeClearance($notes = null): bool
    {
        $this->clearance_completed = true;
        $this->clearance_notes = $notes;
        $this->clearance_completed_at = now();
        return $this->save();
    }
    
    public function recordSettlement($amount, $paidBy = null): bool
    {
        $this->settlement_amount = $amount;
        $this->final_settlement_amount = $amount;
        $this->settlement_paid = true;
        $this->settlement_paid_at = now();
        return $this->save();
    }
    
    public function requiresSettlement(): bool
    {
        return $this->requires_settlement && !$this->settlement_paid;
    }

    public function requiresExitInterview(): bool
    {
        return !$this->exit_interview_completed;
    }

    public function requiresClearance(): bool
    {
        return !$this->clearance_completed;
    }
    
    public function getCurrentStep(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'Pending Approval';
            case self::STATUS_APPROVED:
                return 'Awaiting Processing';
            case self::STATUS_PROCESSING:
                if (!$this->exit_interview_completed) {
                    return 'Exit Interview Pending';
                }
                if (!$this->clearance_completed) {
                    return 'Clearance Pending';
                }
                if ($this->requiresSettlement()) {
                    return 'Settlement Pending';
                }
                return 'Processing';
            case self::STATUS_COMPLETED:
                return 'Completed';
            case self::STATUS_REJECTED:
                return 'Rejected';
            default:
                return 'Unknown';
        }
    }
    
    public function getProgressPercentage(): int
    {
        if ($this->status === self::STATUS_COMPLETED) {
            return 100;
        }
        
        if ($this->status === self::STATUS_REJECTED) {
            return 0;
        }
        
        $steps = 0;
        $totalSteps = 4; // Approval, Processing, Exit Interview, Clearance
        
        if ($this->status === self::STATUS_APPROVED) {
            $steps = 1;
        } elseif ($this->status === self::STATUS_PROCESSING) {
            $steps = 2;
            if ($this->exit_interview_completed) $steps++;
            if ($this->clearance_completed) $steps++;
        }
        
        return round(($steps / $totalSteps) * 100);
    }

    public function getFormattedReasonAttribute(): string
    {
        return $this->dismissal_reason ?? $this->reason ?? 'No reason provided';
    }

    public function getFormattedTypeAttribute(): string
    {
        $types = [
            self::TYPE_GROSS_MISCONDUCT => 'Gross Misconduct',
            self::TYPE_PERFORMANCE => 'Performance Related',
            self::TYPE_ATTENDANCE => 'Attendance Related',
            self::TYPE_REDUNDANCY => 'Redundancy',
            self::TYPE_OTHER => 'Other'
        ];
        
        return $types[$this->dismissal_type] ?? ucfirst(str_replace('_', ' ', $this->dismissal_type));
    }
    
    public function getFormattedStatusAttribute(): string
    {
        $statuses = [
            self::STATUS_PENDING => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed'
        ];
        
        return $statuses[$this->status] ?? ucfirst($this->status);
    }
    
    public function getStatusBadgeClassAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'bg-yellow-100 text-yellow-800';
            case self::STATUS_APPROVED:
                return 'bg-green-100 text-green-800';
            case self::STATUS_REJECTED:
                return 'bg-red-100 text-red-800';
            case self::STATUS_PROCESSING:
                return 'bg-blue-100 text-blue-800';
            case self::STATUS_COMPLETED:
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
    
    public function getTotalSettlementAttribute(): float
    {
        return $this->final_settlement_amount ?? $this->settlement_amount ?? 0;
    }

    public function isFullyCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED &&
               $this->exit_interview_completed &&
               $this->clearance_completed &&
               (!$this->requires_settlement || $this->settlement_paid);
    }
}