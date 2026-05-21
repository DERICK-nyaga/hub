<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'theme',
        'brightness',
    ];

    protected $casts = [
        'brightness' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isDarkMode(): bool
    {
        return $this->theme === 'dark';
    }

    public function getBrightnessPercentage(): int
    {
        return $this->brightness ?? 100;
    }
    
}