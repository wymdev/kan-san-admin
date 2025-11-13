<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DevicePushToken;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PushTokenController extends Controller
{
    /**
     * Register push token (PUBLIC - No authentication required)
     * This endpoint handles anonymous users
     */
    public function registerAnonymous(Request $request)
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

            // Update or create token record (anonymous, no customer_id)
            $pushToken = DevicePushToken::updateOrCreate(
                ['token' => $validated['expo_push_token']],
                [
                    'customer_id' => null, // ✅ Anonymous user
                    'device_id' => $validated['device_id'] ?? null,
                    'platform' => $validated['platform'] ?? null,
                    'app_version' => $validated['app_version'] ?? null,
                    'is_active' => true,
                    'last_seen_at' => now(),
                ]
            );

            \Log::info('Anonymous push token registered', [
                'token_id' => $pushToken->id,
                'token' => substr($validated['expo_push_token'], 0, 30) . '...',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Push token registered successfully',
                'data' => [
                    'token_id' => $pushToken->id,
                    'registered_at' => $pushToken->created_at,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to register anonymous push token', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register push token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ NEW: Bind push token to customer during login/register
     * This is called from AuthController immediately during authentication
     */
    public static function bindToCustomer($customerId, $expoPushToken, array $additionalData = [])
    {
        try {
            if (!$expoPushToken || !$customerId) {
                \Log::warning('Cannot bind push token: missing data', [
                    'customer_id' => $customerId,
                    'has_token' => !empty($expoPushToken),
                ]);
                return false;
            }

            // Update or create and link to customer IMMEDIATELY
            $pushToken = DevicePushToken::updateOrCreate(
                ['token' => $expoPushToken],
                [
                    'customer_id' => $customerId, // ✅ Bind to customer
                    'device_id' => $additionalData['device_id'] ?? null,
                    'platform' => $additionalData['platform'] ?? null,
                    'app_version' => $additionalData['app_version'] ?? null,
                    'is_active' => true,
                    'last_seen_at' => now(),
                ]
            );

            \Log::info("Push token bound to customer during auth", [
                'customer_id' => $customerId,
                'token_id' => $pushToken->id,
                'token' => substr($expoPushToken, 0, 30) . '...',
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to bind push token to customer', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Link push token to authenticated user (PROTECTED)
     * This is a FALLBACK endpoint for delayed binding
     */
    public function linkToCustomer(Request $request)
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

            // Update or create and link to customer
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

            // Also update customer record (for backward compatibility)
            $customer->update([
                'expo_push_token' => $validated['expo_push_token'],
                'push_token_updated_at' => now(),
            ]);

            \Log::info("Push token linked to customer {$customer->id}", [
                'token_id' => $pushToken->id,
                'token' => substr($validated['expo_push_token'], 0, 30) . '...',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Push token linked to account successfully',
                'data' => [
                    'customer_id' => $customer->id,
                    'token_id' => $pushToken->id,
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
            \Log::error('Failed to link push token to customer', [
                'customer_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to link push token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate push token (PUBLIC)
     * Called when app is uninstalled or user opts out
     */
    public function deactivate(Request $request)
    {
        try {
            $validated = $request->validate([
                'expo_push_token' => 'required|string',
            ]);

            $pushToken = DevicePushToken::where('token', $validated['expo_push_token'])->first();

            if ($pushToken) {
                $pushToken->update(['is_active' => false]);
                
                \Log::info('Push token deactivated', [
                    'token_id' => $pushToken->id,
                    'customer_id' => $pushToken->customer_id,
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Push token deactivated',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Token not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Failed to deactivate token', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ NEW: Get all push tokens for authenticated customer
     */
    public function getCustomerTokens(Request $request)
    {
        try {
            $customer = $request->user();
            
            $tokens = DevicePushToken::where('customer_id', $customer->id)
                ->where('is_active', true)
                ->orderBy('last_seen_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'tokens' => $tokens->map(function ($token) {
                        return [
                            'id' => $token->id,
                            'device_id' => $token->device_id,
                            'platform' => $token->platform,
                            'app_version' => $token->app_version,
                            'last_seen_at' => $token->last_seen_at,
                        ];
                    }),
                    'total' => $tokens->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get tokens',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}