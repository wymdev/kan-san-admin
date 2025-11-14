<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\TicketPurchaseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DrawInfoController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AppVersionController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AppConfigController;
use App\Http\Controllers\DrawResultController;
use App\Http\Controllers\AppBannerController;
use App\Http\Controllers\AppPageController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DailyQuoteController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware(['guest','sanitizeInput', 'fileTypeCheck','throttle:9,10'])->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('auth/reset-password', function() {
        return view('auth.boxed-reset-password');
    })->name('password.request');
});

// Authenticated routes - OTP verification required
Route::middleware(['auth','sanitizeInput', 'fileTypeCheck','throttle:9,10'])->group(function () {
    Route::get('/verify-otp', [OtpController::class, 'verifyOtpForm'])->name('send.otp');
    Route::post('/verify-otp-action', [OtpController::class, 'verifyOtp'])->name('verify.otp');
    Route::post('/resend-otp', [OtpController::class, 'resendOtp'])->name('resend.otp');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});

// Protected routes - only after OTP verified
Route::middleware(['auth', 'otp.verified','sanitizeInput', 'fileTypeCheck'])->group(function () {
    Route::get('/', [RoutingController::class, 'index'])->name('root');
    Route::get('/dashboard', [RoutingController::class, 'index'])->name('dashboard');
    
    // User & Role Management
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('customers', CustomerController::class);
    
    // Lottery System
    Route::resource('tickets', TicketController::class);
    Route::resource('drawinfos', DrawInfoController::class);

    // ========================================
    // PURCHASE MANAGEMENT - CORRECT ORDER
    // ========================================
    // IMPORTANT: Specific routes MUST come BEFORE resource routes with wildcards
    
    // Lottery checking routes (specific routes first)
    Route::get('/purchases/check-results-page', [TicketPurchaseController::class, 'checkResultsPage'])
         ->name('purchases.check-results-page');
    Route::post('/purchases/check-results', [TicketPurchaseController::class, 'checkResults'])
         ->name('purchases.check-results');
    Route::post('/purchases/notify-results', [TicketPurchaseController::class, 'bulkNotifyResults'])
         ->name('purchases.notify-results');
    
    // Purchase resource routes (only index and show)
    Route::get('/purchases', [TicketPurchaseController::class, 'index'])
         ->name('purchases.index');
    Route::get('/purchases/{purchase}', [TicketPurchaseController::class, 'show'])
         ->name('purchases.show');
    
    // Purchase action routes (with {purchase} parameter)
    Route::post('/purchases/{purchase}/approve', [TicketPurchaseController::class, 'approve'])
         ->name('purchases.approve');
    Route::post('/purchases/{purchase}/reject', [TicketPurchaseController::class, 'reject'])
         ->name('purchases.reject');
    // ========================================

    // Mobile App Configuration
    Route::resource('app-configs', AppConfigController::class);
    Route::resource('app-banners', AppBannerController::class);
    Route::resource('app-pages', AppPageController::class);
    Route::resource('app-versions', AppVersionController::class);

    Route::get('/draw_results', [DrawResultController::class, 'index'])->name('draw_results.index');
    Route::get('/draw_results/syncLatest', [DrawResultController::class, 'syncLatest'])->name('draw_results.syncLatest');
    Route::get('/draw_results/syncAll', [DrawResultController::class, 'syncAll'])->name('draw_results.syncAll');
    Route::get('/draw_results/{id}/detail', [DrawResultController::class, 'showDetail'])->name('draw_results.show');
    Route::get('/draw_results/{id}', [DrawResultController::class, 'show']); // AJAX endpoint for modal
    
    // Announcements & Daily Quotes
    Route::resource('announcements', AnnouncementController::class);
    Route::resource('daily-quotes', DailyQuoteController::class);
    Route::post('/daily-quotes/{quote}/send-now', [DailyQuoteController::class, 'sendNow'])
         ->name('daily-quotes.send-now');
    
    // Catch-all routing
    Route::get('/{first}', [RoutingController::class, 'root'])->name('first');
    Route::get('/{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('/{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
});