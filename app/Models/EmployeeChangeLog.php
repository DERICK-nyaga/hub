<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeChangeLog extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'changed_by',
        'change_type',
        'field_name',
        'old_value',
        'new_value',
        'additional_data',
        'status',
        'requires_approval',
        'change_log'
    ];

    protected $casts = [
        'additional_data' => 'array',
        'requires_approval' => 'boolean'
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_profile_id');
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
