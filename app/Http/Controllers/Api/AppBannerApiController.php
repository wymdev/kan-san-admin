<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppBannerApiController extends Controller
{
    /**
     * Get active banners, optionally filtered by type
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type');
        
        $query = AppBanner::active()->orderBy('display_order');
        
        if ($type) {
            $query->ofType($type);
        }
        
        $banners = $query->get()->map(function ($banner) {
            return [
                'id' => $banner->id,
                'title' => $banner->title,
                'description' => $banner->description,
                'image_url' => $banner->image_url,
                'banner_type' => $banner->banner_type,
                'action_url' => $banner->action_url,
                'action_type' => $banner->action_type,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $banners,
        ]);
    }
}
