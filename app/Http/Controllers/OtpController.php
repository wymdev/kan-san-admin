<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Services\OtpService;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function verifyOtpForm()
    {
        return view('auth.boxed-two-steps');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:4'
        ]);

        $user = auth()->user();
        $otpRecord = Otp::where('user_id', $user->id)->first();

        if (!$otpRecord || $otpRecord->otp !== $request->otp) {
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }

        if ($otpRecord->expires_at < now()) {
            return back()->withErrors(['otp' => 'OTP expired']);
        }

        // Mark as verified and delete
        $otpRecord->delete();
        
        return redirect()->route('dashboard');
    }

    public function resendOtp(Request $request)
    {
        $user = auth()->user();
        
        // Delete old OTP
        Otp::where('user_id', $user->id)->delete();
        
        // Generate and store new OTP
        $otp = rand(1000, 9999);
        Otp::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10)
        ]);
        
        // Send via email
        (new OtpService())->sendOtp($user, $otp);
        
        return back()->with('message', 'OTP resent to ' . $user->email);
    }
}
