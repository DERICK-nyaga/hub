<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $table = 'notification_logs';
    
    protected $fillable = [
        'payment_id',
        'payment_type',
        'reminder_type',
        'days_until_due',
        'sent_at',
        'station_id',
        'provider_id'
    ];
    
    protected $casts = [
        'sent_at' => 'datetime',
        'days_until_due' => 'integer'
    ];
    
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id');
    }
    
    public function provider(): BelongsTo
    {
        return $this->belongsTo(InternetProvider::class, 'provider_id', 'vendor_id');
    }
}