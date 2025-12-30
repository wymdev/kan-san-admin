<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class TicketPurchase extends Model
{
    use LogsActivity;


    protected $fillable = [
        'customer_id', 'lottery_ticket_id', 'order_number', 'quantity',
        'total_price', 'currency', 'status', 'payment_screenshot', 'rejection_reason',
        'approved_by', 'approved_at', 'draw_result_id', 'prize_won', 'checked_at'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'approved_at' => 'datetime',
        'checked_at' => 'datetime',
    ];

    // Currency constants
    const CURRENCY_THB = 'THB';
    const CURRENCY_MMK = 'MMK';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_WON = 'won';
    const STATUS_NOT_WON = 'not_won';

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

    public function drawResult(): BelongsTo
    {
        return $this->belongsTo(DrawResult::class);
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

    public function scopeWon($query)
    {
        return $query->where('status', 'won');
    }

    public function scopeNotWon($query)
    {
        return $query->where('status', 'not_won');
    }

    public function scopeUnchecked($query)
    {
        return $query->where('status', 'approved')
                    ->whereNull('checked_at');
    }

    public function scopeNeedsChecking($query)
    {
        return $query->where('status', 'approved')
                    ->whereNull('checked_at');
    }

    public function getLogIdentifier(): string
    {
        return "Order #" . $this->order_number;
    }
}