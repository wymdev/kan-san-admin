<?php

use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\LotteryTicketController;
use App\Http\Controllers\Api\DrawInfoController;
use App\Http\Controllers\Api\CustomerPurchaseController;
use Illuminate\Support\Facades\Route;

// Public endpoints
Route::get('public-tickets', [LotteryTicketController::class, 'index'])
    ->middleware(['sanitizeInput', 'fileTypeCheck', 'throttle:20,1']);

Route::get('draw-results/upcoming', [DrawInfoController::class, 'index'])
    ->middleware(['sanitizeInput', 'fileTypeCheck', 'throttle:20,1']);

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

    // Customer purchase actions
    Route::prefix('customer')->group(function () {
        Route::get('/tickets/available', [CustomerPurchaseController::class, 'availableTickets']);
        Route::post('/purchase', [CustomerPurchaseController::class, 'purchase']);
        Route::get('/purchases', [CustomerPurchaseController::class, 'myPurchases']);
        Route::get('/my-tickets', [CustomerPurchaseController::class, 'myTickets']);
    });
});
