<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\TicketPurchaseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DrawInfoController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TicketController;
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
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('tickets', TicketController::class);
    Route::resource('drawinfos', DrawInfoController::class);

    // Purchase Management (Read Only)
    Route::resource('purchases', TicketPurchaseController::class)->only(['index', 'show']);
    
    // Purchase Actions (approve/reject)
    Route::post('/purchases/{purchase}/approve', [TicketPurchaseController::class, 'approve'])
         ->name('purchases.approve');
    Route::post('/purchases/{purchase}/reject', [TicketPurchaseController::class, 'reject'])
         ->name('purchases.reject');
    
    Route::get('/{first}', [RoutingController::class, 'root'])->name('first');
    Route::get('/{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('/{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
});
