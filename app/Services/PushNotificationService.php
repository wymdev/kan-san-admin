<?php

namespace App\Services;

use App\Models\DevicePushToken;
use App\Models\PushNotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class PushNotificationService
{
    private $expoUrl = 'https://exp.host/--/api/v2/push/send';
    private $expoAccessToken;
    private $batchSize = 100;

    public function __construct()
    {
        $this->expoAccessToken = config('services.expo.access_token');
    }

    /**
     * Broadcast notification to ALL active users
     * (Both anonymous and authenticated)
     */
    public function broadcastAnnouncement(
        string $title,
        string $body,
        array $data = []
    ): array {
        $tokens = DevicePushToken::active()->get();

        Log::info("Broadcasting announcement to {$tokens->count()} devices");

        return $this->sendBatchToTokens(
            tokens: $tokens,
            title: $title,
            body: $body,
            type: 'announcement',
            data: $data
        );
    }

    /**
     * Send to authenticated users only
     */
    public function sendToAuthenticatedUsers(
        string $title,
        string $body,
        string $type,
        array $data = []
    ): array {
        $tokens = DevicePushToken::active()->authenticated()->get();

        Log::info("Sending to {$tokens->count()} authenticated devices");

        return $this->sendBatchToTokens(
            tokens: $tokens,
            title: $title,
            body: $body,
            type: $type,
            data: $data
        );
    }

    /**
     * Send to anonymous users only
     */
    public function sendToAnonymousUsers(
        string $title,
        string $body,
        string $type,
        array $data = []
    ): array {
        $tokens = DevicePushToken::active()->anonymous()->get();

        Log::info("Sending to {$tokens->count()} anonymous devices");

        return $this->sendBatchToTokens(
            tokens: $tokens,
            title: $title,
            body: $body,
            type: $type,
            data: $data
        );
    }

    /**
     * Send notifications in batches
     */
    private function sendBatchToTokens(
        Collection $tokens,
        string $title,
        string $body,
        string $type,
        array $data = []
    ): array {
        $results = [];
        $chunks = $tokens->chunk($this->batchSize);

        foreach ($chunks as $chunk) {
            $messages = [];
            $tokenMap = [];

            foreach ($chunk as $deviceToken) {
                $messages[] = [
                    'to' => $deviceToken->token,
                    'sound' => 'default',
                    'title' => $title,
                    'body' => $body,
                    'data' => array_merge($data, [
                        'customer_id' => $deviceToken->customer_id,
                        'type' => $type,
                    ]),
                    'badge' => 1,
                    'priority' => 'high',
                    'channelId' => 'default',
                ];

                $tokenMap[$deviceToken->token] = [
                    'device_token_id' => $deviceToken->id,
                    'customer_id' => $deviceToken->customer_id,
                ];

                // Log notification
                PushNotificationLog::create([
                    'customer_id' => $deviceToken->customer_id,
                    'notification_type' => $type,
                    'title' => $title,
                    'body' => $body,
                    'payload' => $data,
                    'status' => 'pending',
                ]);

                // Update last_seen_at
                $deviceToken->update(['last_seen_at' => now()]);
            }

            try {
                $response = Http::acceptJson()
                    ->withHeader('Authorization', 'Bearer ' . $this->expoAccessToken)
                    ->post($this->expoUrl, $messages);

                if ($response->successful()) {
                    $responseData = $response->json();

                    if (isset($responseData['data']) && is_array($responseData['data'])) {
                        foreach ($responseData['data'] as $index => $result) {
                            $message = $messages[$index];
                            $tokenInfo = $tokenMap[$message['to']] ?? null;

                            if (isset($result['status']) && $result['status'] === 'ok') {
                                $results[$tokenInfo['device_token_id']] = true;

                                // Update log
                                if ($tokenInfo['customer_id']) {
                                    PushNotificationLog::where('customer_id', $tokenInfo['customer_id'])
                                        ->where('status', 'pending')
                                        ->latest()
                                        ->first()
                                        ?->update([
                                            'expo_ticket_id' => $result['id'] ?? null,
                                            'status' => 'sent',
                                        ]);
                                }
                            } else {
                                $results[$tokenInfo['device_token_id']] = false;

                                // Deactivate invalid tokens
                                if (isset($result['details']['error']) && 
                                    in_array($result['details']['error'], ['DeviceNotRegistered', 'InvalidCredentials'])) {
                                    DevicePushToken::where('token', $message['to'])
                                        ->update(['is_active' => false]);
                                }

                                // Update log with error
                                if ($tokenInfo['customer_id']) {
                                    PushNotificationLog::where('customer_id', $tokenInfo['customer_id'])
                                        ->where('status', 'pending')
                                        ->latest()
                                        ->first()
                                        ?->update([
                                            'status' => 'failed',
                                            'error_message' => json_encode($result['details'] ?? $result),
                                        ]);
                                }
                            }
                        }
                    }
                } else {
                    Log::error('Batch notification failed', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);

                    foreach ($chunk as $deviceToken) {
                        $results[$deviceToken->id] = false;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Batch notification error: ' . $e->getMessage());

                foreach ($chunk as $deviceToken) {
                    $results[$deviceToken->id] = false;
                }
            }

            // Delay between batches
            if ($chunks->count() > 1) {
                usleep(100000);
            }
        }

        return $results;
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        $totalTokens = DevicePushToken::count();
        $activeTokens = DevicePushToken::active()->count();
        $authenticatedTokens = DevicePushToken::active()->authenticated()->count();
        $anonymousTokens = DevicePushToken::active()->anonymous()->count();

        $recentNotifications = PushNotificationLog::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total_tokens' => $totalTokens,
            'active_tokens' => $activeTokens,
            'authenticated_tokens' => $authenticatedTokens,
            'anonymous_tokens' => $anonymousTokens,
            'recent_notifications' => [
                'sent' => $recentNotifications['sent'] ?? 0,
                'failed' => $recentNotifications['failed'] ?? 0,
                'pending' => $recentNotifications['pending'] ?? 0,
            ],
        ];
    }

    /**
     * Clean up old inactive tokens
     */
    public function cleanupOldTokens(int $daysInactive = 90): int
    {
        return DevicePushToken::where('is_active', false)
            ->where('updated_at', '<', now()->subDays($daysInactive))
            ->delete();
    }
}
