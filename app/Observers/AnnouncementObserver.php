<?php

namespace App\Observers;

use App\Models\Announcement;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

class AnnouncementObserver
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Handle the Announcement "created" event.
     */
    public function created(Announcement $announcement): void
    {
        // Auto-send if published and not scheduled
        if ($announcement->is_published && !$announcement->scheduled_at && !$announcement->is_sent) {
            $this->sendAnnouncementNotification($announcement);
        }
    }

    /**
     * Handle the Announcement "updated" event.
     */
    public function updated(Announcement $announcement): void
    {
        // Check if just published
        if ($announcement->wasChanged('is_published') && 
            $announcement->is_published && 
            !$announcement->is_sent && 
            (!$announcement->scheduled_at || $announcement->scheduled_at <= now())) {
            $this->sendAnnouncementNotification($announcement);
        }
    }

    /**
     * Send announcement push notification to all customers
     */
    protected function sendAnnouncementNotification(Announcement $announcement): void
    {
        try {
            Log::info("Sending announcement notification: {$announcement->id}");

            $results = $this->pushService->broadcastAnnouncement(
                title: $announcement->title,
                body: $announcement->body,
                data: array_merge($announcement->data ?? [], [
                    'announcement_id' => $announcement->id,
                    'type' => 'announcement',
                    'announcement_type' => $announcement->type,
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

            Log::info("Announcement sent: {$successCount} success, {$failedCount} failed");
        } catch (\Exception $e) {
            Log::error("Failed to send announcement: " . $e->getMessage());
        }
    }
}
