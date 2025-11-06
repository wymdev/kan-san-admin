<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function sendOtp($user)
    {
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        // Generate a 6-digit OTP
        $otpCode = rand(1000, 9999);

        // Delete old OTPs for this user
        Otp::where('user_id', $user->id)->delete();

        // Save new OTP
        Otp::create([
            'user_id' => $user->id,
            'otp' => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(10), // OTP valid for 10 minutes
        ]);

        // Send OTP via email
        Mail::to($user->email)->send(new OtpMail($otpCode));

        return ['message' => 'OTP sent to your email.'];
    }
}
