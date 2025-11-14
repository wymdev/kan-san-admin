<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CpanelService
{
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $baseUrl;
    
    public function __construct()
    {
        $this->host = config('cpanel.host');
        $this->port = config('cpanel.port');
        $this->username = config('cpanel.username');
        $this->password = config('cpanel.password');
        $this->baseUrl = "https://{$this->host}:{$this->port}";
    }
    
    /**
     * Get account statistics from cPanel
     */
    public function getStats()
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withOptions(['verify' => false])
            ->get("https://{$this->host}:{$this->port}/execute/StatsBar/get_stats", [
                'display' => 'bandwidthusage|diskusage|emailaccounts|ftpaccounts|sqldatabases|subdomains|addondomains|parkeddomains'
            ]);

        // dd($response->body()); // This will show the actual response from cPanel

        if ($response->successful()) {
            return $response->json();
        }
        return null;
    }

    public function getCpuUsage()
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withOptions(['verify' => false])
            ->get("{$this->baseUrl}/execute/ResourceUsage/get_usages");
        return $response->successful() ? $response->json() : null;
    }

    public function getProcessList()
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withOptions(['verify' => false])
            ->get("{$this->baseUrl}/execute/Proc/listprocs");
        return $response->successful() ? $response->json() : null;
    }

    public function getCronJobs()
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withOptions(['verify' => false])
            ->get("{$this->baseUrl}/execute/Cron/listcron");
        return $response->successful() ? $response->json() : null;
    }

    public function getSSLCertificates()
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withOptions(['verify' => false])
            ->get("{$this->baseUrl}/execute/SSL/list_certs");
        return $response->successful() ? $response->json() : null;
    }

    public function getDomainAliases()
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withOptions(['verify' => false])
            ->get("{$this->baseUrl}/execute/Aliases/listaliases");
        return $response->successful() ? $response->json() : null;
    }

    public function getPHPVersions()
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withOptions(['verify' => false])
            ->get("{$this->baseUrl}/execute/PHP/get_installed_versions");
        return $response->successful() ? $response->json() : null;
    }

    public function getConnections()
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withOptions(['verify' => false])
            ->get("{$this->baseUrl}/execute/ServerInformation/fetch_information");
        return $response->successful() ? $response->json() : null;
    }

    public function getBackupStatus()
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withOptions(['verify' => false])
            ->get("{$this->baseUrl}/execute/Backup/list_backups");
        return $response->successful() ? $response->json() : null;
    }

    public function getAutoSSLStatus()
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withOptions(['verify' => false])
            ->get("{$this->baseUrl}/execute/AutoSSL/list_autossl_log");
        return $response->successful() ? $response->json() : null;
    }

    public function getEmailDiskUsage()
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withOptions(['verify' => false])
            ->get("{$this->baseUrl}/execute/Email/get_disk_usage");
        return $response->successful() ? $response->json() : null;
    }

    
    /**
     * Get formatted statistics for display
     */
    public function getFormattedStats()
    {
        $stats = $this->getStats();
        if (!$stats || !isset($stats['data'])) {
            return [];
        }
        $formatted = [];
        foreach ($stats['data'] as $stat) {
            $itemName = $stat['item']; // e.g. "Monthly Bandwidth Transfer"
            $apiToUiMap = [
                "Monthly Bandwidth Transfer" => "Bandwidth Usage",
                "Disk Space Usage" => "Disk Usage",
                "Email Accounts" => "Email Accounts",
                "FTP Accounts" => "FTP Accounts",
                "All SQL Databases" => "SQL Databases",
                "Subdomains" => "Subdomains",
                "Addon Domains" => "Addon Domains",
                "Parked Domains" => "Parked Domains",
            ];
            $uiName = $apiToUiMap[$itemName] ?? $itemName;
            $formatted[] = [
                'name'      => $uiName,
                'current'   => $stat['count'] ?? 0,
                'max'       => $stat['max'] ?? 'Unlimited',
                'unit'      => $stat['units'] ?? '',
                'percent'   => $stat['percent'] ?? 0,
            ];
        }
        return $formatted;
    }
    
    /**
     * Format stat name for display
     */
    protected function formatStatName($name)
    {
        $names = [
            'bandwidthusage' => 'Bandwidth Usage',
            'diskusage' => 'Disk Usage',
            'emailaccounts' => 'Email Accounts',
            'ftpaccounts' => 'FTP Accounts',
            'sqldatabases' => 'SQL Databases',
            'subdomains' => 'Subdomains',
            'addondomains' => 'Addon Domains',
            'parkeddomains' => 'Parked Domains'
        ];
        
        return $names[$name] ?? ucfirst($name);
    }
    
    /**
     * Clear cached stats
     */
    public function clearCache()
    {
        Cache::forget('cpanel_stats');
    }
}
