<?php

namespace App\Services;

use App\Models\AppConfig;

class AppVersionService
{
    public function getLatestVersion(): array
    {
        return [
            'ios' => AppConfig::get('app_version_ios_latest', '1.0.0'),
            'android' => AppConfig::get('app_version_android_latest', '1.0.0'),
            'ios_minimum' => AppConfig::get('app_version_ios_minimum', '1.0.0'),
            'android_minimum' => AppConfig::get('app_version_android_minimum', '1.0.0'),
            'force_update_ios' => AppConfig::get('app_force_update_ios', false),
            'force_update_android' => AppConfig::get('app_force_update_android', false),
        ];
    }

    public function checkUpdate(string $platform, string $currentVersion): array
    {
        $versions = $this->getLatestVersion();
        $key = $platform === 'ios' ? 'ios' : 'android';
        $minKey = $key . '_minimum';
        $forceUpdateKey = 'force_update_' . $key;

        $latestVersion = $versions[$key];
        $minimumVersion = $versions[$minKey];
        $forceUpdate = $versions[$forceUpdateKey];

        $needsUpdate = $this->compareVersions($currentVersion, $latestVersion) < 0;
        $forcedUpdate = $this->compareVersions($currentVersion, $minimumVersion) < 0;

        return [
            'update_available' => $needsUpdate,
            'force_update' => $forcedUpdate && $forceUpdate,
            'latest_version' => $latestVersion,
            'current_version' => $currentVersion,
        ];
    }

    private function compareVersions(string $version1, string $version2): int
    {
        return version_compare($version1, $version2);
    }
}
