<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Boot the trait
     */
    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            self::logModelActivity('CREATE', $model, null, $model->getAttributes());
        });
        
        static::updated(function ($model) {
            if ($model->wasChanged()) {
                self::logModelActivity('UPDATE', $model, $model->getOriginal(), $model->getChanges());
            }
        });
        
        static::deleted(function ($model) {
            self::logModelActivity('DELETE', $model, $model->getAttributes(), null);
        });
        
        // If using soft deletes
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                self::logModelActivity('RESTORE', $model, null, $model->getAttributes());
            });
        }
    }
    
    /**
     * Log model activity
     */
    protected static function logModelActivity(
        string $action,
        $model,
        ?array $old,
        ?array $new
    ): void {
        // Detect authenticated user and context
        [$actorType, $actorId, $context, $guard] = self::detectAuthContext();
        
        // Skip logging if no authenticated user and not a system action
        if (!$actorType && !$actorId && !config('activity-log.log_system_actions', true)) {
            return;
        }
        
        try {
            ActivityLog::create([
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'loggable_type' => get_class($model),
                'loggable_id' => $model->getKey(),
                'action' => $action,
                'description' => self::generateModelDescription($action, $model),
                'old_values' => $old ? self::filterSensitiveData($old) : null,
                'new_values' => $new ? self::filterSensitiveData($new) : null,
                'context' => $context,
                'guard' => $guard,
                'route' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'model_class' => get_class($model),
                    'model_id' => $model->getKey(),
                    'changed_attributes' => $model->wasChanged() ? array_keys($model->getChanges()) : [],
                ]
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::error('Activity logging failed', [
                'error' => $e->getMessage(),
                'model' => get_class($model),
                'action' => $action
            ]);
        }
    }
    
    /**
     * Detect authentication context
     */
    protected static function detectAuthContext(): array
    {
        // Check web guard (admin users)
        if (Auth::guard('web')->check()) {
            return [
                'App\Models\User',
                Auth::guard('web')->id(),
                'admin_portal',
                'web'
            ];
        }
        
        // Check customer guard (API customers)
        if (Auth::guard('customer')->check()) {
            return [
                'App\Models\Customer',
                Auth::guard('customer')->id(),
                'api',
                'customer'
            ];
        }
        
        // Check default API guard
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            return [
                get_class($user),
                $user->id,
                'api',
                'api'
            ];
        }
        
        // Check sanctum guard
        if (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            return [
                get_class($user),
                $user->id,
                'api',
                'sanctum'
            ];
        }
        
        // System action (no authenticated user)
        return [null, null, 'system', null];
    }
    
    /**
     * Generate human-readable description
     */
    protected static function generateModelDescription(string $action, $model): string
    {
        $modelName = class_basename($model);
        $identifier = method_exists($model, 'getLogIdentifier') 
            ? $model->getLogIdentifier() 
            : "#" . $model->getKey();
        
        return sprintf('%s %s %s', $action, $modelName, $identifier);
    }
    
    /**
     * Filter sensitive data from logs
     */
    protected static function filterSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'api_token',
            'remember_token',
            'secret',
            'api_secret',
            'private_key',
            'card_number',
            'cvv',
            'ssn',
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***REDACTED***';
            }
        }
        
        return $data;
    }
}
