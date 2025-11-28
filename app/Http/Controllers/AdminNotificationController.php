<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    /**
     * Get all notifications (paginated)
     */
    public function index()
    {
        $notifications = AdminNotification::latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get unread notifications (for AJAX)
     */
    public function unread()
    {
        $notifications = AdminNotification::unread()
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => AdminNotification::unread()->count(),
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead($id)
    {
        $notification = AdminNotification::findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        AdminNotification::unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $notification = AdminNotification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }
}
