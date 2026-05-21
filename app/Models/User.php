<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
// use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;
    use HasFactory, Notifiable;
    // use HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function hasRole($role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        return $this->role === $role;
    }

    public function isHrManager(): bool
    {
        return $this->hasRole('hr_manager');
    }

    public function isDepartmentHead(): bool
    {
        return $this->hasRole('department_head');
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole('supervisor');
    }
    public function createdPayments()
    {
        return $this->hasMany(Payment::class, 'created_by');
    }

    public function approvedPayments()
    {
        return $this->hasMany(Payment::class, 'approved_by');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function preference()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function getThemeAttribute()
    {
        return Cache::remember("user_{$this->id}_theme", 3600, function () {
            return $this->preference?->theme ?? 'light';
        });
    }

    public function getBrightnessAttribute(): int
    {
        return $this->preference?->brightness ?? 100;
    }
}
