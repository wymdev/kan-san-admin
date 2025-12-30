@extends('layouts.vertical', ['title' => 'Login Activity Details'])

@section('content')
@include('layouts.partials/page-title', ['subtitle' => 'Security', 'title' => 'Login Activity Details'])

<div class="max-w-4xl mx-auto">
    <div class="card mb-6">
        <div class="card-header flex justify-between items-center">
            <h6 class="card-title">Login Details</h6>
            <span class="px-3 py-1 rounded-full text-sm font-bold
                {{ $loginActivity->status === 'success' ? 'bg-success/10 text-success' : '' }}
                {{ $loginActivity->status === 'failed' ? 'bg-danger/10 text-danger' : '' }}
                {{ $loginActivity->status === 'blocked' ? 'bg-warning/10 text-warning' : '' }}
                {{ $loginActivity->status === 'locked' ? 'bg-info/10 text-info' : '' }}">
                {{ ucfirst($loginActivity->status) }}
            </span>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div>
                    <p class="text-sm text-default-500 mb-1">User</p>
                    <p class="font-semibold">
                        @if($loginActivity->user)
                            {{ $loginActivity->user->name ?? $loginActivity->user->full_name ?? $loginActivity->user->email }}
                        @else
                            <span class="text-danger">User not found ({{ $loginActivity->email }})</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-default-500 mb-1">User Type</p>
                    <p class="font-semibold">{{ class_basename($loginActivity->user_type) }}</p>
                </div>
                <div>
                    <p class="text-sm text-default-500 mb-1">Login Time</p>
                    <p class="font-semibold">{{ $loginActivity->login_at->format('M d, Y - h:i A') }}</p>
                </div>
                <div>
                    <p class="text-sm text-default-500 mb-1">Email</p>
                    <p class="font-semibold">{{ $loginActivity->email }}</p>
                </div>
            </div>

            @if($loginActivity->failure_reason)
            <div class="mt-4 p-4 bg-danger/10 border border-danger/20 rounded-lg">
                <p class="text-sm font-semibold text-danger mb-1">Failure Reason</p>
                <p class="text-danger">{{ $loginActivity->failure_reason }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-header">
            <h6 class="card-title">Location & Device Information</h6>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center">
                            <i class="size-6 text-primary" data-lucide="map-pin"></i>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Location</p>
                            <p class="font-semibold text-lg">{{ $loginActivity->location }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <p class="text-sm text-default-500">IP Address</p>
                            <p class="font-mono font-semibold">{{ $loginActivity->ip_address }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Country</p>
                            <p class="font-semibold">{{ $loginActivity->country ?? 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">City</p>
                            <p class="font-semibold">{{ $loginActivity->city ?? 'Unknown' }}</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="size-12 rounded-full bg-info/10 flex items-center justify-center">
                            <i class="size-6 text-info" data-lucide="monitor"></i>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Device</p>
                            <p class="font-semibold text-lg">{{ $loginActivity->device_type ?? 'Unknown' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <p class="text-sm text-default-500">Browser</p>
                            <p class="font-semibold">{{ $loginActivity->browser ?? 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-default-500">Operating System</p>
                            <p class="font-semibold">{{ $loginActivity->os ?? 'Unknown' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <p class="text-sm text-default-500 mb-2">User Agent</p>
                <code class="block p-3 bg-default-100 rounded text-xs font-mono">{{ $loginActivity->user_agent ?? 'N/A' }}</code>
            </div>
        </div>
    </div>

    @if($loginActivity->user)
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">User Actions</h6>
        </div>
        <div class="card-body flex gap-3">
            <a href="{{ route('login-activities.user', ['userType' => class_basename($loginActivity->user_type), 'userId' => $loginActivity->user_id]) }}" class="btn bg-primary text-white">
                <i class="size-4 me-1" data-lucide="history"></i> View All Login History
            </a>
            @if(class_basename($loginActivity->user_type) === 'Customer')
                <a href="{{ route('customers.show', $loginActivity->user_id) }}" class="btn bg-info text-white">
                    <i class="size-4 me-1" data-lucide="user"></i> View Customer Profile
                </a>
            @elseif(class_basename($loginActivity->user_type) === 'User')
                <a href="{{ route('users.show', $loginActivity->user_id) }}" class="btn bg-info text-white">
                    <i class="size-4 me-1" data-lucide="user"></i> View User Profile
                </a>
            @endif
            <a href="{{ route('login-activities.index', ['ip_address' => $loginActivity->ip_address]) }}" class="btn bg-warning text-white">
                <i class="size-4 me-1" data-lucide="search"></i> Search by IP
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
