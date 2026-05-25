<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'report_month',
        'data',
        'generated_at'
    ];

    protected $casts = [
        'report_month' => 'date',
        'data' => 'array',
        'generated_at' => 'datetime'
    ];
}