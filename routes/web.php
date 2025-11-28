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
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AnalyticsDashboardController;
use App\Http\Controllers\CustomerAnalyticsController;
use App\Http\Controllers\CpanelStatsController;

use Illuminate\Support\Facades\Route;

// Public pages (accessible without authentication)
Route::get('/privacy-policy', [App\Http\Controllers\PublicPageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-conditions', [App\Http\Controllers\PublicPageController::class, 'termsConditions'])->name('terms-conditions');
Route::get('/about-us', [App\Http\Controllers\PublicPageController::class, 'aboutUs'])->name('about-us');

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
    // Redirect to Analytics Dashboard
    Route::get('/', function() {
        return redirect()->route('analytics.index');
    })->name('root');
    
    Route::get('/dashboard', function() {
        return redirect()->route('analytics.index');
    })->name('dashboard');
    
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


    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/statistics', [ActivityLogController::class, 'statistics'])->name('activity-logs.statistics');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::delete('/activity-logs/cleanup', [ActivityLogController::class, 'cleanup'])->name('activity-logs.cleanup');

    // Main Analytics Dashboard
    Route::get('/analytics', [AnalyticsDashboardController::class, 'index'])->name('analytics.index');

    Route::get('/analytics/customers', [CustomerAnalyticsController::class, 'index'])
        ->name('analytics.customers');
    
    // Export Analytics Data
    Route::get('/analytics/customers/export', [CustomerAnalyticsController::class, 'export'])
        ->name('analytics.customers.export');

    // Admin Notifications
    Route::prefix('notifications')->name('notifications.')->group(function() {
        Route::get('/', [App\Http\Controllers\AdminNotificationController::class, 'index'])->name('index');
        Route::get('/unread', [App\Http\Controllers\AdminNotificationController::class, 'unread'])->name('unread');
        Route::post('/{id}/read', [App\Http\Controllers\AdminNotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [App\Http\Controllers\AdminNotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{id}', [App\Http\Controllers\AdminNotificationController::class, 'destroy'])->name('destroy');
    });

    Route::get('/cpanel/stats', [CpanelStatsController::class, 'index'])->name('cpanel.stats');
    Route::post('/cpanel/stats/refresh', [CpanelStatsController::class, 'refresh'])->name('cpanel.stats.refresh');
    
    // Redirect legacy analytics route
    Route::get('/dashboards/analytics', function() {
        return redirect()->route('analytics.index');
    });

    // Catch-all routing
    Route::get('/{first}', [RoutingController::class, 'root'])->name('first');
    Route::get('/{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('/{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
});