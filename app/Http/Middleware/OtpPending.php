<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OtpPending
{
    public function handle(Request $request, Closure $next)
    {
        // Allow OTP form and resend routes even if already verified
        if (session('otp_verified')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
