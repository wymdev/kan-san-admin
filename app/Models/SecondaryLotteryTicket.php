<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecondaryLotteryTicket extends Model
{
    use HasUuids;

    protected $fillable = [
        'batch_number',
        'ticket_name',
        'signature',
        'withdraw_date',
        'ticket_type',
        'numbers',
        'bar_code',
        'period',
        'big_num',
        'set_no',
        'price',
        'source_image',
        'source_seller',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'numbers' => 'array',
        'withdraw_date' => 'date',
        'price' => 'decimal:2',
    ];

    /**
     * Get the transactions for this ticket
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(SecondarySalesTransaction::class, 'secondary_ticket_id');
    }

    /**
     * Get the user who created this ticket
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: tickets for a specific draw date
     */
    public function scopeForDrawDate($query, $date)
    {
        return $query->whereDate('withdraw_date', $date);
    }

    /**
     * Scope: tickets with pending transactions (not yet checked)
     */
    public function scopeWithPendingTransactions($query)
    {
        return $query->whereHas('transactions', function ($q) {
            $q->where('status', 'pending')->whereNull('checked_at');
        });
    }

    /**
     * Scope: upcoming draw dates
     */
    public function scopeUpcoming($query)
    {
        return $query->where('withdraw_date', '>=', now()->toDateString());
    }

    /**
     * Scope: past draw dates
     */
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
            return implode('', $this->numbers);
        }
        return $this->numbers;
    }

    /**
     * Get the ticket numbers as formatted string (with spaces)
     */
    public function getFormattedNumbersAttribute()
    {
        if (is_array($this->numbers)) {
            return implode(' ', $this->numbers);
        }
        return $this->numbers;
    }

    /**
     * Check if this ticket has been sold
     */
    public function isSold(): bool
    {
        return $this->transactions()->exists();
    }

    /**
     * Get total revenue from this ticket
     */
    public function getTotalRevenueAttribute()
    {
        return $this->transactions()->sum('amount_thb');
    }
}
