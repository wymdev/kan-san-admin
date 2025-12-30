<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoginActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'device_type',
        'browser',
        'os',
        'login_at',
        'logout_at',
        'status',
        'failure_reason',
        'remember_token',
        'expires_at',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_LOCKED = 'locked';

    public function user()
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('login_at', '>=', now()->subDays($days));
    }

    public function scopeByUser($query, $userType, $userId)
    {
        return $query->where('user_type', $userType)->where('user_id', $userId);
    }

    public function scopeIpAddress($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    public function getDeviceIconAttribute()
    {
        $device = strtolower($this->device_type ?? '');
        if (str_contains($device, 'mobile') || str_contains($device, 'phone')) {
            return 'smartphone';
        } elseif (str_contains($device, 'tablet')) {
            return 'tablet';
        } elseif (str_contains($device, 'desktop') || str_contains($device, 'computer')) {
            return 'monitor';
        }
        return 'globe';
    }

    public function getLocationAttribute()
    {
        $parts = array_filter([$this->city, $this->country]);
        return !empty($parts) ? implode(', ', $parts) : 'Unknown';
    }
}
