<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevicePushToken extends Model
{
    protected $fillable = [
        'token',
        'customer_id',
        'device_id',
        'platform',
        'app_version',
        'is_active',
        'last_seen_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get the customer that owns this token
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope to get only active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get anonymous tokens (no customer linked)
     */
    public function scopeAnonymous($query)
    {
        return $query->whereNull('customer_id');
    }

    /**
     * Scope to get authenticated tokens (customer linked)
     */
    public function scopeAuthenticated($query)
    {
        return $query->whereNotNull('customer_id');
    }

    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope to get inactive tokens
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
