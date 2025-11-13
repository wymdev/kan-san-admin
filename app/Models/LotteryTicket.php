<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LotteryTicket extends Model
{
    protected $fillable = [
        'ticket_name', 'signature', 'withdraw_date', 'ticket_type',
        'numbers', 'bar_code', 'period', 'big_num', 'set_no',
        'price', 'left_icon', 'description',
        'status', 'stock' 
    ];

    protected $casts = [
        'numbers' => 'array',
        'withdraw_date' => 'date',
        'price' => 'decimal:2'
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(TicketPurchase::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
                    ->where('stock', '>', 0)
                    ->whereDate('withdraw_date', '>=', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('withdraw_date', '>=', now()->toDateString());
    }

    public function scopePast($query)
    {
        return $query->where('withdraw_date', '<', now()->toDateString());
    }

    /**
     * Get the ticket number(s) as a string
     * Handles both array and string formats
     */
    public function getTicketNumberAttribute()
    {
        if (is_array($this->numbers)) {
            return implode(', ', $this->numbers);
        }
        return $this->numbers;
    }
}