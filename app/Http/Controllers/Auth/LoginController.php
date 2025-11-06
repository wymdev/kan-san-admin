<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected string $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Show the custom login form
     */
    public function showLoginForm()
    {
        // Point to your custom login blade view
        return view('auth.boxed-login'); // Change this to your custom path
        // Example: return view('dashboards.auth.login');
    }

    protected function authenticated(Request $request, $user)
    {
        // Send OTP after successful login
        (new OtpService())->sendOtp($user);
        
        return redirect()->route('send.otp');
    }
}
