<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CpanelService;
use Illuminate\Http\Request;

class CpanelStatsController extends Controller
{
    protected $cpanelService;
    
    public function __construct(CpanelService $cpanelService)
    {
        $this->cpanelService = $cpanelService;
    }
    
    /**
     * Display server statistics dashboard
     */
    public function index()
    {
        $cpanel = new CpanelService();

        return view('dashboards.cpanel-stats', [
            'stats' => $cpanel->getFormattedStats(),
            'cpu' => $cpanel->getCpuUsage(),
            'procs' => $cpanel->getProcessList(),
            'cron' => $cpanel->getCronJobs(),
            'ssl' => $cpanel->getSSLCertificates(),
            'aliases' => $cpanel->getDomainAliases(),
            'php' => $cpanel->getPHPVersions(),
            'connections' => $cpanel->getConnections(),
            'backups' => $cpanel->getBackupStatus(),
            'autossl' => $cpanel->getAutoSSLStatus(),
            'emailDisk' => $cpanel->getEmailDiskUsage()
        ]);
    }
    
    /**
     * Get statistics as JSON (for API endpoints)
     */
    public function getStatsJson()
    {
        $stats = $this->cpanelService->getFormattedStats();
        // dd($stats);
        if (empty($stats)) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch cPanel statistics'
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Refresh cached statistics
     */
    public function refresh()
    {
        $this->cpanelService->clearCache();
        $stats = $this->cpanelService->getFormattedStats();
        
        return redirect()->route('admin.cpanel.stats')
            ->with('success', 'Statistics refreshed successfully');
    }
}
