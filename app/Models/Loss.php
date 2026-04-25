<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Loss extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'station_id',
        'employee_id',
        'amount',
        'description',
        'type',
        'date_occurred',
        'resolved',
        'resolution_notes'
    ];

    protected $casts = [
        'date_occurred' => 'date',
        'resolved' => 'boolean'
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
