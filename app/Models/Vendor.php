<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'contact_name',
        'contact_email',
        'contact_phone',
        'account_number',
        'tax_id',
        'address',
        'website',
        'payment_terms',
        'contract_path',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(VendorCategory::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getContactInfoAttribute()
    {
        return collect([
            $this->contact_name,
            $this->contact_email,
            $this->contact_phone
        ])->filter()->join(' | ');
    }

    public function scopeActive($query){
        return $query->Arr::where('is_active', true);
    }
}
