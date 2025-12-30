<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable , LogsActivity ;

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
        'is_blocked',
        'blocked_at',
        'blocked_by',
        'block_reason',
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
            'blocked_at' => 'datetime',
            'is_blocked' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope: Get non-blocked customers
     */
    public function scopeActive($query)
    {
        return $query->where('is_blocked', false);
    }

    /**
     * Scope: Get blocked customers
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    /**
     * Check if customer is blocked
     */
    public function isBlocked(): bool
    {
        return $this->is_blocked;
    }

    /**
     * Relationship: Customer blocked by admin user
     */
    public function blockedByUser()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Scope: Get customers with push tokens
     */
    public function scopeWithPushToken($query)
    {
        return $query->whereNotNull('expo_push_token');
    }

    /**
     * Relationship: Customer has many push tokens (devices)
     */
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

    /**
     * ✅ Get the primary (most recent) push token
     */
    public function primaryPushToken()
    {
        return $this->pushTokens()
            ->where('is_active', true)
            ->orderBy('last_seen_at', 'desc')
            ->first();
    }

    /**
     * ✅ Check if customer has any active push tokens
     */
    public function hasActivePushToken(): bool
    {
        return $this->pushTokens()->where('is_active', true)->exists();
    }

    /**
     * ✅ Get all active push token strings (useful for sending notifications to all devices)
     */
    public function getAllActivePushTokens(): array
    {
        return $this->pushTokens()
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();
    }

    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'actor')
            ->orderBy('created_at', 'desc');
    }
    
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable')
            ->orderBy('created_at', 'desc');
    }
    
    public function getLogIdentifier(): string
    {
        return $this->email ?? "#" . $this->id;
    }

    public function purchases()
    {
        return $this->hasMany(TicketPurchase::class)->orderBy('created_at', 'desc');
    }

    public function totalPurchases(): int
    {
        return $this->purchases()->whereIn('status', ['approved', 'won', 'not_won'])->count();
    }

    public function totalSpent(): float
    {
        return $this->purchases()->whereIn('status', ['approved', 'won', 'not_won'])->sum('total_price');
    }

    public function winCount(): int
    {
        return $this->purchases()->where('status', 'won')->count();
    }

    public function notWonCount(): int
    {
        return $this->purchases()->where('status', 'not_won')->count();
    }

    public function pendingCount(): int
    {
        return $this->purchases()->where('status', 'pending')->count();
    }

    public function approvedCount(): int
    {
        return $this->purchases()->where('status', 'approved')->count();
    }

    public function rejectedCount(): int
    {
        return $this->purchases()->where('status', 'rejected')->count();
    }

    public function winRate(): float
    {
        $checkedPurchases = $this->purchases()->whereIn('status', ['won', 'not_won'])->count();
        if ($checkedPurchases === 0) {
            return 0;
        }
        return round(($this->winCount() / $checkedPurchases) * 100, 2);
    }

    public function totalPrizeWon(): float
    {
        return $this->purchases()->where('status', 'won')->sum('prize_won');
    }

    public function biggestWin(): ?TicketPurchase
    {
        return $this->purchases()->where('status', 'won')->orderByDesc('prize_won')->first();
    }

    public function averagePrizePerWin(): float
    {
        $wins = $this->winCount();
        if ($wins === 0) {
            return 0;
        }
        return round($this->totalPrizeWon() / $wins, 2);
    }

    public function recentPurchases(int $limit = 10)
    {
        return $this->purchases()->with(['lotteryTicket'])->limit($limit)->get();
    }

    public function monthlyPurchases(int $months = 6)
    {
        return TicketPurchase::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN status IN ("won", "not_won") THEN 1 ELSE 0 END) as checked,
                SUM(total_price) as total_spent
            ')
            ->where('customer_id', $this->id)
            ->where('created_at', '>=', now()->subMonths($months))
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();
    }

    public function winLossTrend()
    {
        $data = TicketPurchase::selectRaw('
                DATE_FORMAT(checked_at, "%Y-%m-%d") as date,
                COUNT(CASE WHEN status = "won" THEN 1 END) as wins,
                COUNT(CASE WHEN status = "not_won" THEN 1 END) as losses
            ')
            ->where('customer_id', $this->id)
            ->whereNotNull('checked_at')
            ->whereIn('status', ['won', 'not_won'])
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return [
            'labels' => $data->pluck('date')->toArray(),
            'wins' => $data->pluck('wins')->toArray(),
            'losses' => $data->pluck('losses')->toArray(),
        ];
    }

    public function purchaseFrequency()
    {
        $firstPurchase = $this->purchases()->whereIn('status', ['approved', 'won', 'not_won'])->min('created_at');
        if (!$firstPurchase) {
            return 0;
        }

        $totalPurchases = $this->totalPurchases();
        $daysActive = now()->diffInDays($firstPurchase);

        if ($daysActive === 0) {
            return $totalPurchases;
        }

        return round($totalPurchases / $daysActive, 2);
    }
}