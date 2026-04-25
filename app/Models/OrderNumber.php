<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderNumber extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    // protected $primaryKey = 'order_id';

    public $incrementing = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'station_id',
        'employee_id',
        'order_date',
        'order_status',
        'total_amount',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the station that owns the order.
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id', 'station_id');
    }

    /**
     * employee assigned to the order.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * deductions for the order.
     */
    public function deductions(): HasMany
    {
        return $this->hasMany(DeductionTransaction::class, 'order_number', 'order_number');
    }

    /**
     *query to only include pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('order_status', 'pending');
    }

    /**
     * query to only include completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('order_status', 'completed');
    }

    /**
     * query to only include orders for a specific station.
     */
    public function scopeForStation($query, $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * query to only include orders for a specific employee.
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Check if the order has any deductions.
     */
    public function hasDeductions(): bool
    {
        return $this->deductions()->exists();
    }

    /**
     *total deductions amount for this order.
     */
    public function getTotalDeductionsAttribute()
    {
        return $this->deductions()->where('status', 'approved')->sum('deduction_amount');
    }

    /**
     *net amount after deductions.
     */
    public function getNetAmountAttribute()
    {
        return $this->total_amount - $this->total_deductions;
    }

    public function getFormattedOrderDateAttribute()
    {
        if (empty($this->order_date)) {
            return 'N/A';
        }

        try {
            if ($this->order_date instanceof \Carbon\Carbon) {
                return $this->order_date->format('Y-m-d');
            }

            if (is_string($this->order_date)) {
                return \Carbon\Carbon::parse($this->order_date)->format('Y-m-d');
            }

            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
        public function getEmployeeNameAttribute()
        {
            if ($this->employee) {
                $name = $this->employee->first_name . ' ' . $this->employee->last_name;
                if ($this->employee->employee_id) {
                    $name .= ' (' . $this->employee->employee_id . ')';
                }
                return $name;
            }

            return 'Not Assigned';
        }
        }
