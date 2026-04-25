<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_profile_id',
        'document_type',
        'document_name',
        'file_path',
        'expiry_date',
        'is_verified',
        'remarks'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_verified' => 'boolean'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(EmployeeProfile::class,'employee_profile_id');
    }
}
