<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SecondarySalesTransaction extends Model
{
    protected $fillable = [
        'transaction_number',
        'sale_type',
        'secondary_ticket_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'purchased_at',
        'amount_thb',
        'amount_mmk',
        'is_paid',
        'payment_method',
        'payment_date',
        'status',
        'draw_result_id',
        'prize_won',
        'checked_at',
        'notes',
        'public_token',
        'batch_token',
        'created_by',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'is_paid' => 'boolean',
        'payment_date' => 'datetime',
        'checked_at' => 'datetime',
        'amount_thb' => 'decimal:2',
        'amount_mmk' => 'decimal:2',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_WON = 'won';
    const STATUS_NOT_WON = 'not_won';

    // Sale type constants
    const SALE_TYPE_OWN = 'own';      // Sell by my own (with customer info)
    const SALE_TYPE_OTHER = 'other';  // Sold by other (no customer info)

    /**
     * Get the secondary ticket for this transaction
     */
    public function secondaryTicket(): BelongsTo
    {
        return $this->belongsTo(SecondaryLotteryTicket::class, 'secondary_ticket_id');
    }

    /**
     * Get the customer for this transaction
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the draw result for this transaction
     */
    public function drawResult(): BelongsTo
    {
        return $this->belongsTo(DrawResult::class);
    }

    /**
     * Get the user who created this transaction
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate a unique transaction number
     */
    public static function generateTransactionNumber(): string
    {
        return 'SEC-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));
    }

    /**
     * Generate a unique public token for result checking
     */
    public static function generatePublicToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('public_token', $token)->exists());
        
        return $token;
    }

    /**
     * Get the public URL for result checking
     */
    public function getPublicResultUrlAttribute(): ?string
    {
        if (!$this->public_token) {
            return null;
        }
        return url('/lottery-result/' . $this->public_token);
    }

    /**
     * Check if this is sold by own (with customer)
     */
    public function isSoldByOwn(): bool
    {
        return $this->sale_type === self::SALE_TYPE_OWN;
    }

    /**
     * Check if this is sold by other (no customer)
     */
    public function isSoldByOther(): bool
    {
        return $this->sale_type === self::SALE_TYPE_OTHER;
    }

    /**
     * Get the display name for the customer
     */
    public function getCustomerDisplayNameAttribute(): string
    {
        if ($this->customer) {
            return $this->customer->full_name ?? $this->customer->phone_number;
        }
        return $this->customer_name ?? $this->customer_phone ?? 'Unknown';
    }

    /**
     * Get the display phone for the customer
     */
    public function getCustomerDisplayPhoneAttribute(): ?string
    {
        return $this->customer?->phone_number ?? $this->customer_phone;
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeWon($query)
    {
        return $query->where('status', self::STATUS_WON);
    }

    public function scopeNotWon($query)
    {
        return $query->where('status', self::STATUS_NOT_WON);
    }

    public function scopeUnchecked($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->whereNull('checked_at');
    }

    public function scopeChecked($query)
    {
        return $query->whereNotNull('checked_at');
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public function scopeNeedsChecking($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->whereNull('checked_at');
    }

    /**
     * Scope: filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('purchased_at', [$startDate, $endDate]);
    }

    /**
     * Scope: filter by customer search
     */
    public function scopeCustomerSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('customer_name', 'like', "%{$search}%")
              ->orWhere('customer_phone', 'like', "%{$search}%")
              ->orWhereHas('customer', function ($cq) use ($search) {
                  $cq->where('full_name', 'like', "%{$search}%")
                     ->orWhere('phone_number', 'like', "%{$search}%");
              });
        });
    }
}
