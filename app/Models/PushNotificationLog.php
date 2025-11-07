<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotificationLog extends Model
{
    protected $fillable = [
        'customer_id',
        'notification_type',
        'title',
        'body',
        'payload',
        'expo_ticket_id',
        'status',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'json',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
