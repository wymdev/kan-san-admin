<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketPurchase extends Model
{
    protected $fillable = [
        'customer_id', 'lottery_ticket_id', 'order_number', 'quantity',
        'total_price', 'status', 'payment_screenshot', 'rejection_reason',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lotteryTicket(): BelongsTo
    {
        return $this->belongsTo(LotteryTicket::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
