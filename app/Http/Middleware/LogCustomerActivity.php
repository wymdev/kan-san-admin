<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogCustomerActivity
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
        if (auth()->guard('customer')->check() || auth()->guard('sanctum')->check()) {
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
            $duration = (microtime(true) - $startTime) * 1000;
            
            $guard = auth()->guard('customer')->check() ? 'customer' : 'sanctum';
            $user = auth()->guard($guard)->user();
            
            ActivityLog::create([
                'actor_type' => get_class($user),
                'actor_id' => $user->id,
                'action' => $request->method(),
                'description' => $this->generateDescription($request),
                'context' => 'api',
                'guard' => $guard,
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
                    'api_version' => $request->header('Accept-Version'),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Customer activity logging failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Generate description
     */
    protected function generateDescription(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();
        
        return sprintf('[API] %s %s', $method, $path);
    }
    
    /**
     * Sanitize request data
     */
    protected function sanitizeRequestData(Request $request): array
    {
        $data = $request->except([
            'password',
            'password_confirmation',
            'token',
            'api_token',
            'card_number',
            'cvv',
        ]);
        
        if (count($data) > 50) {
            $data = ['__truncated' => 'Request body too large'];
        }
        
        return $data;
    }
}
