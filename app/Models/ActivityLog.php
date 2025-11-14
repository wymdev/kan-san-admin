<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;
    
    protected $fillable = [
        'actor_type',
        'actor_id',
        'loggable_type',
        'loggable_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'context',
        'route',
        'guard',
        'ip_address',
        'user_agent',
        'metadata',
        // Virtual columns are auto-generated, don't add them here
    ];
    
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'response_status' => 'integer',
        'duration_ms' => 'decimal:2',
    ];
    
    /**
     * Get the actor (User or Customer)
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
    
    /**
     * Get the loggable model
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
    
    /**
     * Scope: Admin portal activities
     */
    public function scopeAdminPortal(Builder $query): Builder
    {
        return $query->where('context', 'admin_portal');
    }
    
    /**
     * Scope: API activities
     */
    public function scopeApi(Builder $query): Builder
    {
        return $query->where('context', 'api');
    }
    
    /**
     * Scope: By actor type and ID
     */
    public function scopeByActor(Builder $query, string $actorType, ?int $actorId = null): Builder
    {
        $query->where('actor_type', $actorType);
        
        if ($actorId) {
            $query->where('actor_id', $actorId);
        }
        
        return $query;
    }
    
    /**
     * Scope: Filter by action
     */
    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }
    
    /**
     * Scope: Slow requests (duration > threshold)
     */
    public function scopeSlowRequests(Builder $query, float $thresholdMs = 1000): Builder
    {
        return $query->where('duration_ms', '>', $thresholdMs);
    }
    
    /**
     * Scope: Failed requests
     */
    public function scopeFailedRequests(Builder $query): Builder
    {
        return $query->whereNotNull('response_status')
            ->where('response_status', '>=', 400);
    }
    
    /**
     * Scope: Date range
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
