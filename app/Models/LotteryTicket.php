<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function purchases()
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
}

