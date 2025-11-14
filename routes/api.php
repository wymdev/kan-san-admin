<?php

use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\PushTokenController;
use App\Http\Controllers\Api\LotteryTicketController;
use App\Http\Controllers\Api\DrawInfoController;
use App\Http\Controllers\Api\AppVersionController;
use App\Http\Controllers\Api\CustomerPurchaseController;
use App\Http\Controllers\Api\CustomerNotificationController;
use App\Http\Controllers\Api\ActivityLogApiController;
use App\Http\Controllers\Api\DrawResultApiController;
use Illuminate\Support\Facades\Route;


// Public endpoints
Route::get('public-tickets', [LotteryTicketController::class, 'index'])
    ->middleware(['sanitizeInput', 'fileTypeCheck']);

Route::get('upcoming-draw-date', [DrawInfoController::class, 'index'])
    ->middleware(['sanitizeInput', 'fileTypeCheck']);

// Public endpoints - App Configuration & Content
Route::get('/config', [AppConfigApiController::class, 'index'])
    ->middleware(['sanitizeInput', 'fileTypeCheck']);

Route::get('/config/{key}', [AppConfigApiController::class, 'show'])
    ->middleware(['sanitizeInput', 'fileTypeCheck']);

Route::get('/banners', [AppBannerApiController::class, 'index'])
    ->middleware(['sanitizeInput', 'fileTypeCheck']);

Route::prefix('draw-results')->group(function () {
    // GET /api/draw-results
    // GET /api/draw-results?latest=true
    // GET /api/draw-results?draw_date=YYYY-MM-DD
    Route::get('/', [DrawResultApiController::class, 'index']);


    // GET /api/draw-results/dates
    Route::get('/dates', [DrawResultApiController::class, 'dates']);


    // POST /api/draw-results/check
    Route::post('/check', [DrawResultApiController::class, 'checkLottery']);
});

Route::post('/version/check', [AppVersionController::class, 'checkUpdate']);
Route::get('/version/latest', [AppVersionController::class, 'getLatest']);
Route::get('/version/history', [AppVersionController::class, 'history']);

Route::get('/pages/{slug}', [AppPageApiController::class, 'show'])
    ->middleware(['sanitizeInput', 'fileTypeCheck']);

Route::get('/pages/by-type/{type}', [AppPageApiController::class, 'byType'])
    ->middleware(['sanitizeInput', 'fileTypeCheck']);

// NEW: Public Push Token Endpoints (NO AUTHENTICATION)
Route::prefix('push-tokens')->group(function () {
    Route::post('/register', [PushTokenController::class, 'registerAnonymous'])
        ->middleware(['sanitizeInput', 'fileTypeCheck']);
    Route::post('/deactivate', [PushTokenController::class, 'deactivate'])
        ->middleware(['sanitizeInput', 'fileTypeCheck']);
});

// Customer Auth endpoints
Route::prefix('auth')->group(function () {
    Route::post('/register', [CustomerAuthController::class, 'register'])
        ->middleware(['sanitizeInput', 'fileTypeCheck', 'throttle:5,10']);
    Route::post('/login', [CustomerAuthController::class, 'login'])
        ->middleware(['sanitizeInput', 'fileTypeCheck', 'throttle:20,1']);
    Route::post('/send-otp', [CustomerAuthController::class, 'sendOtp'])
        ->middleware(['sanitizeInput', 'fileTypeCheck', 'throttle:3,15']);
    Route::post('/welcome-email', [CustomerAuthController::class, 'sendWelcomeEmail'])
        ->middleware(['sanitizeInput', 'fileTypeCheck', 'throttle:2,60']);
    
    // Link token to authenticated user
    Route::post('/link-push-token', [PushTokenController::class, 'linkToCustomer'])
        ->middleware(['auth:sanctum', 'sanitizeInput', 'fileTypeCheck']);
        
    // Push Token Management
    Route::prefix('push-tokens')->group(function () {
        // Get all active tokens for current customer
        Route::get('/my-tokens', [PushTokenController::class, 'getCustomerTokens']);
        
        // Alternative endpoint (same as /auth/link-push-token)
        Route::post('/link', [PushTokenController::class, 'linkToCustomer']);
    });
    
});

// CUSTOMER ROUTES WITH TOKEN (auth:sanctum required)
Route::middleware(['auth:sanctum', 'sanitizeInput', 'fileTypeCheck'])->group(function () {
    // Profile & session
    Route::prefix('auth')->group(function () {
        Route::get('/me', [CustomerAuthController::class, 'me']);
        Route::put('/profile', [CustomerAuthController::class, 'update']);
        Route::post('/refresh', [CustomerAuthController::class, 'refreshToken']);
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
    });

    Route::get('/activity-logs', [ActivityLogApiController::class, 'index']);
    Route::post('/activity-logs', [ActivityLogApiController::class, 'store']);

    // Customer purchase actions
    Route::prefix('customer')->group(function () {
        Route::get('/tickets/available', [CustomerPurchaseController::class, 'availableTickets']);
        Route::post('/purchase', [CustomerPurchaseController::class, 'purchase']);
        Route::get('/purchases', [CustomerPurchaseController::class, 'myPurchases']);
        Route::get('/my-tickets', [CustomerPurchaseController::class, 'myTickets']);

        Route::get('/noti', [CustomerNotificationController::class, 'index']);
        Route::post('/noti/mark-read', [CustomerNotificationController::class, 'markAllRead']);
        Route::post('/noti/{id}/mark-read', [CustomerNotificationController::class, 'markRead']);

    });
});
