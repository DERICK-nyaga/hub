<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'message',
        'user_id',
        'related_id',
        'related_type',
        'read_at',
        'metadata',
        'channel',
        'sent'
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent' => 'boolean',
        'read_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function related()
    {
        return $this->morphTo()->withTrashed();
    }

    // Custom relationship for station (through related)
    public function station()
    {
        if ($this->related && method_exists($this->related, 'station')) {
            return $this->related->station;
        }
        return null;
    }

    // Get related item URL
    public function getRelatedUrlAttribute()
    {
        if (!$this->related) return null;

        switch ($this->related_type) {
            case 'App\Models\AirtimePayment':
                return route('payments.airtime.index');

            case 'App\Models\InternetPayment':
                return route('payments.internet.index');

            case 'App\Models\PaymentSchedule':
                return route('payment-schedules.index');

            default:
                return route('dashboard');
        }
    }

    // Get notification icon
    public function getIconAttribute()
    {
        switch ($this->type) {
            case 'airtime_expiry':
                return 'fa-phone-alt text-warning';

            case 'internet_due':
                return 'fa-wifi text-info';

            case 'internet_overdue':
                return 'fa-wifi text-danger';

            case 'payment_schedule':
                return 'fa-calendar-alt text-primary';

            default:
                return 'fa-bell text-secondary';
        }
    }

    // Get notification color class
    public function getBadgeClassAttribute()
    {
        switch ($this->type) {
            case 'airtime_expiry':
                return 'bg-warning';

            case 'internet_due':
                return 'bg-info';

            case 'internet_overdue':
                return 'bg-danger';

            case 'payment_schedule':
                return 'bg-primary';

            default:
                return 'bg-secondary';
        }
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeUnsent($query)
    {
        return $query->where('sent', false);
    }

    public function scopeExpiryNotifications($query)
    {
        return $query->whereIn('type', ['airtime_expiry', 'internet_due', 'internet_overdue', 'payment_schedule']);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Mark as read
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
        return $this;
    }

    // Mark as sent
    public function markAsSent()
    {
        $this->update(['sent' => true]);
        return $this;
    }

    // Get metadata attribute
    public function getMetadataValue($key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }
}
