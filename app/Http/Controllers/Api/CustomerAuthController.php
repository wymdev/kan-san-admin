<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DevicePushToken;
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
     * Register a new customer with IMMEDIATE push token binding
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'unique:customers,phone_number', new ValidPhoneNumber()],
            'password' => 'required|min:8|confirmed',
            // ✅ Push token fields (optional)
            'expo_push_token' => 'nullable|string|starts_with:ExponentPushToken',
            'device_id' => 'nullable|string|max:255',
            'platform' => 'nullable|string|in:ios,android',
            'app_version' => 'nullable|string|max:50',
        ], [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.unique' => 'This phone number is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'expo_push_token.starts_with' => 'Invalid push token format.',
        ]);

        try {
            $customer = Customer::create([
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($validated['password']),
                'expo_push_token' => $validated['expo_push_token'] ?? null, // ✅ Store immediately
            ]);

            // Access token: expires in 15 minutes
            $accessTokenExpiresAt = now()->addMinutes(15); 
            $accessToken = $customer->createToken(
                'access_token',
                ['*'],
                $accessTokenExpiresAt
            )->plainTextToken;

            // Refresh token: expires in 30 days
            $refreshTokenExpiresAt = now()->addDays(30);
            $refreshToken = $customer->createToken(
                'refresh_token', 
                ['*'], 
                $refreshTokenExpiresAt
            )->plainTextToken;

            // ✅ BIND PUSH TOKEN IMMEDIATELY (if provided)
            if (!empty($validated['expo_push_token'])) {
                $this->bindPushToken($customer, $validated);
            }

            // ✅ CREATE ADMIN NOTIFICATION
            \App\Models\AdminNotification::createCustomerRegistration($customer);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'customer' => $customer->fresh(), // Refresh to get updated data
                    'access_token' => $accessToken,
                    'access_token_expires_at' => $accessTokenExpiresAt,
                    'refresh_token' => $refreshToken,
                    'refresh_token_expires_at' => $refreshTokenExpiresAt,
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'phone' => $validated['phone_number'] ?? 'unknown',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login customer with IMMEDIATE push token binding
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => ['required', new ValidPhoneNumber()],
            'password' => 'required',
            // ✅ Push token fields (optional)
            'expo_push_token' => 'nullable|string|starts_with:ExponentPushToken',
            'device_id' => 'nullable|string|max:255',
            'platform' => 'nullable|string|in:ios,android',
            'app_version' => 'nullable|string|max:50',
        ], [
            'phone_number.required' => 'Phone number is required.',
            'password.required' => 'Password is required.',
            'expo_push_token.starts_with' => 'Invalid push token format.',
        ]);

        $customer = Customer::where('phone_number', $validated['phone_number'])->first();

        if (!$customer || !Hash::check($validated['password'], $customer->password)) {
            throw ValidationException::withMessages([
                'phone_number' => 'Invalid credentials - phone or password incorrect',
            ]);
        }

        // Clear old tokens for security
        $customer->tokens()->delete();

        // Access token: expires in 15 minutes
        $accessTokenExpiresAt = now()->addMinutes(15); 
        $accessToken = $customer->createToken(
            'access_token',
            ['*'],
            $accessTokenExpiresAt
        )->plainTextToken;

        // Refresh token: expires in 30 days
        $refreshTokenExpiresAt = now()->addDays(30);
        $refreshToken = $customer->createToken(
            'refresh_token', 
            ['*'], 
            $refreshTokenExpiresAt
        )->plainTextToken;

        // ✅ BIND PUSH TOKEN IMMEDIATELY (if provided)
        if (!empty($validated['expo_push_token'])) {
            // Update customer's push token
            $customer->expo_push_token = $validated['expo_push_token'];
            $customer->push_token_updated_at = now();
            $customer->save();

            // Bind to device_push_tokens table
            $this->bindPushToken($customer, $validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'customer' => $customer->fresh(), // Refresh to get updated data
                'access_token' => $accessToken,
                'access_token_expires_at' => $accessTokenExpiresAt,
                'refresh_token' => $refreshToken,
                'refresh_token_expires_at' => $refreshTokenExpiresAt,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * ✅ Helper method to bind push token to customer
     * This links the device_push_tokens table with customer_id
     */
    private function bindPushToken(Customer $customer, array $data)
    {
        try {
            if (empty($data['expo_push_token'])) {
                return;
            }

            // Update or create push token record and link to customer
            $pushToken = DevicePushToken::updateOrCreate(
                ['token' => $data['expo_push_token']],
                [
                    'customer_id' => $customer->id, // ✅ Link to customer
                    'device_id' => $data['device_id'] ?? null,
                    'platform' => $data['platform'] ?? null,
                    'app_version' => $data['app_version'] ?? null,
                    'is_active' => true,
                    'last_seen_at' => now(),
                ]
            );

            \Log::info("Push token bound to customer", [
                'customer_id' => $customer->id,
                'token_id' => $pushToken->id,
                'token' => substr($data['expo_push_token'], 0, 30) . '...',
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to bind push token", [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the whole auth request if push token binding fails
        }
    }

    /**
     * Store or update Expo push token for the customer (FALLBACK)
     * This is kept as a fallback for delayed binding
     */
    public function storePushToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'expo_push_token' => 'required|string|starts_with:ExponentPushToken',
                'device_id' => 'nullable|string|max:255',
                'platform' => 'nullable|string|in:ios,android',
                'app_version' => 'nullable|string|max:50',
            ], [
                'expo_push_token.required' => 'Push token is required.',
                'expo_push_token.starts_with' => 'Invalid push token format.',
            ]);

            $customer = $request->user();

            // Store in device_push_tokens table
            $pushToken = DevicePushToken::updateOrCreate(
                ['token' => $validated['expo_push_token']],
                [
                    'customer_id' => $customer->id,
                    'device_id' => $validated['device_id'] ?? null,
                    'platform' => $validated['platform'] ?? null,
                    'app_version' => $validated['app_version'] ?? null,
                    'is_active' => true,
                    'last_seen_at' => now(),
                ]
            );

            // Also update customer record for backward compatibility
            $customer->update([
                'expo_push_token' => $validated['expo_push_token'],
                'push_token_updated_at' => now(),
            ]);

            \Log::info("Push token updated for customer {$customer->id}", [
                'token_id' => $pushToken->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Push token stored successfully',
                'data' => [
                    'customer_id' => $customer->id,
                    'token_updated_at' => $pushToken->updated_at,
                ],
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
                'message' => 'Failed to store push token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh access token
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

        // New access token: expires in 15 minutes
        $newAccessTokenExpiresAt = now()->addMinutes(15);
        $newAccessToken = $customer->createToken(
            'access_token',
            ['*'],
            $newAccessTokenExpiresAt
        )->plainTextToken;

        // New refresh token: expires in 30 days
        $newRefreshTokenExpiresAt = now()->addDays(30);
        $newRefreshToken = $customer->createToken(
            'refresh_token',
            ['*'],
            $newRefreshTokenExpiresAt
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed',
            'data' => [
                'access_token' => $newAccessToken,
                'access_token_expires_at' => $newAccessTokenExpiresAt,
                'refresh_token' => $newRefreshToken,
                'refresh_token_expires_at' => $newRefreshTokenExpiresAt,
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
                'data' => $customer->fresh(),
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