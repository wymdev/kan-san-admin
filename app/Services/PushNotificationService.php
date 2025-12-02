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
    // public function broadcastAnnouncement(
    //     string $title,
    //     string $body,
    //     array $data = []
    // ): array {
    //     $tokens = DevicePushToken::active()->get();

    //     Log::info("Broadcasting announcement to {$tokens->count()} devices");

    //     return $this->sendBatchToTokens(
    //         tokens: $tokens,
    //         title: $title,
    //         body: $body,
    //         type: 'announcement',
    //         data: $data
    //     );
    // }
    public function broadcastAnnouncement(
        string $title,
        string $body,
        array $data = []
    ): array {
        $tokens = DevicePushToken::active()->get();

        Log::info("Broadcasting announcement to {$tokens->count()} devices");

        // Extract type from data or default to 'announcement'
        $type = $data['type'] ?? 'announcement';

        return $this->sendBatchToTokens(
            tokens: $tokens,
            title: $title,
            body: $body,
            type: $type, // Use dynamic type from data
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

        // Ensure proper UTF-8 encoding
        $title = $this->sanitizeUtf8($title);
        $body = $this->sanitizeUtf8($body);
        $data = $this->sanitizeArrayUtf8($data);

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

    public function sendSingleNotification(array $message, int $customerId = null): bool
    {
        // Sanitize UTF-8 in the message
        $message['title'] = $this->sanitizeUtf8($message['title'] ?? '');
        $message['body'] = $this->sanitizeUtf8($message['body'] ?? '');
        if (isset($message['data'])) {
            $message['data'] = $this->sanitizeArrayUtf8($message['data']);
        }

        // Log notification (bind to customer_id)
        $notificationType = $message['data']['type'] ?? 'other';

        PushNotificationLog::create([
            'customer_id' => $customerId,
            'notification_type' => $message['data']['type'] ?? 'other',
            'title' => $message['title'],
            'body' => $message['body'],
            'payload' => $message['data'] ?? [],
            'status' => 'pending',
        ]);

        try {
            $response = Http::acceptJson()
                ->withHeader('Authorization', 'Bearer ' . $this->expoAccessToken)
                ->post($this->expoUrl, [$message]); // Expo expects an array

            if ($response->successful()) {
                $result = $response->json();
                $resultData = $result['data'][0] ?? [];
                if (($resultData['status'] ?? null) === 'ok') {
                    PushNotificationLog::where('customer_id', $customerId)
                        ->where('status', 'pending')
                        ->latest()
                        ->first()
                        ?->update([
                            'expo_ticket_id' => $resultData['id'] ?? null,
                            'status' => 'sent',
                        ]);
                    return true;
                } else {
                    PushNotificationLog::where('customer_id', $customerId)
                        ->where('status', 'pending')
                        ->latest()
                        ->first()
                        ?->update([
                            'status' => 'failed',
                            'error_message' => $resultData['message'] ?? json_encode($resultData),
                        ]);
                    // Optionally clear invalid expo token from Customer model
                    return false;
                }
            } else {
                // HTTP error
                PushNotificationLog::where('customer_id', $customerId)
                    ->where('status', 'pending')
                    ->latest()
                    ->first()
                    ?->update([
                        'status' => 'failed',
                        'error_message' => $response->body(),
                    ]);
                return false;
            }
        } catch (\Exception $e) {
            PushNotificationLog::where('customer_id', $customerId)
                ->where('status', 'pending')
                ->latest()
                ->first()
                ?->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            return false;
        }
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

    /**
     * Sanitize a string to ensure valid UTF-8 encoding
     */
    private function sanitizeUtf8(string $string): string
    {
        // Remove invalid UTF-8 characters
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        
        // Remove any null bytes
        $string = str_replace("\0", '', $string);
        
        // Ensure the string is valid UTF-8
        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8', 'auto');
        }
        
        // Remove any remaining invalid characters
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $string);
        
        return $string;
    }

    /**
     * Recursively sanitize an array for UTF-8 encoding
     */
    private function sanitizeArrayUtf8(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $array[$key] = $this->sanitizeUtf8($value);
            } elseif (is_array($value)) {
                $array[$key] = $this->sanitizeArrayUtf8($value);
            }
        }
        return $array;
    }
}
