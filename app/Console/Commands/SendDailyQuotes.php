<?php

namespace App\Console\Commands;

use App\Models\DailyQuote;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDailyQuotes extends Command
{
    protected $signature = 'quotes:send-daily';
    protected $description = 'Send daily quote notification to all customers';

    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        parent::__construct();
        $this->pushService = $pushService;
    }

    public function handle()
    {
        $this->info('Sending daily quotes...');

        // Get pending quote for today
        $quote = DailyQuote::pending()
            ->orderBy('created_at')
            ->first();

        if (!$quote) {
            $this->warn('No pending quotes found for today');
            return 0;
        }

        try {
            $title = 'Daily Quote';
            $body = strlen($quote->quote) > 100 
                ? substr($quote->quote, 0, 97) . '...' 
                : $quote->quote;

            $this->info("Sending: {$body}");

            $results = $this->pushService->broadcastAnnouncement(
                title: $title,
                body: $body,
                data: [
                    'quote_id' => $quote->id,
                    'type' => 'daily_quote',
                    'full_quote' => $quote->quote,
                    'author' => $quote->author,
                    'category' => $quote->category,
                ]
            );

            $successCount = collect($results)->filter()->count();

            $quote->update([
                'is_sent' => true,
                'sent_at' => now(),
                'recipients_count' => count($results),
            ]);

            $this->info("✓ Daily quote sent to {$successCount} customers");
            Log::info("Daily quote sent", ['quote_id' => $quote->id, 'recipients' => $successCount]);

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to send daily quote: ' . $e->getMessage());
            Log::error('Daily quote sending failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
