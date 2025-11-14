<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogApiController extends Controller
{
    /**
     * Get customer's own activity logs
     */
    public function index(Request $request)
    {
        $customer = auth()->guard('customer')->user();
        
        $logs = ActivityLog::where('actor_type', get_class($customer))
            ->where('actor_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json($logs);
    }
    
    /**
     * Log custom client-side action
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string|max:50',
            'description' => 'nullable|string',
            'route' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);
        
        $customer = auth()->guard('customer')->user();
        
        $log = ActivityLog::create([
            'actor_type' => get_class($customer),
            'actor_id' => $customer->id,
            'action' => $validated['action'],
            'description' => $validated['description'] ?? $validated['action'],
            'context' => 'api',
            'guard' => 'customer',
            'route' => $validated['route'] ?? $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => array_merge(
                $validated['metadata'] ?? [],
                [
                    'client_logged' => true,
                    'client_timestamp' => now()->toIso8601String(),
                ]
            ),
        ]);
        
        return response()->json([
            'message' => 'Activity logged successfully',
            'log' => $log
        ], 201);
    }
}
