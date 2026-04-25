<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingApproval extends Model
{
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'requester_id',
        'approver_id',
        'type',
        'status',
        'deadline',
        'priority',
        'comments',
        'data',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason'
    ];

    protected $casts = [
        'data' => 'array',
        'deadline' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime'
    ];

    public function approvable()
    {
        return $this->morphTo();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
