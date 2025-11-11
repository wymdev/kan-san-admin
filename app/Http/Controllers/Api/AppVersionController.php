<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppVersionController extends Controller
{
    /**
     * Check for app updates
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkUpdate(Request $request): JsonResponse
    {
        // Validate API Key
        $apiKey = $request->header('X-API-KEY');
        if ($apiKey !== config('services.api_key')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid API Key',
            ], 401);
        }

        // Validate request
        $validated = $request->validate([
            'version_code' => 'required|integer',
            'platform' => 'required|in:android,ios',
        ]);

        $currentVersionCode = $validated['version_code'];
        $platform = $validated['platform'];

        try {
            $updateInfo = AppVersion::checkUpdateRequired($currentVersionCode, $platform);

            return response()->json([
                'success' => true,
                'data' => $updateInfo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while checking for updates',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get latest version info
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getLatest(Request $request): JsonResponse
    {
        // Validate API Key
        $apiKey = $request->header('X-API-KEY');
        if ($apiKey !== config('services.api_key')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid API Key',
            ], 401);
        }

        $platform = $request->input('platform', 'both');

        $latestVersion = AppVersion::where('platform', $platform)
            ->where('is_active', true)
            ->where('is_latest', true)
            ->first();

        if (!$latestVersion) {
            return response()->json([
                'success' => false,
                'message' => 'No active version found for this platform',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'version' => $latestVersion->version,
                'version_code' => $latestVersion->version_code,
                'platform' => $latestVersion->platform,
                'release_notes' => $latestVersion->release_notes,
                'download_url' => $latestVersion->download_url,
                'features' => $latestVersion->features,
                'bug_fixes' => $latestVersion->bug_fixes,
                'release_date' => $latestVersion->release_date,
                'force_update' => $latestVersion->force_update,
            ],
        ]);
    }

    /**
     * Get version history
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        // Validate API Key
        $apiKey = $request->header('X-API-KEY');
        if ($apiKey !== config('services.api_key')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid API Key',
            ], 401);
        }

        $platform = $request->input('platform', 'both');
        $limit = $request->input('limit', 10);

        $versions = AppVersion::where('platform', $platform)
            ->where('is_active', true)
            ->orderBy('version_code', 'desc')
            ->limit($limit)
            ->get(['version', 'version_code', 'release_notes', 'release_date', 'features', 'bug_fixes']);

        return response()->json([
            'success' => true,
            'data' => $versions,
        ]);
    }
}
