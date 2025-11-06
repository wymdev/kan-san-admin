<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Rules\ValidPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class CustomerAuthController extends Controller
{
    /**
     * Register a new customer
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'unique:customers,phone_number', new ValidPhoneNumber()],
            'password' => 'required|min:8|confirmed',
        ], [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.unique' => 'This phone number is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        try {
            $customer = Customer::create([
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($validated['password']),
            ]);

            // Access token: expires in 365 days (for inactive users)
            $accessTokenExpiresAt = now()->addMinutes(15); 
            $accessToken = $customer->createToken(
                'access_token',
                ['*'],
                $accessTokenExpiresAt
            )->plainTextToken;

            $refreshTokenExpiresAt = now()->addDays(30);
            $refreshToken = $customer->createToken('refresh_token', ['*'], $refreshTokenExpiresAt)->plainTextToken;


            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'customer' => $customer,
                    'access_token' => $accessToken,
                    'access_token_expires_at' => $accessTokenExpiresAt,
                    'refresh_token' => $refreshToken,
                    'refresh_token_expires_at' => $refreshTokenExpiresAt,
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login customer and return access token
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => ['required', new ValidPhoneNumber()],
            'password' => 'required',
        ], [
            'phone_number.required' => 'Phone number is required.',
            'password.required' => 'Password is required.',
        ]);

        $customer = Customer::where('phone_number', $validated['phone_number'])->first();

        if (!$customer || !Hash::check($validated['password'], $customer->password)) {
            throw ValidationException::withMessages([
                'phone_number' => 'Invalid credentials - phone or password incorrect',
            ]);
        }

        // Clear old tokens for security
        $customer->tokens()->delete();

        // Access token: expires in 365 days (for inactive users)
        $accessTokenExpiresAt = now()->addMinutes(15); 
        $accessToken = $customer->createToken(
            'access_token',
            ['*'],
            $accessTokenExpiresAt
        )->plainTextToken;

        $refreshTokenExpiresAt = now()->addDays(30);
        $refreshToken = $customer->createToken('refresh_token', ['*'], $refreshTokenExpiresAt)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'customer' => $customer,
                'access_token' => $accessToken,
                'access_token_expires_at' => $accessTokenExpiresAt,
                'refresh_token' => $refreshToken,
                'refresh_token_expires_at' => $refreshTokenExpiresAt,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Refresh access token (optional - for future use)
     * Currently using 365-day tokens, but this is available if needed
     */
    public function refreshToken(Request $request)
    {
        $currentToken = $request->bearerToken();
        $token = PersonalAccessToken::findToken($currentToken);

        if (!$token || $token->expires_at?->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token',
                'error' => 'Please login again',
            ], 401);
        }

        $customer = $token->tokenable;
        $token->delete();

        // New token: expires in 365 days
        $newAccessTokenExpiresAt = now()->addDays(365);
        $newAccessToken = $customer->createToken(
            'access_token',
            ['*'],
            $newAccessTokenExpiresAt
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed',
            'data' => [
                'access_token' => $newAccessToken,
                'access_token_expires_at' => $newAccessTokenExpiresAt,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Get current authenticated customer profile
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    /**
     * Update customer profile (protected route)
     */
    public function update(Request $request)
    {
        try {
            $customer = $request->user();

            // Build validation rules dynamically
            $rules = [
                'phone_number' => ['required', "unique:customers,phone_number,{$customer->id}", new ValidPhoneNumber()],
                'full_name' => 'nullable|string|max:255',
                'email' => "nullable|email|unique:customers,email,{$customer->id}",
                'gender' => 'nullable|in:M,F,Other',
                'dob' => 'nullable|date',
                'thai_pin' => "nullable|string|unique:customers,thai_pin,{$customer->id}",
                'address' => 'nullable|string|max:500',
            ];

            // Only require password confirmation if password is provided
            if ($request->filled('password')) {
                $rules['password'] = 'required|string|min:8|confirmed';
                $rules['password_confirmation'] = 'required';
            }

            $validated = $request->validate($rules, [
                'phone_number.required' => 'Phone number is required.',
                'phone_number.unique' => 'This phone number is already registered.',
                'email.unique' => 'This email already exists.',
                'thai_pin.unique' => 'This PIN is already registered.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.confirmed' => 'Password confirmation does not match.',
                'password_confirmation.required' => 'Password confirmation is required.',
            ]);

            $input = Arr::except($validated, ['password_confirmation']);

            // Only hash password if provided
            if ($request->filled('password')) {
                $input['password'] = Hash::make($input['password']);
            } else {
                $input = Arr::except($input, ['password']);
            }

            $customer->update($input);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $customer,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout customer (delete all tokens)
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
