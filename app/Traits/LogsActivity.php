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
        [$actorType, $actorId, $context, $guard] = self::detectAuthContext();

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
            \Log::error('Activity logging failed', [
                'error' => $e->getMessage(),
                'model' => get_class($model),
                'action' => $action
            ]);
        }
    }

    /**
     * Log sensitive operation manually (for non-model actions)
     */
    public static function logSensitiveOperation(
        string $action,
        string $description,
        ?string $loggableType = null,
        ?int $loggableId = null,
        ?array $metadata = null
    ): ?ActivityLog {
        [$actorType, $actorId, $context, $guard] = self::detectAuthContext();

        try {
            return ActivityLog::create([
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'loggable_type' => $loggableType,
                'loggable_id' => $loggableId,
                'action' => $action,
                'description' => $description,
                'old_values' => null,
                'new_values' => null,
                'context' => $context,
                'guard' => $guard,
                'route' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => array_merge($metadata ?? [], [
                    'sensitive_operation' => true,
                    'logged_at' => now()->toIso8601String(),
                ])
            ]);
        } catch (\Exception $e) {
            \Log::error('Sensitive operation logging failed', [
                'error' => $e->getMessage(),
                'action' => $action,
                'description' => $description
            ]);
            return null;
        }
    }

    /**
     * Log login attempt
     */
    public static function logLoginAttempt(string $email, string $status, ?string $reason = null): void
    {
        [$actorType, $actorId, $context, $guard] = self::detectAuthContext();

        try {
            ActivityLog::create([
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'loggable_type' => null,
                'loggable_id' => null,
                'action' => 'LOGIN_' . strtoupper($status),
                'description' => "Login attempt: {$status}" . ($reason ? " - {$reason}" : ""),
                'context' => $context ?? 'system',
                'guard' => $guard,
                'route' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'email' => $email,
                    'status' => $status,
                    'failure_reason' => $reason,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Login attempt logging failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Log password change
     */
    public static function logPasswordChange(string $userType, int $userId): void
    {
        self::logSensitiveOperation(
            'PASSWORD_CHANGE',
            "Password changed for {$userType} #{$userId}",
            $userType,
            $userId,
            ['password_changed' => true]
        );
    }

    /**
     * Log account blocking
     */
    public static function logAccountBlock(string $userType, int $userId, ?string $reason = null): void
    {
        self::logSensitiveOperation(
            'ACCOUNT_BLOCK',
            "Account blocked: {$userType} #{$userId}" . ($reason ? " - Reason: {$reason}" : ""),
            $userType,
            $userId,
            ['blocked' => true, 'block_reason' => $reason]
        );
    }

    /**
     * Log account unblocking
     */
    public static function logAccountUnblock(string $userType, int $userId): void
    {
        self::logSensitiveOperation(
            'ACCOUNT_UNBLOCK',
            "Account unblocked: {$userType} #{$userId}",
            $userType,
            $userId,
            ['blocked' => false]
        );
    }

    /**
     * Log permission change
     */
    public static function logPermissionChange(int $userId, array $addedRoles, array $removedRoles): void
    {
        self::logSensitiveOperation(
            'PERMISSION_CHANGE',
            "Permission change for User #{$userId}: +" . implode(', ', $addedRoles) . " / -" . implode(', ', $removedRoles),
            'App\Models\User',
            $userId,
            ['added_roles' => $addedRoles, 'removed_roles' => $removedRoles]
        );
    }

    /**
     * Log data export
     */
    public static function logDataExport(string $exportType, ?int $targetId = null, ?int $recordCount = null): void
    {
        [$actorType, $actorId, $context, $guard] = self::detectAuthContext();

        self::logSensitiveOperation(
            'DATA_EXPORT',
            "Data export: {$exportType}" . ($recordCount ? " ({$recordCount} records)" : ""),
            $actorType,
            $actorId,
            [
                'export_type' => $exportType,
                'target_id' => $targetId,
                'record_count' => $recordCount,
            ]
        );
    }

    /**
     * Detect authentication context
     */
    protected static function detectAuthContext(): array
    {
        if (Auth::guard('web')->check()) {
            return [
                'App\Models\User',
                Auth::guard('web')->id(),
                'admin_portal',
                'web'
            ];
        }

        if (Auth::guard('customer')->check()) {
            return [
                'App\Models\Customer',
                Auth::guard('customer')->id(),
                'api',
                'customer'
            ];
        }

        if (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            return [
                get_class($user),
                $user->id,
                'api',
                'sanctum'
            ];
        }

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
            'thai_pin',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***REDACTED***';
            }
        }

        return $data;
    }
}
