<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeQualification extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'qualification_type',
        'title',
        'institution',
        'grade',
        'year_obtained',
        'certificate_path'
    ];
    public function employee()
    {
        return $this->belongsTo(EmployeeProfile::class,'employee_id');
    }
}
