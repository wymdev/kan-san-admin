<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PulseAuthorize
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow all authenticated users to access Pulse
        // For production, you can restrict to specific roles:
        // return auth()->check() && auth()->user()->hasRole('admin') 
        //     ? $next($request) 
        //     : abort(403);

        return auth()->check()
            ? $next($request)
            : abort(403, 'Unauthorized access to Pulse dashboard.');
    }
}
