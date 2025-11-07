<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledAnnouncements extends Command
{
    protected $signature = 'announcements:send-scheduled';
    protected $description = 'Send scheduled announcements that are due';

    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        parent::__construct();
        $this->pushService = $pushService;
    }

    public function handle()
    {
        $this->info('Checking for scheduled announcements...');

        $announcements = Announcement::published()
            ->unsent()
            ->scheduled()
            ->get();

        if ($announcements->isEmpty()) {
            $this->info('No scheduled announcements due');
            return 0;
        }

        $this->info("Found {$announcements->count()} scheduled announcements");

        foreach ($announcements as $announcement) {
            try {
                $this->info("Sending: {$announcement->title}");

                $results = $this->pushService->broadcastAnnouncement(
                    title: $announcement->title,
                    body: $announcement->body,
                    data: array_merge($announcement->data ?? [], [
                        'announcement_id' => $announcement->id,
                        'type' => 'announcement',
                        'category' => $announcement->type,
                    ])
                );

                $successCount = collect($results)->filter()->count();
                $failedCount = count($results) - $successCount;

                $announcement->update([
                    'is_sent' => true,
                    'sent_at' => now(),
                    'recipients_count' => count($results),
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                ]);

                $this->info("✓ Sent to {$successCount} customers");
                Log::info("Scheduled announcement sent", [
                    'id' => $announcement->id,
                    'success' => $successCount,
                    'failed' => $failedCount
                ]);
            } catch (\Exception $e) {
                $this->error("Failed: {$e->getMessage()}");
                Log::error("Announcement sending failed", [
                    'id' => $announcement->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return 0;
    }
}
