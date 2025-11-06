<?php

namespace App\Http\Middleware;

use App\Models\Otp;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpVerifiedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Otp::where('user_id', Auth::id())->exists()) {
            return redirect()->route('send.otp');
        }
        
        return $next($request);
    }
}
