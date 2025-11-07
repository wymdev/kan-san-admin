<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\JsonResponse;

class AppConfigApiController extends Controller
{
    /**
     * Get all active app configurations
     */
    public function index(): JsonResponse
    {
        $configs = AppConfig::all();
        
        $result = [];
        foreach ($configs as $config) {
            $result[$config->config_key] = $config->value_type === 'json' 
                ? json_decode($config->config_value, true)
                : ($config->value_type === 'boolean' ? (bool)$config->config_value : $config->config_value);
        }
        
        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get specific configuration by key
     */
    public function show($key): JsonResponse
    {
        $config = AppConfig::where('config_key', $key)->first();
        
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration not found',
            ], 404);
        }

        $value = $config->value_type === 'json' 
            ? json_decode($config->config_value, true)
            : ($config->value_type === 'boolean' ? (bool)$config->config_value : $config->config_value);

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $config->config_key,
                'value' => $value,
                'type' => $config->value_type,
            ],
        ]);
    }
}
