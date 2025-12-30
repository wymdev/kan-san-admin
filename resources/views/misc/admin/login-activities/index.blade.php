@extends('layouts.vertical', ['title' => 'Login Activity'])

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .filter-section {
        background: linear-gradient(to bottom, #f8fafc, #f1f5f9);
        padding: 1.5rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
    }
    .stat-card {
        transition: all 0.2s ease-in-out;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .suspicious-ip {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
</style>
@endsection

@section('content')
@include('layouts.partials/page-title', ['subtitle' => 'Security', 'title' => 'Login Activity'])

@if(session('success'))
    <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded relative mb-4">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
    <div class="card stat-card bg-primary/5 border border-primary/10">
        <div class="card-body text-center">
            <p class="text-sm text-default-500 mb-1">Total Logins</p>
            <p class="text-2xl font-bold text-primary">{{ number_format($stats['total_logins']) }}</p>
        </div>
    </div>
    <div class="card stat-card bg-success/5 border border-success/10">
        <div class="card-body text-center">
            <p class="text-sm text-default-500 mb-1">Successful</p>
            <p class="text-2xl font-bold text-success">{{ number_format($stats['success_logins']) }}</p>
        </div>
    </div>
    <div class="card stat-card bg-danger/5 border border-danger/10">
        <div class="card-body text-center">
            <p class="text-sm text-default-500 mb-1">Failed</p>
            <p class="text-2xl font-bold text-danger">{{ number_format($stats['failed_logins']) }}</p>
        </div>
    </div>
    <div class="card stat-card bg-info/5 border border-info/10">
        <div class="card-body text-center">
            <p class="text-sm text-default-500 mb-1">Unique IPs</p>
            <p class="text-2xl font-bold text-info">{{ number_format($stats['unique_ips']) }}</p>
        </div>
    </div>
    <div class="card stat-card bg-warning/5 border border-warning/10">
        <div class="card-body text-center">
            <p class="text-sm text-default-500 mb-1">Unique Users</p>
            <p class="text-2xl font-bold text-warning">{{ number_format($stats['unique_users']) }}</p>
        </div>
    </div>
</div>

@if($suspiciousIps->isNotEmpty())
<div class="card mb-6 border-danger/20 bg-danger/5">
    <div class="card-header bg-danger/10">
        <h6 class="card-title text-danger flex items-center gap-2">
            <i class="size-5" data-lucide="alert-triangle"></i>
            Suspicious Activity Detected
        </h6>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($suspiciousIps as $ip)
            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-danger/20">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-full bg-danger/10 flex items-center justify-center">
                        <i class="size-5 text-danger" data-lucide="shield-alert"></i>
                    </div>
                    <div>
                        <p class="font-mono font-semibold text-danger">{{ $ip->ip_address }}</p>
                        <p class="text-xs text-default-500">{{ $ip->failed_count }} failed attempts</p>
                    </div>
                </div>
                <a href="{{ route('login-activities.index', ['ip_address' => $ip->ip_address, 'status' => 'failed']) }}" class="btn btn-xs bg-danger text-white">
                    View
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header flex justify-between items-center">
        <h6 class="card-title">Login History</h6>
        <div class="flex gap-2">
            <a href="{{ route('login-activities.export', request()->query()) }}" class="btn btn-sm bg-success text-white">
                <i class="size-4 me-1" data-lucide="download"></i> Export
            </a>
        </div>
    </div>

    <div class="filter-section">
        <form method="GET" action="{{ route('login-activities.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input class="form-input form-input-sm w-full" placeholder="Email, IP, browser..." type="text" name="search" value="{{ request('search') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="form-select form-select-sm w-full">
                        <option value="">All Status</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Locked</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
                    <select name="user_type" class="form-select form-select-sm w-full">
                        <option value="">All Types</option>
                        <option value="App\Models\User" {{ request('user_type') == 'App\Models\User' ? 'selected' : '' }}>Admin User</option>
                        <option value="App\Models\Customer" {{ request('user_type') == 'App\Models\Customer' ? 'selected' : '' }}>Customer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Records per page</label>
                    <select name="per_page" class="form-select form-select-sm w-full">
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 50) == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <input type="text" name="daterange" id="daterange" class="form-input form-input-sm w-full" placeholder="Select date range" value="{{ request('start_date') && request('end_date') ? request('start_date') . ' - ' . request('end_date') : '' }}">
                    <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn btn-sm bg-primary text-white mr-2">
                        <i class="size-4 me-1" data-lucide="search"></i> Search
                    </button>
                    <a href="{{ route('login-activities.index') }}" class="btn btn-sm bg-default-200 text-default-600">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-default-200">
                        <thead class="bg-default-150">
                            <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                                <th class="px-3.5 py-3 text-start">#</th>
                                <th class="px-3.5 py-3 text-start">User</th>
                                <th class="px-3.5 py-3 text-start">IP Address</th>
                                <th class="px-3.5 py-3 text-start">Location</th>
                                <th class="px-3.5 py-3 text-start">Device</th>
                                <th class="px-3.5 py-3 text-start">Status</th>
                                <th class="px-3.5 py-3 text-start">Login At</th>
                                <th class="px-3.5 py-3 text-start">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activities as $activity)
                                <tr class="text-default-800 font-normal text-sm whitespace-nowrap hover:bg-default-50">
                                    <td class="px-3.5 py-3">{{ $activities->firstItem() + $loop->index }}</td>
                                    <td class="px-3.5 py-3">
                                        @if($activity->user)
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ $activity->user->name ?? $activity->user->full_name ?? $activity->user->email }}</span>
                                                <span class="text-xs text-default-500">{{ class_basename($activity->user_type) }}</span>
                                            </div>
                                        @else
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ $activity->email }}</span>
                                                <span class="text-xs text-danger">User not found</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-3 font-mono text-xs">{{ $activity->ip_address ?? 'N/A' }}</td>
                                    <td class="px-3.5 py-3">
                                        <div class="flex items-center gap-2">
                                            <i class="size-4 text-default-400" data-lucide="map-pin"></i>
                                            <span>{{ $activity->location }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3.5 py-3">
                                        <div class="flex flex-col">
                                            <span class="text-xs">{{ $activity->device_type ?? 'N/A' }}</span>
                                            <span class="text-xs text-default-500">{{ $activity->browser }} / {{ $activity->os }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3.5 py-3">
                                        @switch($activity->status)
                                            @case('success')
                                                <span class="px-2 py-1 text-xs rounded-full bg-success/10 text-success">
                                                    <i class="size-3 mr-1" data-lucide="check-circle"></i> Success
                                                </span>
                                                @break
                                            @case('failed')
                                                <span class="px-2 py-1 text-xs rounded-full bg-danger/10 text-danger">
                                                    <i class="size-3 mr-1" data-lucide="x-circle"></i> Failed
                                                </span>
                                                @break
                                            @case('blocked')
                                                <span class="px-2 py-1 text-xs rounded-full bg-warning/10 text-warning">
                                                    <i class="size-3 mr-1" data-lucide="shield-alert"></i> Blocked
                                                </span>
                                                @break
                                            @case('locked')
                                                <span class="px-2 py-1 text-xs rounded-full bg-info/10 text-info">
                                                    <i class="size-3 mr-1" data-lucide="lock"></i> Locked
                                                </span>
                                                @break
                                        @endswitch
                                        @if($activity->failure_reason)
                                            <p class="text-xs text-danger mt-1" title="{{ $activity->failure_reason }}">
                                                {{ Str::limit($activity->failure_reason, 20) }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-3">
                                        <div class="flex flex-col">
                                            <span>{{ $activity->login_at->format('M d, Y') }}</span>
                                            <span class="text-xs text-default-500">{{ $activity->login_at->format('h:i A') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3.5 py-3">
                                        <a href="{{ route('login-activities.show', $activity) }}" class="btn btn-xs bg-primary/10 text-primary hover:bg-primary hover:text-white">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3.5 py-8 text-center text-default-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="mgc_inbox_line text-4xl"></i>
                                            <p>No login activities found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer flex items-center justify-between">
            <p class="text-default-500 text-sm">
                Showing <b>{{ $activities->firstItem() ?? 0 }}</b> to <b>{{ $activities->lastItem() ?? 0 }}</b> of <b>{{ $activities->total() }}</b> Results
            </p>
            <nav aria-label="Pagination" class="flex items-center gap-2">
                {{ $activities->appends(request()->query())->links() }}
            </nav>
        </div>
    </div>
</div>

@if($recentFailed->isNotEmpty())
<div class="card mt-6">
    <div class="card-header">
        <h6 class="card-title flex items-center gap-2">
            <i class="size-5 text-danger" data-lucide="alert-circle"></i>
            Recent Failed Logins
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead class="bg-default-150">
                <tr class="text-sm font-normal text-default-700">
                    <th class="px-3.5 py-2 text-start">User</th>
                    <th class="px-3.5 py-2 text-start">IP Address</th>
                    <th class="px-3.5 py-2 text-start">Reason</th>
                    <th class="px-3.5 py-2 text-start">Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentFailed as $failed)
                <tr class="text-sm">
                    <td class="px-3.5 py-2">{{ $failed->email }}</td>
                    <td class="px-3.5 py-2 font-mono">{{ $failed->ip_address }}</td>
                    <td class="px-3.5 py-2 text-danger">{{ $failed->failure_reason }}</td>
                    <td class="px-3.5 py-2">{{ $failed->login_at->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(function() {
    $('#daterange').daterangepicker({
        autoUpdateInput: false,
        locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' },
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
        $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
    });

    $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $('#start_date').val('');
        $('#end_date').val('');
    });
});
</script>
@endsection
