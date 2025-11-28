<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'icon',
        'color',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Get formatted time ago
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get icon with default
     */
    public function getIconAttribute($value)
    {
        return $value ?? 'bell';
    }

    /**
     * Static method to create customer registration notification
     */
    public static function createCustomerRegistration($customer)
    {
        return self::create([
            'type' => 'customer_registered',
            'title' => 'New Customer Registered',
            'message' => $customer->name . ' has registered',
            'data' => [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
            ],
            'icon' => 'user-plus',
            'color' => 'success',
        ]);
    }

    /**
     * Static method to create new order notification
     */
    public static function createNewOrder($order, $customer)
    {
        return self::create([
            'type' => 'new_order',
            'title' => 'New Order Received',
            'message' => 'Order #' . $order->id . ' from ' . $customer->name,
            'data' => [
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'amount' => $order->total_amount ?? 0,
            ],
            'icon' => 'shopping-cart',
            'color' => 'primary',
        ]);
    }
}
