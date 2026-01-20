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
use App\Http\Controllers\SecondaryTicketController;
use App\Http\Controllers\SecondarySalesController;
use App\Http\Controllers\SecondarySalesDashboardController;
use App\Http\Controllers\PublicResultController;
use App\Http\Controllers\PublicLotteryController;

use Illuminate\Support\Facades\Route;

// Public pages (accessible without authentication)
Route::get('/privacy-policy', [App\Http\Controllers\PublicPageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-conditions', [App\Http\Controllers\PublicPageController::class, 'termsConditions'])->name('terms-conditions');
Route::get('/about-us', [App\Http\Controllers\PublicPageController::class, 'aboutUs'])->name('about-us');

// Public Lottery Portal (accessible without authentication)
Route::prefix('lottery')->name('public.')->group(function() {
    // Main lottery check page
    Route::get('/check', [PublicLotteryController::class, 'index'])->name('lottery-check');
    Route::post('/check', [PublicLotteryController::class, 'check'])->name('lottery-check.submit');
    
    // Historical results
    Route::get('/history', [PublicLotteryController::class, 'history'])->name('lottery-history');
    Route::get('/result/{date}', [PublicLotteryController::class, 'showResult'])->name('lottery-result');
    
    // Customer batch view (unique link)
    Route::get('/my/{token}', [PublicLotteryController::class, 'customerBatch'])->name('customer-batch');
});

// Legacy lottery result check (single transaction unique link)
Route::get('/lottery-result/{token}', [PublicResultController::class, 'show'])->name('public.lottery-result-legacy');

// Guest routes
Route::middleware(['guest','sanitizeInput', 'fileTypeCheck'])->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('auth/reset-password', function() {
        return view('auth.boxed-reset-password');
    })->name('password.request');
});

// Authenticated routes - OTP verification required
Route::middleware(['auth','sanitizeInput', 'fileTypeCheck','throttle:60,1'])->group(function () {
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
    
    // Customer blocking routes
    Route::post('/customers/{customer}/block', [CustomerController::class, 'block'])
         ->name('customers.block');
    Route::post('/customers/{customer}/unblock', [CustomerController::class, 'unblock'])
         ->name('customers.unblock');
    Route::get('/customers/export/excel', [CustomerController::class, 'export'])
         ->name('customers.export');
    Route::get('/customers/{customer}/gdpr-export', [CustomerController::class, 'exportGdpr'])
         ->name('customers.gdpr-export');
    
    // Lottery System
    Route::resource('tickets', TicketController::class);
    Route::get('/tickets/export/excel', [TicketController::class, 'export'])
         ->name('tickets.export');
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
    Route::get('/purchases/export/excel', [TicketPurchaseController::class, 'export'])
         ->name('purchases.export');
    // ========================================

    // Mobile App Configuration
    Route::resource('app-configs', AppConfigController::class);
    Route::post('/app-configs/update-exchange-rate', [App\Http\Controllers\AppConfigController::class, 'updateExchangeRate'])
         ->name('app-configs.exchange-rate');

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

    // Login Activity
    Route::get('/login-activities', [App\Http\Controllers\LoginActivityController::class, 'index'])->name('login-activities.index');
    Route::get('/login-activities/export', [App\Http\Controllers\LoginActivityController::class, 'export'])->name('login-activities.export');
    Route::get('/login-activities/{loginActivity}', [App\Http\Controllers\LoginActivityController::class, 'show'])->name('login-activities.show');
    Route::get('/login-activities/user/{userType}/{userId}', [App\Http\Controllers\LoginActivityController::class, 'userActivity'])->name('login-activities.user');

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

    // ========================================
    // SECONDARY SALES MODULE
    // ========================================
    
    // Secondary Sales Dashboard
    Route::get('/secondary-sales/dashboard', [SecondarySalesDashboardController::class, 'index'])
         ->name('secondary-sales.dashboard');
    
    // Secondary Tickets (with OCR)
    Route::get('/secondary-tickets/export/excel', [SecondaryTicketController::class, 'export'])
         ->name('secondary-tickets.export');
    Route::post('/secondary-tickets/extract-ocr', [SecondaryTicketController::class, 'extractOcr'])
         ->name('secondary-tickets.extract-ocr');
    Route::resource('secondary-tickets', SecondaryTicketController::class);
    
    // Secondary Transactions
    Route::get('/secondary-transactions/check-results', [SecondarySalesController::class, 'checkResultsPage'])
         ->name('secondary-transactions.check-results');
    Route::post('/secondary-transactions/check-all', [SecondarySalesController::class, 'checkResults'])
         ->name('secondary-transactions.check-all');
    Route::post('/secondary-transactions/recheck-all', [SecondarySalesController::class, 'recheckAll'])
         ->name('secondary-transactions.recheck-all');
    Route::post('/secondary-transactions/recheck-selected', [SecondarySalesController::class, 'recheckSelected'])
         ->name('secondary-transactions.recheck-selected');
    Route::get('/secondary-transactions/export', [SecondarySalesController::class, 'export'])
         ->name('secondary-transactions.export');
    Route::post('/secondary-transactions/{secondaryTransaction}/mark-paid', [SecondarySalesController::class, 'markPaid'])
         ->name('secondary-transactions.mark-paid');
    Route::resource('secondary-transactions', SecondarySalesController::class);
    
    // Customer search API for secondary sales
    Route::get('/api/customers/search', [SecondarySalesController::class, 'searchCustomers'])
         ->name('api.customers.search');
    // ========================================

    // Catch-all routing
    Route::get('/{first}', [RoutingController::class, 'root'])->name('first');
    Route::get('/{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('/{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
});
