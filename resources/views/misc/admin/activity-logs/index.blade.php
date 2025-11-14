@extends('layouts.vertical', ['title' => 'Activity Logs'])

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

    /* Enhanced Select Box Styling */
    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1rem;
        padding-right: 2.5rem;
        transition: all 0.2s ease-in-out;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background-color: #ffffff;
        font-size: 0.875rem;
        color: #374151;
    }

    .form-select:hover {
        border-color: #d1d5db;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background-color: #ffffff;
    }

    .form-select-sm {
        padding: 0.5rem 2.5rem 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
    }

    /* Enhanced Checkbox Styling */
    .form-checkbox {
        appearance: none;
        width: 1.125rem;
        height: 1.125rem;
        border: 2px solid #d1d5db;
        border-radius: 0.25rem;
        background-color: #ffffff;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease-in-out;
        flex-shrink: 0;
    }

    .form-checkbox:hover {
        border-color: #9ca3af;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .form-checkbox:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-checkbox:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    .form-checkbox:checked::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 0.375rem;
        height: 0.625rem;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: translate(-50%, -60%) rotate(45deg);
    }

    .form-checkbox:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Checkbox Label Styling */
    .inline-flex.items-center {
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease-in-out;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
    }

    .inline-flex.items-center:hover {
        background-color: rgba(59, 130, 246, 0.05);
    }

    .inline-flex.items-center:hover .form-checkbox {
        border-color: #9ca3af;
    }

    .inline-flex.items-center span {
        color: #374151;
        font-weight: 500;
    }

    /* Enhanced Input Styling */
    .form-input {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background-color: #ffffff;
        transition: all 0.2s ease-in-out;
        font-size: 0.875rem;
        color: #374151;
    }

    .form-input:hover {
        border-color: #d1d5db;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background-color: #ffffff;
    }

    .form-input-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
    }

    .form-input::placeholder {
        color: #9ca3af;
    }

    /* Label Styling */
    label.block {
        font-weight: 500;
        letter-spacing: 0.01em;
    }

    /* Button Hover Effects */
    .btn {
        transition: all 0.2s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Admin', 'title' => 'Activity Logs'])

    @if ($message = Session::get('success'))
        <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Activity Logs</h6>
        </div>

        <!-- Advanced Filters -->
        <div class="filter-section">
            <form method="GET" action="{{ route('activity-logs.index') }}" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input
                            class="form-input form-input-sm w-full"
                            placeholder="Search description, route, IP..."
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                        />
                    </div>

                    <!-- Action Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                        <select name="action" class="form-select form-select-sm w-full">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $action)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User/Customer Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
                        <select name="actor_type" class="form-select form-select-sm w-full">
                            <option value="">All Types</option>
                            @foreach($actorTypes as $type)
                                <option value="{{ $type }}" {{ request('actor_type') == $type ? 'selected' : '' }}>
                                    {{ class_basename($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Context Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Context</label>
                        <select name="context" class="form-select form-select-sm w-full">
                            <option value="">All Contexts</option>
                            @foreach($contexts as $context)
                                <option value="{{ $context }}" {{ request('context') == $context ? 'selected' : '' }}>
                                    {{ ucfirst($context) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <input
                            type="text"
                            name="daterange"
                            id="daterange"
                            class="form-input form-input-sm w-full"
                            placeholder="Select date range"
                            value="{{ request('start_date') && request('end_date') ? request('start_date') . ' - ' . request('end_date') : '' }}"
                            
                        />
                        <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="form-select form-select-sm w-full">
                            <option value="">All Status</option>
                            <option value="200" {{ request('status') == '200' ? 'selected' : '' }}>Success (200)</option>
                            <option value="201" {{ request('status') == '201' ? 'selected' : '' }}>Created (201)</option>
                            <option value="400" {{ request('status') == '400' ? 'selected' : '' }}>Bad Request (400)</option>
                            <option value="401" {{ request('status') == '401' ? 'selected' : '' }}>Unauthorized (401)</option>
                            <option value="403" {{ request('status') == '403' ? 'selected' : '' }}>Forbidden (403)</option>
                            <option value="404" {{ request('status') == '404' ? 'selected' : '' }}>Not Found (404)</option>
                            <option value="500" {{ request('status') == '500' ? 'selected' : '' }}>Server Error (500)</option>
                        </select>
                    </div>

                    <!-- Per Page -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Records per page</label>
                        <select name="per_page" class="form-select form-select-sm w-full">
                            <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 50) == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                            <option value="200" {{ request('per_page') == '200' ? 'selected' : '' }}>200</option>
                        </select>
                    </div>

                    <!-- Quick Filters -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quick Filters</label>
                        <div class="flex gap-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="failed_only" value="1" class="form-checkbox" {{ request('failed_only') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm">Failed Only</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="slow_only" value="1" class="form-checkbox" {{ request('slow_only') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm">Slow (>1s)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-sm bg-primary text-white">
                        <i class="mgc_search_line me-2"></i> Apply Filters
                    </button>
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-sm bg-default-200 text-default-600 hover:bg-default-300">
                        <i class="mgc_refresh_line me-2"></i> Reset
                    </a>
                    <button type="button" onclick="exportLogs()" class="btn btn-sm bg-success text-white ml-auto">
                        <i class="mgc_download_line me-2"></i> Export CSV
                    </button>
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
                                    <th class="px-3.5 py-3 text-start">Action</th>
                                    <th class="px-3.5 py-3 text-start">User/Customer</th>
                                    <th class="px-3.5 py-3 text-start">Subject</th>
                                    <th class="px-3.5 py-3 text-start">Context</th>
                                    <th class="px-3.5 py-3 text-start">Status</th>
                                    <th class="px-3.5 py-3 text-start">Duration</th>
                                    <th class="px-3.5 py-3 text-start">Date/Time</th>
                                    <th class="px-3.5 py-3 text-start">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr class="text-default-800 font-normal text-sm whitespace-nowrap hover:bg-default-50">
                                        <td class="px-3.5 py-3">{{ $logs->firstItem() + $loop->index }}</td>
                                        <td class="px-3.5 py-3">
                                            <span class="px-2 py-1 text-xs rounded-full bg-primary/10 text-primary">
                                                {{ $log->action }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            @if($log->actor)
                                                <div class="flex flex-col">
                                                    <span class="font-medium">{{ $log->actor->name ?? $log->actor->email ?? 'N/A' }}</span>
                                                    <span class="text-xs text-default-500">{{ class_basename($log->actor_type) }}</span>
                                                </div>
                                            @else
                                                <span class="text-default-500 italic">System</span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-3">
                                            @if($log->loggable)
                                                <div class="flex flex-col">
                                                    <span class="font-medium">{{ $log->loggable->getLogIdentifier() ?? $log->loggable->id }}</span>
                                                    <span class="text-xs text-default-500">{{ class_basename($log->loggable_type) }}</span>
                                                </div>
                                            @else
                                                <span class="text-default-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <span class="px-2 py-1 text-xs rounded bg-default-100 text-default-700">
                                                {{ $log->context }}
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            @if($log->response_status)
                                                <span class="px-2 py-1 text-xs rounded-full 
                                                    {{ $log->response_status >= 200 && $log->response_status < 300 ? 'bg-success/10 text-success' : '' }}
                                                    {{ $log->response_status >= 400 && $log->response_status < 500 ? 'bg-warning/10 text-warning' : '' }}
                                                    {{ $log->response_status >= 500 ? 'bg-danger/10 text-danger' : '' }}
                                                ">
                                                    {{ $log->response_status }}
                                                </span>
                                            @else
                                                <span class="text-default-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-3">
                                            @if($log->duration_ms)
                                                <span class="{{ $log->duration_ms > 1000 ? 'text-danger font-semibold' : 'text-default-600' }}">
                                                    {{ number_format($log->duration_ms, 0) }}ms
                                                </span>
                                            @else
                                                <span class="text-default-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <div class="flex flex-col">
                                                <span>{{ $log->created_at->format('M d, Y') }}</span>
                                                <span class="text-xs text-default-500">{{ $log->created_at->format('h:i A') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3.5 py-3">
                                            <a href="{{ route('activity-logs.show', $log) }}" class="btn btn-xs bg-primary/10 text-primary hover:bg-primary hover:text-white">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-3.5 py-8 text-center text-default-500">
                                            <div class="flex flex-col items-center gap-2">
                                                <i class="mgc_inbox_line text-4xl"></i>
                                                <p>No activity logs found matching your filters.</p>
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
                    Showing <b>{{ $logs->firstItem() ?? 0 }}</b> to <b>{{ $logs->lastItem() ?? 0 }}</b> of <b>{{ $logs->total() }}</b> Results
                </p>
                <nav aria-label="Pagination" class="flex items-center gap-2">
                    {{ $logs->appends(request()->query())->links() }}
                </nav>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
$(function() {
    // Initialize date range picker
    $('#daterange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear',
            format: 'YYYY-MM-DD'
        },
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

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', '1');
    window.location.href = '{{ route("activity-logs.index") }}?' + params.toString();
}
</script>
@endsection