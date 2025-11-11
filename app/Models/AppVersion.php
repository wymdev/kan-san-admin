<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'version_code',
        'platform',
        'minimum_version',
        'minimum_version_code',
        'force_update',
        'release_notes',
        'download_url',
        'is_active',
        'is_latest',
        'release_date',
        'features',
        'bug_fixes',
        'display_order',
    ];

    protected $casts = [
        'force_update' => 'boolean',
        'is_active' => 'boolean',
        'is_latest' => 'boolean',
        'release_date' => 'datetime',
        'features' => 'array',
        'bug_fixes' => 'array',
        'version_code' => 'integer',
        'minimum_version_code' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Boot method to handle is_latest flag
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($version) {
            if ($version->is_latest) {
                // Set all other versions for this platform to not latest
                static::where('platform', $version->platform)
                    ->where('id', '!=', $version->id)
                    ->update(['is_latest' => false]);
            }
        });
    }

    /**
     * Scope: Get latest version for a platform
     */
    public function scopeLatestForPlatform($query, $platform)
    {
        return $query->where('platform', $platform)
            ->where('is_active', true)
            ->where('is_latest', true)
            ->first();
    }

    /**
     * Check if current version needs update
     */
    public static function checkUpdateRequired($currentVersionCode, $platform)
    {
        $latestVersion = static::where('platform', $platform)
            ->where('is_active', true)
            ->orderBy('version_code', 'desc')
            ->first();

        if (!$latestVersion) {
            return [
                'update_required' => false,
                'force_update' => false,
                'message' => 'No updates available'
            ];
        }

        $updateRequired = $currentVersionCode < $latestVersion->version_code;
        $forceUpdate = $latestVersion->minimum_version_code && 
                       $currentVersionCode < $latestVersion->minimum_version_code;

        return [
            'update_required' => $updateRequired,
            'force_update' => $forceUpdate || $latestVersion->force_update,
            'latest_version' => $latestVersion->version,
            'latest_version_code' => $latestVersion->version_code,
            'current_version_code' => $currentVersionCode,
            'release_notes' => $latestVersion->release_notes,
            'download_url' => $latestVersion->download_url,
            'features' => $latestVersion->features,
            'bug_fixes' => $latestVersion->bug_fixes,
            'release_date' => $latestVersion->release_date,
        ];
    }
}
