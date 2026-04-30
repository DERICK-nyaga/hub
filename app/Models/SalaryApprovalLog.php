<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryApprovalLog extends Model
{
    use HasFactory;
    
    protected $table = 'salary_approval_logs';
    protected $fillable = ['approvable_type', 'approvable_id', 'user_id', 'action', 'comments', 'old_data', 'new_data'];
    
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];
    
    public function approvable()
    {
        return $this->morphTo();
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}