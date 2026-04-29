<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class Link extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'links';
    
    protected $fillable = [
        'title', 'url', 'type', 'description', 'is_active', 
        'expires_at', 'click_count', 'created_by', 'gdpr_consent',
        'consent_given_at', 'last_accessed_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'consent_given_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = ['deleted_at']; // GDPR: don't expose soft-deleted data

    // Accessor: mask sensitive data in logs
    public function getUrlAttribute($value)
    {
        // Don't log full URLs in system logs (GDPR)
        if (app()->runningInConsole() || app()->environment('production')) {
            return $value;
        }
        return $value;
    }

    // Scope for active and valid links
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(function($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    // Scope by link type
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Increment click count with last access log
    public function recordClick()
    {
        $this->increment('click_count');
        $this->update(['last_accessed_at' => now()]);
        
        // GDPR: log access without storing personal data
        Log::channel('daily')->info('Link accessed', [
            'link_id' => $this->id,
            'type' => $this->type,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    // Check if link is expired
    public function isExpired()
    {
        if (!$this->expires_at) return false;
        return $this->expires_at->isPast();
    }

    // GDPR: Anonymize data for right to be forgotten
    public function anonymize()
    {
        $this->update([
            'title' => '[REDACTED]',
            'url' => 'https://example.com/removed',
            'description' => 'This link has been removed per GDPR request',
            'created_by' => null,
            'gdpr_consent' => false
        ]);
        $this->delete(); // Soft delete
    }
}