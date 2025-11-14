<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /**
     * Display activity logs with advanced filters
     */
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with(['actor', 'loggable']);
        
        // Search in database
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('route', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }
        
        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        // Filter by actor type (User/Customer)
        if ($request->filled('actor_type')) {
            $query->where('actor_type', $request->actor_type);
        }
        
        // Filter by context
        if ($request->filled('context')) {
            $query->where('context', $request->context);
        }
        
        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
        } elseif ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        
        // Filter by response status
        if ($request->filled('status')) {
             $query->where('metadata->response_status', $request->status);
        }
        
        // Filter slow requests (> 1 second)
        if ($request->boolean('slow_only')) {
            $query->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.duration_ms')) AS UNSIGNED) > 1000");
        }
        
        // Filter failed requests (4xx and 5xx)
        if ($request->boolean('failed_only')) {
            $query->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.response_status')) AS UNSIGNED) >= 400");
        }
        
        // Get distinct values for filters
        $actions = ActivityLog::distinct()->pluck('action')->filter();
        $actorTypes = ActivityLog::distinct()->whereNotNull('actor_type')->pluck('actor_type')->filter();
        $contexts = ActivityLog::distinct()->pluck('context')->filter();
        
        // Handle export
        if ($request->has('export')) {
            return $this->exportToCsv($query->get());
        }
        
        // Paginate results
        $perPage = $request->get('per_page', 50);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return view('misc.admin.activity-logs.index', compact('logs', 'actions', 'actorTypes', 'contexts'));
    }
    
    /**
     * Show single activity log
     */
    public function show(ActivityLog $activityLog)
    {
        $activityLog->load(['actor', 'loggable']);
        
        return view('misc.admin.activity-logs.show', compact('activityLog'));
    }
    
    /**
     * Export logs to CSV
     */
    private function exportToCsv($logs)
    {
        $filename = 'activity-logs-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Action',
                'User Type',
                'User',
                'Subject Type',
                'Subject',
                'Context',
                'Status',
                'Duration (ms)',
                'IP Address',
                'Route',
                'Description',
                'Date Time'
            ]);
            
            // CSV Rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->action,
                    $log->actor_type ? class_basename($log->actor_type) : 'System',
                    $log->actor ? ($log->actor->name ?? $log->actor->email ?? 'N/A') : 'System',
                    $log->loggable_type ? class_basename($log->loggable_type) : '-',
                    $log->loggable ? ($log->loggable->getLogIdentifier() ?? $log->loggable->id) : '-',
                    $log->context,
                    $log->response_status ?? '-',
                    $log->duration_ms ?? '-',
                    $log->ip_address ?? '-',
                    $log->route ?? '-',
                    $log->description ?? '-',
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Get statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(7));
        $endDate = $request->get('end_date', now());
        
        $stats = [
            'total_actions' => ActivityLog::whereBetween('created_at', [$startDate, $endDate])->count(),
            'admin_actions' => ActivityLog::where('context', 'admin_portal')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'api_actions' => ActivityLog::where('context', 'api')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'failed_requests' => ActivityLog::where('response_status', '>=', 400)
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'slow_requests' => ActivityLog::where('duration_ms', '>', 1000)
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'avg_duration' => ActivityLog::whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('duration_ms')
                ->avg('duration_ms'),
            'top_actions' => ActivityLog::whereBetween('created_at', [$startDate, $endDate])
                ->select('action', DB::raw('COUNT(*) as count'))
                ->groupBy('action')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'top_users' => ActivityLog::whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('actor_id')
                ->select('actor_type', 'actor_id', DB::raw('COUNT(*) as count'))
                ->groupBy(['actor_type', 'actor_id'])
                ->orderByDesc('count')
                ->limit(10)
                ->with('actor')
                ->get(),
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Delete old logs
     */
    public function cleanup(Request $request)
    {
        $days = $request->get('days', 90);
        
        $deleted = ActivityLog::where('created_at', '<', now()->subDays($days))
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} log entries older than {$days} days"
        ]);
    }
}
