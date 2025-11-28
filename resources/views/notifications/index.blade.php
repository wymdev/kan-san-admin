@extends('layouts.vertical', ['title' => 'Notifications'])

@section('css')
<style>
    .notification-card {
        transition: all 0.3s ease;
    }
    .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .notification-unread {
        background: linear-gradient(to right, rgba(59, 130, 246, 0.05), transparent);
        border-left: 3px solid #3b82f6;
    }
</style>
@endsection

@section('content')
    {{-- Header --}}
    <div class="card mb-6">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-2xl font-bold text-default-900 mb-2">Notifications</h4>
                    <p class="text-default-600">Stay updated with customer activities and orders</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="btn bg-default-100 text-default-700 hover:bg-default-200" onclick="window.location.reload()">
                        <i class="size-4 me-1" data-lucide="refresh-cw"></i>
                        Refresh
                    </button>
                    <button class="btn bg-primary text-white hover:bg-primary/90" onclick="markAllAsRead()">
                        <i class="size-4 me-1" data-lucide="check-check"></i>
                        Mark All as Read
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-6 mb-6">
        <div class="card bg-gradient-to-br from-primary/10 to-primary/5 border-0">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="size-12 rounded-lg bg-primary/20 flex items-center justify-center">
                        <i class="size-6 text-primary" data-lucide="bell"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-default-900">{{ $notifications->total() }}</h3>
                        <p class="text-sm text-default-600">Total Notifications</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-warning/10 to-warning/5 border-0">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="size-12 rounded-lg bg-warning/20 flex items-center justify-center">
                        <i class="size-6 text-warning" data-lucide="bell-ring"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-default-900">{{ $notifications->where('is_read', false)->count() }}</h3>
                        <p class="text-sm text-default-600">Unread</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-success/10 to-success/5 border-0">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="size-12 rounded-lg bg-success/20 flex items-center justify-center">
                        <i class="size-6 text-success" data-lucide="user-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-default-900">{{ $notifications->where('type', 'customer_registered')->count() }}</h3>
                        <p class="text-sm text-default-600">New Customers</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-info/10 to-info/5 border-0">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    <div class="size-12 rounded-lg bg-info/20 flex items-center justify-center">
                        <i class="size-6 text-info" data-lucide="shopping-cart"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-default-900">{{ $notifications->where('type', 'new_order')->count() }}</h3>
                        <p class="text-sm text-default-600">New Orders</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifications List --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">All Notifications</h5>
        </div>
        <div class="card-body p-0">
            @forelse($notifications as $notification)
                <div class="notification-card {{ !$notification->is_read ? 'notification-unread' : '' }} p-4 border-b border-default-200 last:border-b-0">
                    <div class="flex gap-4">
                        <div>
                            <div class="size-14 rounded-xl {{ getIconColor($notification->color) }} flex items-center justify-center">
                                <i class="size-7" data-lucide="{{ $notification->icon }}"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h6 class="font-bold text-default-900">{{ $notification->title }}</h6>
                                        @if(!$notification->is_read)
                                            <span class="px-2 py-0.5 text-xs bg-primary text-white rounded-full">New</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-default-700 mb-2">{{ $notification->message }}</p>
                                    <div class="flex items-center gap-4 text-xs text-default-500">
                                        <span class="flex items-center gap-1">
                                            <i class="size-3.5" data-lucide="clock"></i>
                                            {{ $notification->time_ago }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i class="size-3.5" data-lucide="tag"></i>
                                            {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    @if(!$notification->is_read)
                                        <button class="btn btn-sm bg-primary/10 text-primary hover:bg-primary/20" onclick="markAsRead({{ $notification->id }})">
                                            <i class="size-4 me-1" data-lucide="check"></i>
                                            Mark as Read
                                        </button>
                                    @endif
                                    <button class="btn btn-sm bg-danger/10 text-danger hover:bg-danger/20" onclick="deleteNotification({{ $notification->id }})">
                                        <i class="size-4" data-lucide="trash-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Additional Data --}}
                            @if($notification->data)
                                <div class="mt-3 p-3 bg-default-50 rounded-lg">
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        @foreach($notification->data as $key => $value)
                                            <div>
                                                <span class="text-default-500">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                <span class="font-semibold text-default-900">{{ $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <div class="flex justify-center mb-4">
                        <i class="size-20 text-default-300" data-lucide="bell-off"></i>
                    </div>
                    <h5 class="text-lg font-semibold text-default-900 mb-2">No notifications yet</h5>
                    <p class="text-default-600">When you receive notifications, they'll appear here</p>
                </div>
            @endforelse
        </div>
        
        @if($notifications->hasPages())
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
function markAsRead(id) {
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
    if (!confirm('Mark all notifications as read?')) return;
    
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(id) {
    if (!confirm('Are you sure you want to delete this notification?')) return;
    
    fetch(`/notifications/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endsection

@php
function getIconColor($color) {
    $colors = [
        'primary' => 'bg-primary/10 text-primary',
        'success' => 'bg-success/10 text-success',
        'danger' => 'bg-danger/10 text-danger',
        'warning' => 'bg-warning/10 text-warning',
        'info' => 'bg-info/10 text-info',
    ];
    return $colors[$color] ?? $colors['primary'];
}
@endphp
