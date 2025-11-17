<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Affiliate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'affiliate_code',
        'custom_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'country',
        'company_name',
        'website',
        'commission_rate',
        'status',
        'notes',
        'single_campaign_mode',
        'email_verified',
        'available_balance',
        'promotional_materials',
        'terms_and_conditions',
        'privacy_policy',
        'blocked',
        '2fa_active',
        'deleted',
        'manager',
        'referrer',
        'payout_groups',
        'payouts',
        'affiliate_group',
        'creation_date',
        'last_login',
        'additional_info',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'email_verified' => 'boolean',
        'blocked' => 'boolean',
        '2fa_active' => 'boolean',
        'deleted' => 'boolean',
        'creation_date' => 'datetime',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope to get only active affiliates
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get only inactive affiliates
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope to get only pending affiliates
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
