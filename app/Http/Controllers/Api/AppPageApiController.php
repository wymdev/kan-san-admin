<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppPage;
use Illuminate\Http\JsonResponse;

class AppPageApiController extends Controller
{
    /**
     * Get page by slug (public endpoint - no auth required)
     */
    public function show($slug): JsonResponse
    {
        $page = AppPage::where('public_slug', $slug)
                      ->where('is_published', true)
                      ->first();
        
        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $page->id,
                'title' => $page->page_name,
                'content' => $page->content,
                'type' => $page->page_type,
                'updated_at' => $page->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get page by type (privacy, terms, etc)
     */
    public function byType($type): JsonResponse
    {
        $page = AppPage::where('page_type', $type)
                      ->where('is_published', true)
                      ->first();
        
        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $page->id,
                'title' => $page->page_name,
                'content' => $page->content,
                'type' => $page->page_type,
                'updated_at' => $page->updated_at->toIso8601String(),
            ],
        ]);
    }
}
