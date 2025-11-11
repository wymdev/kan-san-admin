<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushNotificationLog;
use Illuminate\Http\Request;

class CustomerNotificationController extends Controller
{
    /**
     * Get logged-in customer's purchase notifications.
     * Route: GET /api/customer/noti
     */
    public function index(Request $request)
    {
        $customer = $request->user();

        $q = PushNotificationLog::where('customer_id', $customer->id)
            ->whereIn('notification_type', ['purchase_approved', 'purchase_rejected'])
            ->orderByDesc('created_at');

        // Optional filter: unread only
        if ($request->filled('unread')) {
            $unread = $request->boolean('unread');
            if ($unread) {
                $q->whereNull('read_at');
            }
        }

        $notis = $q->paginate($request->get('per_page', 20));

        // Get unread count for badge
        $unread_count = PushNotificationLog::where('customer_id', $customer->id)
            ->whereIn('notification_type', ['purchase_approved', 'purchase_rejected'])
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => $notis,
            'unread_count' => $unread_count,
        ]);
    }

    /**
     * Mark all notifications as read
     * Route: POST /api/customer/noti/mark-read
     */
    public function markAllRead(Request $request)
    {
        $customer = $request->user();

        PushNotificationLog::where('customer_id', $customer->id)
            ->whereIn('notification_type', ['purchase_approved', 'purchase_rejected'])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Mark single notification as read
     * Route: POST /api/customer/noti/{id}/mark-read
     */
    public function markRead(Request $request, $id)
    {
        $customer = $request->user();

        $noti = PushNotificationLog::where('id', $id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $noti->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }
}
