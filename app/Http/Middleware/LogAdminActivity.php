<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Process the request
        $response = $next($request);
        
        // Log after response
        if (auth()->guard('web')->check()) {
            $this->logActivity($request, $response, $startTime);
        }
        
        return $response;
    }
    
    /**
     * Log the activity
     */
    protected function logActivity(Request $request, Response $response, float $startTime): void
    {
        try {
            $duration = (microtime(true) - $startTime) * 1000; // Convert to ms
            
            ActivityLog::create([
                'actor_type' => 'App\Models\User',
                'actor_id' => auth()->guard('web')->id(),
                'action' => $request->method(),
                'description' => $this->generateDescription($request),
                'context' => 'admin_portal',
                'guard' => 'web',
                'route' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'request_body' => $this->sanitizeRequestData($request),
                    'response_status' => $response->getStatusCode(),
                    'duration_ms' => round($duration, 2),
                    'route_name' => $request->route()?->getName(),
                    'route_params' => $request->route()?->parameters(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Admin activity logging failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Generate description
     */
    protected function generateDescription(Request $request): string
    {
        $routeName = $request->route()?->getName();
        $method = $request->method();
        $path = $request->path();
        
        if ($routeName) {
            return sprintf('[ADMIN] %s %s (%s)', $method, $routeName, $path);
        }
        
        return sprintf('[ADMIN] %s %s', $method, $path);
    }
    
    /**
     * Sanitize request data
     */
    protected function sanitizeRequestData(Request $request): array
    {
        $data = $request->except([
            'password',
            'password_confirmation',
            '_token',
            'token',
            'api_token',
            'secret',
        ]);
        
        // Limit size to prevent huge logs
        if (count($data) > 50) {
            $data = ['__truncated' => 'Request body too large'];
        }
        
        return $data;
    }
}
