<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $station_id
 * @property int|null $vendor_id
 * @property string $title
 * @property string|null $description
 * @property float $amount
 * @property \Illuminate\Support\Carbon $due_date
 * @property string $status
 * @property string $type
 * @property string|null $attachment_path
 * @property bool $is_recurring
 * @property string|null $recurrence
 * @property \Illuminate\Support\Carbon|null $recurrence_ends_at
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \App\Models\Station $station
 * @property-read \App\Models\Vendor|null $vendor
 * @property-read \App\Models\User $creator
 * @property-read \App\Models\User|null $approver
 */
class Payment extends Model
{
    use HasFactory;

protected $fillable = [
    'station_id',
    'vendor_id',
    'title',
    'description',
    'amount',
    'due_date',
    'status',
    'type',
    'attachment_path',
    'is_recurring',
    'recurrence',
    'recurrence_ends_at',
    'created_by',
    'approved_by',
    'approved_at',
    'paid_at'
];

    protected $casts = [
        'due_date' => 'datetime:Y-m-d',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'recurrence_ends_at' => 'datetime:Y-m-d',
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean'
    ];

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id', 'station_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('due_date', '>=', now()->subDays(1))
            ->orderBy('due_date');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('due_date', '<', now());
    }
    public function statusBadgeColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'paid' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }
}
