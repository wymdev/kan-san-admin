<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'phone_number',
        'password',
        'full_name',
        'gender',
        'dob',
        'thai_pin',
        'email',
        'address',
        'expo_push_token',      
        'push_token_updated_at',
    ];

    protected $hidden = [
        'password',
        'thai_pin',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'dob' => 'date',
            'push_token_updated_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
    
    public function scopeWithPushToken($query)
    {
        return $query->whereNotNull('expo_push_token');
    }
    public function pushTokens(): HasMany
    {
        return $this->hasMany(DevicePushToken::class);
    }

    /**
     * Get active push tokens for this customer
     */
    public function activePushTokens(): HasMany
    {
        return $this->hasMany(DevicePushToken::class)->where('is_active', true);
    }
}
